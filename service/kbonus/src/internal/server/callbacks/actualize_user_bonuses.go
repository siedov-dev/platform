package callbacks

import (
	"encoding/json"
	"fmt"
	"kbonus/internal/service/usecases"
	"net/http"
)

func ActualizeUserBonuses(msg []byte, w http.ResponseWriter, r *http.Request) {
	// Извлекаем userID из запроса
	var req UserIDRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := StatusResponse{
			Code:  400,
			Error: fmt.Sprintf("invalid request format: %s", err.Error()),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	// Актуализируем бонусы пользователя
	if err := usecases.ActualizeUserBonuses(req.UserID); err != nil {
		resp := StatusResponse{
			Code:  500,
			Error: fmt.Sprintf("failed to actualize user bonuses: %s", err.Error()),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	// Отправляем успешный ответ
	resp := StatusResponse{
		Code:  200,
		Error: "",
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
