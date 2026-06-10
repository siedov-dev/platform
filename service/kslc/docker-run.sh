#!/bin/sh
docker run --rm --name kslc --mount type=volume,src="$(pwd)/logs",dst=/app/logs,type=bind --network="host" kslc
