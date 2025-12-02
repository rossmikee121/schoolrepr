#!/bin/bash

BASE_URL="http://localhost:8000/api"

TOKEN=$(curl -s -X POST -H "Content-Type: application/json" -d '{"email": "principal@schoolerp.com", "password": "password"}' "$BASE_URL/login" | jq -r '.data.token')

echo "Available Report Models:"
curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL/reports/models" | jq '.'