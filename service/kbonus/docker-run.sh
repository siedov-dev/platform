#!/bin/sh
docker run --rm --name kbonus --mount type=volume,src="$(pwd)/logs",dst=/app/logs,type=bind --network="host" kbonus
