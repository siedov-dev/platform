package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func CancelBonusesOnPayout(msg []byte, w http.ResponseWriter, r *http.Request) {
	var req UserIDRequest
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
	usecases.CancelBonusesOnPayout(req.UserID)
	resp := GenericResponse{
		Code: 200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
