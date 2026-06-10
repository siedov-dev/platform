// Пакет restbonus реализует бизнес-логику работы с бонусами пользователя.
package restbonus

import (
	"bytes"
	"encoding/json"
	"errors"
	"io"
	"log/slog"
	"net/http"
	"strconv"
	"user-manager/internal/config"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
)

var _ stypes.IBonus = (*Bonus)(nil)

// Bonus — основная структура для работы с бонусом пользователя.
// Содержит данные о бонусе, обработчики и вспомогательные объекты.
type Bonus struct {
	UserBonus   *models.UserBonus      // Модель бонуса пользователя из БД
	GWallet     stypes.RealMoneyWallet // Кошелек пользователя для реальных денег
	WagerPrc    int16                  // Процент вейджера
	BackupState models.UserBonus       // Резервная копия состояния бонуса
	BetTrID     string                 // Идентификатор транзакции ставки
	game_system string                 // Игровая система, для которой используются внешние спины
}

func (b *Bonus) GetID() int64 {
	return int64(b.UserBonus.ID)
}

// IsActive проверяет, активен ли бонус.
func (b *Bonus) IsActive() bool {
	return b.UserBonus.Status == stypes.STATUS_ACTIVE
}

// GetStatusPad возвращает статус бонуса для отображения.
func (b *Bonus) GetStatusPad() stypes.BonusStatusPad {
	return stypes.BonusStatusPad{}
}

// GetExtFsHandler возвращает обработчик внешних free spins для указанной игровой системы.
func (b *Bonus) GetExtFsHandler(game_system string) (stypes.BonusExternalFsHandler, error) {
	b.game_system = game_system
	return b, nil
}

func (b *Bonus) GetCurrencyName() string {
	n, err := currency.CurrencyID(b.UserBonus.Currency).Name()
	if err != nil {
		return ""
	}
	return n
}
func (b *Bonus) GetBalance() float64 {
	resp, err := b.request("bonusBalance", map[string]any{
		"bonus_id": b.UserBonus.ID,
	})
	if err != nil {
		return 0
	}
	var balanceResp BonusBalanceResponse
	if err := json.Unmarshal(resp, &balanceResp); err != nil {
		return 0
	}
	if balanceResp.Code != 200 {
		return 0
	}
	return balanceResp.Balance
}
func (b *Bonus) AddMoney(amount float64) (float64, float64, float64) {
	req := BonusGameMoneyRequest{
		BonusID:  int64(b.UserBonus.ID),
		WagerPrc: b.WagerPrc,
		Amount:   amount,
	}
	resp, err := b.request("bonusAddMoney", req)
	if err != nil {
		return 0, 0, 0
	}
	var moneyResp BonusMoneyChangeResponse
	if err := json.Unmarshal(resp, &moneyResp); err != nil {
		return 0, 0, 0
	}
	if moneyResp.Code != 200 {
		return 0, 0, 0
	}
	return moneyResp.Balance, moneyResp.RealAmount, moneyResp.BonusAmount
}

func (b *Bonus) RemMoney(amount float64) (float64, float64, float64, error) {
	req := BonusGameMoneyRequest{
		BonusID:  int64(b.UserBonus.ID),
		WagerPrc: b.WagerPrc,
		Amount:   amount,
	}
	resp, err := b.request("bonusRemMoney", req)
	if err != nil {
		return 0, 0, 0, err
	}
	var moneyResp BonusMoneyChangeResponse
	if err := json.Unmarshal(resp, &moneyResp); err != nil {
		return 0, 0, 0, err
	}
	if moneyResp.Code != 200 {
		return 0, 0, 0, errors.New(moneyResp.Error)
	}
	return moneyResp.Balance, moneyResp.RealAmount, moneyResp.BonusAmount, nil
}

func (b *Bonus) GetSpinsAvailable() int {
	return b.UserBonus.SpinsAvailable
}

func (b *Bonus) GetSpinsTotal() int {
	return b.UserBonus.SpinsTotal
}

