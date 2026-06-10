package repositories

import (
	"games_manager/internal/db"
	"log/slog"
)

type GameSegmentRepo struct{}

// GetGameSegments возвращает все сегменты игр из базы данных.
func (g GameSegmentRepo) GetGameSegments() (map[uint64][]string, error) {
	db := db.New()
	defer db.Close()
	data := make(map[uint64][]string)
	rows, err := db.Query(`
		SELECT game_configs.cid as cid, game_config_game_segment.game_segment_id as gsegm_id FROM game_configs 
		INNER JOIN game_config_game_segment on (game_config_game_segment.game_config_id = game_configs.id);
	`)
	if err != nil {
		slog.Error(err.Error())
		return nil, err
	}
	defer rows.Close()
	for rows.Next() {
		var cid string
		var gsegm_id uint64
		err := rows.Scan(&cid, &gsegm_id)
		if err != nil {
			slog.Error(err.Error())
			return nil, err
		}
		data[gsegm_id] = append(data[gsegm_id], cid)
	}
	return data, nil
}
