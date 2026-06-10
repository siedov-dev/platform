package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func BonusSpinWin(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req BonusGameMoneyRequest
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := GenericResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}

	err := usecases.BonusOnWinSpin(req.BonusID, req.WagerPrc, req.Amount)
	if err != nil {
		resp := GenericResponse{
			Code:  500,
			Error: "Failed to retrieve bonus balance",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := GenericResponse{
		Code: 200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