func (b *Bonus) GetSpinsMade() int {
	return b.UserBonus.SpinsTotal - b.UserBonus.SpinsAvailable
}

// Save сохраняет текущее состояние бонуса в репозитории.
func (b *Bonus) Save() error {
	return nil
	// return container.GetUserBonusRepository().Update(b.UserBonus)
}

// Actualize актуализирует состояние бонуса.
// Возвращает сумму выигрыша при завешении бонуса.
func (b *Bonus) Actualize() (win float64) {
	resp, err := b.request("bonusActualize", map[string]any{
		"bonus_id": b.UserBonus.ID,
	})
	if err != nil {
		return 0
	}
	var actResp BonusActualizeResponse
	if err := json.Unmarshal(resp, &actResp); err != nil {
		return 0
	}
	if actResp.Code != 200 {
		return 0
	}
	return actResp.Win
}

func (b *Bonus) OnUserWin(amount float64) {
	req := BonusGameMoneyRequest{
		BonusID:  int64(b.UserBonus.ID),
		WagerPrc: b.WagerPrc,
		Amount:   amount,
	}
	_, err := b.request("bonusSpinWin", req)
	if err != nil {
		logger.Get().Error("bonus OnUserWin error: " + err.Error())
	}
}
func (b *Bonus) OnUserBet(amount float64) {
	req := BonusGameMoneyRequest{
		BonusID:  int64(b.UserBonus.ID),
		WagerPrc: b.WagerPrc,
		Amount:   amount,
	}
	_, err := b.request("bonusSpinBet", req)
	if err != nil {
		logger.Get().Error("bonus OnUserBet error: " + err.Error())
	}
}

func (b *Bonus) GetUserBonus() *models.UserBonus {
	return b.UserBonus
}
func (b *Bonus) SetBetTransactionID(tr_id string) {
	b.BetTrID = tr_id
}
func (b *Bonus) GetBetTransactionID() string {
	return b.BetTrID
}
func (b *Bonus) GetBackupState() *models.UserBonus {
	return &b.BackupState
}
func (b *Bonus) SetBackupState(ub *models.UserBonus) {
	if ub != nil {
		b.BackupState = *ub
	}
}
func (b *Bonus) RestoreState() {
	*b.UserBonus = b.BackupState
	container.GetUserBonusRepository().Update(b.UserBonus)
}

func (b *Bonus) request(method string, payload any) ([]byte, error) {
	url := config.Get().KBonus.APIURL
	token := config.Get().KBonus.Token
	jsonPayload, _ := json.Marshal(payload)
	logger.Get().Debug("bonus request", slog.String("method", method), slog.String("payload", string(jsonPayload)))
	req, err := http.NewRequest(http.MethodPost, url+"/api/"+method, bytes.NewReader(jsonPayload))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Authorization", "Bearer "+token) // токен
	req.Header.Set("Content-Type", "application/json")

	resp, err := http.DefaultClient.Do(req)
	if err != nil {
		return nil, err
	}
	if resp.StatusCode != 200 {
		logger.Get().Error("bonus request error: " + resp.Status)
		return nil, errors.New("request error: " + resp.Status)
	}
	body, err := io.ReadAll(resp.Body)
	defer resp.Body.Close()
	if err != nil {
		return nil, err
	}
	logger.Get().Debug("bonus response", slog.String("method", method), slog.String("response", string(body)))
	return body, nil
}

func (b *Bonus) ExternFreeSpinBet(amount float64) (float64, error) {
	req := BonusGameExtFsMoneyRequest{
		BonusID:  int64(b.UserBonus.ID),
		WagerPrc: b.WagerPrc,
		Amount:   amount,
		Provider: b.game_system,
	}
	resp, err := b.request("bonusExtFsBet", req)
	if err != nil {
		return 0, err
	}
	var fsResp BonusBalanceResponse
	if err := json.Unmarshal(resp, &fsResp); err != nil {
		return 0, err
	}
	if fsResp.Code != 200 {
		return 0, errors.New(fsResp.Error)
	}
	return fsResp.Balance, nil
}

