package responses

import "games_manager/internal/games/games_t"

type GamesStatsResponse struct {
	Status int                `json:"Status"`
	Error  string             `json:"Error"`
	Stats  games_t.GamesStats `json:"Stats"`
}
