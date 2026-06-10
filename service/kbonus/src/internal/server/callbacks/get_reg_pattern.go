package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"
	"net/http"
)

func GetRegPattern(msg []byte, w http.ResponseWriter, r *http.Request) {
	// Парсим запрос
	var req struct {
		Currency string `json:"currency"`
		Type     string `json:"type"`
		Num      int    `json:"num"`
	}
	if err := json.Unmarshal(msg, &req); err != nil {
		logger.Get().Error("Failed to parse GetRegPattern request", "error", err)
		resp := GetPatternResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	pattern, err := usecases.GetRegPattern(req.Currency, req.Type, req.Num)
	if err != nil {
		logger.Get().Error("Failed to get registration pattern", "error", err)
		resp := GetPatternResponse{
			Code:  500,
			Error: "Failed to get registration pattern",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusInternalServerError)
		w.Write(respData)
		return
	}
	resp := GetPatternResponse{
		Attributes: *pattern,
		Code:       200,
	}
	respData, _ := json.Marshal(resp)
	w.WriteHeader(http.StatusOK)
	w.Write(respData)
}