// ExternFreeSpinWin вызывается при выигрыше во free spin.
// Увеличивает сумму доступных средств и общий выигрыш по бонусу.
func (b *Bonus) ExternFreeSpinWin(amount float64) (float64, error) {
	req := BonusGameExtFsMoneyRequest{
		BonusID:  int64(b.UserBonus.ID),
		WagerPrc: b.WagerPrc,
		Amount:   amount,
		Provider: b.game_system,
	}
	resp, err := b.request("bonusExtFsWin", req)
	if err != nil {
		return 0, err
	}
	var fsResp BonusBalanceResponse
	if err := json.Unmarshal(resp, &fsResp); err != nil {
		return 0, err
	}
	if fsResp.Code != 200 {
		return 0, errors.New(fsResp.Error)
	}
	return fsResp.Balance, nil
}

// ExternFreeSpinsCompleted вызывается при завершении всех free spins.
// Обновляет состояние бонуса, пересчитывает wager, снимает ограничения по играм и сохраняет статистику.
func (b *Bonus) ExternFreeSpinsCompleted(amount float64) (float64, error) {
	req := BonusGameExtFsMoneyRequest{
		BonusID:  int64(b.UserBonus.ID),
		WagerPrc: b.WagerPrc,
		Amount:   amount,
		Provider: b.game_system,
	}
	resp, err := b.request("bonusExtFsCompleted", req)
	if err != nil {
		return 0, err
	}
	var fsResp BonusBalanceResponse
	if err := json.Unmarshal(resp, &fsResp); err != nil {
		return 0, err
	}
	if fsResp.Code != 200 {
		return 0, errors.New(fsResp.Error)
	}
	return fsResp.Balance, nil
}

func (b *Bonus) GetFsMaxBet() (float64, error) {
	maxBet := 0.0
	jsonParams := make(map[string]any)
	if err := json.Unmarshal([]byte(b.UserBonus.JSONParams.String), &jsonParams); err != nil {
		return 0, err
	}
	if val, ok := jsonParams["max_bet"]; ok {
		switch mb := val.(type) {
		case float64:
			maxBet = mb
		case int:
			maxBet = float64(mb)
		case string:
			m, err := strconv.ParseFloat(mb, 64)
			if err != nil {
				return 0, errors.New("invalid max_bet value")
			}
			maxBet = m
		default:
			return 0, errors.New("invalid max_bet value")
		}
	}
	return maxBet, nil
}

type BonusDataRequest struct {
	Attributes models.UserBonus `json:"attributes"`
}

type GetBonusGameRequest struct {
	BonusID  int64 `json:"bonus_id"`
	WagerPrc int16 `json:"wager_prc"`
}

type BonusGameMoneyRequest struct {
	BonusID  int64   `json:"bonus_id"`
	WagerPrc int16   `json:"wager_prc"`
	Amount   float64 `json:"amount"`
}

type BonusGameExtFsMoneyRequest struct {
	BonusID  int64   `json:"bonus_id"`
	WagerPrc int16   `json:"wager_prc"`
	Amount   float64 `json:"amount"`
	Provider string  `json:"provider"`
}

type BonusBalanceResponse struct {
	Balance float64 `json:"balance"`
	Code    int     `json:"cd"`
	Error   string  `json:"err,omitempty"`
}

type BonusMoneyChangeResponse struct {
	Balance     float64 `json:"balance"`
	RealAmount  float64 `json:"real_amount"`
	BonusAmount float64 `json:"bonus_amount"`
	Code        int     `json:"cd"`
	Error       string  `json:"err,omitempty"`
}

type BonusActualizeResponse struct {
	Win   float64 `json:"win"`
	Code  int     `json:"cd"`
	Error string  `json:"err,omitempty"`
}
