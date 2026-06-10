FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./games_manager ./games_manager

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 8095
CMD ["/app/games_manager"]
