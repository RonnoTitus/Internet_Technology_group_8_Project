# Freelance Service Marketplace

A fully functional, web-based Freelance Marketplace platform built using a native PHP backend, MySQL database architecture, and a responsive HTML/CSS frontend. This application allows clients to post job opportunities and enables freelancers to browse projects, submit competitive bids, and communicate securely.

## 🎯 Project Goals

*   **Role Separation:** Provide distinct dashboards and permissions for `Clients` and `Freelancers`.
*   **Dynamic Data Management:** Enable real-time job posting, category filtering, and bid submissions.
*   **Secure Interaction:** Implement a protected environment for user authentication and internal messaging.
*   **Security Best Practices:** Protect user data against SQL Injection and Cross-Site Scripting (XSS) threats.

## 🚀 Key Features

*   **User Management:** Secure registration and login using PHP `password_hash()` and session handling.
*   **Client Dashboard:** Post new projects, view received bids, and award contracts to freelancers.
*   **Freelancer Dashboard:** Search and filter open jobs, submit proposals, and track current bids.
*   **Communication Hub:** Internal, database-driven text messaging system between clients and hired freelancers.
*   **Review System:** Star rating and text feedback loops upon successful project completion.

## 🛠️ Tech Stack

*   **Backend:** PHP 8.x (Object-Oriented/Procedural with PDO)
*   **Database:** MySQL
*   **Frontend:** HTML5, CSS3 (Flexbox & Grid Layouts)
*   **Local Server Environment:** WampServer

## 💻 Local Setup Instructions

Follow these steps to deploy and run this project locally on your machine.

### Prerequisites
*   Windows OS (recommended for WampServer)
*   [WampServer Installed](https://wampserver.com) (Ensure Apache and MySQL services are running)
*   A text editor or IDE (e.g., VS Code, Sublime Text)

### Step 1: Clone or Move Project Files
1. Open your WampServer installation directory (usually `C:\wamp64\`).
2. Navigate to the web root folder: `C:\wamp64\www\`.
3. Create a new directory named `freelance-marketplace`.
4. Extract or place all your project `.php` and `.css` files directly into `C:\wamp64\www\freelance-marketplace\`.

### Step 2: Set Up the MySQL Database
1. Launch WampServer and ensure the system tray icon turns green.
2. Open your web browser and navigate to **phpMyAdmin**: `http://localhost/phpmyadmin/`
3. Log in using your MySQL credentials (Default: Username: `root`, Password: *leave blank*).
4. Click on **New** in the left sidebar to create a database.
5. Name the database `freelance_db` and select `utf8mb4_general_ci` as the collation. Click **Create**.
6. Select your newly created `freelance_db`.
7. Go to the **SQL** tab at the top menu.
8. Import your project's schema by pasting your SQL `CREATE TABLE` scripts into the text box and clicking **Go**.

### Step 3: Configure Environment Database Connection
1. Locate your database connection file inside your project folder (e.g., `config/database.php` or `db_connect.php`).
2. Verify the configuration matches your local WampServer settings:
   ```php
   define('DB_SERVER', 'localhost');
   define('DB_USERNAME', 'root');
   define('DB_PASSWORD', ''); // Default is empty
   define('DB_NAME', 'freelance_db');
   ```

### Step 4: Run the Application
1. Open your web browser.
2. Navigate to the project URL: `http://localhost/freelance-marketplace/`
3. The server will load your default `index.php` landing page. You can now register test user accounts to test the application workflow.

## 🛡️ Security Implementations Included
*   **Prepared Statements:** All SQL transactions use PDO parameterized queries to eliminate SQL injection vulnerabilities.
*   **Output Sanitization:** User inputs are wrapped in `htmlspecialchars()` before rendering to prevent malicious HTML/JavaScript execution.
*   **Route Protection:** Active pages use session checks to block unauthenticated layout modifications or URL-guessing hacks.
