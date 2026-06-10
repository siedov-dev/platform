package service

import (
	"context"
	"encoding/json"
	"errors"
	"fmt"
	"kreferrals/internal/db/models"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/container"
	"strconv"
	"sync"
	"time"
)

var (
	main_config         *RefConfig               // Основная конфигурация рефералов
	active_promo_config *RefConfig               // Активная промо-конфигурация рефералов
	active_promo        *models.ReferralConfig   // Активная промо-конфигурация рефералов, для проверки дат
	promo_configs       []*models.ReferralConfig // Список всех ожидающих промо-конфигураций рефералов
	user_configs        map[uint64]*RefConfig    // Конфигурации рефералов для пользователей, ключ - ID пользователя
	mx                  sync.RWMutex
	ctx                 context.Context
	ticker              *time.Ticker                  // Тикер для периодической проверки и обновления конфигураций
	ach_rewards         map[uint64]map[string]float64 // Карта наград за достижения, ключ - ID достижения, значение - карта валют и сумм
)

func MustInit(context context.Context) {
	ctx = context
	err := LoadConfigs()
	if err != nil {
		panic("Failed to load referral configs: " + err.Error())
	}
	// Запускаем тикер для периодической проверки и обновления конфигураций
	ticker = time.NewTicker(1 * time.Minute)
	go func() {
		for {
			select {
			case <-ticker.C:
				// Периодически обновляем конфигурации рефералов
				err := updateConfigs()
				if err != nil {
					// Логируем ошибку, но продолжаем работу
					logger.Get().Error("Failed to update referral configs", "error", err.Error())
				}
				logger.DailyLog.Flush()
			case <-ctx.Done():
				ticker.Stop()
				return
			}
		}
	}()
}

// loadConfigs загружает конфигурации рефералов из базы данных.
func LoadConfigs() error {
	mx.Lock()
	defer mx.Unlock()

	// Получаем основную конфигурацию рефералов
	m_config, err := container.GetReferralConfigRepository().GetReferralMainConfig()
	if err != nil {
		return err
	}
	main_config, err = NewRefConfig(m_config)
	if err != nil {
		return err
	}

	// Получаем конфигурации рефералов для пользователей
	user_confs, err := container.GetReferralConfigRepository().GetReferralUsersConfigs()
	if err != nil {
		return err
	}
	user_configs = make(map[uint64]*RefConfig, len(user_confs))
	for _, conf := range user_confs {
		uconf, err := NewRefConfig(conf)
		if err != nil {
			return err
		}
		user_configs[conf.UserID] = uconf
	}

	// Получаем промо-конфигурации рефералов
	promo_configs, err = container.GetReferralConfigRepository().GetReferralActivePromoConfigs()
	if err != nil {
		return err
	}

	active_promo = nil
	active_promo_config = nil

	// Проверяем, есть ли активные промо-конфигурации
	for i, conf := range promo_configs {
		if conf.StartDate.Before(time.Now()) && conf.EndDate.After(time.Now()) {
			// Если есть активная промо-конфигурация, загружаем ее
			active_promo_config, err = NewRefConfig(conf)
			active_promo = conf
			if err != nil {
				return err
			}
			promo_configs = append(promo_configs[:i], promo_configs[i+1:]...) // Удаляем активную конфигурацию из списка ожидающих
			break
		}
	}

	err = loadAchievementRewards()
	if err != nil {
		return fmt.Errorf("failed to load achievement rewards: %w", err)
	}

	return nil
}

func loadAchievementRewards() error {
	// Получаем награды за достижения
	rewards, err := container.GetAchievementRewardsRepository().GetAchievementRewards()
	if err != nil {
		panic("Failed to load achievement rewards: " + err.Error())
	}
	ach_rewards = make(map[uint64]map[string]float64, len(rewards))
	for _, reward := range rewards {
		ach_rewards[reward.AchievementID] = make(map[string]float64)
		decodedSums := make(map[string]any)
		if err := json.Unmarshal([]byte(reward.RewardSums), &decodedSums); err != nil {
			return err
		}
		for currency, sum := range decodedSums {
			if value, ok := sum.(float64); ok {
				ach_rewards[reward.AchievementID][currency] = value
			} else if value, ok := sum.(int); ok {
				ach_rewards[reward.AchievementID][currency] = float64(value)
			} else if value, ok := sum.(string); ok {
				// Попытка преобразовать строку в float64
				if floatValue, err := strconv.ParseFloat(value, 64); err == nil {
					ach_rewards[reward.AchievementID][currency] = floatValue
				} else {
					return fmt.Errorf("invalid FD sum for currency %s: %s", currency, value)
				}
			} else {
				return errors.New("invalid type for FD sum: " + fmt.Sprintf("%T", sum))
			}
		}
	}

	return nil
}

func updateConfigs() error {
	mx.Lock()
	defer mx.Unlock()

	if active_promo != nil && active_promo.EndDate.Before(time.Now()) {
		// Если активная промо-конфигурация закончилась, сбрасываем ее
		active_promo_config = nil
		active_promo = nil
	} else {
		// Проверяем, есть ли активные промо-конфигурации
		for i, conf := range promo_configs {
			if conf.StartDate.Before(time.Now()) && conf.EndDate.After(time.Now()) {
				var err error
				// Если есть активная промо-конфигурация, загружаем ее
				active_promo_config, err = NewRefConfig(conf)
				active_promo = conf
				if err != nil {
					return err
				}
				promo_configs = append(promo_configs[:i], promo_configs[i+1:]...) // Удаляем активную конфигурацию из списка ожидающих
				break
			}
		}
	}

	return nil
}

// GetUserIDByRefID возвращает ID пользователя по реферальному ид (ref_id).
func GetUserIDByRefID(ref_id uint64) (uint64, error) {
	//todo: добавить кэширование
	referral, err := container.GetUserReferralRepository().GetUserReferralByID(ref_id)
	if err != nil {
		return 0, err
	}
	if referral == nil {
		return 0, nil // Реферальный код не найден
	}
	if referral.UserID == nil {
		return 0, nil // Пользователь не найден
	}
	return *referral.UserID, nil
}

// GetUserConfig возвращает конфигурацию рефералов для пользователя по его ID.
func GetUserConfig(userID uint64) *RefConfig {
	mx.RLock()
	defer mx.RUnlock()

	if active_promo_config != nil {
		return active_promo_config
	}

	if user_config, ok := user_configs[userID]; ok {
		return user_config
	}

	return main_config
}

// GetAchievementReward возвращает награду за достижение по ID достижения и валюте.
func GetAchievementReward(ach_id uint64, currency string) float64 {
	mx.RLock()
	defer mx.RUnlock()

	if rewards, ok := ach_rewards[ach_id]; ok {
		if amount, ok := rewards[currency]; ok {
			return amount
		}
	}

	return 0.0 // Если награда не найдена, возвращаем 0
}

func GetAchievementRewards() map[uint64]map[string]float64 {
	mx.RLock()
	defer mx.RUnlock()

	return ach_rewards
}

func Stop() {
	mx.Lock()
	defer mx.Unlock()

	if ticker != nil {
		ticker.Stop()
	}

	ctx = nil
	main_config = nil
	active_promo_config = nil
	active_promo = nil
	promo_configs = nil
	user_configs = nil
}
