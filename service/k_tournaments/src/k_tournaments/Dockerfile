FROM golang:1.24 AS builder

LABEL stage=gobuilder

ENV GOOS=linux
ENV CGO_ENABLED=0

WORKDIR /build

COPY . .

RUN go mod download

RUN go build -a -installsuffix cgo -o /app/k_tournaments /build/cmd/k_tournaments/main.go

FROM alpine:latest

WORKDIR /app

COPY --from=builder /app/k_tournaments /app/k_tournaments
COPY --from=builder /build/config/config.yaml /app/config/config.yaml

# Install necessary packages for network and database connections
RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata

# Expose the port the service will run on
EXPOSE 23101

CMD ["/app/k_tournaments"]