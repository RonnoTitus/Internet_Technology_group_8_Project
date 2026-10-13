# Library Management System (LMS)

A web-based **Library Management System** designed to streamline library operations, manage book inventories, track circulations, and coordinate communication between administrators, staff members, and library patrons. 

## 🗺️ Project Architecture & File Structure

The project follows a modular, role-based architecture dividing system privileges among **Administrators**, **Staff**, and standard **Users**. 

```text
lms_db/
│
├── index.php                 # Application landing & entry portal
├── pay_fine.php               # Fine processing and penalty management
│
├── admin/                    # Admin Dashboard & Control Panel
│   ├── dashboard.php         # Analytics Overview (Total books, active loans, etc.)
│   ├── login.php             # Administrative authentication portal
│   ├── books.php             # Inventory CRUD controls
│   ├── borrow.php            # Oversight of outbound books
│   ├── return.php            # Oversight of inbound / returned books
│   ├── members.php           # Library cardholder management
│   ├── staff.php             # Library personnel / employee management
│   └── messages.php          # Communication & notification portal
│
├── staff/                    # Staff Operational Module
│   ├── dashboard.php         # Daily task overview
│   ├── books.php             # Quick catalog check and inventory tracking
│   ├── borrow.php            # Process ongoing checkout transactions
│   ├── return.php            # Process book returns and drop-offs
│   └── members.php           # Local patron registry lookup
│
├── user/                     # Student / Patron Interface
│   ├── dashboard.php         # Personal student dashboard
│   ├── books.php             # Searchable catalog library browser
│   ├── borrow.php            # Request or view personal history of book issues
│   └── mybooks.php           # Currently checked-out books and due dates
│
├── includes/                 # Core System Backend & Context Logic
│   ├── db.php                # Database Connection (PDO/MySQLi configuration)
│   ├── auth.php              # Access-control guard & session validation
│   ├── login.php             # Core authentication handler scripts
│   ├── signup.php            # New reader / member registration pipeline
│   └── logout.php            # Safe session termination sequence
│
└── styles/
    └── main.css              # Universal application stylesheets
```

---

## 👥 Role Matrix & Permissions

| Feature / Access | Admin | Staff | User (Patron) |
| :--- | :---: | :---: | :---: |
| Browse Book Catalog | ✔️ | ✔️ | ✔️ |
| View Personal Borrowed Books & Fines | ✔️ | ✔️ | ✔️ |
| Register New User / Signup | ✔️ | ✔️ | ✔️ |
| Issue Book Checkouts & Process Returns | ✔️ | ✔️ | ❌ |
| Edit Book Inventory Data | ✔️ | ❌ | ❌ |
| Manage Staff & Employees | ✔️ | ❌ | ❌ |
| Direct System Messaging & Broadcasts | ✔️ | ❌ | ❌ |

---

## 🚀 Installation & Setup

### Prerequisites
*   **Web Server Environment:** Apache or Nginx (via stacks like XAMPP, WAMP, Laragon, or MAMP)
*   **Backend Runtime:** PHP 7.4 or higher
*   **Database Management:** MySQL or MariaDB

### Step-by-Step Deployment
1.  **Clone / Copy the Project Files:**
    Move the `lms_db` directory into your web server's root folder (e.g., `htdocs/` for XAMPP or `www/` for WAMP).
2.  **Configure Database Connections:**
    Open `lms_db/includes/db.php` in a text editor and adjust the parameters to match your local setup:
    ```php
    // Example database config adjustment
    $host = 'localhost';
    $db   = 'lms_db';
    $user = 'root'; 
    $pass = ''; // Insert password if applicable
    ```
3.  **Import Schema Variables:**
    *   Open your database management client (e.g., **phpMyAdmin**).
    *   Create a new database named `lms_db`.
    *   Locate the matching `.sql` schema file (if provided with your source dump) and import it directly into your new `lms_db` workspace.
4.  **Run the Portal:**
    Launch your internet browser and navigate directly to:
    ```text
    http://localhost/lms_db/index.php
    ```

---

## 🔒 Security Infrastructure
*   **Session Guards (`includes/auth.php`):** Unauthorized users trying to forcefully deep-link directly into administrative screens are actively bounced back out to the main landing index page.
*   **Role Separation:** Each operational tier (Admin, Staff, User) resides within its isolated subdirectory with unique session checks keeping operations tightly siloed.
