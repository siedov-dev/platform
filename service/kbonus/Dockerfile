FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./kbonus ./kbonus

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 23102
CMD ["/app/kbonus"]
