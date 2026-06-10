package b2bslots

import (
	"encoding/json"
	"log/slog"
	"user-manager/internal/config"
	"user-manager/internal/logger"
)

var operator_id int64

func init() {
	operator_id = config.Get().B2BSlots.OperatorID
}

func ApiJsonRequest(jsonRequest string) string {
	logger.Get().Debug("b2bslots", "request", jsonRequest)
	req := requestMethod{}
	err := json.Unmarshal([]byte(jsonRequest), &req)
	if err != nil {
		resp := map[string]any{
			"api":     req.API,
			"success": false,
			"data":    errorResponse{ErrorCode: ErrGeneric, Message: "Invalid request format"},
		}
		respJson, _ := json.Marshal(resp)
		return string(respJson)
	}
	var resp string
	switch req.API {
	case "do-auth-user-ingame":
		resp = Auth(jsonRequest)
	case "do-debit-user-ingame":
		resp = Debit(jsonRequest)
	case "do-credit-user-ingame":
		resp = Credit(jsonRequest)
	case "do-get-features-user-ingame":
		resp = GetFeatures(jsonRequest)
	case "do-activate-features-user-ingame":
		resp = ActivateFeatures(jsonRequest)
	case "do-update-features-user-ingame":
		resp = UpdateFeatures(jsonRequest)
	case "do-end-features-user-ingame":
		resp = EndFeatures(jsonRequest)
	default:
		respData := map[string]any{
			"api":     req.API,
			"success": false,
			"data":    errorResponse{ErrorCode: ErrGeneric, Message: "Unknown API method"},
		}
		respJson, _ := json.Marshal(respData)
		logger.Get().Error("b2bslots", slog.String("method", "ApiJsonRequest"), slog.String("err", "unknown API method"), slog.String("api", req.API))
		return string(respJson)
	}
	logger.Get().Debug("b2bslots", "response", string(resp))
	return resp
}

const (
	Success          = 0 // Успешный ответ
	ErrGeneric       = 1 // Неверный формат запроса
	ErrInsufficient  = 3 // Недостаточно средств
	ErrTokenNotFound = 4 // Токен не найден
	ErrUserNotFound  = 5 // Пользователь не найден
)

type requestMethod struct {
	API string `json:"api"`
}

type errorResponse struct {
	ErrorCode int    `json:"error_code"`
	Message   string `json:"message"`
}

type GameInfo struct {
}
