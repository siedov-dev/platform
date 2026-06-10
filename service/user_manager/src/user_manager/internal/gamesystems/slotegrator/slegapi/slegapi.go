package slegapi

import (
	"crypto/hmac"
	"crypto/sha1"
	"encoding/hex"
	"fmt"
	"io"
	"net/http"
	"net/url"
	"sort"
	"strings"
	"time"
	"user-manager/internal/config"

	"github.com/google/uuid"
)

var MerchantID string = ""
var Key string = ""
var api_url string = "https://gis.slotegrator.network/api/index.php/v1"

// init инициализирует MerchantID и Key
func init() {
	MerchantID = config.Get().Slotegrator.MerchantID
	Key = config.Get().Slotegrator.Key
}

// Sign подписывает запрос
func Sign(requestParams map[string]string, headers map[string]string) string {

	mergedParams := make(map[string]string)

	// join requestParams and headers
	for k, v := range requestParams {
		mergedParams[k] = v
	}
	for k, v := range headers {
		mergedParams[k] = v
	}

	// sort keys
	keys := make([]string, 0, len(mergedParams))
	for k := range mergedParams {
		keys = append(keys, k)
	}
	sort.Strings(keys)

	// create query string
	query := url.Values{}
	for _, key := range keys {
		query.Add(key, mergedParams[key])
	}
	hashString := query.Encode()

	// calc HMAC-SHA1
	h := hmac.New(sha1.New, []byte(Key))
	h.Write([]byte(hashString))
	sign := hex.EncodeToString(h.Sum(nil))

	return sign
}

// Query отправляет запрос на API
func Query(method string, params map[string]string, post bool) (string, error) {
	qurl := api_url + method

	urlvals := url.Values{}
	for k, v := range params {
		urlvals.Add(k, v)
	}

	if !post && len(params) > 0 {
		qurl += "?"
		qurl += urlvals.Encode()
	}

	headers := map[string]string{
		"X-Merchant-Id": MerchantID,
		"X-Timestamp":   fmt.Sprintf("%d", time.Now().Unix()),
		"X-Nonce":       uuid.New().String(),
	}

	xsign := Sign(params, headers)

	headers["X-Sign"] = xsign

	client := &http.Client{}

	if post {
		req, err := http.NewRequest(http.MethodPost, qurl, strings.NewReader(urlvals.Encode()))
		if err != nil {
			return "", err
		}
		for k, v := range headers {
			req.Header.Add(k, v)
		}
		req.Header.Add("Content-Type", "application/x-www-form-urlencoded")
		resp, err := client.Do(req)
		if err != nil {
			return "", err
		}
		defer resp.Body.Close()
		rs, err := io.ReadAll(resp.Body)
		if err != nil {
			return "", err
		}
		return string(rs), nil
	} else {
		req, err := http.NewRequest(http.MethodGet, qurl, nil)
		if err != nil {
			return "", err
		}
		for k, v := range headers {
			req.Header.Add(k, v)
		}
		resp, err := client.Do(req)
		if err != nil {
			return "", err
		}
		defer resp.Body.Close()
		rs, err := io.ReadAll(resp.Body)
		if err != nil {
			return "", err
		}
		return string(rs), nil
	}

}

// SelfValidate проверяет, что MerchantID и Key корректны
func SelfValidate() (string, error) {
	method := "/self-validate"
	params := map[string]string{}

	return Query(method, params, true)
}

// GetFreespinsLimits возвращает лимиты фриспинов
func GetFreespinsLimits() (string, error) {
	method := "/limits/freespin"
	params := map[string]string{}

	return Query(method, params, false)
}

// FreeSpinsGet возвращает информацию о фриспинах
func FreeSpinsGet(id string) (string, error) {
	method := "/freespins/get"
	params := map[string]string{
		"freespin_id": id,
	}

	return Query(method, params, false)
}

// FreeSpinsCancel отменяет фриспины
func FreeSpinsCancel(id string) (string, error) {
	method := "/freespins/cancel"
	params := map[string]string{
		"freespin_id": id,
	}

	return Query(method, params, true)
}
