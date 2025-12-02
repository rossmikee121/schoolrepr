# 🎓 School ERP System - Complete Intern Guide

## 📚 Table of Contents
1. [System Overview](#system-overview)
2. [Architecture Explanation](#architecture-explanation)
3. [Backend Structure (Laravel)](#backend-structure-laravel)
4. [Frontend Structure (React)](#frontend-structure-react)
5. [Database Structure](#database-structure)
6. [API Endpoints](#api-endpoints)
7. [Key Files Explained](#key-files-explained)
8. [How Data Flows](#how-data-flows)
9. [Development Workflow](#development-workflow)
10. [Common Tasks](#common-tasks)

---

## 🎯 System Overview

### What is this System?
This is a **complete Educational ERP (Enterprise Resource Planning) system** for Indian colleges and universities. It manages:

- **Students**: Admissions, profiles, academic records
- **Fees**: Collection, scholarships, online payments
- **Attendance**: Daily tracking, reports, defaulters
- **Examinations**: Marks entry, results, marksheets
- **Laboratory**: Batch management, practical sessions
- **Library**: Book lending, returns, fines
- **HR**: Staff management, payroll
- **Reports**: Dynamic report builder, exports

### Who Uses This System?
- **Students**: Check fees, attendance, results
- **Teachers**: Mark attendance, enter marks
- **HODs**: Manage departments, approve results
- **Accounts**: Handle fee collection, reports
- **Principal**: Overall management, analytics

---

## 🏗️ Architecture Explanation

### High-Level Architecture
```
┌─────────────────┐    HTTP/JSON    ┌─────────────────┐
│   React Frontend │ ◄──────────────► │ Laravel Backend │
│   (Student Portal)│                 │   (API Server)  │
└─────────────────┘                 └─────────────────┘
                                             │
                                             ▼
                                    ┌─────────────────┐
                                    │ PostgreSQL DB   │
                                    │ (Data Storage)  │
                                    └─────────────────┘
```

### Technology Stack

**Backend (API Server)**
- **Laravel 12**: PHP framework for building APIs
- **PostgreSQL**: Database for storing all data
- **JWT Authentication**: Secure token-based login
- **Sanctum**: Laravel's authentication system

**Frontend (Student Portal)**
- **React 18**: JavaScript library for user interfaces
- **TypeScript**: Type-safe JavaScript
- **Tailwind CSS**: Utility-first CSS framework
- **Axios**: HTTP client for API calls

**Additional Tools**
- **Redis**: Caching for better performance
- **Razorpay**: Online payment gateway
- **Composer**: PHP package manager
- **npm**: JavaScript package manager

---

## 🔧 Backend Structure (Laravel)

### Directory Structure
```
schoolerp/
├── app/
│   ├── Http/Controllers/Api/     # API endpoint handlers
│   ├── Models/                   # Database table representations
│   ├── Services/                 # Business logic
│   └── Middleware/               # Request filtering
├── database/
│   ├── migrations/               # Database structure definitions
│   ├── seeders/                  # Sample data generators
│   └── factories/                # Test data generators
├── routes/
│   └── api.php                   # API endpoint definitions
└── config/                       # System configuration
```

### Key Backend Concepts

**1. MVC Pattern**
- **Model**: Represents database tables (Student.php, Fee.php)
- **View**: JSON responses (no traditional views in API)
- **Controller**: Handles HTTP requests (StudentController.php)

**2. Database Relationships**
```php
// Student belongs to Program
Student -> Program (B.Com, B.Sc, MBA)

// Student belongs to Division  
Student -> Division (FY-A, SY-B, TY-C)

// Student has many Fees
Student -> Fees (multiple fee records)

// Student has many Guardians
Student -> Guardians (parents/guardians)
```

**3. API Structure**
```
GET    /api/students           # List all students
POST   /api/students           # Create new student
GET    /api/students/{id}      # Get specific student
PUT    /api/students/{id}      # Update student
DELETE /api/students/{id}      # Delete student
```

---

## ⚛️ Frontend Structure (React)

### Directory Structure
```
student-portal/
├── src/
│   ├── components/              # Reusable UI components
│   ├── pages/                   # Full page components
│   ├── hooks/                   # Custom React hooks
│   ├── services/                # API communication
│   └── types/                   # TypeScript type definitions
├── public/                      # Static files
└── package.json                 # Dependencies and scripts
```

### Key Frontend Concepts

**1. Component-Based Architecture**
```jsx
App.tsx                    // Main application
├── Login.tsx             // Login page
├── Dashboard.tsx         // Main dashboard
└── Layout.tsx           // Common layout (header, sidebar)
```

**2. State Management**
- **React Hooks**: useState, useEffect for local state
- **Context API**: useAuth for global authentication state
- **Props**: Data passed between components

**3. API Integration**
```javascript
// Making API calls
const response = await api.get('/students');
const students = response.data.data;

// With authentication (automatic)
const token = localStorage.getItem('auth_token');
// Token automatically added to all requests
```

---

## 🗄️ Database Structure

### Core Tables

**1. Users Table**
```sql
users
├── id (Primary Key)
├── name
├── email (Unique)
├── password (Hashed)
└── created_at, updated_at
```

**2. Students Table**
```sql
students
├── id (Primary Key)
├── user_id (Foreign Key -> users.id)
├── admission_number (Unique)
├── roll_number (Unique)
├── first_name, middle_name, last_name
├── program_id (Foreign Key -> programs.id)
├── division_id (Foreign Key -> divisions.id)
├── student_status (active, graduated, dropped)
└── created_at, updated_at, deleted_at
```

**3. Programs Table**
```sql
programs
├── id (Primary Key)
├── name (B.Com, B.Sc, MBA, etc.)
├── code (BCOM, BSC, MBA)
├── duration_years (3, 2, etc.)
└── department_id (Foreign Key)
```

### Relationship Examples
```sql
-- Get student with program name
SELECT s.first_name, s.last_name, p.name as program_name
FROM students s
JOIN programs p ON s.program_id = p.id
WHERE s.id = 1;

-- Get all students in B.Com program
SELECT s.*
FROM students s
JOIN programs p ON s.program_id = p.id
WHERE p.code = 'BCOM';
```

---

## 🔌 API Endpoints

### Authentication Endpoints
```
POST /api/login          # User login
POST /api/logout         # User logout
GET  /api/user           # Get current user info
```

### Student Management
```
GET    /api/students                    # List students (with filters)
POST   /api/students                    # Create new student
GET    /api/students/{id}               # Get student details
PUT    /api/students/{id}               # Update student
DELETE /api/students/{id}               # Delete student
GET    /api/students/{id}/profile       # Get complete profile
```

### Fee Management
```
POST /api/fees/assign                   # Assign fees to students
GET  /api/students/{id}/outstanding     # Get pending fees
POST /api/students/{id}/payment         # Record payment
POST /api/payments/create-order         # Create online payment
POST /api/payments/verify               # Verify payment
```

### Reports
```
GET  /api/reports/outstanding           # Outstanding fees report
GET  /api/reports/collection            # Fee collection report
GET  /api/reports/defaulters            # Fee defaulters report
POST /api/reports/build                 # Build custom report
POST /api/reports/export                # Export report
```

---

## 📁 Key Files Explained

### Backend Files

**1. routes/api.php**
- **Purpose**: Defines all API endpoints
- **What it does**: Maps URLs to controller methods
- **Example**: `GET /api/students` → `StudentController@index`

**2. app/Http/Controllers/Api/StudentController.php**
- **Purpose**: Handles student-related API requests
- **Methods**: index(), store(), show(), update(), destroy()
- **What it does**: Processes HTTP requests, validates data, returns JSON

**3. app/Models/User/Student.php**
- **Purpose**: Represents students table in code
- **What it does**: Defines relationships, scopes, accessors
- **Example**: `$student->program->name` gets program name

**4. app/Services/FeeCalculationService.php**
- **Purpose**: Complex business logic for fee calculations
- **What it does**: Calculates fees with scholarships and discounts
- **Why separate**: Reusable across multiple controllers

**5. database/migrations/create_students_table.php**
- **Purpose**: Defines database table structure
- **What it does**: Creates students table with all columns
- **When run**: During `php artisan migrate`

### Frontend Files

**1. src/App.tsx**
- **Purpose**: Main React application component
- **What it does**: Sets up routing and authentication
- **Key features**: Route protection, loading states

**2. src/hooks/useAuth.ts**
- **Purpose**: Authentication state management
- **What it does**: Handles login, logout, token storage
- **Key features**: Persistent login, automatic logout on errors

**3. src/services/api.ts**
- **Purpose**: HTTP client configuration
- **What it does**: Configures Axios for API calls
- **Key features**: Automatic token attachment, error handling

**4. src/pages/Login.tsx**
- **Purpose**: User login interface
- **What it does**: Login form, calls authentication API
- **Flow**: Form submit → API call → Store token → Redirect

**5. src/pages/Dashboard.tsx**
- **Purpose**: Main dashboard after login
- **What it does**: Shows student information, navigation
- **Data source**: Fetches from various API endpoints

---

## 🔄 How Data Flows

### Student Login Flow
```
1. User enters email/password in Login.tsx
2. Form calls useAuth.login() function
3. useAuth makes POST /api/login request
4. Laravel AuthController validates credentials
5. If valid, returns JWT token + user data
6. Frontend stores token in localStorage
7. User redirected to Dashboard.tsx
8. Dashboard makes API calls with token
```

### Creating a Student Flow
```
1. Admin fills student form in frontend
2. Form data sent to POST /api/students
3. StudentController.store() receives request
4. Validates input data (required fields, formats)
5. Creates User account for student login
6. Generates admission number and roll number
7. Creates Student record in database
8. Returns success response with student data
9. Frontend shows success message
```

### Fee Payment Flow
```
1. Student views outstanding fees
2. Clicks "Pay Online" button
3. Frontend calls POST /api/payments/create-order
4. Backend creates Razorpay payment order
5. Frontend opens Razorpay payment gateway
6. Student completes payment
7. Razorpay calls webhook /api/webhooks/razorpay
8. Backend verifies payment and updates records
9. Student sees payment confirmation
```

---

## 💻 Development Workflow

### Setting Up Development Environment

**1. Backend Setup**
```bash
cd schoolerp
composer install                    # Install PHP dependencies
cp .env.example .env               # Copy environment file
php artisan key:generate           # Generate app key
php artisan migrate:fresh --seed   # Create database with sample data
php artisan serve                  # Start development server
```

**2. Frontend Setup**
```bash
cd student-portal
npm install                        # Install JavaScript dependencies
npm start                         # Start development server
```

### Making Changes

**Adding a New API Endpoint**
1. Add route in `routes/api.php`
2. Create/update controller method
3. Test with Postman or browser
4. Update frontend to use new endpoint

**Adding a New Database Table**
1. Create migration: `php artisan make:migration create_table_name`
2. Define table structure in migration file
3. Run migration: `php artisan migrate`
4. Create model: `php artisan make:model ModelName`
5. Define relationships in model

**Adding a New Frontend Page**
1. Create component in `src/pages/`
2. Add route in `App.tsx`
3. Create API calls in component
4. Style with Tailwind CSS

---

## 🛠️ Common Tasks

### Database Tasks
```bash
# Create new migration
php artisan make:migration create_subjects_table

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset database with fresh data
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status
```

### API Testing
```bash
# Test login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"principal@schoolerp.com","password":"password"}'

# Test protected endpoint (replace TOKEN)
curl -X GET http://localhost:8000/api/students \
  -H "Authorization: Bearer TOKEN"
```

### Frontend Development
```bash
# Start development server
npm start

# Build for production
npm run build

# Run tests
npm test

# Check TypeScript errors
npm run type-check
```

### Debugging Tips

**Backend Debugging**
- Check Laravel logs: `storage/logs/laravel.log`
- Use `dd($variable)` to dump and die
- Use `Log::info('Debug message', $data)` for logging
- Check database queries with Laravel Debugbar

**Frontend Debugging**
- Use browser Developer Tools (F12)
- Check Network tab for API calls
- Use `console.log()` for debugging
- Check React Developer Tools extension

**Database Debugging**
- Use `php artisan tinker` for interactive queries
- Check database with GUI tools (phpMyAdmin, pgAdmin)
- Use `DB::enableQueryLog()` to see SQL queries

---

## 🎯 Learning Path for Interns

### Week 1: Understanding the System
1. Read this guide completely
2. Set up development environment
3. Explore the database structure
4. Test API endpoints with Postman
5. Navigate through the frontend

### Week 2: Backend Deep Dive
1. Study Laravel MVC pattern
2. Understand Eloquent relationships
3. Trace through StudentController methods
4. Learn about migrations and seeders
5. Practice creating simple API endpoints

### Week 3: Frontend Deep Dive
1. Study React component structure
2. Understand hooks and state management
3. Learn how API calls work
4. Practice creating simple components
5. Understand TypeScript basics

### Week 4: Full Stack Features
1. Add a new field to student table
2. Update API to handle new field
3. Update frontend forms
4. Test end-to-end functionality
5. Learn about authentication flow

### Week 5+: Advanced Topics
1. Complex database queries
2. Report generation
3. File uploads
4. Email notifications
5. Performance optimization

---

## 📞 Getting Help

### Resources
- **Laravel Documentation**: https://laravel.com/docs
- **React Documentation**: https://react.dev
- **PostgreSQL Documentation**: https://postgresql.org/docs
- **Tailwind CSS**: https://tailwindcss.com/docs

### Common Issues
1. **Database connection errors**: Check .env file settings
2. **API 401 errors**: Check authentication token
3. **CORS errors**: Check Laravel CORS configuration
4. **Migration errors**: Check database permissions
5. **Frontend build errors**: Check Node.js version compatibility

### Best Practices
- Always test API endpoints before frontend integration
- Use meaningful variable and function names
- Add comments for complex business logic
- Follow Laravel and React conventions
- Keep security in mind (validate inputs, sanitize data)

---

**Happy Learning! 🚀**

This system is a complete real-world application that demonstrates modern web development practices. Take your time to understand each part, and don't hesitate to experiment and ask questions!