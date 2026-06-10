package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func GetFirstNotActiveBonus(msg []byte, w http.ResponseWriter, r *http.Request) {
	// Извлекаем userID и currency из запроса
	var request UserIDCurrencyIDRequest
	if err := json.Unmarshal(msg, &request); err != nil {
		w.WriteHeader(http.StatusBadRequest)
		w.Write([]byte("invalid request format"))
		return
	}

	// Получаем первый неактивированный бонус пользователя
	bonus, err := usecases.GetFirstNotActiveBonus(request.UserID, request.CurrencyID)
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
	if bonus == nil {
		resp := GetBonusResponse{
			Code:  404,
			Error: "Bonus not found",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusNotFound)
		w.Write(respData)
		return
	}

	resp := GetBonusResponse{
		Attributes: *bonus.UserBonus,
		Meta:       bonus.GetBonusInfo(),
		Code:       200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
