package stypes

import "user-manager/internal/stypes/currency"

// GameData представляет собой структуру с данными (DTO) об игре и связанной информации, включая бонусы и валюту игры.
// Используется при запуске игровой сессии.
type GameData struct {
	System            string              `json:"system"`               // System игровая система (sleg, pragplay, qt)
	GameID            string              `json:"game_id"`              // GameID уникальный идентификатор игры, используемый для определения конкретной игровой сессии.
	GameName          string              `json:"game_name"`            // GameName представляет название игры.
	Provider          string              `json:"provider"`             // Provider указывает поставщика(группу) игры (например, NetEnt, Microgaming, Pragmatic Play).
	CurrencyID        currency.CurrencyID `json:"currency"`             // CurrencyID представляет идентификатор валюты, используемый для работы с игровыми сессиями и бонусами.
	BonusID           int64               `json:"bonus_id"`             // BonusID представляет идентификатор бонуса, связанного с игровой сессией, если игра запущена для бонуса.
	TntGameID         int64               `json:"tnt_game_id"`          // TntGameID ид турнирной игры (не используется)
	ResidentTntGameId int64               `json:"resident_tnt_game_id"` // ResidentTntGameId не используется
	CashbackBonusID   int64               `json:"cashback_bonus_id"`    // CashbackBonusID ид кешбэк бонуса
	NonDepBonusID     int64               `json:"non_dep_bonus_id"`     // NonDepBonusID ид бездеп бонуса с отыгрышем на реальные средства (не используется на данный момент)
	FilthyBonusID     int64               `json:"filthy_bonus_id"`      // FilthyBonusID не используется
	LoseBonusID       int64               `json:"lose_bonus_id"`        // LoseBonusID ид бонуса, ожидающий проигрыша (не используется)
	LockedMoney       float64             `json:"locked_money"`         // Сумма заблокированных для отыгрыша денег
	WagerPrc          int16               `json:"wager_prc"`            // WagerPrc указывает процент отыгрыша для данной игры. Для некоторых игр может быть установлен уменьшенный процент отыгрыша.
}
