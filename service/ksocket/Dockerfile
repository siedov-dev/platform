FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./ksocket ./ksocket

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 8082
CMD ["/app/ksocket"]
