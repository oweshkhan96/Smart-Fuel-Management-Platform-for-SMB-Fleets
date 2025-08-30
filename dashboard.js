class Dashboard {
    constructor() {
        this.init();
    }

    init() {
        this.setupSidebar();
        this.setupCharts();
        this.setupEventListeners();
        console.log('🚀 Dashboard initialized');
    }

    setupSidebar() {
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                mainContent.style.marginLeft = 'var(--sidebar-collapsed-width)';
            } else {
                mainContent.style.marginLeft = 'var(--sidebar-width)';
            }
        });

        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('open');
            });
        }
    }

    setupCharts() {
        this.initFuelChart();
        this.initPerformanceChart();
    }

    initFuelChart() {
        const ctx = document.getElementById('fuelChart').getContext('2d');
        
        const fuelData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Fuel Consumption (Liters)',
                data: [4200, 3800, 4100, 3900, 4300, 4500, 4200, 4600, 4100, 4400, 4000, 4300],
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#FFFFFF',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: fuelData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        borderColor: '#3B82F6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B',
                            callback: function(value) {
                                return value.toLocaleString() + 'L';
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        hoverBackgroundColor: '#3B82F6'
                    }
                }
            }
        });
    }

    initPerformanceChart() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        const performanceData = {
            labels: ['Trucks', 'Vans', 'Cars', 'Motorcycles'],
            datasets: [{
                label: 'Trips Completed',
                data: [245, 156, 189, 92],
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(99, 102, 241, 0.8)'
                ],
                borderColor: [
                    '#3B82F6',
                    '#10B981',
                    '#F59E0B',
                    '#6366F1'
                ],
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false
            }]
        };

        new Chart(ctx, {
            type: 'bar',
            data: performanceData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#FFFFFF',
                        bodyColor: '#FFFFFF',
                        borderColor: '#3B82F6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        displayColors: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B'
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(226, 232, 240, 0.5)',
                            drawBorder: false
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            color: '#64748B',
                            beginAtZero: true
                        }
                    }
                }
            }
        });
    }

    setupEventListeners() {
        // Chart filter buttons
        document.querySelectorAll('.chart-filter').forEach(button => {
            button.addEventListener('click', (e) => {
                // Remove active class from siblings
                e.target.parentNode.querySelectorAll('.chart-filter').forEach(btn => {
                    btn.classList.remove('active');
                });
                
                // Add active class to clicked button
                e.target.classList.add('active');
                
                // Here you would typically reload chart data
                const period = e.target.dataset.period;
                console.log('Loading data for period:', period);
            });
        });

        // Simulate real-time updates
        this.startRealTimeUpdates();
    }

    startRealTimeUpdates() {
        // Simulate real-time data updates every 30 seconds
        setInterval(() => {
            this.updateStats();
            this.updateActivity();
        }, 30000);
    }

    updateStats() {
        // Simulate stat updates (in real app, fetch from API)
        const stats = document.querySelectorAll('.stat-value');
        stats.forEach(stat => {
            const currentValue = parseInt(stat.textContent.replace(/[^0-9]/g, ''));
            const variation = Math.floor(Math.random() * 10) - 5; // -5 to +5
            const newValue = Math.max(0, currentValue + variation);
            
            if (stat.textContent.includes('L')) {
                stat.textContent = newValue.toLocaleString() + ' L';
            } else if (stat.textContent.includes('km')) {
                stat.textContent = newValue.toLocaleString() + ' km';
            } else {
                stat.textContent = newValue.toLocaleString();
            }
        });
    }

    updateActivity() {
        const activities = [
            { type: 'success', text: 'Vehicle FL-' + String(Math.floor(Math.random() * 999)).padStart(3, '0') + ' completed route successfully', time: 'Just now' },
            { type: 'warning', text: 'Low fuel alert for Vehicle FL-' + String(Math.floor(Math.random() * 999)).padStart(3, '0'), time: '2 minutes ago' },
            { type: 'info', text: 'Driver started shift', time: '5 minutes ago' },
            { type: 'error', text: 'Maintenance required for Vehicle FL-' + String(Math.floor(Math.random() * 999)).padStart(3, '0'), time: '10 minutes ago' }
        ];

        const activityList = document.querySelector('.activity-list');
        const randomActivity = activities[Math.floor(Math.random() * activities.length)];
        
        // Add new activity at the top
        const newActivity = document.createElement('div');
        newActivity.className = 'activity-item';
        newActivity.style.opacity = '0';
        newActivity.innerHTML = `
            <div class="activity-icon ${randomActivity.type}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    ${this.getActivityIcon(randomActivity.type)}
                </svg>
            </div>
            <div class="activity-content">
                <div class="activity-text">${randomActivity.text}</div>
                <div class="activity-time">${randomActivity.time}</div>
            </div>
        `;
        
        activityList.prepend(newActivity);
        
        // Animate in
        setTimeout(() => {
            newActivity.style.opacity = '1';
            newActivity.style.transition = 'opacity 0.3s ease';
        }, 100);
        
        // Remove oldest activity if more than 4
        const activities_items = activityList.querySelectorAll('.activity-item');
        if (activities_items.length > 4) {
            activities_items[activities_items.length - 1].remove();
        }
    }

    getActivityIcon(type) {
        const icons = {
            success: '<polyline points="20,6 9,17 4,12"></polyline>',
            warning: '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>',
            info: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle>',
            error: '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>'
        };
        return icons[type] || icons.info;
    }
}

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new Dashboard();
});

// Handle window resize
window.addEventListener('resize', () => {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        mainContent.style.marginLeft = '0';
        sidebar.classList.remove('collapsed');
    } else {
        mainContent.style.marginLeft = sidebar.classList.contains('collapsed') 
            ? 'var(--sidebar-collapsed-width)' 
            : 'var(--sidebar-width)';
    }
});
