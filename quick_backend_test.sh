#!/bin/bash

# 🧪 Quick Backend Test - GitHub Codespaces
# Educational ERP System - Fast Validation Script

set -e  # Exit on error

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
BASE_URL="${API_URL:-http://localhost:8000}"
API_BASE="$BASE_URL/api"

# Test counters
TOTAL=0
PASSED=0
FAILED=0

# =====================================
# Utility Functions
# =====================================

test_endpoint() {
    local name=$1
    local method=$2
    local endpoint=$3
    local data=$4
    local expected_status=${5:-200}
    
    ((TOTAL++))
    echo -e "\n${BLUE}[$TOTAL] Testing: $name${NC}"
    
    if [ "$method" = "GET" ]; then
        RESPONSE=$(curl -s -w "\n%{http_code}" \
            -H "Authorization: Bearer $TOKEN" \
            -H "Accept: application/json" \
            "$API_BASE$endpoint")
    else
        RESPONSE=$(curl -s -w "\n%{http_code}" \
            -X $method \
            -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            -d "$data" \
            "$API_BASE$endpoint")
    fi
    
    HTTP_BODY=$(echo "$RESPONSE" | sed '$d')
    HTTP_STATUS=$(echo "$RESPONSE" | tail -n1)
    
    if [ "$HTTP_STATUS" -eq "$expected_status" ]; then
        echo -e "${GREEN}✅ PASS${NC} (HTTP $HTTP_STATUS)"
        ((PASSED++))
        echo "$HTTP_BODY" | jq '.' 2>/dev/null | head -5 || echo "$HTTP_BODY" | head -5
        echo "$HTTP_BODY"
        return 0
    else
        echo -e "${RED}❌ FAIL${NC} (Expected $expected_status, got $HTTP_STATUS)"
        ((FAILED++))
        echo "$HTTP_BODY" | jq '.' 2>/dev/null || echo "$HTTP_BODY"
        return 1
    fi
}

extract_id() {
    echo "$1" | jq -r '.data.id // .id // empty'
}

# =====================================
# Pre-flight Checks
# =====================================

clear
echo -e "${BLUE}╔════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║   🧪 Educational ERP - Quick Backend Test    ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${YELLOW}⏱️  Start Time: $(date '+%H:%M:%S')${NC}"
echo -e "${YELLOW}🔗 Base URL: $BASE_URL${NC}"
echo ""

# Check if Laravel is running
echo -e "${BLUE}🔍 Checking if Laravel server is running...${NC}"
if ! curl -sf "$BASE_URL" > /dev/null 2>&1; then
    echo -e "${RED}❌ Laravel is not running!${NC}"
    echo ""
    echo -e "${YELLOW}Start Laravel with:${NC}"
    echo "  php artisan serve --host=0.0.0.0 --port=8000"
    echo ""
    exit 1
fi
echo -e "${GREEN}✅ Laravel is running${NC}"

