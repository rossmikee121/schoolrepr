#!/bin/bash

BASE_URL="http://localhost:8000/api"

# Get token
TOKEN=$(curl -s -X POST -H "Content-Type: application/json" -d '{"email": "principal@schoolerp.com", "password": "password"}' "$BASE_URL/login" | jq -r '.data.token')

echo "🔧 Testing Specific API Endpoints"
echo "================================="

echo -e "\n1. Testing Department Creation..."
DEPT_RESULT=$(curl -s -X POST -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -d '{
    "name": "Test Department",
    "code": "TEST",
    "description": "Test Department for API validation"
}' "$BASE_URL/departments")
echo "$DEPT_RESULT" | jq '.success // .message'

echo -e "\n2. Testing Report Builder..."
REPORT_RESULT=$(curl -s -X POST -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -d '{
    "base_model": "Student",
    "columns": [
        {"field": "first_name", "alias": "First Name"},
        {"field": "last_name", "alias": "Last Name"}
    ],
    "limit": 5
}' "$BASE_URL/reports/build")
echo "$REPORT_RESULT" | jq '.success // .message'

echo -e "\n3. Testing Fee Management..."
FEE_RESULT=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL/students/1/outstanding")
echo "$FEE_RESULT" | jq '.success // .message // "No data"'

echo -e "\n✅ Specific API Tests Complete"