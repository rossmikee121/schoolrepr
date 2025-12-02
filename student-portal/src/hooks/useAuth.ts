/**
 * =============================================================================
 * AUTHENTICATION HOOK - Manages User Login/Logout State
 * =============================================================================
 * 
 * This custom React hook handles all authentication logic for the frontend.
 * It manages user state, login/logout functions, and token storage.
 * 
 * KEY RESPONSIBILITIES:
 * - Store and manage user authentication state
 * - Handle login API calls to backend
 * - Store JWT tokens in localStorage
 * - Provide logout functionality
 * - Check for existing authentication on app load
 * - Provide loading states during auth operations
 * 
 * BACKEND INTEGRATION:
 * - Calls POST /api/login for user authentication
 * - Stores JWT token returned from backend
 * - Token is sent with all future API requests
 * 
 * STORAGE:
 * - JWT token stored in localStorage as 'auth_token'
 * - User data stored in localStorage as 'user'
 * - Data persists across browser sessions
 * 
 * FOR INTERNS:
 * - Custom Hook = Reusable logic that can be used by components
 * - Context = Way to share data across all components without prop drilling
 * - localStorage = Browser storage that persists data
 * - JWT Token = Authentication token for API requests
 * =============================================================================
 */

import { useState, useEffect, createContext, useContext } from 'react';
import api from '../services/api';
import { User } from '../types';

/**
 * AUTHENTICATION CONTEXT TYPE
 * 
 * Defines the shape of data that will be available to all components
 * through the AuthContext.
 */
interface AuthContextType {
  user: User | null;                                          // Current logged-in user data
  login: (email: string, password: string) => Promise<void>;  // Login function
  logout: () => void;                                         // Logout function
  loading: boolean;                                           // Loading state
}

/**
 * AUTHENTICATION CONTEXT
 * 
 * React Context that will hold authentication state and functions.
 * This allows any component in the app to access auth data.
 */
const AuthContext = createContext<AuthContextType | undefined>(undefined);

/**
 * USE AUTH HOOK
 * 
 * This hook allows components to access authentication data from context.
 * It includes error checking to ensure it's used correctly.
 * 
 * USAGE IN COMPONENTS:
 * const { user, login, logout, loading } = useAuth();
 * 
 * ERROR HANDLING:
 * - Throws error if used outside of AuthContext.Provider
 * - Helps catch development mistakes early
 */
export const useAuth = () => {
  const context = useContext(AuthContext);
  
  // Safety check: ensure hook is used within AuthContext.Provider
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  
  return context;
};

/**
 * USE AUTH PROVIDER HOOK
 * 
 * This is the main authentication hook that provides all auth functionality.
 * It manages state and provides functions for login/logout.
 * 
 * STATE MANAGEMENT:
 * - user: Current logged-in user data (null if not logged in)
 * - loading: True while checking authentication status
 * 
 * LIFECYCLE:
 * 1. Component mounts -> check localStorage for existing auth
 * 2. If found -> set user as logged in
 * 3. Set loading to false
 * 4. Provide login/logout functions to components
 */
