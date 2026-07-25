#!/usr/bin/env sh

# Aguarda banco responder
until ./artisan migrate:install > /dev/null 2>&1; do
  echo "Aguardando banco ficar disponível..."
  sleep 2
done

./artisan migrate
