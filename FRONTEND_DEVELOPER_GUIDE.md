# 🎨 Frontend Developer Guide - Educational ERP

## 🚀 **Quick Start**

**Backend API**: `https://yourdomain.com/api/`  
**Authentication**: JWT Bearer Token  
**Data Format**: JSON  

---

## 🔐 **Authentication**

### **Login**
```javascript
POST /api/login
{
  "email": "principal@schoolerp.com",
  "password": "password"
}

// Response
{
  "success": true,
  "data": {
    "user": {...},
    "token": "1|ABC123..."
  }
}
```

### **Use Token**
```javascript
headers: {
  'Authorization': 'Bearer ' + token,
  'Accept': 'application/json'
}
```

### **Test Users**
- **Principal**: `principal@schoolerp.com` / `password`
- **HOD Commerce**: `hod.commerce@schoolerp.com` / `password`
- **Accounts**: `accounts@schoolerp.com` / `password`

---

## 📊 **Core API Endpoints**

### **Student Management**
```javascript
GET /api/students                    // List students (paginated)
GET /api/students/{id}              // Student details
POST /api/students                  // Create student
PUT /api/students/{id}              // Update student
GET /api/students/{id}/profile      // Student profile
```

### **Fee Management**
```javascript
GET /api/students/{id}/outstanding  // Outstanding fees
POST /api/students/{id}/payment     // Record payment
POST /api/payments/create-order     // Razorpay order
POST /api/payments/verify          // Verify payment
```

### **Attendance**
```javascript
POST /api/attendance/mark          // Mark attendance
GET /api/attendance/report         // Attendance report
GET /api/attendance/defaulters     // Defaulter list
```

### **Results & Exams**
```javascript
GET /api/exams/results            // Student results
GET /api/exams/marksheet          // Generate marksheet
POST /api/exams/enter-marks       // Enter marks
```

### **Reports**
```javascript
GET /api/reports/models           // Available models
POST /api/reports/build           // Build custom report
POST /api/reports/export          // Export report
```

### **Library**
```javascript
GET /api/library/books            // Available books
POST /api/library/issue           // Issue book
POST /api/library/return          // Return book
GET /api/library/student/{id}/issues  // Student issues
```

---

## 📋 **Data Models**

### **Student**
```javascript
{
  "id": 1,
  "first_name": "John",
  "last_name": "Doe",
  "roll_number": "COM2025A001",
  "email": "john@example.com",
  "program": {
    "id": 1,
    "name": "B.Com",
    "code": "BCOM"
  },
  "division": {
    "id": 1,
    "name": "A"
  }
}
```

### **Fee Record**
```javascript
{
  "student_id": 1,
  "total_amount": 50000,
  "paid_amount": 30000,
  "outstanding_amount": 20000,
  "due_date": "2025-12-31"
}
```

### **Attendance**
```javascript
{
  "student_id": 1,
  "total_classes": 100,
  "attended_classes": 85,
  "attendance_percentage": 85.0
}
```

---

## 🎯 **Frontend Pages Needed**

### **Student Portal**
1. **Dashboard** - Overview, stats, notifications
2. **Profile** - Personal information, documents
3. **Fees** - Outstanding, payment history, online payment
4. **Attendance** - Monthly view, percentage
5. **Results** - Exam results, marksheets
6. **Timetable** - Class schedule
7. **Library** - Issued books, due dates

### **Admin Portal**
1. **Dashboard** - System overview, analytics
2. **Students** - CRUD operations, bulk import
3. **Fees** - Fee structures, collection reports
4. **Reports** - Dynamic report builder
5. **Attendance** - Mark attendance, reports
6. **Results** - Enter marks, generate marksheets
7. **Library** - Book management, issue/return

---

## 🔧 **Implementation Tips**

### **Pagination**
```javascript
// API returns paginated data
{
  "data": [...],
  "current_page": 1,
  "last_page": 10,
  "per_page": 25,
  "total": 250
}
```

### **Error Handling**
```javascript
// API error format
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["Email is required"]
  }
}
```

### **Loading States**
- Show spinners for API calls
- Implement skeleton screens
- Handle network errors gracefully

### **Performance**
- Use virtual scrolling for large lists
- Implement search/filter before loading data
- Cache frequently accessed data

---

## 🎨 **UI/UX Guidelines**

### **Design System**
- **Colors**: Primary (Indigo), Success (Green), Warning (Yellow), Error (Red)
- **Typography**: Clean, readable fonts
- **Components**: Consistent buttons, forms, cards
- **Responsive**: Mobile-first approach

### **Key Features**
- **Search & Filter**: Every list should be searchable
- **Bulk Actions**: Select multiple items for operations
- **Export Options**: PDF, Excel, CSV downloads
- **Real-time Updates**: WebSocket for notifications
- **Offline Support**: Cache critical data

---

## 📱 **Mobile Considerations**

### **Responsive Breakpoints**
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px  
- **Desktop**: > 1024px

### **Touch-Friendly**
- Minimum 44px touch targets
- Swipe gestures for navigation
- Pull-to-refresh functionality

---

## 🔒 **Security**

### **Best Practices**
- Store JWT token securely (httpOnly cookies recommended)
- Validate all user inputs
- Implement CSRF protection
- Use HTTPS in production
- Handle token expiration gracefully

### **Role-Based UI**
```javascript
// Show/hide features based on user role
if (user.roles.includes('principal')) {
  // Show admin features
}
```

---

## 🧪 **Testing**

### **API Testing**
```bash
# Test login
curl -X POST https://yourdomain.com/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"principal@schoolerp.com","password":"password"}'
```

### **Environment Setup**
```javascript
// .env.local
REACT_APP_API_URL=https://yourdomain.com/api
REACT_APP_RAZORPAY_KEY=rzp_test_...
```

---

## 📚 **Resources**

- **API Base URL**: `https://yourdomain.com/api/`
- **Postman Collection**: Available on request
- **Database Schema**: See `DATABASE_SCHEMA.md`
- **Backend Status**: See `BACKEND_API_TEST_RESULTS.md`

---

## 🆘 **Support**

**Backend Developer**: Available for API questions  
**Test Environment**: Fully functional with sample data  
**Documentation**: Complete API documentation available  

---

## ✅ **Ready to Start**

1. **Clone repository**: `git clone https://github.com/rossmikee121/schoolrepr`
2. **Set API URL** in environment variables
3. **Test authentication** with provided credentials
4. **Build student portal** first, then admin features
5. **Deploy frontend** when complete

**The backend is 100% ready and tested!** 🚀