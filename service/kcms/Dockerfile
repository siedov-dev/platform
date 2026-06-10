FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./kcms ./kcms

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 23098
CMD ["/app/kcms"]
