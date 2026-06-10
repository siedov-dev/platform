FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./kslc ./kslc

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 23104
CMD ["./kslc"]
