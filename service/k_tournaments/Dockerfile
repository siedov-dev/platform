FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./k_tournaments ./k_tournaments

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 23101
CMD ["/app/k_tournaments"]
