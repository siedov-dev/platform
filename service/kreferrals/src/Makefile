.PHONY: k_tournaments
k_tournaments:
	go build -o ./cmd/kreferrals cmd/main.go
	cp -r config cmd/

.PHONY: static
static:
	CGO_ENABLED=0 go build -o ./cmd/kreferrals ./cmd/main.go
	cp -r config cmd/

.PHONY: macos
macos:main
	CGO_ENABLED=0 GOOS=darwin GOARCH=amd64 go build -o ./cmd/k_tournaments_mac ./cmd/main.go
	cp -r config cmd/

.PHONY: test
test:
	go test -v ./test

run: k_tournaments
	./cmd/kreferrals

docker-build:
	docker build -t kreferrals:latest .

docker-run:
	docker run --mount type=volume,src="$(pwd)",dst=/app/logs,type=bind --network="host" kreferrals