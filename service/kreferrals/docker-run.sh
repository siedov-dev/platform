#!/bin/sh
docker run -ti --rm --name kreferrals --mount type=volume,src="$(pwd)/logs",dst=/app/logs,type=bind --network="host" kreferrals
