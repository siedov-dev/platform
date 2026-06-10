package natsrefwallet

import (
	"encoding/json"
	"fmt"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/container"
	"time"

	"github.com/nats-io/nats.go"
)

type NatsRefWallet struct {
	natsConn *nats.Conn
}

var _ container.RefWallet = (*NatsRefWallet)(nil)

func NewNatsRefWallet(conn *nats.Conn) *NatsRefWallet {
	wallet := &NatsRefWallet{}
	// Соединение с NATS
	wallet.natsConn = conn

	// Тест соединения с NATS
	resp, err := wallet.natsConn.Request("usermanager.ping", nil, 50*time.Millisecond)
	if err != nil {
		panic(err)
	}
	if string(resp.Data) != "pong" {
		panic("nats usermanager ping failed")
	}

	return wallet
}

func (n *NatsRefWallet) ChangeRefBalance(userID uint64, amount float64, currency string) error {
	// Отправляем запрос на изменение баланса реферала
	req := changeRefMoneyRequest{
		UserID:   userID,
		Amount:   amount,
		Currency: currency,
	}
	data, err := json.Marshal(req)
	if err != nil {
		return err
	}

	resp, err := n.natsConn.Request("usermanager.change_ref_money", data, 500*time.Millisecond)
	if err != nil {
		return err
	}

	// Обрабатываем ответ от NATS
	errResp := &ErrorResponse{}
	if err := json.Unmarshal(resp.Data, errResp); err != nil {
		return fmt.Errorf("failed to unmarshal error response: %w, resp: %v", err, string(resp.Data))
	}
	if errResp.Code != 200 {
		return fmt.Errorf("error from NATS: %s", errResp.Error)
	}

	logger.Get().Info("ChangeRefBalance", "user_id", userID, "amount", amount, "currency", currency, "response", string(resp.Data))

	return nil
}

func (n *NatsRefWallet) ChangeAccumulativeBalance(userID uint64, amount float64, currency string) error {
	// Отправляем запрос на изменение накопительного баланса реферала
	req := changeAccumulationMoneyRequest{
		UserID:   userID,
		Amount:   amount,
		Currency: currency,
	}
	data, err := json.Marshal(req)
	if err != nil {
		return err
	}

	resp, err := n.natsConn.Request("usermanager.change_bonus_money", data, 500*time.Millisecond)
	if err != nil {
		return err
	}

	// Обрабатываем ответ от NATS
	errResp := &ErrorResponse{}
	if err := json.Unmarshal(resp.Data, errResp); err != nil {
		return fmt.Errorf("failed to unmarshal error response: %w, resp: %v", err, string(resp.Data))
	}
	if errResp.Code != 200 {
		return fmt.Errorf("error from NATS: %s", errResp.Error)
	}

	return nil
}

func (n *NatsRefWallet) TransferRefMoney(userID uint64, currency string) (transferred float64, err error) {
	// Отправляем запрос на перевод реферальных денег
	req := transferRefMoneyRequest{
		UserID:   userID,
		Currency: currency,
	}
	data, err := json.Marshal(req)
	if err != nil {
		return 0, err
	}

	resp, err := n.natsConn.Request("usermanager.transfer_ref_money", data, 500*time.Millisecond)
	if err != nil {
		return 0, err
	}

	// Обрабатываем ответ от NATS
	errResp := &ErrorResponse{}
	if err := json.Unmarshal(resp.Data, errResp); err != nil {
		return 0, fmt.Errorf("failed to unmarshal error response: %w, resp: %v", err, string(resp.Data))
	}
	if errResp.Code != 200 {
		return 0, fmt.Errorf("error from NATS: %s", errResp.Error)
	}

	var result AmountResponse
	if err := json.Unmarshal(resp.Data, &result); err != nil {
		return 0, fmt.Errorf("failed to unmarshal transfer result: %w", err)
	}

	transferred = result.Value
	logger.Get().Info("TransferRefMoney", "user_id", userID, "currency", currency, "amount", transferred, "transferred", transferred)

	return transferred, nil
}

