package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"net/http"
)

func ReloadBonusIfCached(msg []byte, w http.ResponseWriter, r *http.Request) {
	req := struct {
		BonusID int64 `json:"bonus_id"`
		UserID  int64 `json:"user_id"`
	}{}
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
	if req.BonusID == 0 {
		resp := GenericResponse{
			Code:  400,
			Error: "bonus_id is required",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	if err := usecases.ReloadBonusIfCached(req.BonusID, req.UserID); err != nil {
		resp := GenericResponse{
			Code:  500,
			Error: err.Error(),
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
