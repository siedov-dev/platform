#!/bin/sh
docker run -ti --rm --name ktournaments --mount type=volume,src="$(pwd)/logs",dst=/app/logs,type=bind --network="host" ktournaments
