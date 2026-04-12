# Task Manager API (Laravel)

## Overview
This is a RESTful Task Manager API built with Laravel and JWT authentication.

##  Features
- User Authentication (JWT)
- Task CRUD (Create, Read, Update, Delete)
- File Upload (Image/PDF)
- Filtering & Pagination
- Secure API endpoints

##  Tech Stack
- Laravel
- MySQL
- JWT Auth

##  Setup Instructions

1. Clone the repo
git clone https://github.com/kirama1516/task-manager-api.git
cd task-manager-api

markdown
Copy code

2. Install dependencies
composer install

markdown
Copy code

3. Setup environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret

markdown
Copy code

4. Configure database in `.env`

5. Run migrations
php artisan migrate

markdown
Copy code

6. Run server
php artisan serve

##  API Endpoints

### Auth
- POST /api/register
- POST /api/login
- POST /api//forgot-password
- POST /api/('/reset-password
- POST /api/logout

## Profile
- GET /api/update-profile
- POST /api/change-password
- POST /api/fcm-token

### Tasks
- GET /api/tasks
- POST /api/tasks
- GET /api/tasks/{id}
- PUT /api/tasks/{id}
- DELETE /api/tasks/{id}