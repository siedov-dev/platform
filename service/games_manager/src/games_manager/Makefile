.PHONY: games_manager
games_manager:
	go build -o ./cmd/games_manager/games_manager cmd/games_manager/main.go
	cp -r config cmd/games_manager/

.PHONY: static
static:
	CGO_ENABLED=1 CC=musl-gcc go build --ldflags '-linkmode=external -extldflags=-static' -o ./cmd/games_manager/games_manager ./cmd/games_manager/main.go
	cp -r config cmd/games_manager/

.PHONY: gm_macos
gm_macos:
	GOOS=darwin GOARCH=amd64 go build -o ./cmd/games_manager/games_manager_mac ./cmd/games_manager/main.go
	cp -r config cmd/games_manager/

run: games_manager
	./cmd/games_manager/games_manager

docker-build:
	docker build -t games_manager:latest .

docker-run:
	docker run --mount type=volume,src="$(pwd)",dst=/app/logs,type=bind --network="host" games_manager