export const useAuthProvider = () => {
  // STATE: Current user data (null = not logged in)
  const [user, setUser] = useState<User | null>(null);
  
  // STATE: Loading indicator for auth operations
  const [loading, setLoading] = useState(true);

  /**
   * EFFECT: Check for existing authentication on app load
   * 
   * This runs once when the app starts to check if user is already logged in.
   * 
   * PROCESS:
   * 1. Check localStorage for saved token and user data
   * 2. If both exist, user is considered logged in
   * 3. Set user state and stop loading
   * 
   * NOTE: In production, you might want to verify token with backend
   */
  useEffect(() => {
    // Check localStorage for existing authentication
    const token = localStorage.getItem('auth_token');
    const savedUser = localStorage.getItem('user');
    
    // If both token and user data exist, restore login state
    if (token && savedUser) {
      try {
        setUser(JSON.parse(savedUser));
      } catch (error) {
        // If user data is corrupted, clear it
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
      }
    }
    
    // Authentication check complete
    setLoading(false);
  }, []);  // Empty dependency array = run only once on mount

  /**
   * LOGIN FUNCTION
   * 
   * Handles user login by calling the backend API and storing the response.
   * 
   * PROCESS:
   * 1. Send POST request to /api/login with email/password
   * 2. Backend validates credentials and returns JWT token + user data
   * 3. Store token and user data in localStorage
   * 4. Update React state to reflect logged-in status
   * 5. If error occurs, throw it for component to handle
   * 
   * BACKEND API CALL:
   * POST /api/login
   * Body: { email: 'user@example.com', password: 'password123' }
   * 
   * EXPECTED RESPONSE:
   * {
   *   "success": true,
   *   "data": {
   *     "token": "jwt_token_here",
   *     "user": { id, name, email, roles, ... }
   *   }
   * }
   * 
   * STORAGE:
   * - localStorage['auth_token'] = JWT token for API requests
   * - localStorage['user'] = JSON string of user data
   */
  const login = async (email: string, password: string) => {
    try {
      // Call backend login API
      const response = await api.post('/login', { email, password });
      
      // Extract token and user data from response
      const { token, user: userData } = response.data.data;
      
      // Store authentication data in localStorage for persistence
      localStorage.setItem('auth_token', token);
      localStorage.setItem('user', JSON.stringify(userData));
      
      // Update React state to reflect logged-in status
      setUser(userData);
      
    } catch (error) {
      // Re-throw error for component to handle (show error message, etc.)
      throw error;
    }
  };

  /**
   * LOGOUT FUNCTION
   * 
   * Handles user logout by clearing all stored authentication data.
   * 
   * PROCESS:
   * 1. Remove JWT token from localStorage
   * 2. Remove user data from localStorage
   * 3. Update React state to reflect logged-out status
   * 
   * NOTE: This is client-side logout only.
   * In production, you might want to call backend to invalidate token.
   */
  const logout = () => {
    // Clear stored authentication data
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    
    // Update React state to reflect logged-out status
    setUser(null);
  };

  /**
   * RETURN AUTH PROVIDER DATA
   * 
   * This object will be provided to all components through AuthContext.
   * Components can access these values using the useAuth() hook.
   */
  return {
    user,      // Current user data (null if not logged in)
    login,     // Function to log in user
    logout,    // Function to log out user
    loading,   // Boolean indicating if auth check is in progress
  };
};

// Export AuthContext for use in App.tsx
export { AuthContext };

/**
 * =============================================================================
 * AUTHENTICATION HOOK SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * WHAT THIS HOOK PROVIDES:
 * 1. User authentication state management
 * 2. Login function that calls backend API
 * 3. Logout function that clears stored data
 * 4. Loading state for UI feedback
 * 5. Persistent authentication across browser sessions
 * 
 * HOW IT WORKS:
 * 1. App loads -> check localStorage for existing auth
 * 2. User logs in -> call API, store token, update state
 * 3. User navigates -> token is sent with API requests
 * 4. User logs out -> clear storage, update state
 * 
 * USAGE IN COMPONENTS:
 * 
 * // In any component:
 * const { user, login, logout, loading } = useAuth();
 * 
 * // Check if logged in:
 * if (user) {
 *   // User is logged in
 * }
 * 
 * // Login:
 * await login('email@example.com', 'password');
 * 
 * // Logout:
 * logout();
 * 
 * BACKEND INTEGRATION:
 * - Calls POST /api/login for authentication
 * - Stores JWT token for future API requests
 * - Token is automatically included in API calls via api.ts
 * 
 * SECURITY CONSIDERATIONS:
 * - Tokens stored in localStorage (vulnerable to XSS)
 * - In production, consider httpOnly cookies
 * - Implement token refresh mechanism
 * - Add token expiration handling
 * 
 * ERROR HANDLING:
 * - Login errors are thrown to components
 * - Components should catch and display error messages
 * - Invalid stored data is automatically cleared
 * =============================================================================
 */