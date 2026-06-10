package container

var configsRepository ReferralConfigRepository
var achievementRewardsRepository AchievementRewardsRepository
var referralEventRepository ReferralEventRepository
var userReferralRepository UserReferralRepository
var referralMUsersRepository ReferralMUsersRepository
var refWallet RefWallet
var eventsSaver EventsSaver

func SetRepositories(
	referralConfigRepo ReferralConfigRepository,
	achievementRewardsRepo AchievementRewardsRepository,
	referralEventRepo ReferralEventRepository,
	userReferralRepo UserReferralRepository,
	referralMUsersRepository ReferralMUsersRepository,
) {
	SetReferralConfigRepository(referralConfigRepo)
	SetAchievementRewardsRepository(achievementRewardsRepo)
	SetReferralEventRepository(referralEventRepo)
	SetUserReferralRepository(userReferralRepo)
	SetReferralMUsersRepository(referralMUsersRepository)
}

func SetReferralConfigRepository(repo ReferralConfigRepository) {
	configsRepository = repo
}

func GetReferralConfigRepository() ReferralConfigRepository {
	if configsRepository == nil {
		panic("ReferralConfigRepository is not set")
	}
	return configsRepository
}

func SetAchievementRewardsRepository(repo AchievementRewardsRepository) {
	achievementRewardsRepository = repo
}

func GetAchievementRewardsRepository() AchievementRewardsRepository {
	if achievementRewardsRepository == nil {
		panic("AchievementRewardsRepository is not set")
	}
	return achievementRewardsRepository
}

func SetReferralEventRepository(repo ReferralEventRepository) {
	if repo == nil {
		panic("ReferralEventRepository is not set")
	}
	referralEventRepository = repo
}

func GetReferralEventRepository() ReferralEventRepository {
	if referralEventRepository == nil {
		panic("ReferralEventRepository is not set")
	}
	return referralEventRepository
}

func SetReferralMUsersRepository(repo ReferralMUsersRepository) {
	if repo == nil {
		panic("UserReferralRepository is not set")
	}
	referralMUsersRepository = repo
}
func GetReferralMUsersRepository() ReferralMUsersRepository {
	if userReferralRepository == nil {
		panic("UserReferralRepository is not set")
	}
	return referralMUsersRepository
}

func SetUserReferralRepository(repo UserReferralRepository) {
	if repo == nil {
		panic("UserReferralRepository is not set")
	}
	userReferralRepository = repo
}
func GetUserReferralRepository() UserReferralRepository {
	if userReferralRepository == nil {
		panic("UserReferralRepository is not set")
	}
	return userReferralRepository
}

func SetRefWallet(wallet RefWallet) {
	if wallet == nil {
		panic("RefWallet is not set")
	}
	refWallet = wallet
}
func GetRefWallet() RefWallet {
	if refWallet == nil {
		panic("RefWallet is not set")
	}
	return refWallet
}

func SetEventsSaver(saver EventsSaver) {
	if saver == nil {
		panic("EventsSaver is not set")
	}
	eventsSaver = saver
}
func GetEventsSaver() EventsSaver {
	if eventsSaver == nil {
		panic("EventsSaver is not set")
	}
	return eventsSaver
}
