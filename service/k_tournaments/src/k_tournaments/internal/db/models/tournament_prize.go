package models

// TournamentPrizeModel - модель призов турнира
// Содержит информацию о призах турнира (суммы в реальных валютах и/или SLC item)
type TournamentPrizeModel struct {
	ID              uint64  `db:"id" json:"id"`                                       // ID приза
	GroupID         uint64  `db:"group_id" json:"group_id"`                           // ID группы призов
	Place           int     `db:"place" json:"place"`                                 // Место в призовом фонде
	Name            string  `db:"name" json:"name"`                                   // Название приза
	Img             string  `db:"img" json:"img"`                                     // Изображение приза
	Texts           string  `db:"texts" json:"texts"`                                 // Тексты приза
	CurrencyAmounts *string `db:"currency_amounts" json:"currency_amounts,omitempty"` // Суммы денежных вознаграждений в валюте
	ItemType        *int    `db:"item_type" json:"item_type,omitempty"`               // Тип итема приза
	ItemEval        float32 `db:"item_eval" json:"item_eval"`                         // Оценка итема
	Amount          int     `db:"amount" json:"amount"`                               // Количество призовых итемов
	BonusType       int     `db:"bonus_type" json:"bonus_type"`                       // Тип бонуса (если тип итема бонус)
	BonusParams     *string `db:"bonus_params" json:"bonus_params,omitempty"`         // Параметры бонуса
	BonusGames      *string `db:"bonus_games" json:"bonus_games,omitempty"`           // Список игр бонуса
	ContainerImg    string  `db:"container_img" json:"container_img"`                 // Изображение контейнера приза
	ContainerTexts  string  `db:"container_texts" json:"container_texts"`             // Тексты контейнера приза
}
