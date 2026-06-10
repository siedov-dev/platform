.PHONY: k_tournaments
k_tournaments:
	go build -o ./cmd/k_tournaments/k_tournaments cmd/k_tournaments/main.go
	cp -r config cmd/k_tournaments/

.PHONY: static
static:
	CGO_ENABLED=0 go build -o ./cmd/k_tournaments/k_tournaments ./cmd/k_tournaments/main.go
	cp -r config cmd/k_tournaments/

.PHONY: macos
macos:main
	CGO_ENABLED=0 GOOS=darwin GOARCH=amd64 go build -o ./cmd/k_tournaments/k_tournaments_mac ./cmd/k_tournaments/k_tournaments.go
	cp -r config cmd/k_tournaments/

.PHONY: test
test:
	go test -v ./test

run: k_tournaments
	./cmd/k_tournaments/k_tournaments

docker-build:
	docker build -t k_tournaments:latest .

docker-run:
	docker run --mount type=volume,src="$(pwd)",dst=/app/logs,type=bind --network="host" k_tournaments