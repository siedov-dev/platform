package extfshandlers

import "user-manager/internal/stypes"

// Bonuses that should clear game list after completing free spins
// and use default games for wagering.
var dropGamesBonuses map[int8]bool = map[int8]bool{
	stypes.TYPE_PS_SFS_DYNAMIC:   true,
	stypes.TYPE_PS_NON_DEP:       true,
	stypes.TYPE_PS_SMART:         true,
	stypes.TYPE_PS_FS_MENU:       true,
	stypes.TYPE_PS_FS:            true,
	stypes.TYPE_SHARED_MONEY_FS:  true,
	stypes.TYPE_CHOOSE_WAGER_FS2: true,
}
