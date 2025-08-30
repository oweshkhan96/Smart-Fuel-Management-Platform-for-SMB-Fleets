<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Analytics Dashboard - Fleetly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard-styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/date-fns@2.29.3/index.min.js"></script>
</head>
<body class="dashboard-page">
    <!-- Top Navbar -->
    <nav class="navbar">
        <div class="navbar-left">
            <button class="sidebar-toggle" id="sidebarToggle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="logo">
                <svg width="32" height="32" viewBox="0 0 32 32" fill="none">
                    <rect width="32" height="32" rx="8" fill="#1C4E80"/>
                    <text x="6" y="22" fill="white" font-family="Inter" font-weight="700" font-size="12">F</text>
                </svg>
                <span class="logo-text">Fleetly</span>
            </div>
        </div>

        <div class="navbar-center">
            <div class="search-container">
                <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="M21 21l-4.35-4.35"></path>
                </svg>
                <input type="text" placeholder="Search vehicles, drivers, trips..." class="search-input">
            </div>
        </div>

        <div class="navbar-right">
            <div class="navbar-actions">
                <button class="action-btn notification-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span class="badge">3</span>
                </button>
                
                <button class="action-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                </button>

                <div class="user-menu">
                    <div class="user-avatar">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="User Avatar">
                    </div>
                    <div class="user-info">
                        <span class="user-name">John Doe</span>
                        <span class="user-role">Fleet Manager</span>
                    </div>
                    <svg class="dropdown-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="6,9 12,15 18,9"></polyline>
                    </svg>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-content">
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-item active">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        <span class="nav-text">Fleet Overview</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polygon points="10,8 16,12 10,16 10,8"></polygon>
                        </svg>
                        <span class="nav-text">Live Tracking</span>
                    </div>

                    <div class="nav-item" onclick="window.location.href='drivers.php'">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-text">Drivers</span>
                    </div>
                    <div class="nav-item" onclick="window.location.href='vehicles.php'">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-text">Vehicles</span>
                    </div>
                    <div class="nav-item" onclick="window.location.href='route-config.php'">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-text">Fleet Management</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span class="nav-text">Fuel Management</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline>
                        </svg>
                        <span class="nav-text">Analytics</span>
                    </div>
                </div>

                <div class="nav-section">
                    <div class="nav-header">Management</div>
                    
                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                        <span class="nav-text">Maintenance</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                        <span class="nav-text">Reports</span>
                    </div>

                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1 1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                        <span class="nav-text">Settings</span>
                    </div>
                </div>
            </nav>
        </div>

        <div class="sidebar-footer">
            <div class="plan-card">
                <div class="plan-icon">⚡</div>
                <div class="plan-content">
                    <h4>Upgrade to Pro</h4>
                    <p>Get advanced analytics and unlimited tracking</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="content-header">
            <div class="page-title">
                <h1>Fleet Dashboard</h1>
                <p>Monitor your fleet performance and analytics</p>
            </div>
            
            <div class="header-actions">
                <div class="date-range">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Last 30 days</span>
                </div>
                
                <button class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7,10 12,15 17,10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Export Report
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">142</div>
                    <div class="stat-label">Total Vehicles</div>
                    <div class="stat-change positive">+12% from last month</div>
                </div>
                <div class="stat-chart">
                    <svg width="60" height="30">
                        <polyline points="0,20 10,15 20,18 30,12 40,14 50,8 60,10" fill="none" stroke="rgba(59, 130, 246, 0.5)" stroke-width="2"/>
                    </svg>
                </div>
            </div>

            <div class="stat-card success">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">89</div>
                    <div class="stat-label">Active Drivers</div>
                    <div class="stat-change positive">+8% from last month</div>
                </div>
                <div class="stat-chart">
                    <svg width="60" height="30">
                        <polyline points="0,25 10,22 20,20 30,18 40,15 50,12 60,10" fill="none" stroke="rgba(16, 185, 129, 0.5)" stroke-width="2"/>
                    </svg>
                </div>
            </div>

            <div class="stat-card warning">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">45,280 L</div>
                    <div class="stat-label">Fuel Consumed</div>
                    <div class="stat-change negative">-3% from last month</div>
                </div>
                <div class="stat-chart">
                    <svg width="60" height="30">
                        <polyline points="0,15 10,18 20,16 30,19 40,17 50,20 60,22" fill="none" stroke="rgba(245, 158, 11, 0.5)" stroke-width="2"/>
                    </svg>
                </div>
            </div>

            <div class="stat-card info">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="22,12 18,12 15,21 9,3 6,12 2,12"></polyline>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value">32 L/100km</div>
                    <div class="stat-label">Avg. Fuel Efficiency</div>
                    <div class="stat-change positive">+5% improvement</div>
                </div>
                <div class="stat-chart">
                    <svg width="60" height="30">
                        <polyline points="0,22 10,25 20,20 30,18 40,15 50,13 60,10" fill="none" stroke="rgba(99, 102, 241, 0.5)" stroke-width="2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="charts-grid">
            <!-- Fuel Consumption Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <h3>Fuel Consumption Trend</h3>
                        <p>Daily fuel usage over the past 30 days</p>
                    </div>
                    <div class="chart-actions">
                        <button class="chart-filter active" data-period="7d">7D</button>
                        <button class="chart-filter" data-period="30d">30D</button>
                        <button class="chart-filter" data-period="90d">90D</button>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="fuelChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Trip Distribution Chart -->
            <div class="chart-card">
                <div class="chart-header">
                    <div class="chart-title">
                        <h3>Fleet Performance</h3>
                        <p>Distribution of trips by vehicle type</p>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Bottom Row -->
        <div class="bottom-grid">
            <!-- Recent Activity -->
            <div class="activity-card">
                <div class="card-header">
                    <h3>Recent Activity</h3>
                    <button class="view-all-btn">View All</button>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon success">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <polyline points="20,6 9,17 4,12"></polyline>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">Vehicle FL-001 completed route successfully</div>
                            <div class="activity-time">2 minutes ago</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon warning">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">Low fuel alert for Vehicle FL-025</div>
                            <div class="activity-time">15 minutes ago</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon info">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">Driver John Smith started shift</div>
                            <div class="activity-time">1 hour ago</div>
                        </div>
                    </div>

                    <div class="activity-item">
                        <div class="activity-icon error">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="activity-content">
                            <div class="activity-text">Maintenance required for Vehicle FL-018</div>
                            <div class="activity-time">2 hours ago</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fleet Status -->
            <div class="fleet-status-card">
                <div class="card-header">
                    <h3>Fleet Status</h3>
                    <div class="status-legend">
                        <div class="legend-item">
                            <div class="legend-color active"></div>
                            <span>Active</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color maintenance"></div>
                            <span>Maintenance</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color idle"></div>
                            <span>Idle</span>
                        </div>
                    </div>
                </div>
                <div class="status-overview">
                    <div class="status-circle">
                        <svg width="120" height="120" viewBox="0 0 120 120">
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#f1f5f9" stroke-width="8"/>
                            <circle cx="60" cy="60" r="50" fill="none" stroke="#10b981" stroke-width="8" 
                                    stroke-dasharray="251.2" stroke-dashoffset="75.36" 
                                    transform="rotate(-90 60 60)"/>
                        </svg>
                        <div class="circle-content">
                            <div class="circle-value">78%</div>
                            <div class="circle-label">Active</div>
                        </div>
                    </div>
                    <div class="status-stats">
                        <div class="status-stat">
                            <div class="status-count">111</div>
                            <div class="status-label">Active Vehicles</div>
                        </div>
                        <div class="status-stat">
                            <div class="status-count">18</div>
                            <div class="status-label">In Maintenance</div>
                        </div>
                        <div class="status-stat">
                            <div class="status-count">13</div>
                            <div class="status-label">Idle</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Drivers -->
            <div class="drivers-card">
                <div class="card-header">
                    <h3>Top Performers</h3>
                    <button class="view-all-btn">View All</button>
                </div>
                <div class="drivers-list">
                    <div class="driver-item">
                        <div class="driver-avatar">
                            <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=40&h=40&fit=crop&crop=face" alt="Driver">
                        </div>
                        <div class="driver-info">
                            <div class="driver-name">Alex Rodriguez</div>
                            <div class="driver-stats">95.2% efficiency • 1,240 km</div>
                        </div>
                        <div class="driver-badge top">
                            <span>#1</span>
                        </div>
                    </div>

                    <div class="driver-item">
                        <div class="driver-avatar">
                            <img src="https://images.unsplash.com/photo-1494790108755-2616b25c8a67?w=40&h=40&fit=crop&crop=face" alt="Driver">
                        </div>
                        <div class="driver-info">
                            <div class="driver-name">Sarah Johnson</div>
                            <div class="driver-stats">92.8% efficiency • 1,180 km</div>
                        </div>
                        <div class="driver-badge">
                            <span>#2</span>
                        </div>
                    </div>

                    <div class="driver-item">
                        <div class="driver-avatar">
                            <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=40&h=40&fit=crop&crop=face" alt="Driver">
                        </div>
                        <div class="driver-info">
                            <div class="driver-name">Mike Chen</div>
                            <div class="driver-stats">91.5% efficiency • 1,120 km</div>
                        </div>
                        <div class="driver-badge">
                            <span>#3</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="dashboard.js"></script>
</body>
</html>
