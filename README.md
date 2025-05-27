```markdown
# 🚖 Taxi Service Web Application

This project is a full-featured PHP-based Taxi Booking System with user, driver, and admin functionalities.

## 📁 Project Structure

- `/admin/` – Admin dashboard and management tools
- `/api/` – API endpoints for user/admin functionalities
- `/user/` – User registration, login, booking management
- `/driver/` – Driver dashboard, login, booking status updates
- `/config/` – Configuration files (e.g., database connection)
- `/models/` – Core data models (User, Driver, Booking, etc.)
- `/phpqrcode/` – Library for generating QR codes
- `/qrcodes/` – Generated QR codes for bookings
- `/assests/` – Images and other frontend assets

## 🧰 Installation

1. Clone or extract the repository.
2. Import the `demo.sql` file into your MySQL database.
3. Configure your database credentials in:
```

config/database.php

```
4. Run the application via a local or remote server supporting PHP (e.g., XAMPP, LAMP).

## 🔐 Admin Login

You may need to create an admin user using:
```

create\_admin.php

```

## 🚗 Demo Features

- Book a taxi
- Admin manages drivers, bookings, payments
- Drivers manage booking status
- Users can rate and cancel rides
- QR code-based booking tracking

## 📦 Database Schema

Import the SQL file from:
/demo.sql


---

