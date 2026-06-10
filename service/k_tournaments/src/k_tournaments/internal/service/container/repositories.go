// Package container - сервис контейнер приложения
package container

import "ktournaments/internal/repositories"

// Репозиторий призов
var prizes repositories.TournamentPrizeRepository

// Репозиторий турниров
var tournaments repositories.TournamentRepository

// Репозиторий значений турниров
var values repositories.TournamentValueRepository

// Репозиторий логинов пользователей
var logins repositories.UserLoginsRepository

// Репозиторий логов турниров
var tournamentLogs repositories.TournamentLogRepository

// SetPrizeRepository - установка репозитория призов
func SetPrizeRepository(r repositories.TournamentPrizeRepository) {
	prizes = r
}

// GetPrizeRepository - получение репозитория призов
func GetPrizeRepository() repositories.TournamentPrizeRepository {
	return prizes
}

// SetTournamentRepository - установка репозитория турниров
func SetTournamentRepository(r repositories.TournamentRepository) {
	tournaments = r
}

// GetTournamentRepository - получение репозитория турниров
func GetTournamentRepository() repositories.TournamentRepository {
	return tournaments
}

// SetValueRepository - установка репозитория значений турниров
func SetValueRepository(r repositories.TournamentValueRepository) {
	values = r
}

// GetValueRepository - получение репозитория значений турниров
func GetValueRepository() repositories.TournamentValueRepository {
	return values
}

// SetLoginsRepository - установка репозитория логинов пользователей
func SetLoginsRepository(r repositories.UserLoginsRepository) {
	logins = r
}

// GetLoginsRepository - получение репозитория логинов пользователей
func GetLoginsRepository() repositories.UserLoginsRepository {
	return logins
}

// GetTournamentLogsRepository - получение репозитория логов турниров
func GetTournamentLogsRepository() repositories.TournamentLogRepository {
	return tournamentLogs
}

// SetTournamentLogsRepository - установка репозитория логов турниров
func SetTournamentLogsRepository(r repositories.TournamentLogRepository) {
	tournamentLogs = r
}

// SetRepositories - установка репозиториев
func SetRepositories(
	prizeRepo repositories.TournamentPrizeRepository,
	tournamentRepo repositories.TournamentRepository,
	valueRepo repositories.TournamentValueRepository,
	loginsRepo repositories.UserLoginsRepository,
	tournamentLogsRepo repositories.TournamentLogRepository,
) {
	prizes = prizeRepo
	tournaments = tournamentRepo
	values = valueRepo
	logins = loginsRepo
	tournamentLogs = tournamentLogsRepo
}
