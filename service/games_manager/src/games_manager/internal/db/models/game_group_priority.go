package models

// GameGroupPriority - структура для хранения информации о приоритете групп(провайдеров) игр
type GameGroupPriority struct {
	SystemId string `db:"system_id"` // ID игровой системы
	GroupId  string `db:"group_id"`  // ID группы(провайдера) игры
	Priority int    `db:"priority"`  // приоритет группы(провайдера) игры
}
