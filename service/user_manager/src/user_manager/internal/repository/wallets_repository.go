package repository

import (
	"user-manager/internal/db"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
)

// NewWalletsRepository возвращает реализацию интерфейса WalletsRepository для работы кошельками пользователей.
func NewWalletsRepository() container.WalletsRepository {
	return &dbWalletsRepository{}
}

type WalletNotFoundError struct{}

func (wnfe *WalletNotFoundError) Error() string {
	return "Wallet not found"
}

var _ container.WalletsRepository = &dbWalletsRepository{}

type dbWalletsRepository struct{}

// GetByID находит и возвращает информацию о кошельке пользователя по его уникальному идентификатору.
// Возвращает указатель на структуру UserWallet и ошибку, если кошелек не был найден или произошел сбой запроса.
func (r *dbWalletsRepository) GetByID(id int64) (wallet *models.UserWallet, err error) {
	db := db.Instance()
	wallet = &models.UserWallet{}
	err = db.Get(wallet, `
		SELECT id, user_id, currency, active, amount, locked_money, referral_money, bonus_money, TotalBet, TotalWin, bonus_id
		FROM user_wallets
		WHERE id=?
	`, id)
	if err != nil {
		logger.Get().Error("Can't find wallet")
		return nil, err
	}
	return wallet, nil
}

// GetByUserIDCurrID получает кошелек пользователя по его уникальному идентификатору и идентификатору валюты.
// Возвращает указатель на структуру UserWallet и ошибку, если кошелек не найден или произошла ошибка запроса.
func (r *dbWalletsRepository) GetByUserIDCurrID(user_id int64, curr int8) (wallet *models.UserWallet, err error) {
	db := db.Instance()
	wallet = &models.UserWallet{}
	err = db.Get(wallet, `
		SELECT id, user_id, currency, active, amount, locked_money, referral_money, bonus_money, TotalBet, TotalWin, bonus_id
		FROM user_wallets
		WHERE user_id=?
		AND currency=?
	`, user_id, curr)
	if err != nil {
		logger.Get().Error("Can't find wallet")
		return nil, err
	}
	return wallet, nil
}

// GetByUserID получает список кошельков пользователя по его уникальному идентификатору.
// Возвращает срез структур UserWallet и ошибку, если кошельки не найдены или произошла ошибка запроса.
func (r *dbWalletsRepository) GetByUserID(user_id int64) (wallets []models.UserWallet, err error) {
	db := db.Instance()
	wallets = []models.UserWallet{}
	err = db.Select(&wallets, `
		SELECT id, user_id, currency, active, amount, locked_money, referral_money, bonus_money, TotalBet, TotalWin, bonus_id
		FROM user_wallets
		WHERE user_id=?
	`, user_id)
	if err != nil {
		logger.Get().Error("Can't find wallet")
		return nil, err
	}
	return wallets, nil
}

// Update обновляет информацию о кошельке пользователя в базе данных на основе переданной структуры UserWallet.
// Принимает указатель на UserWallet и возвращает ошибку, если обновление не удалось.
func (r *dbWalletsRepository) Update(wallet *models.UserWallet) (err error) {
	db := db.Instance()

	sql_query := `
		UPDATE user_wallets
		SET currency=?, active=?, amount=?, locked_money=?, referral_money=?, bonus_money=?, TotalBet=?, TotalWin=?, bonus_id=?
		WHERE id=?
	`
	_, err = db.Exec(sql_query, wallet.CurrencyID, wallet.Active, wallet.Amount, wallet.LockedMoney, wallet.ReferralMoney, wallet.BonusMoney, wallet.TotalBet, wallet.TotalWin, wallet.BonusID.Int64, wallet.ID)
	return err
}

// Create создает новую запись кошелька в базе данных. Принимает ID пользователя и идентификатор валюты.
// Возвращает ошибку если создание кошелька не удалось.
func (r *dbWalletsRepository) Create(user_id int64, currency int8) (err error) {
	db := db.Instance()

	sql_query := `
		INSERT INTO user_wallets (id, user_id, currency, active, amount, locked_money, referral_money, bonus_money, TotalBet, TotalWin, bonus_id)
		VALUES (NULL,?,?,1,0,0,0,0,0,0,0)
	`
	_, err = db.Exec(sql_query, user_id, currency)
	return err
}

// LoadUserCurrencies загружает список валют, используемых пользователем, из базы данных.
// Возвращает срез структур UserCurrency и ошибку, если произошла ошибка во время выполнения запроса.
func (r *dbWalletsRepository) LoadUserCurrencies() ([]models.UserCurrency, error) {
	db := db.Instance()
	currencies := []models.UserCurrency{}
	err := db.Select(&currencies, `
		SELECT id, name, game_currency, hall_id, symbol, code, is_crypto, max_auto_payout
		FROM user_currencies
	`)
	if err != nil {
		logger.Get().Error("Can't locad currencies info")
		return nil, err
	}
	return currencies, nil
}
