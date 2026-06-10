package activators

import (
	"database/sql"
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/service/usecases"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"log/slog"
	"strconv"
	"time"
)

type WelcomeBonusActivator struct {
	bonus *models.UserBonus
}

func NewWelcomeBonusActivator(bonus *models.UserBonus) stypes.BonusActivator {
	return &WelcomeBonusActivator{bonus: bonus}
}

func (a *WelcomeBonusActivator) ActivateBonus(value float64) error {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status != stypes.STATUS_NOT_ACTIVATED && a.bonus.Status != stypes.STATUS_WAITING_FOR_AGREEMENT {
		return errors.New("bonus is not in a valid state")
	}

	currency_name, err := currencyid.CurrencyID(a.bonus.Currency).Name()
	if err != nil {
		return err
	}

	if a.getStep() < 4 {
		a.setStep(a.getStep() + 1)
		a.bonus.Status = stypes.STATUS_WAITING_FOR_PAYMENT
		pattern, err := container.GetBonusPatternsDataProvider().GetRegPattern(currency_name, "dep_bonus", a.getStep())
		if err != nil {
			return err
		}
		params, err := pattern.GetBonusParams()
		if err != nil {
			return err
		}
		currParams, ok := params["params_"+currency_name]
		if !ok {
			return errors.New("currency params not found")
		}
		if currParamsMap, ok := currParams.(map[string]any); ok {
			if min_sum, ok := currParamsMap["min_sum"]; ok {
				if min_sum_float, ok := min_sum.(float64); ok {
					a.bonus.MoneyToActivate = strconv.FormatFloat(min_sum_float, 'f', 2, 64)
				} else if min_sum_int, ok := min_sum.(int64); ok {
					a.bonus.MoneyToActivate = strconv.FormatInt(min_sum_int, 10)
				} else if min_sum_str, ok := min_sum.(string); ok {
					a.bonus.MoneyToActivate = min_sum_str
				} else {
					return errors.New("unsupported min_sum type")
				}
			}
		} else {
			return errors.New("currency params not found")
		}
	} else {
		if a.getStep() == 4 && a.bonus.Status == stypes.STATUS_WAITING_FOR_AGREEMENT {
			a.bonus.Status = stypes.STATUS_PENDING
			a.setStep(5)
			err := a.createNonDeps(4)
			if err != nil {
				return err
			}
		} else if a.getStep() == 5 && a.bonus.Status == stypes.STATUS_WAITING_FOR_AGREEMENT {
			a.bonus.Status = stypes.STATUS_DONE
			err := a.createNonDeps(5)
			if err != nil {
				return err
			}
		}
	}

	// Записываем статистику по бонусу
	now := time.Now()
	stat := models.BonusStatistic{
		BonusID:      int64(a.bonus.ID),
		BonusType:    int16(a.bonus.Type),
		PatternID:    a.bonus.PatternID.Int64,
		UserID:       int64(a.bonus.UserID),
		Action:       stypes.ACTION_ACTIVATE,
		Amount:       a.bonus.MoneyAvailable,
		Currency:     int8(a.bonus.Currency),
		StatusBefore: stypes.STATUS_WAITING_FOR_AGREEMENT,
		Status:       a.bonus.Status,
		CreatedAt:    &now,
		UpdatedAt:    &now,
	}
	container.GetBonusStatisticStorage().Add(&stat)

	return nil
}

