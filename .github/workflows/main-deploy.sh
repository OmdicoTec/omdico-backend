#!/bin/bash
export $(xargs < /home/projects/omdico/omdico-backend/.env)
set -e

docker compose build
docker run --rm --network omdico-backend_default --env-file .env --entrypoint php omdico-backend:latest artisan migrate --pretend --force
docker run --rm --network omdico-backend_default --env-file .env --entrypoint php omdico-backend:latest artisan migrate --force
docker compose down
docker compose rm -f app
docker compose up -d --build
docker compose stop nginx && docker compose up -d   # Update nginx upstream URLs
