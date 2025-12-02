#!/bin/bash

# Quick API Test Script
BASE_URL="http://localhost:8000/api"

echo "🎓 Educational ERP - Quick API Test"
echo "=================================="

# 1. Login and get token
echo -e "\n1. Testing Authentication..."
LOGIN_RESPONSE=$(curl -s -X POST -H "Content-Type: application/json" -H "Accept: application/json" -d '{
    "email": "principal@schoolerp.com",
    "password": "password"
}' "$BASE_URL/login")

TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token // empty' 2>/dev/null)

if [ -n "$TOKEN" ]; then
    echo "✅ Login successful"
    echo "Token: ${TOKEN:0:20}..."
else
    echo "❌ Login failed"
    echo "$LOGIN_RESPONSE"
    exit 1
fi

# 2. Test authenticated endpoints
echo -e "\n2. Testing Core APIs..."

# Get current user
echo -n "User Profile: "
USER_RESPONSE=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL/user")
if echo "$USER_RESPONSE" | jq -e '.success' >/dev/null 2>&1; then
    echo "✅ Working"
else
    echo "❌ Failed"
fi

# Get departments
echo -n "Departments: "
DEPT_RESPONSE=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL/departments")
if echo "$DEPT_RESPONSE" | jq -e '.success // .data' >/dev/null 2>&1; then
    echo "✅ Working"
else
    echo "❌ Failed"
fi

# Get students
echo -n "Students: "
STUDENT_RESPONSE=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL/students")
if echo "$STUDENT_RESPONSE" | jq -e '.success // .data' >/dev/null 2>&1; then
    echo "✅ Working"
else
    echo "❌ Failed"
fi

# Get reports models
echo -n "Report Models: "
REPORT_RESPONSE=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL/reports/models")
if echo "$REPORT_RESPONSE" | jq -e '.success // .data' >/dev/null 2>&1; then
    echo "✅ Working"
else
    echo "❌ Failed"
fi

# Get library books
echo -n "Library Books: "
LIBRARY_RESPONSE=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL/library/books")
if echo "$LIBRARY_RESPONSE" | jq -e '.success // .data' >/dev/null 2>&1; then
    echo "✅ Working"
else
    echo "❌ Failed"
fi

# Get HR staff
echo -n "HR Staff: "
HR_RESPONSE=$(curl -s -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL/hr/staff")
if echo "$HR_RESPONSE" | jq -e '.success // .data' >/dev/null 2>&1; then
    echo "✅ Working"
else
    echo "❌ Failed"
fi

echo -e "\n🎯 Quick API Test Complete!"
echo "=================================="
echo "✅ Authentication: Working"
echo "✅ Core APIs: Tested"
echo "🔗 Base URL: $BASE_URL"
echo "🔑 Token: Available"

echo -e "\n📋 Available Test Users:"
echo "• Principal: principal@schoolerp.com / password"
echo "• HOD Commerce: hod.commerce@schoolerp.com / password"
echo "• HOD Science: hod.science@schoolerp.com / password"
echo "• Class Teacher: class.teacher@schoolerp.com / password"
echo "• Accounts: accounts@schoolerp.com / password"