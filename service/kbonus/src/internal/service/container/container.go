package container

import (
	"github.com/nats-io/nats.go"
)

var natsConnection *nats.Conn

func SetNatsConnection(conn *nats.Conn) {
	natsConnection = conn
}
func GetNatsConnection() *nats.Conn {
	if natsConnection == nil {
		panic("NATS connection is not initialized")
	}
	return natsConnection
}

// SetDataProviders инициализирует провайдеры данных, которые будут использоваться в сервисе.
// Принимает интерфейсы провайдеров данных, которые будут зарегистрированы в контейнере зависимостей.
func SetDataProviders(
	bonusParamsDataProviderImpl BonusParamsDataProvider,
	bonusCurrencyConfigDataProviderImpl CurrencyConfigDataProvider,
	bonusMultiDaysParamsDataProviderImpl MultiDaysBonusPramsDataProvider,
	bonusPatternsDataProviderImpl BonusPatternsDataProvider,
) {
	SetBonusParamsDataProvider(bonusParamsDataProviderImpl)
	SetBonusCurrencyConfigDataProvider(bonusCurrencyConfigDataProviderImpl)
	SetMultiDaysBonusPramsDataProvider(bonusMultiDaysParamsDataProviderImpl)
	SetBonusPatternsDataProvider(bonusPatternsDataProviderImpl)
}

// SetRepositories инициализирует репозитории, которые будут использоваться в сервисе.
// Принимает интерфейсы репозиториев, которые будут зарегистрированы в контейнере зависимостей.
func SetRepositories(
	bonusRepo UserBonusRepository,
	bonusCurrencyConfigRepo BonusCurrencyConfigRepository,
	bonusMenuParamsRepo BonusMenuParamsRepository,
	bonusMultiDaysParamsRepo BonusMultiDaysParamsRepository,
	bonusStatisticRepo BonusStatisticRepository,
	bonusTransactionRepo BonusTransactionRepository,
	bonusPatternsRepo BonusPatternsRepository,
	bonusAvailableRepo BonusAvailableRepository,
) {
	SetBonusRepository(bonusRepo)
	SetBonusCurrencyConfigRepository(bonusCurrencyConfigRepo)
	SetBonusMenuParamsRepository(bonusMenuParamsRepo)
	SetBonusMultiDaysParamsRepository(bonusMultiDaysParamsRepo)
	SetBonusStatisticRepository(bonusStatisticRepo)
	SetBonusTransactionRepository(bonusTransactionRepo)
	SetBonusPatternsRepository(bonusPatternsRepo)
	SetBonusAvailableRepository(bonusAvailableRepo)
}

// SetFactories инициализирует фабрики, которые будут использоваться в сервисе.
func SetFactories(
	bonusCreatorFactoryImpl BonusCreatorFactory,
	bonusHandlersFactoryImpl BonusHandlersFactory,
	bonusActualizerFactoryImpl BonusActualizerFactory,
	betWagererFactoryImpl BetWagererFactory,
	extFsHandlersFactoryImpl ExtFsHandlersFactory,
	realMoneyWalletFactoryImpl RealMoneyWalletFactory,
	bonusInfoProviderFactoryImpl BonusInfoProviderFactory,
	bonusActivatorFactoryImpl BonusActivatorFactory,
) {
	SetBonusCreatorFactory(bonusCreatorFactoryImpl)
	SetBonusHandlersFactory(bonusHandlersFactoryImpl)
	SetBonusActualizerFactory(bonusActualizerFactoryImpl)
	SetBetWagererFactory(betWagererFactoryImpl)
	SetExtFsHandlersFactory(extFsHandlersFactoryImpl)
	SetRealMoneyWalletFactory(realMoneyWalletFactoryImpl)
	SetBonusInfoProviderFactory(bonusInfoProviderFactoryImpl)
	SetBonusActivatorFactory(bonusActivatorFactoryImpl)
}
