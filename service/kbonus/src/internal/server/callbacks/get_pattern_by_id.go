package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"
	"net/http"
)

func GetPatternById(msg []byte, w http.ResponseWriter, r *http.Request) {
	// Парсим запрос
	var req struct {
		ID int64 `json:"id"`
	}
	if err := json.Unmarshal(msg, &req); err != nil {
		logger.Get().Error("Failed to parse GetPatternById request", "error", err)
		resp := GetPatternResponse{
			Code:  500,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusBadRequest)
		w.Write(respData)
		return
	}
	pattern, err := usecases.GetBonusPattern(req.ID)
	if err != nil {
		logger.Get().Error("Failed to get pattern", "error", err)
		resp := GetPatternResponse{
			Code:  404,
			Error: "Failed to get pattern",
		}
		respData, _ := json.Marshal(resp)
		w.WriteHeader(http.StatusNotFound)
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
