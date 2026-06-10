package repository

import (
	"bytes"
	"encoding/json"
	"errors"
	"io"
	"net/http"
	"user-manager/internal/config"
	"user-manager/internal/db/models"
	"user-manager/internal/service/container"
)

func NewUserBonusRestRepository() container.UserBonusRepository {
	return restUserBonusRepository{}
}

type restUserBonusRepository struct {
}

// GetByID извлекает запись UserBonus из базы данных по заданному идентификатору id.
// Возвращает указатель на структуру UserBonus и ошибку, если операция завершилась неудачно.
func (ub restUserBonusRepository) GetByID(id int64) (*models.UserBonus, error) {
	var result GetBonusResponse
	body, err := ub.request("getBonus", map[string]any{"bonus_id": id})
	if err != nil {
		return nil, err
	}
	err = json.Unmarshal(body, &result)
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
func (ub restUserBonusRepository) Update(bonus *models.UserBonus) error {
	data, err := ub.request("saveBonus", BonusDataRequest{
		Attributes: *bonus,
	})
	if err != nil {
		return err
	}

	var result GetBonusResponse
	err = json.Unmarshal(data, &result)
	if err != nil {
		return err
	}
	if result.Code != 200 {
		return errors.New(result.Error)
	}
	return nil
}

// Insert добавляет новую запись UserBonus в базу данных и возвращает ID добавленной записи или ошибку, если операция не удалась.
func (ub restUserBonusRepository) Insert(bonus *models.UserBonus) (int64, error) {
	data, err := ub.request("saveBonus", BonusDataRequest{
		Attributes: *bonus,
	})
	if err != nil {
		return 0, err
	}

	var result GetBonusResponse
	err = json.Unmarshal(data, &result)
	if err != nil {
		return 0, err
	}
	if result.Code != 200 {
		return 0, errors.New(result.Error)
	}
	return int64(result.Attributes.ID), nil
}

func (ub restUserBonusRepository) request(method string, payload any) ([]byte, error) {
	url := config.Get().KBonus.APIURL
	token := config.Get().KBonus.Token
	jsonPayload, _ := json.Marshal(payload)
	req, err := http.NewRequest(http.MethodPost, url+"/api/"+method, bytes.NewReader(jsonPayload))
	if err != nil {
		return nil, err
	}
	req.Header.Set("Authorization", "Bearer "+token) // токен
	req.Header.Set("Content-Type", "application/json")

	resp, err := http.DefaultClient.Do(req)
	if err != nil {
		return nil, err
	}
	if resp.StatusCode != 200 {
		return nil, errors.New("request error: " + resp.Status)
	}
	body, err := io.ReadAll(resp.Body)
	defer resp.Body.Close()
	if err != nil {
		return nil, err
	}
	return body, nil
}

type BonusDataRequest struct {
	Attributes models.UserBonus `json:"attributes"`
}

type GetBonusResponse struct {
	Attributes models.UserBonus `json:"attributes,omitzero"`
	Code       int              `json:"cd"`  // Код ответа
	Error      string           `json:"err"` // Сообщение об ошибке, если есть
}
