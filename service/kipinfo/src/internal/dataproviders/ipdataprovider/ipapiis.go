package ipdataprovider

import (
	"encoding/json"
	"fmt"
	"kipinfo/internal/db/models"
	"kipinfo/internal/service/container"
	"net/http"
	"strconv"
	"time"
)

var _ container.IPDataProvider = (*IpApiIsDataProvider)(nil)

// NewIpApiIsDataProvider создает новый экземпляр IpApiIsDataProvider
// и возвращает его как интерфейс IPDataProvider
func NewIpApiIsDataProvider() container.IPDataProvider {
	return &IpApiIsDataProvider{}
}

type IpApiIsDataProvider struct {
}

// GetId возвращает идентификатор провайдера.
// Возвращает строку с идентификатором провайдера.
func (p *IpApiIsDataProvider) GetId() string {
	return "ipapi.is"
}

// GetIPInfo получает информацию о IP-адресе с помощью API ipapi.is
// и заполняет модель IpInfoDBModel
func (p *IpApiIsDataProvider) GetIPInfo(ip string, model models.IpInfoDBModel) (models.IpInfoDBModel, error) {
	url := fmt.Sprintf("https://api.ipapi.is/?q=%s", ip)
	client := &http.Client{Timeout: 3 * time.Second}
	req, _ := http.NewRequest("GET", url, nil)
	req.Header.Set("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36")
	req.Header.Set("Accept", "application/json")
	req.Header.Set("Accept-Language", "en-US,en;q=0.9")
	// отправляем запрос
	resp, err := client.Do(req)
	if err != nil {
		return model, fmt.Errorf("request api.ipapi.is error: %w", err)
	}
	defer resp.Body.Close()

	// проверяем статус код
	if resp.StatusCode != http.StatusOK {
		return model, fmt.Errorf("api.ipapi.is error: %s", resp.Status)
	}

	// парсим ответ
	data := map[string]any{}
	if err := json.NewDecoder(resp.Body).Decode(&data); err != nil {
		return model, fmt.Errorf("response api.ipapi.is parse error: %w", err)
	}

	location := map[string]any{}
	if val, ok := data["location"]; ok {
		if val, ok := val.(map[string]any); ok {
			location = val
		}
	} else {
		return model, fmt.Errorf("response api.ipapi.is parse location error: %w", err)
	}

	model.IP = ip

	// наличие елемента в мапе location
	if val, ok := location["city"]; ok {
		// строка
		if val, ok := val.(string); ok {
			model.City = val
		}
	}

	if val, ok := location["continent"]; ok {
		if val, ok := val.(string); ok {
			model.Continent = val
		}
	}

	if val, ok := location["country"]; ok {
		if val, ok := val.(string); ok {
			model.Country = val
		}
	}

	if val, ok := location["country_code"]; ok {
		if val, ok := val.(string); ok {
			model.CountryCode = val
		}
	}

	if val, ok := location["statе"]; ok {
		if val, ok := val.(string); ok {
			model.Region = val
		}
	}

	if val, ok := location["latitude"]; ok {
		if val, ok := val.(float64); ok {
			model.Lat = strconv.FormatFloat(val, 'f', -1, 64)
		}
	}

	if val, ok := location["longitude"]; ok {
		if val, ok := val.(float64); ok {
			model.Long = strconv.FormatFloat(val, 'f', -1, 64)
		}
	}

	if val, ok := location["currency_code"]; ok {
		if val, ok := val.(string); ok {
			model.Currency = val
		}
	}

	asn := map[string]any{}
	if val, ok := data["asn"]; ok {
		if val, ok := val.(map[string]any); ok {
			asn = val
		}
	} else {
		return model, fmt.Errorf("response api.ipapi.is parse asn error: %w", err)
	}

	if val, ok := asn["org"]; ok {
		if val, ok := val.(string); ok {
			model.Org = val
		}
	}

	if val, ok := asn["descr"]; ok {
		if val, ok := val.(string); ok {
			model.ISP = val
		}
	}

	return model, nil
}
