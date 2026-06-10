package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func HasCancelableOnPayout(msg []byte, w http.ResponseWriter, r *http.Request) {
	// Извлекаем userID из запроса
	var request UserIDRequest
	if err := json.Unmarshal(msg, &request); err != nil {
		resp := GetBonusResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	// Проверяем, есть ли у пользователя бонусы с возможностью отмены
	hasCancelable, err := usecases.HasCancelableOnPayout(request.UserID)
	if err != nil {
		resp := GetBonusResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := BoolResponse{
		Code: 200,
		Data: hasCancelable,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
