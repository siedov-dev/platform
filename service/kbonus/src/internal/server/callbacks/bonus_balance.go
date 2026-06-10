package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func BonusBalance(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetBonusGameRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := BonusBalanceResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	balance, err := usecases.GetBonusBalance(req.BonusID, req.WagerPrc)
	if err != nil {
		resp := BonusBalanceResponse{
			Code:  500,
			Error: "Failed to retrieve bonus balance",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := BonusBalanceResponse{
		Balance: balance,
		Code:    200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
