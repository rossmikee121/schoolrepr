# 🚀 Deployment Guide

## Quick Deploy Options

### Option 1: Manual Deployment (Recommended)
```bash
# 1. Clone repository on server
git clone https://github.com/rossmikee121/schoolrepr.git
cd schoolrepr

# 2. Setup backend
cd schoolerp
composer install --no-dev --optimize-autoloader
cp .env.example .env
# Edit .env with production settings
php artisan key:generate
php artisan migrate --force
php artisan config:cache
php artisan route:cache

# 3. Setup frontend
cd ../student-portal
npm install
npm run build
```

### Option 2: GitHub Actions (Auto Deploy)
Add these secrets to GitHub repository settings:
- `DEPLOY_HOST`: Your server IP
- `DEPLOY_USER`: SSH username  
- `DEPLOY_KEY`: SSH private key
- `DEPLOY_PATH`: Server path (optional)

### Option 3: One-Click Deploy
[![Deploy](https://img.shields.io/badge/Deploy-Now-blue)](https://github.com/rossmikee121/schoolrepr)

## Production Requirements
- PHP 8.1+
- PostgreSQL 13+
- Composer
- Node.js 16+
- Web server (Apache/Nginx)

## Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_DATABASE=schoolerp
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password
```