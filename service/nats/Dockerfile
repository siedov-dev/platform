FROM alpine:latest
WORKDIR /app
COPY ./nats-server ./nats-server
COPY ./nats.conf ./nats.conf
COPY ./run.sh ./run.sh

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
EXPOSE 4222
CMD ["./run.sh"]
