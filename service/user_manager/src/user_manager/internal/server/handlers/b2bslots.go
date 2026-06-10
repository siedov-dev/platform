package handlers

import (
	"io"
	"log/slog"
	"net/http"
	"user-manager/internal/gamesystems/b2bslots"
	"user-manager/internal/logger"
)

func B2BSlotsHandler(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error("B2BSlotsHandler: Failed to read request body", slog.String("err", err.Error()))
		http.Error(w, "Failed to read request body", http.StatusBadRequest)
		return
	}
	// обработка запроса и получение ответа
	response := b2bslots.ApiJsonRequest(string(body))
	// отправка ответа клиенту
	w.Header().Set("Content-Type", "application/json")
	w.Write([]byte(response))
}
