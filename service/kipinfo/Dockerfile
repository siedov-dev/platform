FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./kipinfo ./kipinfo

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 23105
CMD ["/app/kipinfo"]
