package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func BonusAddMoney(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req BonusGameMoneyRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := BonusMoneyChangeResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	balance, real_amount, bonus_amount, err := usecases.BonusAddMoney(req.BonusID, req.WagerPrc, req.Amount)
	if err != nil {
		resp := BonusMoneyChangeResponse{
			Code:  500,
			Error: "Failed to retrieve bonus balance",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := BonusMoneyChangeResponse{
		Balance:     balance,
		RealAmount:  real_amount,
		BonusAmount: bonus_amount,
		Code:        200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
