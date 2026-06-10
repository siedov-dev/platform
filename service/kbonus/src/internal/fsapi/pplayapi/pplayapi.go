package pplayapi

import (
	"crypto/md5"
	"encoding/hex"
	"encoding/json"
	"io"
	"kbonus/internal/config"
	"kbonus/internal/logger"
	"net/http"
	"net/url"
	"sort"
	"strconv"
	"strings"
	"time"
)

var login string = ""
var key string = ""
var apiURL string = "https://api-dk2.pragmaticplay.net/IntegrationService/v3/http"

// init инициализирует переменные login, key и apiURL
func init() {
	login = config.Get().PragPlay.Login
	key = config.Get().PragPlay.Key
}

func CreateFRB(player_id string, currency_name string, games_list string, rounds string, bonus_id string, expiration_date int64) (string, error) {
	if strings.Index(player_id, "bonus") != 0 {
		player_id = "bonus" + player_id
	}
	method := "/FreeRoundsBonusAPI/createFRB/"
	params := map[string]string{
		"playerId":       player_id,
		"currency":       currency_name,
		"gameIDList":     games_list,
		"rounds":         rounds,
		"bonusCode":      bonus_id,
		"expirationDate": strconv.FormatInt(expiration_date, 10),
		"secureLogin":    login,
	}
	return Query(method, params, true)
}

func CancelFRB(bonusID string) (string, error) {
	method := "/FreeRoundsBonusAPI/cancelFRB/"
	params := map[string]string{
		"bonusCode":   bonusID,
		"secureLogin": login,
	}
	return Query(method, params, true)
}

type VariableFRBetValue struct {
	TotalBet float64 `json:"totalBet"`
	Currency string  `json:"currency"`
}

type VariableFRGame struct {
	GameID    string               `json:"gameId"`
	BetValues []VariableFRBetValue `json:"betValues"`
}

func CreatePlayerVFRB(player_id string, currency_name string, games_list string, rounds string, bonus_id string, expiration_date int64, max_bet float64) (string, error) {
	if strings.Index(player_id, "bonus") != 0 {
		player_id = "bonus" + player_id
	}
	method := "/FreeRoundsBonusAPI/v2/bonus/player/create/"
	url_params := map[string]string{
		"playerId":       player_id,
		"currency":       currency_name,
		"rounds":         rounds,
		"bonusCode":      bonus_id,
		"expirationDate": strconv.FormatInt(expiration_date, 10),
		"secureLogin":    login,
		"startDate":      strconv.FormatInt(time.Now().Unix(), 10),
	}
	games_ids := strings.Split(games_list, ",")
	gameList := make([]VariableFRGame, 0, len(games_ids))
	for _, game_id := range games_ids {
		bets_resp, err := GetBetScales(game_id, currency_name)
		if err != nil {
			logger.Get().Error("CreatePlayerVFRB error getting bet scales for game", game_id, err)
			return "", err
		}
		bets := &BetScalesResponse{}
		err = json.Unmarshal([]byte(bets_resp), bets)
		if err != nil {
			logger.Get().Error("CreatePlayerVFRB error unmarshalling bet scales response for game", game_id, err)
			return "", err
		}
		if bets.Error != "0" {
			logger.Get().Error("CreatePlayerVFRB error in bet scales response for game", game_id, bets.Description)
			return "", err
		}
		if len(bets.GameList) == 0 {
			logger.Get().Error("CreatePlayerVFRB no game list for game", "game_id", game_id)
			return "", nil
		}
		if len(bets.GameList[0].BetScaleList) == 0 {
			logger.Get().Error("CreatePlayerVFRB no bet scale list for game", "game_id", game_id)
			return "", nil
		}
		if len(bets.GameList[0].BetScaleList[0].TotalBetScales) == 0 {
			logger.Get().Error("CreatePlayerVFRB no total bet scales for game", "game_id", game_id)
			return "", nil
		}
		game_bets := bets.GameList[0].BetScaleList[0].TotalBetScales
		curr_bet := game_bets[0]
		for _, bet := range game_bets {
			if bet > curr_bet && bet < max_bet {
				curr_bet = bet
			}
		}
		gameList = append(gameList, VariableFRGame{
			GameID: game_id,
			BetValues: []VariableFRBetValue{
				{
					TotalBet: curr_bet,
					Currency: currency_name,
				},
			},
		})

	}

	params := map[string]any{
		"gameList": gameList,
	}

	return QueryJSON(method, params, url_params, true)
}

func CancelVFRB(bonusID string) (string, error) {
	method := "/FreeRoundsBonusAPI/v2/bonus/cancel/"
	params := map[string]string{
		"bonusCode":   bonusID,
		"secureLogin": login,
	}
	return Query(method, params, true)
}

