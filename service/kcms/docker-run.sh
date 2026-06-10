#!/bin/sh
docker run --rm --name kcms --mount type=volume,src="$(pwd)/logs",dst=/app/logs,type=bind --network="host" kcms
