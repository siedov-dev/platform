package ipdataprovider

import (
	"encoding/json"
	"fmt"
	"kipinfo/internal/db/models"
	"kipinfo/internal/service/container"
	"net/http"
	"time"
)

var _ container.IPDataProvider = (*IpApiCoProvider)(nil)

// NewIpApiCoProvider создает новый экземпляр IpApiCoProvider
// и возвращает его как интерфейс IPDataProvider
func NewIpApiCoProvider() container.IPDataProvider {
	return &IpApiCoProvider{}
}

type IpApiCoProvider struct {
}

// GetId возвращает идентификатор провайдера.
// Возвращает строку с идентификатором провайдера.
func (p *IpApiCoProvider) GetId() string {
	return "ipapi.co"
}

// GetIPInfo получает информацию о IP-адресе с помощью API ipapi.co
// и заполняет модель IpInfoDBModel
func (p *IpApiCoProvider) GetIPInfo(ip string, model models.IpInfoDBModel) (models.IpInfoDBModel, error) {

	url := fmt.Sprintf("https://ipapi.co/%s/json", ip)
	client := &http.Client{Timeout: 2 * time.Second}
	req, _ := http.NewRequest("GET", url, nil)
	req.Header.Set("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36")
	req.Header.Set("Accept", "application/json")
	req.Header.Set("Accept-Language", "en-US,en;q=0.9")
	// make request
	resp, err := client.Do(req)
	if err != nil {
		return model, fmt.Errorf("request ipapi.co error: %w", err)
	}
	defer resp.Body.Close()

	// check status code
	if resp.StatusCode != http.StatusOK {
		return model, fmt.Errorf("ipapi.co error: %s", resp.Status)
	}

	data := map[string]any{}
	if err := json.NewDecoder(resp.Body).Decode(&data); err != nil {
		return model, fmt.Errorf("response ipapi.co parse error: %w", err)
	}

	// load data into model
	model.IP = ip
	if val, ok := data["continent_code"]; ok {
		if val, ok := val.(string); ok {
			model.Continent = val
		}
	}
	if val, ok := data["country_name"]; ok {
		if val, ok := val.(string); ok {
			model.Country = val
		}
	}
	if val, ok := data["country"]; ok {
		if val, ok := val.(string); ok {
			model.CountryCode = val
		}
	}
	if val, ok := data["region"]; ok {
		if val, ok := val.(string); ok {
			model.Region = val
		}
	}
	if val, ok := data["city"]; ok {
		if val, ok := val.(string); ok {
			model.City = val
		}
	}
	if val, ok := data["latitude"]; ok {
		if val, ok := val.(float64); ok {
			model.Lat = fmt.Sprint(val)
		}
	}
	if val, ok := data["longitude"]; ok {
		if val, ok := val.(float64); ok {
			model.Long = fmt.Sprint(val)
		}
	}
	if val, ok := data["currency"]; ok {
		if val, ok := val.(string); ok {
			model.Currency = val
		}
	}
	if val, ok := data["asn"]; ok {
		if val, ok := val.(string); ok {
			model.ISP = val
		}
	}
	if val, ok := data["org"]; ok {
		if val, ok := val.(string); ok {
			model.Org = val
		}
	}
	now := time.Now()
	model.UpdatedAt = &now

	return model, nil
}
