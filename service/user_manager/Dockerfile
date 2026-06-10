FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./user_manager ./user_manager

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 23100
CMD ["/app/user_manager"]
