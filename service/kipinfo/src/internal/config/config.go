package config

import (
	"fmt"
	"os"
	"sync"
	"time"

	"github.com/ilyakaznacheev/cleanenv"
)

type Config struct {
	Env        string `yaml:"env" env-default:"development"`
	HTTPServer `yaml:"http_server"`
	DB         `yaml:"db"`
}

type HTTPServer struct {
	Address     string        `yaml:"address" env-default:"0.0.0.0:8090"`
	Timeout     time.Duration `yaml:"timeout" env-default:"5s"`
	IdleTimeout time.Duration `yaml:"idle_timeout" env-default:"60s"`
	Token       string        `yaml:"token" env-default:""`
}

type DB struct {
	ConnectionString string `yaml:"connection_string"`
	MaxOpenConns     int    `yaml:"max_open_cons" env-default:"10"`
	MaxIdleConns     int    `yaml:"max_iddle_cons" env-default:"10"`
}

var cfg *Config
var once sync.Once

func Get() *Config {
	once.Do(func() {
		cfg = MustLoad()
	})
	return cfg
}

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
