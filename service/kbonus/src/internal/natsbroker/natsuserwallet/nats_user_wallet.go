package natsuserwallet

import (
	"encoding/json"
	"fmt"
	"kbonus/internal/logger"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"time"

	"github.com/nats-io/nats.go"
)

var _ stypes.RealMoneyWallet = (*NatsUserWallet)(nil)

type NatsUserWallet struct {
	// UserID представляет собой уникальный идентификатор пользователя в системе.
	UserID stypes.UserID
	// Balance представляет собой текущий баланс кошелька в формате float64.
	CurrencyId currencyid.CurrencyID
	// natsConnection представляет собой соединение с NATS, используемое для взаимодействия с брокером сообщений.
	natsConnection *nats.Conn
}

func NewNatsUserWallet(userID stypes.UserID, currencyId currencyid.CurrencyID, conn *nats.Conn) stypes.RealMoneyWallet {
	return &NatsUserWallet{
		UserID:         userID,
		CurrencyId:     currencyId,
		natsConnection: conn,
	}
}

func (n *NatsUserWallet) GetCurrencyName() string {
	currName, _ := n.CurrencyId.Name()
	return currName
}

func (n *NatsUserWallet) GetBalance() float64 {
	if n.UserID == 0 {
		return 0.0 // Возвращаем 0.0, если UserID не установлен
	}
	request := BalanceRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal BalanceRequest", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки сериализации
	}
	logger.Get().Info("Requesting balance", "user_id", n.UserID, "currency_id", n.CurrencyId)
	msg, err := n.natsConnection.Request("usermanager.get_balance", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to request balance", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки запроса
	}
	response := BalanceResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal BalanceResponse", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in BalanceResponse", "code", response.Code, "error", response.Error)
		return 0.0 // Возвращаем 0.0 в случае ошибки в ответе
	}
	return response.Value // Возвращаем значение баланса из ответа
}

func (n *NatsUserWallet) AddMoney(amount float64) float64 {
	if n.UserID == 0 {
		return 0.0 // Возвращаем 0.0, если UserID не установлен
	}
	request := AmountRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
		Amount:     amount,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal AmountRequest", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.add_money", jsonData, 100*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to add money", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки запроса
	}
	response := AmountResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal AmountResponse", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in AmountResponse", "code", response.Code, "error", response.Error)
		return 0.0 // Возвращаем 0.0 в случае ошибки в ответе
	}
	return response.Value // Возвращаем значение баланса из ответа
}

func (n *NatsUserWallet) RemMoney(amount float64) (float64, error) {
	if n.UserID == 0 {
		return 0.0, nil // Возвращаем 0.0, если UserID не установлен
	}

	request := AmountRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
		Amount:     amount,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal AmountRequest", "error", err)
		return 0.0, err // Возвращаем ошибку в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.rem_money", jsonData, 100*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to remove money", "error", err)
		return 0.0, err // Возвращаем ошибку в случае ошибки запроса
	}
	response := AmountResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal AmountResponse", "error", err)
		return 0.0, err // Возвращаем ошибку в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in AmountResponse", "code", response.Code, "error", response.Error)
		return 0.0, fmt.Errorf("error: %s", response.Error) // Возвращаем 0.0 в случае ошибки в ответе
	}
	return response.Value, nil // Возвращаем значение баланса из ответа
}

func (n *NatsUserWallet) GetLockedMoney() float64 {
	if n.UserID == 0 {
		return 0.0 // Возвращаем 0.0, если UserID не установлен
	}

	request := BalanceRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal BalanceRequest", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.get_locked_money", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to request locked money", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки запроса
	}
	response := BalanceResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal BalanceResponse", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in BalanceResponse", "code", response.Code, "error", response.Error)
		return 0.0 // Возвращаем 0.0 в случае ошибки в ответе
	}
	return response.Value // Возвращаем значение замороженных средств из ответа
}

func (n *NatsUserWallet) AddLockedMoney(amount float64) float64 {
	if n.UserID == 0 {
		return 0.0 // Возвращаем 0.0, если UserID не установлен
	}
	request := AmountRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
		Amount:     amount,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal AmountRequest", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.add_locked_money", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to add locked money", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки запроса
	}
	response := AmountResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal AmountResponse", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in AmountResponse", "code", response.Code, "error", response.Error)
		return 0.0 // Возвращаем 0.0 в случае ошибки в ответе
	}
	return response.Value // Возвращаем значение замороженных средств из ответа
}

