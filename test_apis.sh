#!/bin/bash

# Educational ERP API Testing Script
# Tests all major API endpoints

BASE_URL="http://localhost:8000/api"
TOKEN=""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🎓 Educational ERP API Testing${NC}"
echo "=================================="

# Function to make API calls
api_call() {
    local method=$1
    local endpoint=$2
    local data=$3
    local description=$4
    
    echo -e "\n${YELLOW}Testing: $description${NC}"
    echo "Endpoint: $method $endpoint"
    
    if [ "$method" = "GET" ]; then
        if [ -n "$TOKEN" ]; then
            response=$(curl -s -w "\nHTTP_CODE:%{http_code}" -H "Authorization: Bearer $TOKEN" -H "Accept: application/json" "$BASE_URL$endpoint")
        else
            response=$(curl -s -w "\nHTTP_CODE:%{http_code}" -H "Accept: application/json" "$BASE_URL$endpoint")
        fi
    else
        if [ -n "$TOKEN" ]; then
            response=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X $method -H "Authorization: Bearer $TOKEN" -H "Content-Type: application/json" -H "Accept: application/json" -d "$data" "$BASE_URL$endpoint")
        else
            response=$(curl -s -w "\nHTTP_CODE:%{http_code}" -X $method -H "Content-Type: application/json" -H "Accept: application/json" -d "$data" "$BASE_URL$endpoint")
        fi
    fi
    
    http_code=$(echo "$response" | grep "HTTP_CODE:" | cut -d: -f2)
    body=$(echo "$response" | sed '/HTTP_CODE:/d')
    
    if [ "$http_code" -ge 200 ] && [ "$http_code" -lt 300 ]; then
        echo -e "${GREEN}✅ SUCCESS (HTTP $http_code)${NC}"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    else
        echo -e "${RED}❌ FAILED (HTTP $http_code)${NC}"
        echo "$body" | jq '.' 2>/dev/null || echo "$body"
    fi
}

# Test 1: Authentication
echo -e "\n${BLUE}1. AUTHENTICATION TESTS${NC}"
echo "========================"

# Create test user first (this might fail if user exists)
api_call "POST" "/register" '{
    "name": "Test Admin",
    "email": "admin@test.com",
    "password": "password123",
    "password_confirmation": "password123"
}' "User Registration"

# Login
login_response=$(curl -s -X POST -H "Content-Type: application/json" -H "Accept: application/json" -d '{
    "email": "admin@test.com",
    "password": "password123"
}' "$BASE_URL/login")

echo -e "\n${YELLOW}Testing: User Login${NC}"
echo "Endpoint: POST /login"
echo "$login_response" | jq '.' 2>/dev/null || echo "$login_response"

# Extract token
TOKEN=$(echo "$login_response" | jq -r '.data.token // empty' 2>/dev/null)
if [ -n "$TOKEN" ] && [ "$TOKEN" != "null" ]; then
    echo -e "${GREEN}✅ Login successful, token extracted${NC}"
else
    echo -e "${RED}❌ Login failed or no token received${NC}"
    # Try with default credentials
    echo -e "\n${YELLOW}Trying with default credentials...${NC}"
    login_response=$(curl -s -X POST -H "Content-Type: application/json" -H "Accept: application/json" -d '{
        "email": "admin@example.com",
        "password": "password"
    }' "$BASE_URL/login")
    TOKEN=$(echo "$login_response" | jq -r '.data.token // empty' 2>/dev/null)
fi

# Test authenticated user endpoint
api_call "GET" "/user" "" "Get Current User"

# Test 2: Department Management
echo -e "\n${BLUE}2. DEPARTMENT MANAGEMENT${NC}"
echo "========================="

api_call "GET" "/departments" "" "Get All Departments"

api_call "POST" "/departments" '{
    "name": "Test Department",
    "code": "TEST",
    "description": "Test Department Description"
}' "Create Department"

