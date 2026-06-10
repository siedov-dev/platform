// Пакет extfshandlers содержит обработчики внешних free spins для различных игровых систем.
package extfshandlers

import "user-manager/internal/stypes"

// Типы бонусов которые должны сбрасывать установленные для бонуса игры
// после завершения фриспинов и использовать игры по умолчанию.
var dropGamesBonuses map[int8]bool = map[int8]bool{
	stypes.TYPE_PS_SFS_DYNAMIC:   true,
	stypes.TYPE_PS_NON_DEP:       true,
	stypes.TYPE_PS_SMART:         true,
	stypes.TYPE_PS_FS_MENU:       true,
	stypes.TYPE_PS_FS:            true,
	stypes.TYPE_SHARED_MONEY_FS:  true,
	stypes.TYPE_CHOOSE_WAGER_FS2: true,
}

// Тип бонусов которые должны пересчитать максимальный выигрыш
// после завершения фриспинов
var recalcMaxWinAfterFsBonuses map[int8]bool = map[int8]bool{
	stypes.TYPE_PS_NON_DEP: true,
}
