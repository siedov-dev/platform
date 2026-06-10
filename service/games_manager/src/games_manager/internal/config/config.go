package config

import (
	"log"
	"os"
	"time"

	"github.com/ilyakaznacheev/cleanenv"
)

type Config struct {
	Env        string   `yaml:"env" env-default:"development"`
	MainLangs  []string `yaml:"main_langs" env-default:"ru,ua,en,hi,kz,test"`                // основные языки
	AllLangs   []string `yaml:"all_langs" env-default:"ru,ua,en,hi,kz,de,fr,it,pt,es,sk,uz"` // все доступные языки
	HTTPServer `yaml:"http_server"`
	DB         `yaml:"db"`
}

type HTTPServer struct {
	Address     string        `yaml:"address" env-default:"0.0.0.0:8090"`
	Timeout     time.Duration `yaml:"timeout" env-default:"5s"`
	IdleTimeout time.Duration `yaml:"idle_timeout" env-default:"60s"`
}

type DB struct {
	ConnectionString string `yaml:"connection_string"`
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
