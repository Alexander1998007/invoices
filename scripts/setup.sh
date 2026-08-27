#!/usr/bin/env bash
set -e

node --version
npm --version

echo "Installing frontend dependencies locally so the IDE can resolve Nuxt/TypeScript types..."
(cd "$(dirname "$0")/../frontend" && npm install)

echo "Starting Docker services..."
cd "$(dirname "$0")/.."
docker compose up --build
