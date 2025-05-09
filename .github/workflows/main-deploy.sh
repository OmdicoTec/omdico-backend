#!/bin/bash
set -e

# Load .env variables
export $(xargs < /home/projects/omdico/omdico-backend/.env)

# Setup SSH agent and add SSH private key
echo "$SSH_PRIVATE_KEY" > ~/.ssh/id_rsa
chmod 600 ~/.ssh/id_rsa
ssh-keyscan github.com >> ~/.ssh/known_hosts

# Build containers
docker-compose build

# Run migrations (pretend first)
docker run --rm --network omdico-backend_default --env-file .env --entrypoint php omdico-backend:latest artisan migrate --pretend --force

# Run migrations (actual)
docker run --rm --network omdico-backend_default --env-file .env --entrypoint php omdico-backend:latest artisan migrate --force

# Clean up and restart containers
docker-compose down
docker-compose rm -f app
docker-compose up -d --build

# Update nginx upstream URLs
docker-compose stop nginx && docker-compose up -d
