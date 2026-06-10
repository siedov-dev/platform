package config

import (
	"log"
	"os"
	"time"

	"github.com/ilyakaznacheev/cleanenv"
)

type Config struct {
	Env         string `yaml:"env" env-default:"dev"`
	DB          `yaml:"db"`
	NATS        `yaml:"nats"`
	Slotegrator `yaml:"slotegrator"`
	PragPlay    `yaml:"pragplay"`
	QT          `yaml:"qt"`
	HTTPServer  `yaml:"http_server"`
}

type DB struct {
	ConnectionString string `yaml:"connection_string"`
	MaxOpenConns     int    `yaml:"max_open_cons" env-default:"10"`
	MaxIdleConns     int    `yaml:"max_iddle_cons" env-default:"10"`
}

// Slotegrator представляет собой структуру настроек для интеграции с сервисом Slotegrator.
// Поле MerchantID содержит идентификатор мерчанта.
// Поле Key задает ключ API.
type Slotegrator struct {
	MerchantID string `yaml:"merchant_id"`
	Key        string `yaml:"key"`
	ApiURL     string `yaml:"api_url" env-default:"https://gis.slotegrator.network/api/index.php/v1"`
}

// PragPlay представляет собой структуру настроек для интеграции с Pragmatic Play.
// Поле Login указывает логин для аутентификации.
// Поле Key содержит ключ API для доступа к сервису.
type PragPlay struct {
	Login string `yaml:"login"`
	Key   string `yaml:"key"`
}

// QT представляет собой структуру, содержащую ключевые настройки. Поле Key задает ключ-конфигурацию типа string.
type QT struct {
	Key string `yaml:"key"`
}

type NATS struct {
	URL   string `yaml:"url"`
	Token string `yaml:"token"`
}

// HTTPServer структура настроек htt сервера
type HTTPServer struct {
	Address     string        `yaml:"address" env-default:"0.0.0.0:8090"`
	Timeout     time.Duration `yaml:"timeout" env-default:"5s"`
	IdleTimeout time.Duration `yaml:"idle_timeout" env-default:"60s"`
	Token       string        `yaml:"token"`
}

var cfg *Config

func init() {
	cfg = MustLoad()
}

func Get() *Config {
	return cfg
}

func MustLoad() *Config {

	configPath := "config/config.yaml"

	// Проверяем существование конфиг-файла
	if _, err := os.Stat(configPath); err != nil {
		log.Fatalf("error opening config file: %s", err)
	}

	var cfg Config

	// Читаем конфиг-файл и заполняем нашу структуру
	err := cleanenv.ReadConfig(configPath, &cfg)
	if err != nil {
		log.Fatalf("error reading config file: %s", err)
	}

	return &cfg
}
