/**
 * =============================================================================
 * API SERVICE - HTTP Client for Backend Communication
 * =============================================================================
 * 
 * This service configures Axios HTTP client for communicating with the Laravel backend.
 * It handles authentication, request/response interceptors, and error handling.
 * 
 * KEY FEATURES:
 * - Automatic JWT token attachment to requests
 * - Automatic logout on authentication errors
 * - Centralized API configuration
 * - Request/Response interceptors for common functionality
 * 
 * BACKEND CONNECTION:
 * - Connects to Laravel API at /api/* endpoints
 * - Uses JWT tokens for authentication
 * - Handles JSON request/response format
 * 
 * USAGE IN COMPONENTS:
 * import api from '../services/api';
 * const response = await api.get('/students');
 * const data = await api.post('/students', studentData);
 * 
 * FOR INTERNS:
 * - Axios = Popular HTTP client library for JavaScript
 * - Interceptors = Code that runs before/after every API request
 * - JWT Bearer Token = Standard way to send authentication tokens
 * - Base URL = Common prefix for all API endpoints
 * =============================================================================
 */

import axios from 'axios';

/**
 * API BASE URL CONFIGURATION
 * 
 * Determines the backend server URL based on environment.
 * 
 * ENVIRONMENTS:
 * - Development: http://localhost:8000/api (Laravel dev server)
 * - Production: Set via REACT_APP_API_URL environment variable
 * 
 * EXAMPLES:
 * - Local development: http://localhost:8000/api
 * - Production: https://schoolerp.com/api
 */
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

/**
 * AXIOS INSTANCE CREATION
 * 
 * Creates a configured Axios instance with default settings.
 * All API calls in the app will use this configured instance.
 * 
 * CONFIGURATION:
 * - baseURL: Prefix for all requests (e.g., /students becomes /api/students)
 * - Content-Type: Tells server we're sending JSON data
 */
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',  // All requests send JSON
  },
});

/**
 * REQUEST INTERCEPTOR - Automatic Authentication
 * 
 * This interceptor runs BEFORE every API request is sent.
 * It automatically adds the JWT token to the request headers.
 * 
 * PROCESS:
 * 1. Get JWT token from localStorage
 * 2. If token exists, add it to Authorization header
 * 3. Send request with token attached
 * 
 * HEADER FORMAT:
 * Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
 * 
 * WHY THIS IS USEFUL:
 * - No need to manually add token to every API call
 * - Consistent authentication across all requests
 * - Laravel backend automatically validates this token
 * 
 * BACKEND VALIDATION:
 * - Laravel Sanctum middleware checks this token
 * - If valid, request proceeds
 * - If invalid/missing, returns 401 Unauthorized
 */
api.interceptors.request.use(
  (config) => {
    // Get stored JWT token from localStorage
    const token = localStorage.getItem('auth_token');
    
    // If token exists, add it to request headers
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    
    // Return modified config to proceed with request
    return config;
  },
  (error) => {
    // If error occurs in request setup, reject the promise
    return Promise.reject(error);
  }
);

/**
 * RESPONSE INTERCEPTOR - Automatic Error Handling
 * 
 * This interceptor runs AFTER every API response is received.
 * It handles common errors, especially authentication failures.
 * 
 * PROCESS:
 * 1. Check if response has 401 Unauthorized status
 * 2. If yes, user's token is invalid/expired
 * 3. Clear stored authentication data
 * 4. Redirect to login page
 * 
 * ERROR CODES HANDLED:
 * - 401 Unauthorized: Token invalid/expired, force logout
 * 
 * WHY THIS IS USEFUL:
 * - Automatic logout when token expires
 * - Consistent error handling across all API calls
 * - User doesn't get stuck with invalid token
 * - Seamless redirect to login page
 * 
 * SCENARIOS WHERE 401 OCCURS:
 * - JWT token has expired
 * - Token was manually deleted from backend
 * - User account was deactivated
 * - Token format is invalid
 */
api.interceptors.response.use(
  // SUCCESS RESPONSE: Just pass through unchanged
  (response) => response,
  
  // ERROR RESPONSE: Handle authentication errors
  (error) => {
    // Check if error is 401 Unauthorized
    if (error.response?.status === 401) {
      // Token is invalid/expired - force logout
      
      // Clear stored authentication data
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      
      // Redirect to login page
      // Using window.location for immediate redirect
      window.location.href = '/login';
    }
    
    // Re-throw error for component to handle
    return Promise.reject(error);
  }
);

// Export configured Axios instance for use throughout the app
export default api;

/**
 * =============================================================================
 * API SERVICE SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * WHAT THIS SERVICE DOES:
 * 1. Configures HTTP client for backend communication
 * 2. Automatically adds JWT tokens to requests
 * 3. Handles authentication errors automatically
 * 4. Provides centralized API configuration
 * 
 * HOW TO USE IN COMPONENTS:
 * 
 * // Import the service
 * import api from '../services/api';
 * 
 * // GET request
 * const response = await api.get('/students');
 * const students = response.data.data;
 * 
 * // POST request
 * const response = await api.post('/students', {
 *   first_name: 'John',
 *   last_name: 'Doe',
 *   email: 'john@example.com'
 * });
 * 
 * // PUT request
 * const response = await api.put('/students/1', updatedData);
 * 
 * // DELETE request
 * await api.delete('/students/1');
 * 
 * AUTOMATIC FEATURES:
 * - JWT token is automatically added to all requests
 * - 401 errors automatically log user out
 * - JSON content-type is set by default
 * - Base URL is automatically prepended
 * 
 * REQUEST FLOW:
 * 1. Component calls api.get('/students')
 * 2. Request interceptor adds JWT token
 * 3. Request sent to http://localhost:8000/api/students
 * 4. Backend validates token and processes request
 * 5. Response interceptor checks for errors
 * 6. Data returned to component
 * 
 * ERROR HANDLING:
 * - Network errors: Thrown to component
 * - 401 Unauthorized: Automatic logout and redirect
 * - Other HTTP errors: Thrown to component
 * 
 * BACKEND ENDPOINTS:
 * All endpoints defined in Laravel routes/api.php are accessible:
 * - /api/login (authentication)
 * - /api/students (student management)
 * - /api/fees (fee management)
 * - /api/attendance (attendance tracking)
 * - etc.
 * =============================================================================
 */