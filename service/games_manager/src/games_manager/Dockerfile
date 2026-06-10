FROM golang:1.23 AS builder

LABEL stage=gobuilder

ENV GOOS=linux
ENV CGO_ENABLED=0

WORKDIR /build

COPY . .

RUN go mod download

RUN go build -a -installsuffix cgo -o /app/games_manager /build/cmd/games_manager/main.go

FROM alpine:latest

WORKDIR /app

COPY --from=builder /app/games_manager /app/games_manager
COPY --from=builder /build/config/config.yaml /app/config/config.yaml

# Install necessary packages for network and database connections
RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata

# Expose the port the service will run on
EXPOSE 8095

CMD ["/app/games_manager"]