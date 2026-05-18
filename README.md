# Intern-Hours Tracker

A modern, efficient web application designed to track and manage intern working hours, absence requests, and performance logs.

## Contirbutors
**Irven Abarquez**
**Teddy Bermudo**
**Lawrence Heras**
**Ivan Abarquez**


## 🚀 Tech Stack

- **Backend**: PHP 8.x
- **Database**: MySQL
- **Frontend**: Vanilla JavaScript, HTML5, CSS3 (with Tailwind CSS utilities)
- **Authentication**: Google OAuth 2.0
- **CI/CD**: GitHub Actions (Auto-deploy to InfinityFree)

## ✨ Features

### For Interns
- **Hour Logging**: Easy-to-use calendar interface to log daily working hours.
- **Absence Requests**: Submit absence requests with reasons and track approval status.
- **Visual Progress**: Dynamic dashboard showing total hours, monthly totals, and filtered statistics.
- **Colleague View**: See fellow interns in the same office.
- **Holiday Awareness**: Automatic display of important Philippine holidays on the calendar.

### For Supervisors
- **Intern Management**: Overview of all interns within their office/organization.
- **Detailed Logs**: View individual intern logs and calendar history.
- **Absence Approval**: Review, approve, or reject pending absence requests in real-time.
- **Supervisor Badge**: Clear indicators when viewing intern data for administrative purposes.

### System Features
- **Responsive Design**: Mobile-friendly interface for logging hours on the go.
- **Automated Deployment**: Seamless CI/CD pipeline that syncs code to hosting upon every push.
- **Secure Auth**: Integrated with Google for secure and easy login.

## 🛠️ Setup Instructions

1. **Clone the repository**:
   ```bash
   git clone https://github.com/Tedeyy/Intern-Hours.git
   ```

2. **Database Setup**:
   - Create a MySQL database named `intern_hours_db`.
   - Import the schema from `assets/db/mysql.db` (if provided as SQL) or create the necessary tables.
   - Run via CLI: php migrate.php

3. **Configuration**:
   - Run via CLI: cp .env.example .env
   - Update your database credentials and Google OAuth keys.

4. **Run Locally**:
   - Use XAMPP/WAMP or a local PHP server.
   - Access via `http://localhost/Intern-Hours`.
   - Run via CLI: php -S localhost:8000

## 🚢 Deployment

This project uses **GitHub Actions** for CI/CD. To deploy:
1. Ensure `FTP_SERVER`, `FTP_USERNAME`, and `FTP_PASSWORD` are set in your GitHub Repository Secrets.
2. Push to the `main` or `hostingtweaks` branch.
3. The pipeline will automatically sync files to your InfinityFree `htdocs/` folder.

---
Developed with ❤️ for efficient intern management.
