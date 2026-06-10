package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"
	"net/http"
)

func GetRegNonDepPatternIds(msg []byte, w http.ResponseWriter, r *http.Request) {
	// Парсим запрос
	req := struct {
		Currency string `json:"currency"`
	}{}
	if err := json.Unmarshal(msg, &req); err != nil {
		logger.Get().Error("Failed to parse GetRegNonDepPatternIds request", "error", err)
		resp := GetRegPatternIdsResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	ids, err := usecases.GetRegNonDepPatternIds(req.Currency)
	if err != nil {
		logger.Get().Error("Failed to get registration pattern IDs", "error", err)
		resp := GetRegPatternIdsResponse{
			Code:  500,
			Error: "Failed to get registration pattern IDs",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	resp := GetRegPatternIdsResponse{
		IDs:  ids,
		Code: 200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
