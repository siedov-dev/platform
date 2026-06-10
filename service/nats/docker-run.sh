#!/bin/sh
docker run --rm --name knats --mount type=volume,src="$(pwd)/storage",dst=/app/storage,type=bind --network="host" knats