# =====================================
# PHASE 1: Authentication
# =====================================

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PHASE 1: Authentication${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Test login
LOGIN_RESPONSE=$(test_endpoint "Login as Principal" "POST" "/login" \
    '{"email":"principal@schoolerp.com","password":"password"}')

TOKEN=$(echo "$LOGIN_RESPONSE" | jq -r '.data.token // .token // empty')

if [ -z "$TOKEN" ]; then
    echo -e "${RED}❌ CRITICAL: Login failed. Cannot continue tests.${NC}"
    echo -e "${YELLOW}Run database seeders:${NC}"
    echo "  php artisan migrate:fresh --seed"
    exit 1
fi

echo -e "${GREEN}🔑 Token acquired: ${TOKEN:0:30}...${NC}"

# =====================================
# PHASE 2: Get Dynamic IDs
# =====================================

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PHASE 2: Fetch Required Data${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Get programs
PROGRAMS_RESPONSE=$(test_endpoint "Get Programs" "GET" "/programs")
PROGRAM_ID=$(echo "$PROGRAMS_RESPONSE" | jq -r '.data[0].id // empty')

# Get divisions
DIVISIONS_RESPONSE=$(test_endpoint "Get Divisions" "GET" "/divisions")
DIVISION_ID=$(echo "$DIVISIONS_RESPONSE" | jq -r '.data[0].id // empty')

# Get academic sessions
SESSIONS_RESPONSE=$(test_endpoint "Get Academic Sessions" "GET" "/academic-sessions")
SESSION_ID=$(echo "$SESSIONS_RESPONSE" | jq -r '.data[0].id // empty')

if [ -z "$PROGRAM_ID" ] || [ -z "$DIVISION_ID" ] || [ -z "$SESSION_ID" ]; then
    echo -e "${RED}❌ CRITICAL: Missing required data${NC}"
    echo "Program ID: $PROGRAM_ID"
    echo "Division ID: $DIVISION_ID"
    echo "Session ID: $SESSION_ID"
    echo ""
    echo -e "${YELLOW}Run database seeders:${NC}"
    echo "  php artisan migrate:fresh --seed"
    exit 1
fi

echo -e "${GREEN}📚 Program ID: $PROGRAM_ID${NC}"
echo -e "${GREEN}📂 Division ID: $DIVISION_ID${NC}"
echo -e "${GREEN}📅 Session ID: $SESSION_ID${NC}"

# =====================================
# PHASE 3: Student Management
# =====================================

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PHASE 3: Student Management${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Create student
TIMESTAMP=$(date +%s)
STUDENT_DATA="{
    \"first_name\": \"TestStudent\",
    \"last_name\": \"AutoTest\",
    \"date_of_birth\": \"2005-05-15\",
    \"gender\": \"male\",
    \"email\": \"test.student.$TIMESTAMP@test.com\",
    \"mobile_number\": \"9820$TIMESTAMP\",
    \"program_id\": $PROGRAM_ID,
    \"division_id\": $DIVISION_ID,
    \"academic_session_id\": $SESSION_ID,
    \"academic_year\": \"2025-26\",
    \"category\": \"general\",
    \"admission_date\": \"2025-06-01\"
}"

STUDENT_RESPONSE=$(test_endpoint "Create Student" "POST" "/students" "$STUDENT_DATA" 201)
STUDENT_ID=$(extract_id "$STUDENT_RESPONSE")

if [ -n "$STUDENT_ID" ]; then
    echo -e "${GREEN}🎓 Student created with ID: $STUDENT_ID${NC}"
    
    # Get student details
    test_endpoint "Get Student Details" "GET" "/students/$STUDENT_ID"
    
    # Update student
    UPDATE_DATA='{"mobile_number":"9999999999"}'
    test_endpoint "Update Student" "PUT" "/students/$STUDENT_ID" "$UPDATE_DATA"
fi

# List all students
test_endpoint "List All Students" "GET" "/students"

# =====================================
# PHASE 4: Fee Management
# =====================================

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PHASE 4: Fee Management${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [ -n "$STUDENT_ID" ]; then
    # Check outstanding fees
    test_endpoint "Get Outstanding Fees" "GET" "/students/$STUDENT_ID/outstanding"
    
    # Record payment
    PAYMENT_DATA="{
        \"amount\": 5000,
        \"payment_method\": \"cash\",
        \"payment_date\": \"2025-12-01\",
        \"remarks\": \"Test payment\"
    }"
    test_endpoint "Record Fee Payment" "POST" "/students/$STUDENT_ID/payment" "$PAYMENT_DATA"
fi

# =====================================
# PHASE 5: Attendance
# =====================================

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PHASE 5: Attendance Management${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

if [ -n "$STUDENT_ID" ]; then
    # Mark attendance
    ATTENDANCE_DATA="{
        \"student_id\": $STUDENT_ID,
        \"division_id\": $DIVISION_ID,
        \"attendance_date\": \"2025-12-01\",
        \"attendance\": [
            {\"subject_id\": 1, \"period\": 1, \"status\": \"present\"}
        ]
    }"
    test_endpoint "Mark Attendance" "POST" "/attendance/mark" "$ATTENDANCE_DATA"
fi

# Get attendance report
test_endpoint "Get Attendance Report" "GET" "/attendance/report?division_id=$DIVISION_ID&from_date=2025-12-01&to_date=2025-12-01"

# =====================================
# PHASE 6: Examinations
# =====================================

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PHASE 6: Examination System${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Get examinations
test_endpoint "Get Examinations" "GET" "/exams"

# Get results (if available)
test_endpoint "Get Results" "GET" "/exams/results?division_id=$DIVISION_ID"

# =====================================
# PHASE 7: Reporting
# =====================================

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PHASE 7: Dynamic Reporting${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Get available report models
test_endpoint "Get Report Models" "GET" "/reports/models"

# =====================================
# PHASE 8: Library
# =====================================

echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${YELLOW}PHASE 8: Library Management${NC}"
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"

# Get library books
test_endpoint "Get Library Books" "GET" "/library/books"

# =====================================
# Final Summary
# =====================================

echo ""
echo ""
echo -e "${BLUE}╔════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║              TEST RESULTS SUMMARY              ║${NC}"
echo -e "${BLUE}╚════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${GREEN}✅ Total Tests: $TOTAL${NC}"
echo -e "${GREEN}✅ Passed: $PASSED${NC}"
echo -e "${RED}❌ Failed: $FAILED${NC}"
echo ""

if [ $TOTAL -gt 0 ]; then
    SUCCESS_RATE=$((PASSED * 100 / TOTAL))
    echo -e "${BLUE}📊 Success Rate: $SUCCESS_RATE%${NC}"
    echo ""
    
    if [ $SUCCESS_RATE -ge 90 ]; then
        echo -e "${GREEN}🎉 EXCELLENT! Backend is production ready!${NC}"
    elif [ $SUCCESS_RATE -ge 75 ]; then
        echo -e "${YELLOW}⚠️  GOOD, but needs some fixes${NC}"
    else
        echo -e "${RED}❌ CRITICAL ISSUES - Fix before proceeding${NC}"
    fi
fi

echo ""
echo -e "${YELLOW}⏱️  End Time: $(date '+%H:%M:%S')${NC}"
echo ""
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Next Steps:${NC}"
echo -e "  1. Review failed tests above"
echo -e "  2. Check Laravel logs: tail -f storage/logs/laravel.log"
echo -e "  3. Fix issues and re-run this script"
echo -e "  4. Once 100% pass, run comprehensive test script"
echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"