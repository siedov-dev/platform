#!/bin/sh
docker run --rm --name kusers --mount type=volume,src="$(pwd)/logs",dst=/app/logs,type=bind --network="host" kusers
