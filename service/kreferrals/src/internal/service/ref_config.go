package service

import (
	"encoding/json"
	"errors"
	"fmt"
	"kreferrals/internal/db/models"
	"strconv"
)

type RefConfig struct {
	ID     uint64             `json:"id"`      // Идентификатор конфигурации
	GGRPrc int                `json:"ggr_prc"` // Процент GGR (Gross Gaming Revenue)
	FdSums map[string]float64 `json:"fd_sums"` // Суммы FD (First Deposit)
}

// FromDbConfig создает новый RefConfig из базы данных модели ReferralConfig.
func NewRefConfig(dbConfig *models.ReferralConfig) (*RefConfig, error) {
	c := &RefConfig{}
	c.GGRPrc = dbConfig.GGRPrc
	c.FdSums = make(map[string]float64)
	c.ID = dbConfig.ID
	if dbConfig.FdSums != "" {
		decodedSums := make(map[string]any)
		if err := json.Unmarshal([]byte(dbConfig.FdSums), &decodedSums); err != nil {
			return nil, err
		}
		for currency, sum := range decodedSums {
			if value, ok := sum.(float64); ok {
				c.FdSums[currency] = value
			} else if value, ok := sum.(int); ok {
				c.FdSums[currency] = float64(value)
			} else if value, ok := sum.(string); ok {
				// Попытка преобразовать строку в float64
				if floatValue, err := strconv.ParseFloat(value, 64); err == nil {
					c.FdSums[currency] = floatValue
				} else {
					return nil, fmt.Errorf("invalid FD sum for currency %s: %s", currency, value)
				}
			} else {
				return nil, errors.New("invalid type for FD sum: " + fmt.Sprintf("%T", sum))
			}
		}
	}
	return c, nil
}

func (c *RefConfig) GetGGRPrc() int {
	return c.GGRPrc
}

func (c *RefConfig) GetFdSum(currency string) float64 {
	if sum, ok := c.FdSums[currency]; ok {
		return sum
	}
	return 0.0
}
