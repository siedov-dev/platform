package slegapi

import (
	"crypto/hmac"
	"crypto/sha1"
	"encoding/hex"
	"encoding/json"
	"fmt"
	"io"
	"kbonus/internal/config"
	"kbonus/internal/logger"
	"net/http"
	"net/url"
	"sort"
	"strconv"
	"strings"
	"time"

	"github.com/google/uuid"
)

var MerchantID string = ""
var Key string = ""
var api_url string = ""

// init инициализирует MerchantID и Key
func init() {
	MerchantID = config.Get().Slotegrator.MerchantID
	Key = config.Get().Slotegrator.Key
	api_url = config.Get().Slotegrator.ApiURL
}

// Sign подписывает запрос
func Sign(requestParams map[string]string, headers map[string]string) string {

	mergedParams := make(map[string]string)

	// join requestParams and headers
	for k, v := range requestParams {
		mergedParams[k] = v
	}
	for k, v := range headers {
		mergedParams[k] = v
	}

	// sort keys
	keys := make([]string, 0, len(mergedParams))
	for k := range mergedParams {
		keys = append(keys, k)
	}
	sort.Strings(keys)

	// create query string
	query := url.Values{}
	for _, key := range keys {
		query.Add(key, mergedParams[key])
	}
	hashString := query.Encode()

	// calc HMAC-SHA1
	h := hmac.New(sha1.New, []byte(Key))
	h.Write([]byte(hashString))
	sign := hex.EncodeToString(h.Sum(nil))

	return sign
}

// Query отправляет запрос на API
func Query(method string, params map[string]string, post bool) (string, int, error) {
	qurl := api_url + method

	urlvals := url.Values{}
	for k, v := range params {
		urlvals.Add(k, v)
	}

	if !post && len(params) > 0 {
		qurl += "?"
		qurl += urlvals.Encode()
	}

	headers := map[string]string{
		"X-Merchant-Id": MerchantID,
		"X-Timestamp":   fmt.Sprintf("%d", time.Now().Unix()),
		"X-Nonce":       uuid.New().String(),
	}

	xsign := Sign(params, headers)

	headers["X-Sign"] = xsign

	logger.Get().Info("Slegapi Query", "method", method, "url", qurl, "params", params, "headers", headers, "post", post)

	client := &http.Client{}

	if post {
		req, err := http.NewRequest(http.MethodPost, qurl, strings.NewReader(urlvals.Encode()))
		if err != nil {
			return "", 0, err
		}
		for k, v := range headers {
			req.Header.Add(k, v)
		}
		req.Header.Add("Content-Type", "application/x-www-form-urlencoded")
		resp, err := client.Do(req)
		if err != nil {
			logger.Get().Error("Sleg resp error:", "err", err.Error())
			return "", 0, err
		}
		defer resp.Body.Close()
		rs, err := io.ReadAll(resp.Body)
		if err != nil {
			logger.Get().Error("Sleg resp error:", "err", err.Error())
			return "", 0, err
		}
		logger.Get().Debug("sleg resp:", "resp", rs)
		return string(rs), resp.StatusCode, nil
	} else {
		req, err := http.NewRequest(http.MethodGet, qurl, nil)
		if err != nil {
			return "", 0, err
		}
		for k, v := range headers {
			req.Header.Add(k, v)
		}
		resp, err := client.Do(req)
		if err != nil {
			logger.Get().Error("Sleg resp error:", "err", err.Error())
			return "", 0, err
		}
		defer resp.Body.Close()
		rs, err := io.ReadAll(resp.Body)
		if err != nil {
			logger.Get().Error("Sleg resp error:", "err", err.Error())
			return "", 0, err
		}
		logger.Get().Debug("sleg resp:", "resp", rs)
		return string(rs), resp.StatusCode, nil
	}

}

// SelfValidate проверяет, что MerchantID и Key корректны
func SelfValidate() (string, int, error) {
	method := "/self-validate"
	params := map[string]string{}

	return Query(method, params, true)
}

// GetFreespinsLimits возвращает лимиты фриспинов
func GetFreespinsLimits() (string, int, error) {
	method := "/limits/freespin"
	params := map[string]string{}

	return Query(method, params, false)
}

// FreeSpinsGet возвращает информацию о фриспинах
func FreeSpinsGet(id string) (string, int, error) {
	method := "/freespins/get"
	params := map[string]string{
		"freespin_id": id,
	}

	return Query(method, params, false)
}

func FreeSpinsBets(gameId string, currency string) (freeSpinsBetsResponse, int, error) {
	var betsResponse freeSpinsBetsResponse
	method := "/freespins/bets"
	params := map[string]string{
		"game_uuid": gameId,
		"currency":  currency,
	}

	resp, statusCode, err := Query(method, params, false)
	if err != nil {
		logger.Get().Error("Error in FreeSpinsBets", "error", err)
		return betsResponse, statusCode, err
	}
	if resp == "" {
		logger.Get().Error("Empty response in FreeSpinsBets")
		return betsResponse, statusCode, fmt.Errorf("empty response in FreeSpinsBets")
	}

	err = json.Unmarshal([]byte(resp), &betsResponse)
	if err != nil {
		logger.Get().Error("Error in FreeSpinsSet Unmarshal", "error", err)
		return betsResponse, statusCode, err
	}

	return betsResponse, statusCode, nil
}

