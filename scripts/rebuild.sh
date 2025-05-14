#!/bin/bash

echo "🐳 Rebuilding Docker Image..."
docker-compose up -d --build
