package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func CancelBonus(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetBonusRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := GetBonusResponse{
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
	// Отменяем бонус
	if err := usecases.CancelBonus(bonus.GetID()); err != nil {
		resp := GetBonusResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
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
