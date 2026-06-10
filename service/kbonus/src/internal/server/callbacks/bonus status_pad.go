package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func BonusStatusPad(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetBonusRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := BonusStatusResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	sp, err := usecases.BonusStatusPad(req.BonusID)
	if err != nil {
		resp := BonusStatusResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := BonusStatusResponse{
		BonusID:   req.BonusID,
		Status:    int8(sp.BonusStatus),
		StatusPad: sp,
		Code:      200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
