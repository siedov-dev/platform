.PHONY: user_manager
user_manager:
	go build -o ./cmd/user_manager/user_manager cmd/user_manager/main.go
	cp -r config cmd/user_manager/

.PHONY: static
static:
	CGO_ENABLED=0 go build -o ./cmd/user_manager/user_manager ./cmd/user_manager/main.go
	cp -r config cmd/user_manager/

.PHONY: macos
macos:
	CGO_ENABLED=0 GOOS=darwin GOARCH=amd64 go build -o ./cmd/user_manager/user_manager_mac ./cmd/user_manager/main.go
	cp -r config cmd/user_manager/

.PHONY: test
test:
	go test -v ./test

run: user_manager
	./cmd/user_manager/user_manager

docker-build:
	docker build -t user_manager:latest .

docker-run:
	docker run --mount type=volume,src="$(pwd)",dst=/app/logs,type=bind --network="host" user_manager