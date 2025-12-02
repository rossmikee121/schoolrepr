#!/bin/bash

TOKEN=$(curl -s -X POST -H "Content-Type: application/json" -H "Accept: application/json" -d '{"email":"principal@schoolerp.com","password":"password"}' "http://localhost:8000/api/login" | jq -r '.data.token')

echo "Testing Departments API:"
curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "http://localhost:8000/api/departments"