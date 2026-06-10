package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"ktournaments/internal/service/tournament"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// ActiveTournaments - обработчик запроса на получение активных турниров.
func ActiveTournaments(msg *nats.Msg) {
	req := natsrequests.ActiveTournamentsRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}
	// Создаем мапу для хранения турниров
	at := make(map[uint64]*tournament.Tournament)
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()
	// Проходим по всем активным турнирам
	for _, t := range service.ActiveTournaments {
		if req.CurrencyID != 0 && (t.Model.CurrencyID != 0 && t.Model.CurrencyID != req.CurrencyID) {
			continue
		}
		if t.Model.IsTest && !req.Test {
			continue
		}
		at[t.Model.ID] = t
	}
	// Сериализуем мапу в JSON
	resp, err := json.Marshal(at)
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	msg.Respond([]byte(resp))
}

// PendingTournaments - обработчик запроса на получение ожидающих наград турниров.
func PendingTournaments(msg *nats.Msg) {
	req := natsrequests.ActiveTournamentsRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}
	// Создаем мапу для хранения турниров
	at := make(map[uint64]*tournament.Tournament)
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()
	// Проходим по всем ожидающим наград турнирам
	for _, t := range service.PendingTournaments {
		if req.CurrencyID != 0 && (t.Model.CurrencyID != 0 && t.Model.CurrencyID != req.CurrencyID) {
			continue
		}
		if t.Model.IsTest && !req.Test {
			continue
		}
		at[t.Model.ID] = t
	}
	// Сериализуем мапу в JSON
	resp, err := json.Marshal(at)
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	msg.Respond([]byte(resp))
}

// CompletedTournaments - обработчик запроса на получение завершенных турниров.
func CompletedTournaments(msg *nats.Msg) {
	req := natsrequests.ActiveTournamentsRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}
	// Создаем мапу для хранения турниров
	at := make(map[uint64]*tournament.Tournament)
	// Блокируем доступ к мапе турниров
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()
	// Проходим по всем завершенным турнирам
	for _, t := range service.CompletedTournaments {
		if req.CurrencyID != 0 && (t.Model.CurrencyID != 0 && t.Model.CurrencyID != req.CurrencyID) {
			continue
		}
		if t.Model.IsTest && !req.Test {
			continue
		}
		at[t.Model.ID] = t
	}
	// Сериализуем мапу в JSON
	resp, err := json.Marshal(at)
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	msg.Respond([]byte(resp))
}

// PendingAwardTournaments - обработчик запроса на получение ожидающих наград турниров.
func PendingAwardTournaments(msg *nats.Msg) {
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()

	at := make(map[uint64]*tournament.Tournament)

	// Проходим по всем ожидающим наград турнирам
	for _, t := range service.PendingAwardTournaments {
		at[t.Model.ID] = t
	}
	// Сериализуем мапу в JSON
	resp, err := json.Marshal(at)
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	msg.Respond([]byte(resp))
}
