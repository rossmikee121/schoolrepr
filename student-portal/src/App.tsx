/**
 * =============================================================================
 * MAIN APP COMPONENT - React Frontend Entry Point
 * =============================================================================
 * 
 * This is the main React component that sets up the entire frontend application.
 * It handles routing, authentication, and the overall app structure.
 * 
 * KEY RESPONSIBILITIES:
 * - Set up React Router for navigation between pages
 * - Provide authentication context to all components
 * - Handle loading states while checking user authentication
 * - Protect routes (redirect to login if not authenticated)
 * - Manage the overall app layout and structure
 * 
 * AUTHENTICATION FLOW:
 * 1. App loads and checks if user is logged in (via useAuthProvider)
 * 2. If loading, shows spinner
 * 3. If not logged in, redirects to /login
 * 4. If logged in, shows dashboard with layout
 * 
 * ROUTES DEFINED:
 * - /login: Login page (redirects to dashboard if already logged in)
 * - /dashboard: Main dashboard (requires authentication)
 * - /: Root path (redirects to dashboard)
 * 
 * BACKEND CONNECTION:
 * - Communicates with Laravel API at /api/login, /api/user, etc.
 * - Uses JWT tokens for authentication
 * - Stores auth state in React Context
 * 
 * FOR INTERNS:
 * - React Router = Handles navigation without page refresh
 * - Context = Shares data (like user info) across all components
 * - Protected Routes = Pages that require login
 * - Navigate = Programmatic redirection
 * =============================================================================
 */

import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthContext, useAuthProvider } from './hooks/useAuth';
import Layout from './components/Layout';
import Login from './pages/Login';
import Dashboard from './pages/Dashboard';
import './App.css';

/**
 * MAIN APP COMPONENT
 * 
 * This component is the root of the entire React application.
 * It sets up routing and authentication for the student portal.
 */
function App() {
  // Get authentication state and functions from custom hook
  // This includes: user data, login/logout functions, loading state
  const auth = useAuthProvider();

  // LOADING STATE
  // Show spinner while checking if user is authenticated
  // This happens when:
  // - App first loads
  // - Checking stored JWT token validity
  // - Making API call to /api/user
  if (auth.loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        {/* Tailwind CSS classes for centered spinning loader */}
        <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-indigo-500"></div>
      </div>
    );
  }

  // MAIN APP RENDER
  return (
    // AuthContext.Provider makes auth data available to all child components
    // Any component can access user info, login/logout functions, etc.
    <AuthContext.Provider value={auth}>
      {/* React Router setup for single-page application navigation */}
      <Router>
        <div className="App">
          {/* Define all application routes */}
          <Routes>
            
            {/* LOGIN ROUTE - /login */}
            {/* 
              LOGIC: 
              - If user is already logged in -> redirect to dashboard
              - If not logged in -> show login page
            */}
            <Route 
              path="/login" 
              element={auth.user ? <Navigate to="/dashboard" /> : <Login />} 
            />
            
            {/* DASHBOARD ROUTE - /dashboard */}
            {/* 
              LOGIC:
              - If user is logged in -> show dashboard with layout
              - If not logged in -> redirect to login
              
              LAYOUT COMPONENT:
              - Provides header, sidebar, navigation
              - Wraps the main content (Dashboard)
            */}
            <Route 
              path="/dashboard" 
              element={
                auth.user ? (
                  <Layout>
                    <Dashboard />
                  </Layout>
                ) : (
                  <Navigate to="/login" />
                )
              } 
            />
            
            {/* ROOT ROUTE - / */}
            {/* Redirect root path to dashboard */}
            <Route path="/" element={<Navigate to="/dashboard" />} />
            
          </Routes>
        </div>
      </Router>
    </AuthContext.Provider>
  );
}

export default App;

/**
 * =============================================================================
 * APP COMPONENT SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * WHAT THIS COMPONENT DOES:
 * 1. Sets up React Router for navigation
 * 2. Provides authentication context to entire app
 * 3. Handles loading states
 * 4. Implements route protection (login required)
 * 5. Manages overall app structure
 * 
 * AUTHENTICATION FLOW:
 * 1. App loads -> useAuthProvider checks for stored JWT token
 * 2. If token exists -> API call to /api/user to verify
 * 3. If valid -> user is logged in, show dashboard
 * 4. If invalid/missing -> show login page
 * 
 * ROUTE PROTECTION:
 * - /login: Public (but redirects if already logged in)
 * - /dashboard: Protected (requires authentication)
 * - /: Redirects to dashboard
 * 
 * COMPONENT HIERARCHY:
 * App
 * ├── AuthContext.Provider (shares auth state)
 * ├── Router (handles navigation)
 * └── Routes
 *     ├── /login -> Login component
 *     ├── /dashboard -> Layout + Dashboard
 *     └── / -> Redirect to dashboard
 * 
 * BACKEND INTEGRATION:
 * - Uses JWT tokens stored in localStorage
 * - Makes API calls to Laravel backend
 * - Handles authentication state management
 * 
 * STYLING:
 * - Uses Tailwind CSS for styling
 * - Responsive design for mobile/desktop
 * - Loading spinner during auth checks
 * =============================================================================
 */
