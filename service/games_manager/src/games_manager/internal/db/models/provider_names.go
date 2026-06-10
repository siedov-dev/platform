package models

type ProviderNames struct {
	ID         uint64 `json:"id" db:"id"`
	GameSystem string `json:"system" db:"system"`
	Provider   string `json:"provider" db:"group"`
	NewName    string `json:"name" db:"name"`
}
