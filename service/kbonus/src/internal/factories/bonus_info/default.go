package bonusinfo

import (
	"kbonus/internal/db/models"
	"kbonus/internal/stypes"
	"strconv"
)

type defaultBonusInfoProvider struct {
	userBonus *models.UserBonus
}

func NewDefaultBonusInfoProvider(ub *models.UserBonus) stypes.BonusInfoProvider {
	return &defaultBonusInfoProvider{
		userBonus: ub,
	}
}

func (d *defaultBonusInfoProvider) GetBonusInfo() stypes.BonusInfo {
	return createDefaultBonusInfo(d.userBonus)
}

func createDefaultBonusInfo(ub *models.UserBonus) stypes.BonusInfo {
	info := stypes.BonusInfo{
		IsDeposit:            false,
		IsNonDeposit:         false,
		IsFreeSpins:          false,
		IsPaymentRequired:    false,
		RequiredSum:          0.0,
		IsAgreementRequired:  false,
		IsProgressAvailable:  false,
		IsCancelable:         false,
		IsActive:             false,
		IsDone:               false,
		IsCanceled:           false,
		IsPending:            false,
		IsCompleted:          false,
		IsExpired:            false,
		IsListeningDeposit:   false,
		TotalMoneyWon:        0.0,
		IsForceToPlay:        false,
		IsNotPlayable:        false,
		IsLocked:             false,
		IsActivatedByMenu:    false,
		IsCancelebleOnPayout: false,
		IsPaused:             false,
		CanPause:             false,
	}

	if ub.Type == stypes.TYPE_DEPOSIT_WAGER || ub.Type == stypes.TYPE_DEPOSIT_PRIZE {
		info.IsDeposit = true
	}

	if ub.SpinsTotal > 0 {
		info.IsFreeSpins = true
	}

	if ub.Type == stypes.TYPE_PS_NON_DEP || ub.Type == stypes.TYPE_PP_NONDEP || ub.Type == stypes.TYPE_SLEG_NONDEP {
		info.IsNonDeposit = true
	}

	if ub.Status == stypes.STATUS_ACTIVE {
		info.IsActive = true
	}
	if ub.Status == stypes.STATUS_DONE {
		info.IsDone = true
	}
	if ub.Status == stypes.STATUS_CANCELED {
		info.IsCanceled = true
	}
	if ub.Status == stypes.STATUS_PENDING {
		info.IsPending = true
	}
	if ub.Status == stypes.STATUS_EXPIRED {
		info.IsExpired = true
	}
	if ub.Status == stypes.STATUS_PAUSE || ub.Status == stypes.STATUS_PAUSE_PAYMENT {
		info.IsPaused = true
	}
	if ub.Status == stypes.STATUS_EXPIRED || ub.Status == stypes.STATUS_DONE || ub.Status == stypes.STATUS_CANCELED {
		info.IsCompleted = true
	}
	if ub.Status == stypes.STATUS_DEPOSIT_LISTEN {
		info.IsListeningDeposit = true
	}
	if ub.Status == stypes.STATUS_WAITING_FOR_PAYMENT {
		info.IsPaymentRequired = true
	}
	if ub.Status == stypes.STATUS_WAITING_FOR_AGREEMENT || ub.Status == stypes.STATUS_NOT_ACTIVATED {
		info.IsAgreementRequired = true
	}
	if ub.Status != stypes.STATUS_EXPIRED && ub.Status != stypes.STATUS_DONE && ub.Status != stypes.STATUS_CANCELED {
		info.IsCancelable = true
	}
	if ub.Status == stypes.STATUS_LOCKED {
		info.IsLocked = true
	}

	if stypes.OnPayoutCancelableTypes[ub.Type] {
		info.IsCancelebleOnPayout = true
	}

	if ub.Status == stypes.STATUS_ACTIVE && ub.SpinsAvailable < 0 {
		info.CanPause = true
	}

	if ub.Status == stypes.STATUS_WAITING_FOR_PAYMENT {
		info.CanPause = true
	}

	RequiredSum, err := strconv.ParseFloat(ub.MoneyToActivate, 64)
	if err == nil {
		info.RequiredSum = RequiredSum
	}

	info.TotalMoneyWon = ub.MoneyPaidOut + ub.MoneyAvailable

	return info
}
