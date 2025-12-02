#!/bin/bash

echo "=== Educational ERP Backend Test ==="
echo ""

# Get token
echo "1. Testing Login..."
LOGIN_RESPONSE=$(curl -s -X POST -H "Content-Type: application/json" -H "Accept: application/json" -d '{"email":"principal@schoolerp.com","password":"password"}' "http://localhost:8000/api/login")
TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token')

if [ "$TOKEN" != "null" ] && [ -n "$TOKEN" ]; then
    echo "✅ Login successful"
else
    echo "❌ Login failed"
    exit 1
fi

echo ""
echo "2. Testing Core APIs..."

# Test Students API
STUDENTS=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "http://localhost:8000/api/students")
if echo "$STUDENTS" | jq -e '.success' >/dev/null 2>&1; then
    echo "✅ Students API working"
else
    echo "❌ Students API failed"
fi

# Test Departments API
DEPARTMENTS=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "http://localhost:8000/api/departments")
if echo "$DEPARTMENTS" | jq -e '.success' >/dev/null 2>&1; then
    echo "✅ Departments API working"
else
    echo "❌ Departments API failed"
fi

# Test Reports API
REPORTS=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "http://localhost:8000/api/reports/models")
if echo "$REPORTS" | jq -e '.success' >/dev/null 2>&1; then
    echo "✅ Reports API working"
else
    echo "❌ Reports API failed"
fi

# Test Library API
LIBRARY=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "http://localhost:8000/api/library/books")
if echo "$LIBRARY" | jq -e '.success' >/dev/null 2>&1; then
    echo "✅ Library API working"
else
    echo "❌ Library API failed"
fi

# Test HR API
HR=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "http://localhost:8000/api/hr/staff")
if echo "$HR" | jq -e '.success' >/dev/null 2>&1; then
    echo "✅ HR API working"
else
    echo "❌ HR API failed"
fi

echo ""
echo "=== Test Complete ==="