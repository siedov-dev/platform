package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"
	"strconv"

	"github.com/nats-io/nats.go"
)

// SlcItem - структура призового итема
type SlcItem struct {
	ItemType    int     `json:"item_type"`    // Тип призового итема
	Amount      int     `json:"amount"`       // Количество призового итема (например ключей от сундука или ядер)
	BonusType   int     `json:"bonus_type"`   // Тип бонуса (если итем бонус)
	BonusParams *string `json:"bonus_params"` // Параметры бонуса (если итем бонус)
	BonusGames  *string `json:"bonus_games"`  // Игры для бонуса (если итем бонус)
	Img         string  `json:"img"`          // Изображение призового итема
	Texts       string  `json:"texts"`        // Описания призового итема (например название бонуса)
	ItemEval    float32 `json:"item_eval"`    // Оценка итема (если есть)
	// ItemEval используется для оценки итема, если он есть
	// Например, для оценки сундука или другого предмета
	// Если итем не имеет оценки, то это поле будет равно 0
	// Это поле может быть использовано для определения ценности итема в контексте турнира
}

// Prize - структура приза за определенное место
type Prize struct {
	MoneyAmount float64  `json:"money_amount"`   // Сумма денежного вознаграждения
	Item        *SlcItem `json:"item,omitempty"` // Призовой итем
	Img         string   `json:"img"`            // Изображение призового итема
	Texts       string   `json:"texts"`          // Описания призового итема
}

// Prizes - мапа призовых вознаграждений
// Ключ - место в таблице призов
// Значение - призовое вознаграждение
type Prizes map[int]Prize

// GetPrizes - получение таблицы призов для указанной валюты
func GetPrizes(msg *nats.Msg) {
	// Парсинг запроса
	req := natsrequests.TournamentWithCurrencyRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()

	// Получаем турнир по ID
	t, ok := service.Tournaments[req.TournamentId]
	if !ok {
		resp, err := json.Marshal(natsrequests.ErrorResponse{
			Error: "Tournament not found",
		})
		if err != nil {
			logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
			msg.Respond([]byte("Error marshalling response"))
			return
		}
		msg.Respond([]byte(resp))
		return
	}

	// Формируем ответ
	prizes_resp := make(Prizes)
	for _, prize := range t.Prizes {
		// Формируем призовое вознаграждение
		prize_item := Prize{
			Img:   prize.ContainerImg,
			Texts: prize.ContainerTexts,
		}
		// Если есть суммы денежного вознаграждения, то парсим их
		if prize.CurrencyAmounts != nil {
			var money_values = make(map[string]any)
			// Парсим JSON
			err = json.Unmarshal([]byte(*prize.CurrencyAmounts), &money_values)
			if err != nil {
				logger.Get().Error("Error parsing params", slog.String("error", err.Error()))
			} else {
				// Если сумма денежного вознаграждения указана в формате float64, то используем ее
				if val, ok := (money_values[req.Currency]).(float64); ok {
					prize_item.MoneyAmount = val
				}
				// Если сумма денежного вознаграждения указана в формате string, то парсим ее
				if val, ok := (money_values[req.Currency]).(string); ok {
					money, err := strconv.ParseFloat(val, 64)
					if err != nil {
						logger.Get().Error("Error parsing currency amount", slog.String("error", err.Error()))
					}
					prize_item.MoneyAmount = money
				}
			}
		}
		// Если есть призовой итем, то формируем его
		if prize.ItemType != nil {
			prize_item.Item = &SlcItem{
				ItemType:    *prize.ItemType,
				Amount:      prize.Amount,
				BonusType:   prize.BonusType,
				BonusParams: prize.BonusParams,
				BonusGames:  prize.BonusGames,
				Img:         prize.Img,
				Texts:       prize.Texts,
				ItemEval:    prize.ItemEval,
			}
		}
		// Добавляем призовое вознаграждение в ответ
		prizes_resp[prize.Place] = prize_item
	}

	// Формируем ответ
	resp, err := json.Marshal(prizes_resp)
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	// Отправляем ответ
	msg.Respond([]byte(resp))
}
