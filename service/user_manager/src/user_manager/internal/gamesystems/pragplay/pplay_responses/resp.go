package pplayresponses

import (
	"encoding/json"
	"fmt"
	"user-manager/internal/logger"
)

// PPlayResponse - ответ от Pragplay
type PPlayResponse struct {
	Currency    string `json:"currency"`
	Cash        string `json:"cash"`
	Bonus       string `json:"bonus"`
	Error       int    `json:"error"`
	Description string `json:"description"`
}

func MakePPlayResponse(currency string, cash float64, bonus float64) PPlayResponse {
	return PPlayResponse{
		Currency:    currency,
		Cash:        fmt.Sprintf("%.2f", cash),
		Bonus:       fmt.Sprintf("%.2f", bonus),
		Error:       0,
		Description: "Success",
	}
}

// PPlayErrorResponse - ошибка от Pragplay
type PPlayErrorResponse struct {
	Error       int    `json:"error"`
	Description string `json:"description"`
}

func MakePPlayErrorResponse(code int, err string) PPlayErrorResponse {
	return PPlayErrorResponse{
		Error:       code,
		Description: err,
	}
}

// PPlayAuthResponse - ответ на авторизацию от Pragplay
type PPlayAuthResponse struct {
	UserId      string `json:"userId"`
	Currency    string `json:"currency"`
	Cash        string `json:"cash"`
	Bonus       string `json:"bonus"`
	Token       string `json:"token"`
	Error       int    `json:"error"`
	Description string `json:"description"`
}

func MakeAuthResponse(user_id, currency string, cash, bonus float64, token string) PPlayAuthResponse {
	return PPlayAuthResponse{
		UserId:      user_id,
		Currency:    currency,
		Cash:        fmt.Sprintf("%.2f", cash),
		Bonus:       fmt.Sprintf("%.2f", bonus),
		Token:       token,
		Error:       0,
		Description: "Success",
	}
}

// PPlayBetResponse - ответ на ставку от Pragplay
type PPlayBetResponse struct {
	TransactionID string `json:"transactionId"`
	Currency      string `json:"currency"`
	Cash          string `json:"cash"`
	BonusBalance  string `json:"bonus"`
	BonusBet      string `json:"usedPromo"`
	Error         int    `json:"error"`
	Description   string `json:"description"`
}

func MakeBetResponse(tr_id, currency string, cash, bonus, bonus_bet float64) PPlayBetResponse {
	return PPlayBetResponse{
		TransactionID: tr_id,
		Currency:      currency,
		Cash:          fmt.Sprintf("%.2f", cash),
		BonusBalance:  fmt.Sprintf("%.2f", bonus),
		BonusBet:      fmt.Sprintf("%.2f", bonus_bet),
		Error:         0,
		Description:   "Success",
	}
}

func (r *PPlayBetResponse) String() string {
	s, err := json.Marshal(r)
	if err != nil {
		logger.Get().Error(fmt.Errorf("bet responce marshar err: %s", err).Error())
	}
	return string(s)
}

// PPlayWinResponse - ответ на выигрыш от Pragplay
type PPlayWinResponse struct {
	TransactionID string `json:"transactionId"`
	Currency      string `json:"currency"`
	Cash          string `json:"cash"`
	BonusBalance  string `json:"bonus"`
	Error         int    `json:"error"`
	Description   string `json:"description"`
}

func MakeWinResponse(tr_id, currency string, cash, bonus float64) PPlayWinResponse {
	return PPlayWinResponse{
		TransactionID: tr_id,
		Currency:      currency,
		Cash:          fmt.Sprintf("%.2f", cash),
		BonusBalance:  fmt.Sprintf("%.2f", bonus),
		Error:         0,
		Description:   "Success",
	}
}

func (r *PPlayWinResponse) String() string {
	s, err := json.Marshal(r)
	if err != nil {
		logger.Get().Error(fmt.Errorf("win responce marshar err: %s", err).Error())
	}
	return string(s)
}

// PPlayRefundResponse - ответ на возврат от Pragplay
type PPlayRefundResponse struct {
	TransactionID string `json:"transactionId"`
}

// EndRoundResponse - ответ на завершение раунда от Pragplay
type EndRoundResponse struct {
	Cash        string `json:"cash"`
	Bonus       string `json:"bonus"`
	Error       int    `json:"error"`
	Description string `json:"description"`
}

func MakeEndRoundResponse(cash float64, bonus float64) EndRoundResponse {
	return EndRoundResponse{
		Cash:        fmt.Sprintf("%.2f", cash),
		Bonus:       fmt.Sprintf("%.2f", bonus),
		Error:       0,
		Description: "Success",
	}
}
