# Clinic Appointment Management System

A comprehensive, professional, and fully functional online clinic management system built with modern web technologies. This system streamlines appointment scheduling, doctor management, and patient communication for healthcare providers.

![PHP](https://img.shields.io/badge/PHP-8.0+-blue.svg)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange.svg)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.0-purple.svg)
![License](https://img.shields.io/badge/License-MIT-green.svg)

## 🚀 Features

### Core Functionality
- **Multi-panel Dashboard** - Separate interfaces for Patients, Doctors, and Administrators
- **Real-time Slot Availability** - Intelligent scheduling prevents double-booking
- **Doctor Schedule Management** - Weekly schedules with leave management
- **Instant Booking System** - Seamless appointment booking with email confirmations
- **Printable Appointment Tickets** - Professional, formatted tickets for patients

### User Experience
- **Responsive Design** - Optimized for both mobile and desktop devices
- **Modern UI/UX** - Clean interface with cards, modals, and smooth animations
- **Profile Management** - Easy profile editing and password changes
- **Role-based Access Control** - Secure authentication system

### Communication & Notifications
- **Email Notifications** - Automatic emails for bookings, cancellations, and reminders
- **PHPMailer Integration** - Robust email delivery system
- **24-hour Reminders** - Optional cron job for appointment reminders

## 📁 Project Structure

```
clinic-appointment-system/
│
├── includes/                 # Core application files
│   ├── header.php
│   ├── footer.php
│   ├── auth.php             # Authentication middleware
│   ├── db_connect.php       # Database connection
│   └── mailer.php           # Email configuration
│
├── patient/                  # Patient-facing features
│   ├── dashboard.php
│   ├── book_appointment.php
│   ├── process_booking.php
│   ├── my_appointments.php
│   ├── cancel_appointment.php
│   ├── profile.php
│   └── print_ticket.php
│
├── doctor/                   # Doctor management
│   ├── dashboard.php
│   ├── schedule.php
│   ├── leaves.php
│   └── mark_complete.php
│
├── admin/                    # Administrative functions
│   ├── dashboard.php
│   ├── doctors.php
│   ├── add_doctor.php
│   ├── edit_doctor.php
│   └── view_doctor.php
|
├── auth/                   # authentication
│   ├── login.php
|   ├── register.php
|   ├── logout.php
|
|      
├── vendor/                   # Composer dependencies (PHPMailer)
├── index.php                 # Landing page
└── README.md
```

## 🗄️ Database Schema

The system uses a comprehensive MySQL database with the following key tables:

- `users` - User accounts and authentication
- `specializations` - Medical specialties
- `doctors` - Doctor profiles and information
- `doctor_schedule` - Availability schedules
- `doctor_leaves` - Leave management
- `appointments` - Booking records with status tracking

**Key Features:**
- Appointment status tracking (`pending` / `completed` / `cancelled`)
- Cancellation tracking (`cancelled_by` patient/doctor)
- Active schedule management
- Comprehensive leave system

## ⚙️ Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Composer (for dependency management)
- Web server (Apache/Nginx)

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/thisisnotasad/Clinic-Appointment-System.git
   cd clinic-appointment-system
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database setup**
   ```sql
   CREATE DATABASE clinic_appointment_db;
   USE clinic_appointment_db;
   -- Import the provided clinic_db.sql file
   ```

4. **Configure database connection**
   Edit `includes/db_connect.php`:
   ```php
   $conn = new mysqli("localhost", "username", "password", "clinic_appointment_db");
   ```

5. **Email configuration**
   - Enable 2-Factor Authentication on your Gmail
   - Generate App Password: https://myaccount.google.com/apppasswords
   - Update `includes/mailer.php` with your credentials

6. **Start development server**
   ```bash
   php -S localhost:8000
   ```

## 📧 Email Configuration

The system uses PHPMailer for reliable email delivery:

```php
// Configure in includes/mailer.php
$mail->Username = 'your-clinic@gmail.com';
$mail->Password = 'your-app-password';
```

**Automated emails include:**
- New booking confirmations (patient + doctor)
- Cancellation notifications
- 24-hour appointment reminders (cron job optional)

## 🛡️ Security Features

- **SQL Injection Protection** - Prepared statements throughout
- **Role-based Access Control** - Secure user permissions
- **Session Management** - Secure authentication handling
- **Input Sanitization** - Data validation and cleaning
- **Password Security** - Ready for production hashing

> **Note:** For production use, implement `password_hash()` and `password_verify()` functions.

## 🚀 Deployment

### Free Hosting Options

**InfinityFree or 000webhost** (Recommended for quick deployment):

1. Upload all files via File Manager or FTP
2. Create MySQL database through hosting control panel
3. Import `clinic_appointment_db` into your database
4. Update database credentials in `includes/db_connect.php`
5. Configure email settings in `includes/mailer.php`
6. Visit your domain - System ready!


## 👥 User Roles

### Patient
- Book and manage appointments
- View appointment history
- Receive email notifications
- Update personal profile

### Doctor
- Manage availability schedule
- Request leave periods
- View patient appointments
- Mark appointments as complete

### Administrator
- Manage doctor accounts
- Oversee system operations
- Generate reports
- System configuration

## 🤝 Contributing

We welcome contributions! Please feel free to submit pull requests, report bugs, or suggest new features.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🏆 Credits

**Developed by `Asad Ali Sajjad`**

**Powered by:**
- PHP & MySQL
- Bootstrap 5
- PHPMailer
- Font Awesome Icons

---

## 🎯 Perfect For

- **College Projects** - Ideal for final year submissions
- **Portfolio Showcase** - Demonstrate full-stack development skills
- **Freelance Clients** - Ready-to-use clinic management solution
- **Real-world Implementation** - Production-ready for actual clinics

---


<div align="center">

### ⭐ Star this repository if you found it helpful!

[Report Bug](https://github.com/thisisnotasad/clinic-appointment-system/issues) · [Request Feature](https://github.com/thisisnotasad/clinic-appointment-system/issues)

</div>