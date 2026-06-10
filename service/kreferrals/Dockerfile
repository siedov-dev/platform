FROM alpine:latest
WORKDIR /app
COPY ./config ./config
COPY ./kreferrals ./kreferrals

RUN apk add --no-cache ca-certificates
RUN apk update --no-cache && apk add --no-cache tzdata
CMD ["/app/kreferrals"]
