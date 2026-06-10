package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func ReloadService(msg *nats.Msg) {
	// Обработчик события перезагрузки сервиса
	// msg.Data содержит данные события в формате JSON
	err := usecases.Reload()
	if err != nil {
		resp := reloadServiceResponse{
			Code:  500,
			Error: err.Error(),
		}
		jsonResp, err := json.Marshal(resp)
		if err != nil {
			logger.Get().Error("Error marshalling response", "error", err.Error())
			return
		}
		msg.Respond(jsonResp)
		return
	}
	resp := reloadServiceResponse{
		Code:  200,
		Error: "",
	}
	jsonResp, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("Error marshalling response", "error", err.Error())
		return
	}
	msg.Respond(jsonResp)
	logger.Get().Info("Service reloaded successfully")
}

type reloadServiceResponse struct {
	Code  int    `json:"code"`
	Error string `json:"error,omitempty"`
}
