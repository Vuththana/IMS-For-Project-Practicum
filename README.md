# 📦 IMS System

A modern **Inventory Management System (IMS)** built with **Laravel**, **React (via Inertia.js)**, and **Filament**.  
This project was created as part of my **Project Practicum**.

---

## 🚀 Features
- 🔐 User authentication & role management  
- 🏢 Company and client management  
- 📊 Dashboard with analytics  
- 📦 Product & service CRUD (sellable & purchasable items)  
- 💵 Invoice & transaction tracking  
- ⚡ Built with **Laravel + Inertia.js (React + TypeScript) + Filament**

---

## ⚙️ Setup Guide

### 1️⃣ Clone the repository
```bash
git clone https://github.com/USERNAME/ims-system.git
cd ims-system
```
### 2️⃣ Install dependencies
```bash
composer install
npm install && npm run dev
```
### 3️⃣ Environment setup
Copy the `.env.example` file:
```bash
cp .env.example .env
```
Generate the app key:
```
bash
php artisan key:generate
```
### 4️⃣ Database migration & seed
```bash
php artisan migrate --seed
```
### 5️⃣ Start development servers
```bash
php artisan serve
npm run dev
```

### 📖 Documentation
[Laravel Docs](https://laravel.com/docs/12.x)

[React Docs](https://react.dev/)

[Filament Docs](https://filamentphp.com/docs)

### 🤝 Contribution
If you'd like to contribute or suggest improvements, feel free to fork the repo and create a pull request.

### 📜 License
This project is licensed under the MIT License.
