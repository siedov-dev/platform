package container

import (
	"kbonus/internal/db/models"
	"kbonus/internal/stypes"
)

var (
	// bonusCreatorFactory хранит фабрику создателей бонусов.
	bonusCreatorFactory BonusCreatorFactory
	// bonusHandlersFactory хранит фабрику обработчиков бонусов.
	bonusHandlersFactory BonusHandlersFactory
	// bonusActualizerFactory хранит фабрику актуализаторов бонусов.
	bonusActualizerFactory BonusActualizerFactory
	// betWagererFactory хранит фабрику обработчиков ставок для бонусов.
	betWagererFactory BetWagererFactory
	// extFsHandlersFactory хранит фабрику обработчиков внешних free spins.
	extFsHandlersFactory ExtFsHandlersFactory
	// realMoneyWalletFactory хранит фабрику реальных кошельков.
	realMoneyWalletFactory RealMoneyWalletFactory
	// bonusInfoProviderFactory хранит фабрику провайдеров информации о бонусах.
	bonusInfoProviderFactory BonusInfoProviderFactory
	// bonusActivatorFactory хранит фабрику активаторов бонусов.
	bonusActivatorFactory BonusActivatorFactory
)

type BonusCreator interface {
	CreateBonus(bonusType int8, jsonParams string, currency int8, userID stypes.UserID, keepParams bool) (*models.UserBonus, error)
}

type BonusCreatorFactory interface {
	GetBonusCreator(bonusType int8) BonusCreator
}

type BonusHandlersFactory interface {
	GetBonusHandler(ub *models.UserBonus, wagerPercent int16, userWallet stypes.RealMoneyWallet) (stypes.BonusHandler, error)
}

type BonusActualizerFactory interface {
	GetActualizer(bonus *models.UserBonus, wallet stypes.RealMoneyWallet) (stypes.BonusActualizer, error)
}

type BetWagererFactory interface {
	GetBetWagerer(bonus *models.UserBonus, wallet stypes.RealMoneyWallet) stypes.BonusBetWagerer
}

type ExtFsHandlersFactory interface {
	GetExtFsHandler(game_system string, bonus *models.UserBonus) (stypes.BonusExternalFsHandler, error)
}

type RealMoneyWalletFactory interface {
	GetMoneyWallet(userID stypes.UserID, currencyID int8) stypes.RealMoneyWallet
}

type BonusInfoProviderFactory interface {
	GetBonusInfoProvider(bonus *models.UserBonus) stypes.BonusInfoProvider
}

type BonusActivatorFactory interface {
	GetBonusActivator(bonus *models.UserBonus, wallet stypes.GameWallet) (stypes.BonusActivator, error)
}

func GetBonusCreatorFactory() BonusCreatorFactory {
	if bonusCreatorFactory == nil {
		panic("BonusCreatorFactory is not initialized")
	}
	return bonusCreatorFactory
}

func SetBonusCreatorFactory(factory BonusCreatorFactory) {
	bonusCreatorFactory = factory
}

func GetBonusHandlersFactory() BonusHandlersFactory {
	if bonusHandlersFactory == nil {
		panic("BonusHandlersFactory is not initialized")
	}
	return bonusHandlersFactory
}

func SetBonusHandlersFactory(factory BonusHandlersFactory) {
	bonusHandlersFactory = factory
}

func GetBonusActualizerFactory() BonusActualizerFactory {
	if bonusActualizerFactory == nil {
		panic("BonusActualizerFactory is not initialized")
	}
	return bonusActualizerFactory
}

func SetBonusActualizerFactory(factory BonusActualizerFactory) {
	bonusActualizerFactory = factory
}

func GetBetWagererFactory() BetWagererFactory {
	if betWagererFactory == nil {
		panic("BetWagererFactory is not initialized")
	}
	return betWagererFactory
}

func SetBetWagererFactory(factory BetWagererFactory) {
	betWagererFactory = factory
}

func GetExtFsHandlersFactory() ExtFsHandlersFactory {
	if extFsHandlersFactory == nil {
		panic("ExtFsHandlersFactory is not initialized")
	}
	return extFsHandlersFactory
}

func SetExtFsHandlersFactory(factory ExtFsHandlersFactory) {
	extFsHandlersFactory = factory
}

func GetRealMoneyWalletFactory() RealMoneyWalletFactory {
	if realMoneyWalletFactory == nil {
		panic("RealMoneyWalletFactory is not initialized")
	}
	return realMoneyWalletFactory
}

func SetRealMoneyWalletFactory(factory RealMoneyWalletFactory) {
	realMoneyWalletFactory = factory
}

func GetBonusInfoProviderFactory() BonusInfoProviderFactory {
	if bonusInfoProviderFactory == nil {
		panic("BonusInfoProviderFactory is not initialized")
	}
	return bonusInfoProviderFactory
}

func SetBonusInfoProviderFactory(factory BonusInfoProviderFactory) {
	bonusInfoProviderFactory = factory
}

func GetBonusActivatorFactory() BonusActivatorFactory {
	if bonusActivatorFactory == nil {
		panic("BonusActivatorFactory is not initialized")
	}
	return bonusActivatorFactory
}
func SetBonusActivatorFactory(factory BonusActivatorFactory) {
	bonusActivatorFactory = factory
}
