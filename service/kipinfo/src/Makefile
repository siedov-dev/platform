.PHONY: kipinfo
kipinfo:
	go build -o ./cmd/kipinfo cmd/main.go
	cp -r config cmd/

.PHONY: static
static:
	CGO_ENABLED=0 go build -o ./cmd/kipinfo ./cmd/main.go
	cp -r config cmd/

.PHONY: macos
macos:main
	CGO_ENABLED=0 GOOS=darwin GOARCH=amd64 go build -o ./cmd/kipinfo_mac ./cmd/main.go
	cp -r config cmd/

.PHONY: test
test:
	go test -v ./test

run: kipinfo
	./cmd/kipinfo

docker-build:
	docker build -t kipinfo:latest .

docker-run:
	docker run --mount type=volume,src="$(pwd)",dst=/app/logs,type=bind --network="host" kipinfo:latest