func (n *NatsRefWallet) TransferAccumMoney(userID uint64, currency string) (transferred float64, err error) {
	// Отправляем запрос на перевод накопительных денег
	req := transferRefMoneyRequest{
		UserID:   userID,
		Currency: currency,
	}
	data, err := json.Marshal(req)
	if err != nil {
		return 0, err
	}

	resp, err := n.natsConn.Request("usermanager.transfer_accum_money", data, 500*time.Millisecond)
	if err != nil {
		return 0, err
	}

	// Обрабатываем ответ от NATS
	errResp := &ErrorResponse{}
	if err := json.Unmarshal(resp.Data, errResp); err != nil {
		return 0, fmt.Errorf("failed to unmarshal error response: %w, resp: %v", err, string(resp.Data))
	}
	if errResp.Code != 200 {
		return 0, fmt.Errorf("error from NATS: %s", errResp.Error)
	}

	var result AmountResponse
	if err := json.Unmarshal(resp.Data, &result); err != nil {
		return 0, fmt.Errorf("failed to unmarshal transfer result: %w", err)
	}

	transferred = result.Value
	logger.Get().Info("TransferAccumulatedMoney", "user_id", userID, "currency", currency, "amount", transferred, "transferred", transferred)
	return transferred, nil
}

func (n *NatsRefWallet) TransferAllAccumulatedMoney(userID uint64) (container.TransferAllAccumMoneyResponse, error) {
	req := TransferAllAccumMoneyRequest{
		UserID: userID,
	}
	data, err := json.Marshal(req)
	if err != nil {
		return container.TransferAllAccumMoneyResponse{}, err
	}

	resp, err := n.natsConn.Request("usermanager.transfer_all_accum_money", data, 500*time.Millisecond)
	if err != nil {
		return container.TransferAllAccumMoneyResponse{}, err
	}

	// Обрабатываем ответ от NATS
	errResp := &ErrorResponse{}
	if err := json.Unmarshal(resp.Data, errResp); err != nil {
		return container.TransferAllAccumMoneyResponse{}, fmt.Errorf("failed to unmarshal error response: %w, resp: %v", err, string(resp.Data))
	}
	if errResp.Code != 200 {
		return container.TransferAllAccumMoneyResponse{}, fmt.Errorf("error from NATS: %s", errResp.Error)
	}

	// Парсим результат
	var result container.TransferAllAccumMoneyResponse
	if err := json.Unmarshal(resp.Data, &result); err != nil {
		return container.TransferAllAccumMoneyResponse{}, fmt.Errorf("failed to unmarshal transfer result: %w", err)
	}
	return result, nil
}

type changeAccumulationMoneyRequest struct {
	UserID   uint64  `json:"user_id"`  // ID пользователя, которому добавляются реферальные средства
	Amount   float64 `json:"amount"`   // Сумма реферальных средств, которые нужно добавить/убрать (может быть отрицательной для уменьшения)
	Currency string  `json:"currency"` // Валюта, в которой указана сумма
}

type changeRefMoneyRequest struct {
	UserID   uint64  `json:"user_id"`  // ID пользователя, которому добавляются реферальные средства
	Amount   float64 `json:"amount"`   // Сумма реферальных средств, которые нужно добавить/убрать (может быть отрицательной для уменьшения)
	Currency string  `json:"currency"` // Валюта, в которой указана сумма
}

type transferRefMoneyRequest struct {
	UserID   uint64 `json:"user_id"`  // ID пользователя, которому переводятся средства
	Currency string `json:"currency"` // Валюта, в которой переводятся средства
}

type ErrorResponse struct {
	Code  int    `json:"cd"`
	Error string `json:"err"`
}

type AmountResponse struct {
	Code     int     `json:"cd"`  // Code определяет код статуса HTTP-ответа.
	Error    string  `json:"err"` // Error содержит сообщение об ошибке, если таковая имеется.
	Value    float64 `json:"v"`   // Value указывает значение суммы.
	CurrName string  `json:"cn"`  // CurrName обозначает название валюты.
	CurrId   int8    `json:"ci"`  // CurrId идентифицирует код валюты в виде числа.
}

type TransferAllAccumMoneyRequest struct {
	UserID uint64 `json:"user_id"` // ID пользователя, которому переводятся все накопленные средства
}
