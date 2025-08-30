#Smart Fuel Management Platform for SMB Fleets

A web-based fuel tracking and management solution tailored for small and medium-sized business (SMB) fleets. Built with PHP, JavaScript, CSS, HTML, and Hack, this platform empowers fleet operators to monitor fuel usage, manage vehicles and drivers, process receipts, and optimize fleet routes.

#Overview
This platform helps SMB fleet operators streamline fuel management by providing:

Fleet-wide fuel tracking and monitoring

Digital receipt processing and logging

Driver and vehicle management

Route visualization and optimization

Secure, role-based authentication and dashboard access

#Features

User Authentication: Secure login and signup with PHP sessions

Dashboard & Profile Management: Interactive overview and user profile functionalities

Vehicle & Driver Modules: Add, edit, and view vehicles; manage drivers and their details

Receipts & Fuel Logging: Upload and process fuel receipts

Route Management: Display and configure fleet routes using maps

Configuration Files: Update system and route-specific settings

Frontend Design: Clean, modular styling with responsive interactions

#Tech Stack
Backend: PHP, Hack
Frontend: HTML, CSS, JavaScript
UI Enhancements: Custom JS and CSS
Authentication: PHP sessions
Mapping: JavaScript map libraries (Google Maps / Leaflet)
Storage: MySQL database

##Setup & Installation

```
Clone the repository
git clone https://github.com/oweshkhan96/Smart-Fuel-Management-Platform-for-SMB-Fleets
cd Smart-Fuel-Management-Platform-for-SMB-Fleets
```
Set up a local server environment (XAMPP, MAMP, etc.)

Configure the environment in config.php (database credentials and other settings)

If using a database, create tables for users, vehicles, drivers, receipts, etc.

Place project files in your webserver root directory.

Access the app via browser (e.g., http://localhost/Smart-Fuel-Management-Platform-for-SMB-Fleets
).

Login or register and start using the features.

Project Structure

index.php

signin.html

signup.js

login.php

register.php

session.php

dashboard.php

profile.php

vehicles.php, vehicles_api.php, vehicles.js, vehicles-styles.css

drivers.php, drivers_api.php, drivers.js, drivers-styles.css

receipts.php, process_receipt.php, save_receipt.php

fleet-routes.php, fleet_route_manager.php, fleet-routes.js, fleet-routes-styles.css

maps.php

route-config.php

config.php

styles.css

Usage

Login or register to access the system

Use dashboard for insights

Manage vehicles and drivers

Upload receipts to track fuel usage

Visualize and manage routes on maps

API Endpoints

vehicles_api.php → CRUD for vehicles

drivers_api.php → CRUD for drivers

process_receipt.php / save_receipt.php → Receipt uploads and logging

#Contributing

Fork the repository

Create a new branch

Commit your changes

Submit a pull request
