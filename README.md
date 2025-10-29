# LifeCares
A HealthCare Website 
# LifeCare - Healthcare & Hospital Management Website

LifeCare is a comprehensive web application designed for managing healthcare services, connecting patients with doctors, and handling administrative tasks. It provides distinct portals for patients, doctors, and administrators.

## Description

This project is a dynamic, PHP-based website that serves as a central hub for a hospital or clinic. It facilitates user registration and login, patient dashboard management, doctor information, and administrative oversight.

## Features

* **User Authentication:** Secure registration, login, and logout system for all user types.
* **Role-Based Access:**
    * **Admin Portal:** Manage doctors, patients, and site settings.
    * **Doctor Portal:** View appointments and manage patient interactions (implied).
    * **Patient Portal:** Book appointments, view medical history, and manage personal information.
* **Dynamic Pages:**
    * **Home:** Landing page with an overview of services.
    * **About Us:** Information about the hospital/clinic.
    * **Doctors:** A filterable list of specialists (like the "Specialists" section shown).
    * **Contact:** Contact form and location details.
    * **Blog:** Articles and health tips.
* **Appointment Booking:** An endpoint for handling appointment scheduling.

## Tech Stack

* **Frontend:**
    * HTML5
    * CSS3
    * JavaScript (for dynamic content, form validation, and API calls)
* **Backend:**
    * PHP (for server-side logic, session management, and database interaction)
* **Database:**
    * MySQL/MariaDB (implied, to be configured in `config.php`)


## Getting Started
Follow these instructions to get a copy of the project up and running on your local machine.

### Prerequisites

You will need a local web server environment like [XAMPP](https://www.apachefriends.org/index.html) or [WAMP](https://www.wampserver.com/en/). These packages include:
* Apache Web Server
* PHP
* MySQL/MariaDB Database

### Installation

1.  **Clone the repository** (or download and extract the files) into your web server's root directory (e.g., `C:\xampp\htdocs\LifeCare` or `C:\wamp64\www\LifeCare`).

2.  **Create the Database:**
    * Open phpMyAdmin (usually `http://localhost/phpmyadmin`).
    * Create a new database (e.g., `lifecare_db`).
    * Import the provided `.sql` file (if you have one) to set up the tables.

3.  **Configure the Database:**
    * Open the `config.php` file.
    * Update the database credentials (host, username, password, and database name) to match your local setup.

    ```php
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', ''); // Your WAMP/XAMPP password, often empty by default
    define('DB_NAME', 'lifecare_db');
    ```

4.  **Run the Application:**
    * Start your Apache and MySQL services from the XAMPP/WAMP control panel.
    * Open your web browser and navigate to `http://localhost/LifeCare/` (or the folder name you used).

## Usage

* **Register:** Navigate to `register.php` to create a new patient account.
* **Login:** Use `login.php` to access the system as a patient, doctor, or admin.
* **Browse:** Explore the public pages like `index.html`, `doctor.html`, and `about.html`.
 
