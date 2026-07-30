#!/usr/bin/env bash
# Bootstrap script — run as root on a fresh Ubuntu 24.04 Droplet.
# Installs Docker, firewall, clones the repo, and starts the stack.
set -euo pipefail

REPO_URL="https://github.com/Iiyas12j/kna-home-php.git"
APP_DIR="/opt/kna-home-php"

echo "==> Setting up swap (small droplet — Docker + MySQL need headroom)"
if [ ! -f /swapfile ]; then
  fallocate -l 2G /swapfile
  chmod 600 /swapfile
  mkswap /swapfile
  swapon /swapfile
  echo "/swapfile none swap sw 0 0" >> /etc/fstab
  sysctl -w vm.swappiness=10
  echo "vm.swappiness=10" >> /etc/sysctl.conf
fi

echo "==> Updating packages"
apt-get update -y
apt-get install -y ca-certificates curl git ufw

echo "==> Installing Docker"
if ! command -v docker &>/dev/null; then
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
  chmod a+r /etc/apt/keyrings/docker.asc
  echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
    > /etc/apt/sources.list.d/docker.list
  apt-get update -y
  apt-get install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin
fi

echo "==> Configuring firewall"
ufw allow OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

echo "==> Cloning repo"
if [ -d "$APP_DIR/.git" ]; then
  git -C "$APP_DIR" pull
else
  git clone "$REPO_URL" "$APP_DIR"
fi

cd "$APP_DIR"

if [ ! -f .env ]; then
  echo "!! No .env found in $APP_DIR — scp one up before running docker compose:"
  echo "   scp deploy/.env.production root@<droplet-ip>:$APP_DIR/.env"
  exit 1
fi

echo "==> Building and starting containers"
docker compose up -d --build

DB_PASS_VAL="$(grep '^DB_PASS=' .env | cut -d= -f2-)"
DB_NAME_VAL="$(grep '^DB_NAME=' .env | cut -d= -f2-)"

echo "==> Waiting for MySQL to be healthy"
until docker compose exec -T mysql mysqladmin ping -h localhost -p"$DB_PASS_VAL" --silent; do
  sleep 2
done

if [ -f deploy/seed-data.sql ]; then
  echo "==> Importing seed data"
  docker compose exec -T mysql mysql -uroot -p"$DB_PASS_VAL" "$DB_NAME_VAL" < deploy/seed-data.sql
else
  echo "!! deploy/seed-data.sql not found — skipping data import (tables will be empty)"
fi

echo "==> Done. Site should be live on http://<droplet-ip>/"
