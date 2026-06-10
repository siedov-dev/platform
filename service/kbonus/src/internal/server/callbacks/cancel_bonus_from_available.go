package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func CancelBonusFromAvailable(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req CancelFromBonusAvailable
	if err := json.Unmarshal(msg, &req); err != nil {
		resp := BoolResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	err := usecases.CancelBonusAvailable(req.BonusID, req.UserID)
	if err != nil {
		resp := BoolResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}

	resp := BoolResponse{
		Code: 200,
		Data: true,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
