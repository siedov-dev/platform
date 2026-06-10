package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func BonusActualize(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req GetBonusGameRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := BonusActualizeResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	win, err := usecases.BonusGameActualize(req.BonusID, req.WagerPrc)
	if err != nil {
		resp := BonusActualizeResponse{
			Code:  500,
			Error: "Failed to retrieve bonus win",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := BonusActualizeResponse{
		Win:  win,
		Code: 200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
