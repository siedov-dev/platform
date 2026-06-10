package callbacks

import (
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func TransferAllRefAccumMoney(msg *nats.Msg) {
	err := usecases.TransferAllAccumMoney()
	if err != nil {
		logger.Get().Error("Error transferring all accumulated money", "error", err.Error())
	}
}
