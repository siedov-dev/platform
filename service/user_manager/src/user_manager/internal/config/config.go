package config

import (
	"fmt"
	"os"
	"sync"
	"time"

	"github.com/ilyakaznacheev/cleanenv"
)

// Config представляет собой структуру для хранения конфигурации приложения, включающей настройки различных компонентов.
type Config struct {
	Env         string `yaml:"env" env-default:"development"`
	HTTPServer  `yaml:"http_server"`
	DB          `yaml:"db"`
	Slotegrator `yaml:"slotegrator"`
	PragPlay    `yaml:"pragplay"`
	QT          `yaml:"qt"`
	B2BSlots    `yaml:"b2bslots"`
	Kraken      `yaml:"kraken"`
	NATS        `yaml:"nats"`
	KBonus      `yaml:"kbonus"`
}

// HTTPServer структура настроек htt сервера
type HTTPServer struct {
	Address     string        `yaml:"address" env-default:"0.0.0.0:8090"`
	Timeout     time.Duration `yaml:"timeout" env-default:"5s"`
	IdleTimeout time.Duration `yaml:"idle_timeout" env-default:"60s"`
}

// DB представляет конфигурацию подключения к базе данных.
// Поле ConnectionString задает строку подключения к БД.
// Поле MaxOpenConns определяет максимально допустимое количество открытых соединений.
// Поле MaxIdleConns задает максимально допустимое количество простаивающих соединений.
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

type B2BSlots struct {
	OperatorID int64  `yaml:"operator_id"`
	Password   string `yaml:"password"`
	APIURL     string `yaml:"api_url"`
}

// Kraken представляет собой структуру, содержащую базовую настройку.
// Поле Key используется для хранения ключа API формата string.
type Kraken struct {
	Key string `yaml:"key"`
}

type NATS struct {
	URL     string `yaml:"url"`
	Token   string `yaml:"token"`
	Enabled bool   `yaml:"enabled" env-default:"false"`
}

type KBonus struct {
	APIURL string `yaml:"api_url"`
	Token  string `yaml:"token"`
}

var cfg *Config
var once sync.Once

// Get обеспечивает получение глобального экземпляра конфигурации приложения. Гарантируется выполнение только один раз.
func Get() *Config {
	once.Do(func() {
		cfg = MustLoad()
	})
	return cfg
}

// MustLoad загружает конфигурацию приложения из файла config.yaml и вызывает panic при ошибке чтения или отсутствии файла.
func MustLoad() *Config {

	configPath := "config/config.yaml"

	// Проверяем существование конфиг-файла
	if _, err := os.Stat(configPath); err != nil {
		panic(fmt.Errorf("error opening config file: %s", err))
	}

	var cfg Config

	// Читаем конфиг-файл и заполняем нашу структуру
	err := cleanenv.ReadConfig(configPath, &cfg)
	if err != nil {
		panic(fmt.Errorf("error opening config file: %s", err))
	}

	return &cfg
}