# Test 3: Student Management
echo -e "\n${BLUE}3. STUDENT MANAGEMENT${NC}"
echo "====================="

api_call "GET" "/students" "" "Get All Students"

api_call "POST" "/students" '{
    "first_name": "John",
    "last_name": "Doe",
    "date_of_birth": "2000-01-01",
    "gender": "male",
    "program_id": 1,
    "academic_year": "2025-26",
    "division_id": 1,
    "academic_session_id": 1,
    "admission_date": "2025-06-01",
    "email": "john.doe@student.com"
}' "Create Student"

# Test 4: Fee Management
echo -e "\n${BLUE}4. FEE MANAGEMENT${NC}"
echo "=================="

api_call "POST" "/fees/assign" '{
    "student_id": 1,
    "fee_structure_id": 1,
    "academic_year": "2025-26"
}' "Assign Fees to Student"

api_call "GET" "/students/1/outstanding" "" "Get Student Outstanding Fees"

# Test 5: Lab Management
echo -e "\n${BLUE}5. LAB MANAGEMENT${NC}"
echo "=================="

api_call "POST" "/labs/create-batches" '{
    "lab_id": 1,
    "division_id": 1,
    "batch_size": 25,
    "academic_year": "2025-26"
}' "Create Lab Batches"

api_call "GET" "/labs/sessions" "" "Get Lab Sessions"

# Test 6: Reports
echo -e "\n${BLUE}6. REPORTING SYSTEM${NC}"
echo "==================="

api_call "GET" "/reports/models" "" "Get Available Report Models"

api_call "POST" "/reports/build" '{
    "base_model": "Student",
    "columns": [
        {"field": "first_name", "alias": "First Name"},
        {"field": "last_name", "alias": "Last Name"},
        {"field": "roll_number", "alias": "Roll Number"}
    ],
    "limit": 10
}' "Build Custom Report"

# Test 7: Library Management
echo -e "\n${BLUE}7. LIBRARY MANAGEMENT${NC}"
echo "====================="

api_call "GET" "/library/books" "" "Get Library Books"

api_call "POST" "/library/issue" '{
    "book_id": 1,
    "student_id": 1,
    "due_date": "2025-12-31"
}' "Issue Book to Student"

# Test 8: HR Management
echo -e "\n${BLUE}8. HR MANAGEMENT${NC}"
echo "================"

api_call "GET" "/hr/staff" "" "Get Staff List"

api_call "GET" "/hr/salary-structures" "" "Get Salary Structures"

# Test 9: Attendance
echo -e "\n${BLUE}9. ATTENDANCE MANAGEMENT${NC}"
echo "========================"

api_call "POST" "/attendance/mark" '{
    "student_id": 1,
    "subject_id": 1,
    "date": "2025-12-01",
    "status": "present"
}' "Mark Student Attendance"

api_call "GET" "/attendance/report?student_id=1" "" "Get Attendance Report"

# Test 10: Results & Examinations
echo -e "\n${BLUE}10. RESULTS & EXAMINATIONS${NC}"
echo "=========================="

api_call "POST" "/exams/enter-marks" '{
    "student_id": 1,
    "subject_id": 1,
    "examination_id": 1,
    "marks_obtained": 85,
    "total_marks": 100
}' "Enter Student Marks"

api_call "GET" "/exams/results?student_id=1" "" "Get Student Results"

# Summary
echo -e "\n${BLUE}🎯 API TESTING COMPLETE${NC}"
echo "========================"
echo -e "${GREEN}✅ All major API endpoints tested${NC}"
echo -e "${YELLOW}📊 Check the results above for any failures${NC}"
echo -e "${BLUE}🔗 Base URL: $BASE_URL${NC}"

if [ -n "$TOKEN" ]; then
    echo -e "${GREEN}🔑 Authentication Token: ${TOKEN:0:20}...${NC}"
else
    echo -e "${RED}❌ No authentication token available${NC}"
fi