package repository

import (
	"encoding/json"
	"errors"
	"user-manager/internal/db/models"
	"user-manager/internal/service/container"
)

func NewUserBonusNatsRepository() container.UserBonusRepository {
	return natsUserBonusRepository{}
}

type natsUserBonusRepository struct {
}

// GetByID извлекает запись UserBonus из базы данных по заданному идентификатору id.
// Возвращает указатель на структуру UserBonus и ошибку, если операция завершилась неудачно.
func (ub natsUserBonusRepository) GetByID(id int64) (*models.UserBonus, error) {
	nats := container.GetMsgBroker()
	data, err := nats.Request("bonus.getBonus", map[string]any{
		"id": id,
	})
	if err != nil {
		return nil, err
	}

	var result GetBonusResponse
	err = json.Unmarshal(data.([]byte), &result)
	if err != nil {
		return nil, err
	}
	if result.Code != 200 {
		return nil, errors.New(result.Error)
	}
	return &result.Attributes, nil
}

// Update обновляет запись UserBonus в базе данных, используя данные из переданной структуры.
// Возвращает ошибку, если операция завершилась неудачно.
func (ub natsUserBonusRepository) Update(bonus *models.UserBonus) error {
	nats := container.GetMsgBroker()
	data, err := nats.Request("bonus.saveBonus", BonusDataRequest{
		Attributes: *bonus,
	})
	if err != nil {
		return err
	}

	var result GetBonusResponse
	err = json.Unmarshal(data.([]byte), &result)
	if err != nil {
		return err
	}
	if result.Code != 200 {
		return errors.New(result.Error)
	}
	return nil
}

// Insert добавляет новую запись UserBonus в базу данных и возвращает ID добавленной записи или ошибку, если операция не удалась.
func (ub natsUserBonusRepository) Insert(bonus *models.UserBonus) (int64, error) {
	nats := container.GetMsgBroker()
	data, err := nats.Request("bonus.saveBonus", BonusDataRequest{
		Attributes: *bonus,
	})
	if err != nil {
		return 0, err
	}

	var result GetBonusResponse
	err = json.Unmarshal(data.([]byte), &result)
	if err != nil {
		return 0, err
	}
	if result.Code != 200 {
		return 0, errors.New(result.Error)
	}
	return int64(result.Attributes.ID), nil
}
