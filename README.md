# Smart Fuel Management Platform for SMB Fleets

A web-based fuel tracking and management solution tailored for small and medium-sized business (SMB) fleets. Built with PHP, JavaScript, CSS, HTML, and Hack, this platform empowers fleet operators to monitor fuel usage, manage vehicles and drivers, process receipts, and optimize fleet routes.

---

## Overview

This platform helps SMB fleet operators streamline fuel management by providing:

- **Fleet-wide fuel tracking and monitoring**
- **Digital receipt processing and logging**
- **Driver and vehicle management**
- **Route visualization and optimization**
- **Secure, role-based authentication and dashboard access**

---

## Features

- **User Authentication:** Secure login and signup with PHP sessions
- **Dashboard & Profile Management:** Interactive overview and user profile functionalities
- **Vehicle & Driver Modules:** Add, edit, and view vehicles; manage drivers and their details
- **Receipts & Fuel Logging:** Upload and process fuel receipts
- **Route Management:** Display and configure fleet routes using maps
- **Configuration Files:** Update system and route-specific settings
- **Frontend Design:** Clean, modular styling with responsive interactions

---

## Tech Stack

- **Backend:** PHP, Hack
- **Frontend:** HTML, CSS, JavaScript
- **UI Enhancements:** Custom JS and CSS
- **Authentication:** PHP sessions
- **Mapping:** JavaScript map libraries (Google Maps / Leaflet)
- **Storage:** MySQL database

---

## Setup & Installation

1. **Clone the repository**
    ```bash
    git clone https://github.com/oweshkhan96/Smart-Fuel-Management-Platform-for-SMB-Fleets
    cd Smart-Fuel-Management-Platform-for-SMB-Fleets
    ```
2. **Set up a local server environment** (XAMPP, MAMP, etc.)
3. **Configure the environment** in `config.php` (database credentials and other settings)
4. **Create database tables** for users, vehicles, drivers, receipts, etc.
5. **Place project files** in your webserver root directory
6. **Access the app via browser:**  
   `http://localhost/Smart-Fuel-Management-Platform-for-SMB-Fleets`
7. **Login or register** and start using the features

---

## Project Structure

```
index.php
signin.html
signup.js
login.php
register.php
session.php
dashboard.php
profile.php
vehicles.php
vehicles_api.php
vehicles.js
vehicles-styles.css
drivers.php
drivers_api.php
drivers.js
drivers-styles.css
receipts.php
process_receipt.php
save_receipt.php
fleet-routes.php
fleet_route_manager.php
fleet-routes.js
fleet-routes-styles.css
maps.php
route-config.php
config.php
styles.css
```

---

## Usage

- Login or register to access the system
- Use dashboard for insights
- Manage vehicles and drivers
- Upload receipts to track fuel usage
- Visualize and manage routes on maps

---

## API Endpoints

- `vehicles_api.php` &rarr; CRUD for vehicles
- `drivers_api.php` &rarr; CRUD for drivers
- `process_receipt.php` / `save_receipt.php` &rarr; Receipt uploads and logging

---

## Contributing

1. Fork the repository
2. Create a new branch
3. Commit your changes
4. Submit a pull request

