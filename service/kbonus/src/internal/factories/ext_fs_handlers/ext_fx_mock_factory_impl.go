package extfshandlers

import (
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
)

type ExtFsHandlersMockFactoryImpl struct{}

func NewExtFsHandlersMockFactoryImpl() container.ExtFsHandlersFactory {
	return &ExtFsHandlersMockFactoryImpl{}
}

func (f *ExtFsHandlersMockFactoryImpl) GetExtFsHandler(game_system string, ub *models.UserBonus) (stypes.BonusExternalFsHandler, error) {
	return NewMockExtFsHandler(ub), nil
}
