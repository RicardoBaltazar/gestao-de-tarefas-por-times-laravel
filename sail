#!/usr/bin/env bash

UNAMEOUT="$(uname -s)"

# Verify operating system is supported...
case "${UNAMEOUT}" in
    Linux*)             MACHINE=linux;;
    Darwin*)            MACHINE=mac;;
    *)                  MACHINE="UNKNOWN"
esac

if [ "$MACHINE" == "UNKNOWN" ]; then
    echo "Unsupported operating system [$(uname -s)]. Laravel Sail supports macOS, Linux, and Windows (WSL2)." >&2
    exit 1
fi

# Define environment variables...
export APP_PORT=${APP_PORT:-80}
export APP_SERVICE=${APP_SERVICE:-"laravel.test"}
export DB_PORT=${DB_PORT:-3306}
export WWWUSER=${WWWUSER:-$UID}
export WWWGROUP=${WWWGROUP:-$(id -g)}

# Define Docker Compose command prefix...
COMPOSE_COMMAND="docker compose -f src/compose.yaml"

if [ $# -eq 0 ]; then
    # If no arguments, show help
    $COMPOSE_COMMAND ps
elif [ "$1" == "artisan" ] || [ "$1" == "art" ]; then
    # Run Laravel Artisan commands
    shift 1
    $COMPOSE_COMMAND exec laravel.test php artisan "$@"
elif [ "$1" == "composer" ]; then
    # Run Composer commands
    shift 1
    $COMPOSE_COMMAND exec laravel.test composer "$@"
elif [ "$1" == "npm" ]; then
    # Run NPM commands
    shift 1
    $COMPOSE_COMMAND exec laravel.test npm "$@" 
elif [ "$1" == "npx" ]; then
    # Run NPX commands
    shift 1
    $COMPOSE_COMMAND exec laravel.test npx "$@"
elif [ "$1" == "node" ]; then
    # Run Node commands
    shift 1
    $COMPOSE_COMMAND exec laravel.test node "$@"
elif [ "$1" == "shell" ] || [ "$1" == "bash" ]; then
    # Open shell in container
    $COMPOSE_COMMAND exec laravel.test bash
elif [ "$1" == "tinker" ]; then
    # Run Laravel Tinker
    $COMPOSE_COMMAND exec laravel.test php artisan tinker
else
    # Pass through all other commands to docker compose
    $COMPOSE_COMMAND "$@"
fi