#!/bin/sh
docker run --rm --name kpay --mount type=volume,src="$(pwd)/logs",dst=/app/logs,type=bind --network="host" kpay
