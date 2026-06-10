package ipdataprovider

import (
	"encoding/json"
	"fmt"
	"kipinfo/internal/db/models"
	"kipinfo/internal/service/container"
	"net/http"
	"strings"
	"time"
)

var _ container.IPDataProvider = (*IpInfoProvider)(nil)

// NewIpInfoProvider создает новый экземпляр IpInfoProvider
// и возвращает его как интерфейс IPDataProvider
func NewIpInfoProvider() container.IPDataProvider {
	return &IpInfoProvider{}
}

type IpInfoProvider struct {
}

// GetId возвращает идентификатор провайдера.
// Возвращает строку с идентификатором провайдера.
func (p *IpInfoProvider) GetId() string {
	return "ipinfoio"
}

// GetIPInfo получает информацию о IP-адресе с помощью API ipinfo.io
// и заполняет модель IpInfoDBModel
func (p *IpInfoProvider) GetIPInfo(ip string, model models.IpInfoDBModel) (models.IpInfoDBModel, error) {

	url := fmt.Sprintf("https://ipinfo.io/%s/json", ip)
	client := &http.Client{Timeout: 2 * time.Second}
	req, _ := http.NewRequest("GET", url, nil)
	req.Header.Set("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36")
	req.Header.Set("Accept", "application/json")
	req.Header.Set("Accept-Language", "en-US,en;q=0.9")
	// make request
	resp, err := client.Do(req)
	if err != nil {
		return model, fmt.Errorf("request ipinfo.io error: %w", err)
	}
	defer resp.Body.Close()

	// check status code
	if resp.StatusCode != http.StatusOK {
		return model, fmt.Errorf("ipinfo.io error: %s", resp.Status)
	}

	data := map[string]any{}
	if err := json.NewDecoder(resp.Body).Decode(&data); err != nil {
		return model, fmt.Errorf("response ipinfo.io parse error: %w", err)
	}

	// load data into model
	model.IP = ip
	if val, ok := data["city"]; ok {
		if val, ok := val.(string); ok {
			if val != "" {
				model.City = val
			}
		}
	}
	if val, ok := data["region"]; ok {
		if val, ok := val.(string); ok {
			if val != "" {
				model.Region = val
			}
		}
	}
	if val, ok := data["country"]; ok {
		if val, ok := val.(string); ok {
			if val != "" {
				model.CountryCode = val
			}
		}
	}
	if val, ok := data["org"]; ok {
		if val, ok := val.(string); ok {
			if val != "" {
				model.ISP = val
			}
		}
	}

	if val, ok := data["loc"]; ok {
		if loc, ok := val.(string); ok {
			parts := strings.Split(loc, ",")
			if len(parts) == 2 {
				model.Lat = parts[0]
				model.Long = parts[1]
			}
		}
	}

	now := time.Now()
	model.UpdatedAt = &now

	return model, nil
}
