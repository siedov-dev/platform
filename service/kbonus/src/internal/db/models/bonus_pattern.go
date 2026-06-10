package models

import (
	"database/sql"
	"encoding/json"
)

// BonusPattern - структура, описывающая шаблон бонуса
type BonusPattern struct {
	ID                  int64          `db:"id" json:"id"`                                         // Уникальный идентификатор шаблона
	Name                string         `db:"name" json:"name"`                                     // Уникальное имя шаблона
	Description         string         `db:"description" json:"description"`                       // Описание шаблона
	DescriptionPageID   int            `db:"description_page_id" json:"description_page_id"`       // Идентификатор страницы описания
	BonusType           int            `db:"bonus_type" json:"bonus_type"`                         // Тип бонуса (см. константы в bonus.go)
	BonusParams         string         `db:"bonus_params" json:"bonus_params"`                     // Параметры бонуса в формате JSON
	OnCreateCommands    sql.NullString `db:"on_create_commands" json:"on_create_commands"`         // Команды, выполняемые при создании бонуса (не используется)
	OnActivateCommands  sql.NullString `db:"on_activate_commands" json:"on_activate_commands"`     // Команды, выполняемые при активации бонуса (не используется)
	OnDoneCommands      sql.NullString `db:"on_done_commands" json:"on_done_commands"`             // Команды, выполняемые при завершении бонуса (не используется)
	CreatedAt           sql.NullTime   `db:"created_at" json:"created_at"`                         // Дата и время создания записи
	UpdatedAt           sql.NullTime   `db:"updated_at" json:"updated_at"`                         // Дата и время последнего обновления записи
	AllowedGames        sql.NullString `db:"allowed_games" json:"allowed_games"`                   // Список разрешенных игр в формате JSON
	Title               sql.NullString `db:"title" json:"title"`                                   // Заголовок бонуса
	ImgURL              sql.NullString `db:"img_url" json:"img_url"`                               // URL изображения бонуса
	SegmentIDs          sql.NullString `db:"segment_ids" json:"segment_ids"`                       // Идентификаторы сегментов игр
	Disabled            int8           `db:"disabled" json:"disabled"`                             // Флаг, указывающий, отключен ли бонус
	WagerGamesSegmentID sql.NullInt64  `db:"wager_games_segment_id" json:"wager_games_segment_id"` // Идентификатор сегмента игр для отыгрыша
}

func (p *BonusPattern) GetBonusParams() (map[string]any, error) {
	var params map[string]any
	err := json.Unmarshal([]byte(p.BonusParams), &params)
	if err != nil {
		return nil, err
	}
	return params, nil
}