type gameBet struct {
	BetID      string `json:"bet_id"`
	BetPerLine any    `json:"bet_per_line"`
	Lines      int    `json:"lines"`
}

type totalBet struct {
	BetID  int     `json:"bet_id"`
	Amount float64 `json:"amount"`
}

type freeSpinsBetsResponse struct {
	Denimitations []any      `json:"denominations"`
	TotalBets     []totalBet `json:"total_bets"`
	GameBets      []gameBet  `json:"bets"`
}

func FreeSpinsSet(id string, game_id string, player_id string, quantity int, currency string, active_to *int, max_bet float64) (string, int, error) {
	if currency == "USDT" {
		currency = "USD"
	}
	betsValues, statusCode, err := FreeSpinsBets(game_id, currency)
	if err != nil {
		return "", statusCode, fmt.Errorf("error getting free spins bets: %w", err)
	}
	logger.Get().Info("FreeSpinsSet", "betsValues", betsValues)
	logger.Get().Info("FreeSpinsSet", "max_bet", max_bet)
	if len(betsValues.TotalBets) > 0 {
		logger.Get().Info("using total bets", "total_bets", betsValues.TotalBets)
		currBet := betsValues.TotalBets[0].BetID
		currAmount := betsValues.TotalBets[0].Amount
		for _, bet := range betsValues.TotalBets {
			if bet.Amount > currAmount && bet.Amount <= max_bet {
				currBet = bet.BetID
				currAmount = bet.Amount
			}
		}
		method := "/freespins/set"
		params := map[string]string{
			"player_id":    player_id,
			"player_name":  "player",
			"currency":     currency,
			"quantity":     fmt.Sprintf("%d", quantity),
			"valid_from":   fmt.Sprintf("%d", time.Now().Unix()),
			"freespin_id":  id,
			"game_uuid":    game_id,
			"total_bet_id": fmt.Sprintf("%d", currBet),
		}
		if active_to != nil {
			params["valid_until"] = fmt.Sprintf("%d", *active_to)
		} else {
			params["valid_until"] = fmt.Sprintf("%d", time.Now().AddDate(0, 0, 3).Unix())
		}
		return Query(method, params, true)
	}

	if len(betsValues.GameBets) == 0 || len(betsValues.Denimitations) == 0 {
		return "", 0, fmt.Errorf("no bets or denominations in response for game %s in currency %s", game_id, currency)
	}
	logger.Get().Info("using game bets", "game_bets", betsValues.GameBets)
	curr_bet := 0.0
	demon := ""
	bet_id := ""
	for _, bet := range betsValues.GameBets {
		for _, denom := range betsValues.Denimitations {
			d := 0.0
			switch v := denom.(type) {
			case float64:
				d = v
			case int:
				d = float64(v)
			case int64:
				d = float64(v)
			case string:
				d, err = strconv.ParseFloat(v, 64)
				if err != nil {
					logger.Get().Error("Error parsing denomination", "denomination", v, "error", err)
					continue
				}
			default:
				logger.Get().Error("Unknown denomination type", "type", fmt.Sprintf("%T", v))
				continue
			}
			bpl := 0.0
			switch v := bet.BetPerLine.(type) {
			case float64:
				bpl = v
			case int:
				bpl = float64(v)
			case int64:
				bpl = float64(v)
			case string:
				bpl, err = strconv.ParseFloat(v, 64)
				if err != nil {
					logger.Get().Error("Error parsing bet per line", "bet_per_line", v, "error", err)
					continue
				}
			default:
				logger.Get().Error("Unknown bet per line type", "type", fmt.Sprintf("%T", v))
				continue
			}
			bet_val := bpl * float64(bet.Lines) * d
			if curr_bet == 0 {
				curr_bet = bet_val
				demon = fmt.Sprintf("%f", d)
				bet_id = bet.BetID
			}

			if bet_val > curr_bet && bet_val <= max_bet {
				curr_bet = bet_val
				demon = fmt.Sprintf("%f", d)
				bet_id = bet.BetID
			}
		}
	}
	if curr_bet == 0 {
		return "", 0, fmt.Errorf("no valid bet found for game %s in currency %s", game_id, currency)
	}
	logger.Get().Info("Selected bet", "bet_id", bet_id, "denomination", demon, "bet_value", curr_bet)
	method := "/freespins/set"
	params := map[string]string{
		"player_id":    player_id,
		"player_name":  "player",
		"currency":     currency,
		"quantity":     fmt.Sprintf("%d", quantity),
		"valid_from":   fmt.Sprintf("%d", time.Now().Unix()),
		"freespin_id":  id,
		"game_uuid":    game_id,
		"bet_id":       bet_id,
		"denomination": demon,
	}
	if active_to != nil {
		params["valid_until"] = fmt.Sprintf("%d", *active_to)
	} else {
		params["valid_until"] = fmt.Sprintf("%d", time.Now().AddDate(0, 0, 3).Unix())
	}
	return Query(method, params, true)
}

// FreeSpinsCancel отменяет фриспины
func FreeSpinsCancel(id string) (string, int, error) {
	method := "/freespins/cancel"
	params := map[string]string{
		"freespin_id": id,
	}

	return Query(method, params, true)
}