type BetScale struct {
	BetPerLineScales []float64 `json:"betPerLineScales"`
	Currency         string    `json:"currency"`
	TotalBetScales   []float64 `json:"totalBetScales"`
}

type GameBetScales struct {
	GameID       string     `json:"gameID"`
	BetScaleList []BetScale `json:"betScaleList"`
}

type BetScalesResponse struct {
	Description string          `json:"description"`
	Error       string          `json:"error"`
	GameList    []GameBetScales `json:"gameList"`
}

func GetBetScales(game_id string, currency string) (string, error) {
	method := "/CasinoGameAPI/getBetScales"
	params := map[string]string{
		"gameIDs":     game_id,
		"currencies":  currency,
		"secureLogin": login,
	}
	return Query(method, params, true)
}

func CalcHash(data map[string]string) string {
	// Удаляем "hash" если есть
	delete(data, "hash")

	// Сортируем ключи лексикографически
	keys := make([]string, 0, len(data))
	for k := range data {
		keys = append(keys, k)
	}
	sort.Strings(keys)

	// Собираем "key1=val1&key2=val2"
	var dstr []string
	for _, k := range keys {
		dstr = append(dstr, k+"="+data[k])
	}

	// md5 от строки + секрет
	hashSource := strings.Join(dstr, "&") + key
	hash := md5.Sum([]byte(hashSource))
	return hex.EncodeToString(hash[:])
}

func Query(method string, params map[string]string, post bool) (string, error) {

	// Вычисляем хеш
	params["hash"] = CalcHash(params)
	qurl := apiURL + method

	urlvals := url.Values{}
	for k, v := range params {
		urlvals.Add(k, v)
	}
	if !post && len(params) > 0 {
		qurl += "?"
		qurl += urlvals.Encode()
	}

	client := &http.Client{}

	if post {
		logger.Get().Debug("PPlay API request", "url", qurl, "params", urlvals.Encode())
		req, err := http.NewRequest(http.MethodPost, qurl, strings.NewReader(urlvals.Encode()))
		if err != nil {
			return "", err
		}
		req.Header.Add("Content-Type", "application/x-www-form-urlencoded")
		resp, err := client.Do(req)
		logger.Get().Debug("PPlay API response", "error", err, "status", resp.StatusCode)
		if err != nil {
			return "", err
		}
		defer resp.Body.Close()
		// dump, err := httputil.DumpRequest(req, true)
		// if err != nil {
		// 	logger.Get().Error("PPlay API error dumping request", "error", err)
		// } else {
		// 	logger.Get().Debug("PPlay API full request", "dump", string(dump))
		// }
		rs, err := io.ReadAll(resp.Body)
		logger.Get().Debug("PPlay API response", "response", string(rs), "error", err, "status", resp.StatusCode)
		if err != nil {
			return "", err
		}
		return string(rs), nil
	} else {
		req, err := http.NewRequest(http.MethodGet, qurl, nil)
		if err != nil {
			return "", err
		}
		resp, err := client.Do(req)
		if err != nil {
			return "", err
		}
		defer resp.Body.Close()
		rs, err := io.ReadAll(resp.Body)
		logger.Get().Debug("PPlay API response", "response", string(rs), "error", err, "status", resp.StatusCode)
		if err != nil {
			return "", err
		}
		return string(rs), nil
	}

}

func QueryJSON(method string, params map[string]any, url_params map[string]string, post bool) (string, error) {
	url_params["hash"] = CalcHash(url_params)

	qurl := apiURL + method

	// query-string из url_params
	if len(url_params) > 0 {
		urlvals := url.Values{}
		for k, v := range url_params {
			urlvals.Add(k, v)
		}
		qurl += "?" + urlvals.Encode()
	}

	payload, err := json.Marshal(params)
	if err != nil {
		return "", err
	}

	req, err := http.NewRequest(http.MethodPost, qurl, strings.NewReader(string(payload)))
	if err != nil {
		return "", err
	}
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Accept", "application/json")

	// dump, err := httputil.DumpRequest(req, true)
	// if err != nil {
	// 	logger.Get().Error("PPlay API error dumping request", "error", err)
	// } else {
	// 	logger.Get().Debug("PPlay API full request", "dump", string(dump))
	// }

	client := &http.Client{}
	logger.Get().Debug("PPlay API request", "url", qurl, "json", string(payload))
	resp, err := client.Do(req)

	if err != nil {
		return "", err
	}
	defer resp.Body.Close()

	rs, err := io.ReadAll(resp.Body)
	logger.Get().Debug("PPlay API response", "response", string(rs), "error", err, "status", resp.StatusCode)
	if err != nil {
		return "", err
	}
	return string(rs), nil
}
