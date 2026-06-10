FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./kpay ./kpay
COPY ./keys ./keys

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 23106
CMD ["./kpay"]
