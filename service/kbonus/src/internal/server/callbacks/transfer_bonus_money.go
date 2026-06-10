package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func TransferBonusMoney(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetBonusRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := TransferBonusMoneyResponse{
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
		resp := TransferBonusMoneyResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	if bonus == nil {
		resp := TransferBonusMoneyResponse{
			Code:  404,
			Error: "Bonus not found",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusNotFound)
		w.Write(respData)
		return
	}
	// Переводим бонус
	amount, err := usecases.TransferBonusMoney(bonus.GetID())
	if err != nil {
		resp := TransferBonusMoneyResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := TransferBonusMoneyResponse{
		Attributes: *bonus.UserBonus,
		Meta:       bonus.GetBonusInfo(),
		Code:       200,
		Amount:     amount,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
