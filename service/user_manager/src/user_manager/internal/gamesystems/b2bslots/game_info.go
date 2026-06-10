package b2bslots

import (
	"crypto/sha1"
	"encoding/json"
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"user-manager/internal/config"
	"user-manager/internal/logger"
)

func GetGameInfo(game_id int64, currency string) (GameInfoResponse, error) {
	password := config.Get().B2BSlots.Password
	if password == "" {
		return GameInfoResponse{}, errors.New("b2bslots: operator password not set")
	}
	sha1pass := GetSHA1Hash(password)

	req := GameInfoRequest{
		API: "plugin",
		Data: GameInfoRequestData{
			Name:   "info",
			Action: "getGameSettings",
			Data: GameInfoRequestInner{
				GameID:   game_id,
				Currency: currency,
				Provider: operator_id,
				Pass:     sha1pass,
			},
		},
	}
	reqJson, err := json.Marshal(req)
	if err != nil {
		return GameInfoResponse{}, err
	}
	logger.Get().Debug("b2bslots", slog.String("method", "GetGameInfo"), slog.String("request", string(reqJson)))
	url := config.Get().B2BSlots.APIURL + ".gambleApi?" + string(reqJson)
	resp, err := http.Get(url)
	if err != nil {
		return GameInfoResponse{}, err
	}
	defer resp.Body.Close()
	if resp.StatusCode != 200 {
		logger.Get().Error("b2bslots", slog.String("method", "GetGameInfo"), slog.String("err", "request error: "+resp.Status))
		return GameInfoResponse{}, errors.New("request error: " + resp.Status)
	}
	var giResp GameInfoResponse
	err = json.NewDecoder(resp.Body).Decode(&giResp)
	if err != nil {
		return GameInfoResponse{}, err
	}
	return giResp, nil
}

func GetSHA1Hash(text string) string {
	h := sha1.New()
	h.Write([]byte(text))
	return fmt.Sprintf("%x", h.Sum(nil))
}

type GameInfoRequest struct {
	API  string              `json:"api"`
	Data GameInfoRequestData `json:"data"`
}

type GameInfoRequestData struct {
	Name   string               `json:"name"`
	Action string               `json:"action"`
	Data   GameInfoRequestInner `json:"data"`
}

type GameInfoRequestInner struct {
	GameID   int64  `json:"gameId"`
	Currency string `json:"currency"`
	Provider int64  `json:"provider"`
	Pass     string `json:"pass"`
}

type GameInfoResponse struct {
	Data    GameInfoData `json:"data"`
	Success bool         `json:"success"`
}

type GameInfoData struct {
	ID       int       `json:"id"`
	Name     string    `json:"name"`
	Group    string    `json:"group"`
	Lines    []int     `json:"lines"`
	Bets     []int     `json:"bets"`
	DNMS     []float64 `json:"dnms"`
	HardBets bool      `json:"hardBets"`
}
