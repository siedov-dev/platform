package realmoneywallet

import (
	"kbonus/internal/natsbroker/natsuserwallet"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
)

func NewRealMoneyWalletFactory() container.RealMoneyWalletFactory {
	return &realMoneyWalletFactory{}
}

type realMoneyWalletFactory struct{}

func (f *realMoneyWalletFactory) GetMoneyWallet(userID stypes.UserID, currencyID int8) stypes.RealMoneyWallet {
	return natsuserwallet.NewNatsUserWallet(userID, currencyid.CurrencyID(currencyID), container.GetNatsConnection())
}
