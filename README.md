# Task Manager API (Laravel)

## 📌 Overview
This is a RESTful Task Manager API built with Laravel and JWT authentication.

## 🚀 Features
- User Authentication (JWT)
- Task CRUD (Create, Read, Update, Delete)
- File Upload (Image/PDF)
- Filtering & Pagination
- Secure API endpoints

## 🛠️ Tech Stack
- Laravel
- MySQL
- JWT Auth

## ⚙️ Setup Instructions

1. Clone the repo
git clone https://github.com/kirama1516/task-manager-api.git
cd task-manager-api

2. Install dependencies
composer install

3. Setup environment
cp .env.example .env
php artisan key:generate
php artisan jwt:secret


4. Configure database in
`.env`

6. Run migrations
php artisan migrate

6. Run server
php artisan serve

## API Endpoints
### Auth
- POST /api/register
- POST /api/login
- POST /api/forgot-password
- POST /api/reset-password

### Tasks
- GET /api/tasks
- POST /api/tasks
- GET /api/tasks/{id}
- PUT /api/tasks/{id}
- DELETE /api/tasks/{id}

## 📸 Screenshots
(Add screenshots here)

## 📄 Notes
- Requires Bearer Token for protected routes
