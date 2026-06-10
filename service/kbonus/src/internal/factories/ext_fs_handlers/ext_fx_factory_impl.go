package extfshandlers

import (
	"errors"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
)

type ExtFsHandlersFactoryImpl struct{}

func NewExtFsHandlersFactoryImpl() container.ExtFsHandlersFactory {
	return &ExtFsHandlersFactoryImpl{}
}

func (f *ExtFsHandlersFactoryImpl) GetExtFsHandler(game_system string, ub *models.UserBonus) (stypes.BonusExternalFsHandler, error) {
	switch game_system {
	case "sleg":
		return NewSlegExtFsHandler(ub), nil
	case "pragplay":
		return NewPPlayExtFsHandler(ub), nil
	case "pplay":
		return NewPPlayExtFsHandler(ub), nil
	case "qt":
		return NewQTExtFsHandler(ub), nil
	case "b2bslots":
		return NewB2BSlotsExtFsHandler(ub), nil
	}
	return nil, errors.New("game system not operable")
}
