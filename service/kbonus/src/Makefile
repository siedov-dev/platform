.PHONY: kbonus
kbonus:
	go build -o ./cmd/kbonus cmd/main.go
	cp -r config cmd/

.PHONY: tools
tools:
	CGO_ENABLED=0 go build -o ./cmd/tools/extfs cmd/tools/extfs.go
	cp -r config cmd/tools/

.PHONY: static
static:
	CGO_ENABLED=0 go build -o ./cmd/kbonus ./cmd/main.go
	cp -r config cmd/

.PHONY: macos
macos:main
	CGO_ENABLED=0 GOOS=darwin GOARCH=amd64 go build -o ./cmd/kbonus_mac ./cmd/main.go
	cp -r config cmd/

.PHONY: test
test:
	cp -r config test/
	go test -v ./test

run: kbonus
	./cmd/kbonus

docker-build:
	docker build -t kbonus:latest .

docker-run:
	docker run --mount type=volume,src="$(pwd)",dst=/app/logs,type=bind --network="host" kbonus