func (a *WelcomeBonusActivator) ConfirmPayment(amount float64) (err error) {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status != stypes.STATUS_WAITING_FOR_PAYMENT {
		return errors.New("bonus is not in a valid state")
	}
	if a.bonus.MoneyToActivate == "" {
		return errors.New("bonus money to activate not found")
	}
	moneyToActivate, err := strconv.ParseFloat(a.bonus.MoneyToActivate, 64)
	if err != nil {
		return errors.New("failed to parse bonus money to activate ")
	}

	if moneyToActivate > amount {
		return errors.New("insufficient payment amount")
	}

	currency_name, err := currencyid.CurrencyID(a.bonus.Currency).Name()
	if err != nil {
		return err
	}

	now := time.Now()

	a.bonus.Status = stypes.STATUS_WAITING_FOR_AGREEMENT
	a.bonus.CompletedAt = sql.NullTime{Time: now, Valid: true}

	// Записываем статистику по бонусу
	stat := models.BonusStatistic{
		BonusID:      int64(a.bonus.ID),
		BonusType:    int16(a.bonus.Type),
		PatternID:    a.bonus.PatternID.Int64,
		UserID:       int64(a.bonus.UserID),
		Action:       stypes.ACTION_DEPOSIT,
		Amount:       amount,
		Currency:     int8(a.bonus.Currency),
		StatusBefore: stypes.STATUS_WAITING_FOR_PAYMENT,
		Status:       a.bonus.Status,
		CreatedAt:    &now,
		UpdatedAt:    &now,
	}
	container.GetBonusStatisticStorage().Add(&stat)

	if a.getStep() < 4 {
		// Получаем паттерн депозитного бонуса
		dep_bonus_ptn, err := container.GetBonusPatternsDataProvider().GetRegPattern(currency_name, "dep_bonus", a.getStep())
		if err != nil {
			return err
		}
		if dep_bonus_ptn == nil {
			return errors.New("deposit bonus pattern not found")
		}
		// Создаем бонус на основе паттерна
		dep_bonus, err := usecases.CreateBonusFromPattern(stypes.BonusPatternCreateParams{
			Currency:     a.bonus.Currency,
			UserID:       a.bonus.UserID,
			PatternID:    dep_bonus_ptn.ID,
			CreatorType:  stypes.CREATOR_BONUS_PATTERN,
			CreatorID:    dep_bonus_ptn.ID,
			Title:        dep_bonus_ptn.Title.String,
			ImgURL:       dep_bonus_ptn.ImgURL.String,
			Tmp:          false,
			AllowedGames: dep_bonus_ptn.AllowedGames.String,
		})
		if err != nil {
			return err
		}
		b_id, err := dep_bonus.Save()
		if err != nil {
			return err
		}

		container.GetBonusSegmentsStorage().AddBonusToSegmentName(b_id, fmt.Sprintf("Welcome Bonus Dep %d", a.getStep()))
		err = dep_bonus.Activate(0)
		if err != nil {
			return err
		}
		err = dep_bonus.ConfirmPayment(amount, a.bonus.Currency)
		if err != nil {
			return err
		}
		_, err = dep_bonus.Save()
		if err != nil {
			return err
		}

		// Проверяем, нужно ли выдавать фриспины
		fs_dep_sum := 0.0
		fs_dep_sum_param, ok := dep_bonus.GetJsonParam("fs_dep_sum")
		if ok {
			if v, ok := fs_dep_sum_param.(float64); ok {
				fs_dep_sum = v
			} else if v, ok := fs_dep_sum_param.(int64); ok {
				fs_dep_sum = float64(v)
			} else if v, ok := fs_dep_sum_param.(string); ok {
				fs_dep_sum, err = strconv.ParseFloat(v, 64)
				if err != nil {
					logger.Get().Error("Failed to parse fs_dep_sum", slog.String("err", err.Error()))
				}
			}
		}
		if amount >= fs_dep_sum {
			fs_pattern, err := container.GetBonusPatternsDataProvider().GetRegPattern(currency_name, "fd_fs", a.getStep())
			if err != nil {
				logger.Get().Error("Failed to get fd_fs pattern", slog.String("err", err.Error()))
				return err
			}
			fs_bonus, err := usecases.CreateBonusFromPattern(stypes.BonusPatternCreateParams{
				Currency:     a.bonus.Currency,
				UserID:       a.bonus.UserID,
				PatternID:    fs_pattern.ID,
				CreatorType:  stypes.CREATOR_BONUS_PATTERN,
				CreatorID:    fs_pattern.ID,
				Title:        fs_pattern.Title.String,
				ImgURL:       fs_pattern.ImgURL.String,
				Tmp:          false,
				AllowedGames: fs_pattern.AllowedGames.String,
			})
			fs_bonus.UserBonus.Status = stypes.STATUS_LOCKED
			if err != nil {
				return err
			}
			b_id, err := fs_bonus.Save()
			if err != nil {
				return err
			}

			container.GetBonusSegmentsStorage().AddBonusToSegmentName(b_id, fmt.Sprintf("Welcome Bonus FS %d", a.getStep()))
		}

		if a.getStep() == 3 {
			a.setStep(4)
			a.bonus.Status = stypes.STATUS_PENDING
			fs_pattern_ids, err := container.GetBonusPatternsDataProvider().GetRegFsPatternIds(currency_name)
			if err != nil {
				return err
			}
			gotFsBonusesCnt, err := usecases.GetUserBonusWithPatternsIDsCount(a.bonus.UserID, fs_pattern_ids)
			if err != nil {
				return err
			}
			if gotFsBonusesCnt < 3 {
				a.bonus.Status = stypes.ACTION_DONE
			}
		}
	}

	return nil
}

func (a *WelcomeBonusActivator) getStep() int {
	return int(a.bonus.Tries)
}

func (a *WelcomeBonusActivator) setStep(day_n int) {
	a.bonus.Tries = day_n
}

func (a *WelcomeBonusActivator) createNonDeps(dep_n int) error {
	currency_name, err := currencyid.CurrencyID(a.bonus.Currency).Name()
	if err != nil {
		return err
	}
	pattern, err := container.GetBonusPatternsDataProvider().GetRegPattern(currency_name, "fd_fs", dep_n)
	if err != nil {
		return err
	}

	bonus, err := usecases.CreateBonusFromPattern(stypes.BonusPatternCreateParams{
		Currency:     a.bonus.Currency,
		UserID:       a.bonus.UserID,
		PatternID:    pattern.ID,
		CreatorType:  stypes.CREATOR_BONUS_PATTERN,
		CreatorID:    pattern.ID,
		Title:        pattern.Title.String,
		ImgURL:       pattern.ImgURL.String,
		Tmp:          false,
		AllowedGames: pattern.AllowedGames.String,
	})
	if err != nil {
		logger.Get().Error("Failed to create non-dep bonus", slog.String("err", err.Error()))
		return err
	}
	b_id, err := bonus.Save()
	if err != nil {
		logger.Get().Error("Failed to save non-dep bonus", slog.String("err", err.Error()))
		return err
	}

	container.GetBonusSegmentsStorage().AddBonusToSegmentName(b_id, fmt.Sprintf("Welcome Bonus бездеп %d", dep_n-3))
	return nil
}

func (a *WelcomeBonusActivator) OnDeposit(amount float64) (err error) {
	return nil
}
