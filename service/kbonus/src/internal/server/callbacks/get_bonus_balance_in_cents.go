package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func GetBonusBalanceInCents(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetBonusRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := BonusBalanceInCentsResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	bonus, err := usecases.GetBonus(req.BonusID)
	if err != nil {
		resp := BonusBalanceInCentsResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	if bonus == nil {
		resp := BonusBalanceInCentsResponse{
			Code:  404,
			Error: "Bonus not found",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusNotFound)
		w.Write(respData)
		return
	}
	balance, err := usecases.GetBonusBalanceInCents(req.BonusID)
	if err != nil {
		resp := BonusBalanceInCentsResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := BonusBalanceInCentsResponse{
		Balance: balance,
		Code:    200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