func (n *NatsUserWallet) RemLockedMoney(amount float64) float64 {
	if n.UserID == 0 {
		return 0.0 // Возвращаем 0.0, если UserID не установлен
	}
	request := AmountRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
		Amount:     amount,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal AmountRequest", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.rem_locked_money", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to remove locked money", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки запроса
	}
	response := AmountResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal AmountResponse", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in AmountResponse", "code", response.Code, "error", response.Error)
		return 0.0 // Возвращаем 0.0 в случае ошибки в ответе
	}
	return response.Value // Возвращаем значение замороженных средств из ответа
}

func (n *NatsUserWallet) SetLockedMoney(amount float64) {
	if n.UserID == 0 {
		return // Возвращаем без действия, если UserID не установлен
	}
	request := AmountRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
		Amount:     amount,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal AmountRequest", "error", err)
		return // Возвращаем без действия в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.set_locked_money", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to set locked money", "error", err)
		return // Возвращаем без действия в случае ошибки запроса
	}
	response := AmountResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal AmountResponse", "error", err)
		return // Возвращаем без действия в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in AmountResponse", "code", response.Code, "error", response.Error)
		return // Возвращаем без действия в случае ошибки в ответе
	}
}

func (n *NatsUserWallet) GetTotalBet() float64 {
	if n.UserID == 0 {
		return 0.0 // Возвращаем 0.0, если UserID не установлен
	}

	request := BalanceRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal BalanceRequest", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.get_total_bet", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to request total bet", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки запроса
	}
	response := BalanceResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal BalanceResponse", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in BalanceResponse", "code", response.Code, "error", response.Error)
		return 0.0 // Возвращаем 0.0 в случае ошибки в ответе
	}
	return response.Value // Возвращаем значение общего количества ставок из ответа
}

func (n *NatsUserWallet) AddTotalBet(amount float64) {
	if n.UserID == 0 {
		return
	}

	request := AmountRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
		Amount:     amount,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal AmountRequest", "error", err)
		return // Возвращаем без действия в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.add_total_bet", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to add total bet", "error", err)
		return // Возвращаем без действия в случае ошибки запроса
	}
	response := AmountResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal AmountResponse", "error", err)
		return // Возвращаем без действия в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in AmountResponse", "code", response.Code, "error", response.Error)
		return // Возвращаем без действия в случае ошибки в ответе
	}
}

func (n *NatsUserWallet) GetTotalWin() float64 {
	if n.UserID == 0 {
		return 0.0 // Возвращаем 0.0, если UserID не установлен
	}

	request := BalanceRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal BalanceRequest", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.get_total_win", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to request total win", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки запроса
	}
	response := BalanceResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal BalanceResponse", "error", err)
		return 0.0 // Возвращаем 0.0 в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in BalanceResponse", "code", response.Code, "error", response.Error)
		return 0.0 // Возвращаем 0.0 в случае ошибки в ответе
	}
	return response.Value // Возвращаем значение общего количества выигрышей из ответа
}

func (n *NatsUserWallet) AddTotalWin(amount float64) {
	if n.UserID == 0 {
		return
	}
	request := AmountRequest{
		UserId:     n.UserID,
		CurrencyID: n.CurrencyId,
		Amount:     amount,
	}
	jsonData, err := json.Marshal(request)
	if err != nil {
		logger.Get().Error("Failed to marshal AmountRequest", "error", err)
		return // Возвращаем без действия в случае ошибки сериализации
	}
	msg, err := n.natsConnection.Request("usermanager.add_total_win", jsonData, 50*time.Millisecond)
	if err != nil {
		logger.Get().Error("Failed to add total win", "error", err)
		return // Возвращаем без действия в случае ошибки запроса
	}
	response := AmountResponse{}
	if err := json.Unmarshal(msg.Data, &response); err != nil {
		logger.Get().Error("Failed to unmarshal AmountResponse", "error", err)
		return // Возвращаем без действия в случае ошибки десериализации
	}
	if response.Code != 200 {
		logger.Get().Error("Error in AmountResponse", "code", response.Code, "error", response.Error)
		return // Возвращаем без действия в случае ошибки в ответе
	}
}
