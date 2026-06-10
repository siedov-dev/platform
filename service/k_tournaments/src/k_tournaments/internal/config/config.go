package config

import (
	"log"
	"os"
	"time"

	"github.com/ilyakaznacheev/cleanenv"
)

type Config struct {
	Env        string `yaml:"env" env-default:"development"`
	HTTPServer `yaml:"http_server"`
	DB         `yaml:"db"`
	NATS       `yaml:"nats"`
}

type HTTPServer struct {
	Address     string        `yaml:"address" env-default:"0.0.0.0:8090"`
	Timeout     time.Duration `yaml:"timeout" env-default:"5s"`
	IdleTimeout time.Duration `yaml:"idle_timeout" env-default:"60s"`
}

type DB struct {
	ConnectionString string `yaml:"connection_string"`
	MaxOpenConns     int    `yaml:"max_open_cons" env-default:"10"`
	MaxIdleConns     int    `yaml:"max_iddle_cons" env-default:"10"`
}

type NATS struct {
	URL   string `yaml:"url"`
	Token string `yaml:"token"`
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
