<?php

/**
 * =============================================================================
 * DATABASE CONFIGURATION - School ERP Database Settings
 * =============================================================================
 * 
 * This file configures all database connections for the School ERP system.
 * It supports multiple database types and environments.
 * 
 * SUPPORTED DATABASES:
 * - SQLite: For development and testing (single file database)
 * - PostgreSQL: Recommended for production (robust, scalable)
 * - MySQL/MariaDB: Alternative for production
 * - SQL Server: For Windows environments
 * 
 * CURRENT SETUP:
 * - Development: SQLite (database/database.sqlite)
 * - Production: PostgreSQL (recommended)
 * 
 * ENVIRONMENT VARIABLES:
 * All database settings are controlled via .env file:
 * - DB_CONNECTION: Which database to use (sqlite, pgsql, mysql)
 * - DB_HOST: Database server address
 * - DB_PORT: Database server port
 * - DB_DATABASE: Database name
 * - DB_USERNAME: Database username
 * - DB_PASSWORD: Database password
 * 
 * REDIS CONFIGURATION:
 * - Used for caching and session storage
 * - Improves performance by storing frequently accessed data in memory
 * - Optional but recommended for production
 * 
 * FOR INTERNS:
 * - Database = Where all data is stored (students, fees, etc.)
 * - Connection = How Laravel connects to the database
 * - Environment Variables = Settings that change between dev/production
 * - Redis = Fast in-memory storage for caching
 * =============================================================================
 */

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    /**
     * DEFAULT DATABASE CONNECTION
     * 
     * This determines which database Laravel will use by default.
     * 
     * ENVIRONMENTS:
     * - Development: 'sqlite' (simple, no setup required)
     * - Production: 'pgsql' (PostgreSQL - robust and scalable)
     * 
     * The value comes from .env file's DB_CONNECTION setting.
     */
    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    'connections' => [

        /**
         * SQLITE CONNECTION - Development Database
         * 
         * SQLite is perfect for development because:
         * - No server setup required
         * - Single file database (database/database.sqlite)
         * - Fast for small datasets
         * - Easy to reset and recreate
         * 
         * LIMITATIONS:
         * - Not suitable for production with multiple users
         * - Limited concurrent access
         * - No user management
         */
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),  // Enforce relationships
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        /**
         * POSTGRESQL CONNECTION - Production Database (Recommended)
         * 
         * PostgreSQL is excellent for production because:
         * - Handles thousands of concurrent users
         * - Advanced features (JSON, full-text search, etc.)
         * - Excellent performance and reliability
         * - Strong data integrity and ACID compliance
         * - Free and open source
         * 
         * SCHOOL ERP USAGE:
         * - Stores all student, fee, attendance data
         * - Handles complex queries and reports
         * - Supports large datasets (10,000+ students)
         * - Reliable for financial data (fees, payments)
         */
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),        // Database server IP
            'port' => env('DB_PORT', '5432'),             // PostgreSQL default port
            'database' => env('DB_DATABASE', 'laravel'),  // Database name
            'username' => env('DB_USERNAME', 'root'),     // Database user
            'password' => env('DB_PASSWORD', ''),         // Database password
            'charset' => env('DB_CHARSET', 'utf8'),       // Character encoding
            'prefix' => '',                               // Table name prefix
            'prefix_indexes' => true,
            'search_path' => 'public',                    // PostgreSQL schema
            'sslmode' => 'prefer',                        // SSL connection preference
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    /**
     * MIGRATION TRACKING
     * 
     * Laravel tracks which database migrations have been run using this table.
     * 
     * HOW IT WORKS:
     * 1. When you run 'php artisan migrate'
     * 2. Laravel checks 'migrations' table to see what's already been run
     * 3. Only runs new migrations that haven't been executed
     * 4. Records each migration in this table with timestamp
     * 
     * EXAMPLE MIGRATIONS TABLE:
     * | id | migration                           | batch |
     * |----|-------------------------------------|-------|
     * | 1  | 2024_01_01_000000_create_users     | 1     |
     * | 2  | 2024_01_02_000001_create_students  | 1     |
     * | 3  | 2024_01_03_000001_create_fees     | 2     |
     */
    'migrations' => [
        'table' => 'migrations',              // Table name for tracking migrations
        'update_date_on_publish' => true,     // Update timestamps when publishing
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        /**
         * DEFAULT REDIS CONNECTION
         * 
         * Redis is used for caching and improving performance.
         * 
         * SCHOOL ERP USAGE:
         * - Cache frequently accessed data (student lists, fee structures)
         * - Store user sessions
         * - Queue background jobs (report generation, email sending)
         * - Cache database query results
         * 
         * PERFORMANCE BENEFITS:
         * - Faster page loads (data served from memory)
         * - Reduced database load
         * - Better user experience
         * 
         * OPTIONAL:
         * - System works without Redis
         * - Recommended for production
         */
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),     // Redis server IP
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),          // Redis default port
            'database' => env('REDIS_DB', '0'),           // Redis database number
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];

/**
 * =============================================================================
 * DATABASE CONFIGURATION SUMMARY FOR INTERNS
 * =============================================================================
 * 
 * WHAT THIS FILE DOES:
 * 1. Configures database connections for different environments
 * 2. Sets up Redis for caching and performance
 * 3. Configures migration tracking
 * 4. Provides flexibility to switch between database types
 * 
 * ENVIRONMENT SETUP:
 * 
 * DEVELOPMENT (.env file):
 * DB_CONNECTION=sqlite
 * DB_DATABASE=database/database.sqlite
 * 
 * PRODUCTION (.env file):
 * DB_CONNECTION=pgsql
 * DB_HOST=your-postgres-server.com
 * DB_PORT=5432
 * DB_DATABASE=schoolerp_production
 * DB_USERNAME=schoolerp_user
 * DB_PASSWORD=secure_password_here
 * 
 * DATABASE CHOICE GUIDE:
 * 
 * SQLite (Development):
 * ✅ Easy setup, no server required
 * ✅ Perfect for learning and testing
 * ❌ Not suitable for production
 * ❌ Limited concurrent users
 * 
 * PostgreSQL (Production):
 * ✅ Excellent performance and reliability
 * ✅ Handles thousands of users
 * ✅ Advanced features and data types
 * ✅ Strong data integrity
 * ❌ Requires server setup
 * 
 * MySQL (Alternative):
 * ✅ Widely supported
 * ✅ Good performance
 * ✅ Easy to find hosting
 * ❌ Some limitations vs PostgreSQL
 * 
 * REDIS BENEFITS:
 * - 10x faster data access (memory vs disk)
 * - Reduces database load
 * - Improves user experience
 * - Handles session storage
 * - Enables background job processing
 * 
 * COMMON COMMANDS:
 * php artisan migrate          # Run database migrations
 * php artisan migrate:status   # Check migration status
 * php artisan migrate:rollback # Undo last migration
 * php artisan db:seed         # Add sample data
 * php artisan tinker          # Interactive database shell
 * 
 * TROUBLESHOOTING:
 * - Check .env file for correct database settings
 * - Ensure database server is running
 * - Verify database user has proper permissions
 * - Check Laravel logs for detailed error messages
 * =============================================================================
 */
