package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func DropBonusCallback(msg []byte, w http.ResponseWriter, r *http.Request) {
	var data struct {
		BonusID int64 `json:"bonus_id"`
	}
	if err := json.Unmarshal(msg, &data); err != nil {
		resp := GetBonusResponse{Error: "invalid json"}
		respBytes, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respBytes)
		return
	}
	bonus, err := usecases.DropBonus(data.BonusID)
	if err != nil {
		resp := GetBonusResponse{Error: err.Error()}
		respBytes, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respBytes)
		return
	}
	resp := GetBonusResponse{
		Attributes: *bonus.UserBonus,
		Meta:       bonus.GetBonusInfo(),
		Code:       200,
	}
	respBytes, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respBytes)
}
