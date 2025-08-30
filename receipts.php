<?php
// receipts.php - Receipts Management with Fixed UI
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'sdfdokln_fleet';
$username = 'sdfdokln_admin';
$password = ';cX6,?[]dCkL';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get receipts with driver information
function getReceipts($pdo, $search = '', $limit = 10, $offset = 0) {
    // Fix: Ensure limit and offset are integers
    $limit = (int)$limit;
    $offset = (int)$offset;
    
    $sql = "SELECT r.*, COALESCE(d.full_name, 'Unknown Driver') as full_name 
            FROM receipts r 
            LEFT JOIN drivers d ON r.driver_id = d.driver_id 
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (COALESCE(d.full_name, '') LIKE ? OR r.file_name LIKE ? OR r.driver_id LIKE ? OR r.extracted_text LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $sql .= " ORDER BY r.created_at DESC LIMIT $limit OFFSET $offset";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get total count for pagination
function getTotalReceipts($pdo, $search = '') {
    $sql = "SELECT COUNT(*) FROM receipts r 
            LEFT JOIN drivers d ON r.driver_id = d.driver_id 
            WHERE 1=1";
    
    $params = [];
    
    if (!empty($search)) {
        $sql .= " AND (COALESCE(d.full_name, '') LIKE ? OR r.file_name LIKE ? OR r.driver_id LIKE ? OR r.extracted_text LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get_receipts':
            try {
                $search = $_POST['search'] ?? '';
                $page = max(1, intval($_POST['page'] ?? 1));
                $limit = 10;
                $offset = ($page - 1) * $limit;
                
                $receipts = getReceipts($pdo, $search, $limit, $offset);
                $total = getTotalReceipts($pdo, $search);
                
                echo json_encode([
                    'success' => true,
                    'receipts' => $receipts,
                    'total' => $total,
                    'page' => $page,
                    'totalPages' => ceil($total / $limit)
                ]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
            exit;
            
        case 'delete_receipt':
            $receiptId = $_POST['receipt_id'] ?? '';
            
            if (empty($receiptId)) {
                echo json_encode(['success' => false, 'message' => 'Receipt ID is required']);
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM receipts WHERE id = ?");
                $stmt->execute([$receiptId]);
                
                echo json_encode(['success' => true, 'message' => 'Receipt deleted successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to delete receipt: ' . $e->getMessage()]);
            }
            exit;
            
        case 'get_receipt_details':
            $receiptId = $_POST['receipt_id'] ?? '';
            
            if (empty($receiptId)) {
                echo json_encode(['success' => false, 'message' => 'Receipt ID is required']);
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT r.*, COALESCE(d.full_name, 'Unknown Driver') as full_name 
                                     FROM receipts r 
                                     LEFT JOIN drivers d ON r.driver_id = d.driver_id 
                                     WHERE r.id = ?");
                $stmt->execute([$receiptId]);
                $receipt = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($receipt) {
                    echo json_encode(['success' => true, 'receipt' => $receipt]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Receipt not found']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Failed to get receipt details: ' . $e->getMessage()]);
            }
            exit;
    }
}

// Get stats for dashboard
try {
    $totalReceipts = getTotalReceipts($pdo);
    $todayReceipts = $pdo->query("SELECT COUNT(*) FROM receipts WHERE DATE(created_at) = CURDATE()")->fetchColumn();
    $thisMonthReceipts = $pdo->query("SELECT COUNT(*) FROM receipts WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())")->fetchColumn();
    $uniqueDrivers = $pdo->query("SELECT COUNT(DISTINCT driver_id) FROM receipts")->fetchColumn();
} catch (Exception $e) {
    $totalReceipts = $todayReceipts = $thisMonthReceipts = $uniqueDrivers = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipts Management - Fleetly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard-styles.css">
    <style>
        /* Additional styles for receipts page */
        .receipts-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-card.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .stat-card.success {
            background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%);
            color: white;
        }

        .stat-card.info {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #f39c12 0%, #f1c40f 100%);
            color: white;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.1);
        }

        .stat-content {
            flex: 1;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-top: 0.25rem;
        }

        .receipt-text-preview {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            color: #64748b;
            background: #f8fafc;
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
        }

        .receipt-text-preview:hover {
            background: #f1f5f9;
        }

        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            min-width: 32px;
            height: 32px;
            margin-right: 0.5rem;
        }

        .action-btn.view {
            background: #e0f2fe;
            color: #0277bd;
        }

        .action-btn.view:hover {
            background: #b3e5fc;
        }

        .action-btn.delete {
            background: #ffebee;
            color: #c62828;
        }

        .action-btn.delete:hover {
            background: #ffcdd2;
        }

        .receipts-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .receipts-table th,
        .receipts-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .receipts-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #334155;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .receipts-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Modal styles */
        .receipt-modal .modal {
            max-width: 700px;
        }

        .receipt-details {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem;
            margin: 1rem 0;
        }

        .receipt-text {
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
            line-height: 1.5;
            white-space: pre-wrap;
            background: white;
            padding: 1rem;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            max-height: 300px;
            overflow-y: auto;
        }

        .receipt-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .meta-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .meta-value {
            font-weight: 500;
            color: #1e293b;
        }

        /* Loading and empty states */
        .loading-row {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }

        .empty-row {
            text-align: center;
            padding: 3rem 2rem;
            color: #6b7280;
        }

        .error-row {
            text-align: center;
            padding: 2rem;
            color: #ef4444;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .receipts-stats {
                grid-template-columns: 1fr;
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .receipts-table {
                font-size: 0.875rem;
            }

            .receipts-table th,
            .receipts-table td {
                padding: 0.75rem 0.5rem;
            }

            .receipt-text-preview {
                max-width: 150px;
            }
        }
    </style>
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
                <input type="text" placeholder="Search receipts..." class="search-input" id="searchInput">
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
                    <div class="nav-item">
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
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span class="nav-text">Drivers</span>
                    </div>
                    <div class="nav-item active">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14,2 14,8 20,8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10,9 9,9 8,9"></polyline>
                        </svg>
                        <span class="nav-text">Receipts</span>
                    </div>
                    <div class="nav-item">
                        <svg class="nav-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27,6.96 12,12.01 20.73,6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span class="nav-text">Fuel Management</span>
                    </div>
                </div>
            </nav>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="content-header">
            <div class="page-title">
                <h1>Receipts Management</h1>
                <p>Manage and view all uploaded receipts</p>
            </div>
            
            <div class="header-actions">
                <button class="btn-primary" id="refreshBtn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="m3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="receipts-stats">
            <div class="stat-card primary">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14,2 14,8 20,8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="totalReceipts"><?php echo $totalReceipts; ?></div>
                    <div class="stat-label">Total Receipts</div>
                </div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="16,12 12,16 8,12"></polyline>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="todayReceipts"><?php echo $todayReceipts; ?></div>
                    <div class="stat-label">Today's Receipts</div>
                </div>
            </div>
            <div class="stat-card info">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="monthReceipts"><?php echo $thisMonthReceipts; ?></div>
                    <div class="stat-label">This Month</div>
                </div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div class="stat-content">
                    <div class="stat-value" id="uniqueDrivers"><?php echo $uniqueDrivers; ?></div>
                    <div class="stat-label">Unique Drivers</div>
                </div>
            </div>
        </div>

        <!-- Receipts Table -->
        <div class="table-container">
            <div class="table-header">
                <h3>All Receipts</h3>
                <div class="table-actions">
                    <button class="btn-secondary" id="exportBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7,10 12,15 17,10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="receipts-table" id="receiptsTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Receipt ID</th>
                            <th>Driver</th>
                            <th>File Name</th>
                            <th>Text Preview</th>
                            <th>Upload Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="receiptsTableBody">
                        <tr>
                            <td colspan="7" class="loading-row">Loading receipts...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <div class="table-info">
                    Showing <span id="showingFrom">0</span> to <span id="showingTo">0</span> of <span id="totalRecords">0</span> receipts
                </div>
                <div class="table-pagination">
                    <button class="pagination-btn" id="prevBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                    </button>
                    <span class="pagination-info">Page <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
                    <button class="pagination-btn" id="nextBtn" disabled>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Receipt Details Modal -->
    <div class="modal-overlay receipt-modal" id="receiptModal">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitle">Receipt Details</h3>
                <button class="modal-close" id="closeModal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="receipt-details">
                    <div class="receipt-meta" id="receiptMeta">
                        <!-- Receipt metadata will be populated here -->
                    </div>
                    <div class="meta-item">
                        <div class="meta-label">Extracted Text</div>
                        <div class="receipt-text" id="receiptText">
                            <!-- Extracted text will be shown here -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="closeReceiptModal">Close</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal delete-modal">
            <div class="modal-header">
                <h3>Delete Receipt</h3>
                <button class="modal-close" id="closeDeleteModal">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="delete-content">
                    <div class="delete-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    <h4>Are you sure?</h4>
                    <p>This action cannot be undone. This will permanently delete this receipt and all associated data.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelDeleteBtn">Cancel</button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn">
                    <span class="btn-text">Delete Receipt</span>
                    <div class="btn-loader" style="display: none;">
                        <div class="spinner"></div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <div class="toast-content">
            <div class="toast-icon"></div>
            <div class="toast-message"></div>
        </div>
        <button class="toast-close">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <script>
        class ReceiptsManager {
            constructor() {
                this.currentPage = 1;
                this.totalPages = 1;
                this.searchTerm = '';
                this.selectedReceipts = new Set();
                this.deleteReceiptId = null;
                
                this.initializeElements();
                this.bindEvents();
                this.loadReceipts();
            }
            
            initializeElements() {
                // Search and navigation
                this.searchInput = document.getElementById('searchInput');
                this.sidebarToggle = document.getElementById('sidebarToggle');
                this.sidebar = document.getElementById('sidebar');
                this.mainContent = document.getElementById('mainContent');
                
                // Table elements
                this.receiptsTableBody = document.getElementById('receiptsTableBody');
                this.selectAllCheckbox = document.getElementById('selectAll');
                
                // Pagination
                this.prevBtn = document.getElementById('prevBtn');
                this.nextBtn = document.getElementById('nextBtn');
                this.currentPageSpan = document.getElementById('currentPage');
                this.totalPagesSpan = document.getElementById('totalPages');
                this.showingFromSpan = document.getElementById('showingFrom');
                this.showingToSpan = document.getElementById('showingTo');
                this.totalRecordsSpan = document.getElementById('totalRecords');
                
                // Modals
                this.receiptModal = document.getElementById('receiptModal');
                this.deleteModal = document.getElementById('deleteModal');
                this.toast = document.getElementById('toast');
                
                // Buttons
                this.refreshBtn = document.getElementById('refreshBtn');
                this.exportBtn = document.getElementById('exportBtn');
                this.closeModal = document.getElementById('closeModal');
                this.closeReceiptModal = document.getElementById('closeReceiptModal');
                this.closeDeleteModal = document.getElementById('closeDeleteModal');
                this.confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
                this.cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            }
            
            bindEvents() {
                // Search
                if (this.searchInput) {
                    this.searchInput.addEventListener('input', this.debounce(() => {
                        this.searchTerm = this.searchInput.value;
                        this.currentPage = 1;
                        this.loadReceipts();
                    }, 300));
                }
                
                // Sidebar toggle
                if (this.sidebarToggle) {
                    this.sidebarToggle.addEventListener('click', () => {
                        this.sidebar.classList.toggle('collapsed');
                        this.mainContent.classList.toggle('expanded');
                    });
                }
                
                // Pagination
                if (this.prevBtn) {
                    this.prevBtn.addEventListener('click', () => {
                        if (this.currentPage > 1) {
                            this.currentPage--;
                            this.loadReceipts();
                        }
                    });
                }
                
                if (this.nextBtn) {
                    this.nextBtn.addEventListener('click', () => {
                        if (this.currentPage < this.totalPages) {
                            this.currentPage++;
                            this.loadReceipts();
                        }
                    });
                }
                
                // Buttons
                if (this.refreshBtn) {
                    this.refreshBtn.addEventListener('click', () => this.loadReceipts());
                }
                
                if (this.exportBtn) {
                    this.exportBtn.addEventListener('click', () => this.exportReceipts());
                }
                
                // Modals
                if (this.closeModal) {
                    this.closeModal.addEventListener('click', () => this.closeReceiptModal());
                }
                
                if (this.closeReceiptModal) {
                    this.closeReceiptModal.addEventListener('click', () => this.closeReceiptModal());
                }
                
                if (this.closeDeleteModal) {
                    this.closeDeleteModal.addEventListener('click', () => this.closeDeleteModalHandler());
                }
                
                if (this.cancelDeleteBtn) {
                    this.cancelDeleteBtn.addEventListener('click', () => this.closeDeleteModalHandler());
                }
                
                if (this.confirmDeleteBtn) {
                    this.confirmDeleteBtn.addEventListener('click', () => this.confirmDelete());
                }
                
                // Select all
                if (this.selectAllCheckbox) {
                    this.selectAllCheckbox.addEventListener('change', (e) => {
                        const checkboxes = document.querySelectorAll('#receiptsTableBody input[type="checkbox"]');
                        checkboxes.forEach(cb => {
                            cb.checked = e.target.checked;
                            if (e.target.checked) {
                                this.selectedReceipts.add(cb.value);
                            } else {
                                this.selectedReceipts.delete(cb.value);
                            }
                        });
                    });
                }
                
                // Close modals on overlay click
                if (this.receiptModal) {
                    this.receiptModal.addEventListener('click', (e) => {
                        if (e.target === this.receiptModal) {
                            this.closeReceiptModal();
                        }
                    });
                }
                
                if (this.deleteModal) {
                    this.deleteModal.addEventListener('click', (e) => {
                        if (e.target === this.deleteModal) {
                            this.closeDeleteModalHandler();
                        }
                    });
                }
            }
            
            async loadReceipts() {
                console.log('Loading receipts...');
                
                if (this.receiptsTableBody) {
                    this.receiptsTableBody.innerHTML = '<tr><td colspan="7" class="loading-row">Loading receipts...</td></tr>';
                }
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'get_receipts');
                    formData.append('search', this.searchTerm);
                    formData.append('page', this.currentPage);
                    
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const data = await response.json();
                    console.log('Received data:', data);
                    
                    if (data.success) {
                        this.renderReceipts(data.receipts);
                        this.updatePagination(data.total, data.page, data.totalPages);
                        this.updateStats(data.total);
                    } else {
                        this.receiptsTableBody.innerHTML = `<tr><td colspan="7" class="error-row">Error: ${data.error || 'Unknown error'}</td></tr>`;
                    }
                } catch (error) {
                    console.error('Error loading receipts:', error);
                    if (this.receiptsTableBody) {
                        this.receiptsTableBody.innerHTML = `<tr><td colspan="7" class="error-row">Failed to load receipts: ${error.message}</td></tr>`;
                    }
                }
            }
            
            renderReceipts(receipts) {
                if (!this.receiptsTableBody) return;
                
                if (!receipts || receipts.length === 0) {
                    this.receiptsTableBody.innerHTML = '<tr><td colspan="7" class="empty-row">No receipts found</td></tr>';
                    return;
                }
                
                this.receiptsTableBody.innerHTML = '';
                
                receipts.forEach(receipt => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>
                            <input type="checkbox" value="${receipt.id}" onchange="receiptsManager.toggleReceiptSelection(${receipt.id}, this.checked)">
                        </td>
                        <td>
                            <span style="font-weight: 600; color: #1e293b;">#${receipt.id}</span>
                        </td>
                        <td>
                            <div>
                                <div style="font-weight: 500; color: #1e293b;">${receipt.full_name || 'Unknown Driver'}</div>
                                <div style="font-size: 0.8rem; color: #64748b;">ID: ${receipt.driver_id}</div>
                            </div>
                        </td>
                        <td>
                            <span style="font-family: 'Courier New', monospace; font-size: 0.85rem; color: #475569;">
                                ${receipt.file_name || 'N/A'}
                            </span>
                        </td>
                        <td>
                            <div class="receipt-text-preview" title="${this.escapeHtml(receipt.extracted_text)}" onclick="receiptsManager.viewReceipt(${receipt.id})">
                                ${this.truncateText(receipt.extracted_text, 50)}
                            </div>
                        </td>
                        <td>
                            <div>
                                <div style="font-weight: 500; color: #1e293b;">${this.formatDate(receipt.created_at)}</div>
                                <div style="font-size: 0.8rem; color: #64748b;">${this.formatTime(receipt.created_at)}</div>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <button class="action-btn view" onclick="receiptsManager.viewReceipt(${receipt.id})" title="View Receipt">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                                <button class="action-btn delete" onclick="receiptsManager.confirmDeleteReceipt(${receipt.id})" title="Delete Receipt">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <polyline points="3,6 5,6 21,6"></polyline>
                                        <path d="m19,6v14a2,2 0 0,1 -2,2H7a2,2 0 0,1 -2,-2V6m3,0V4a2,2 0 0,1 2,-2h4a2,2 0 0,1 2,2v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    `;
                    this.receiptsTableBody.appendChild(row);
                });
                
                // Reset select all checkbox
                if (this.selectAllCheckbox) {
                    this.selectAllCheckbox.checked = false;
                }
                this.selectedReceipts.clear();
            }
            
            async viewReceipt(receiptId) {
                console.log('Viewing receipt:', receiptId);
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'get_receipt_details');
                    formData.append('receipt_id', receiptId);
                    
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.showReceiptDetails(data.receipt);
                    } else {
                        this.showToast('Failed to load receipt details: ' + data.message, 'error');
                    }
                } catch (error) {
                    console.error('Error loading receipt details:', error);
                    this.showToast('Failed to load receipt details', 'error');
                }
            }
            
            showReceiptDetails(receipt) {
                const receiptMeta = document.getElementById('receiptMeta');
                const receiptText = document.getElementById('receiptText');
                
                if (receiptMeta) {
                    receiptMeta.innerHTML = `
                        <div class="meta-item">
                            <div class="meta-label">Receipt ID</div>
                            <div class="meta-value">#${receipt.id}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Driver</div>
                            <div class="meta-value">${receipt.full_name || 'Unknown Driver'}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Driver ID</div>
                            <div class="meta-value">${receipt.driver_id}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">File Name</div>
                            <div class="meta-value">${receipt.file_name || 'N/A'}</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Upload Date</div>
                            <div class="meta-value">${this.formatDateTime(receipt.created_at)}</div>
                        </div>
                    `;
                }
                
                if (receiptText) {
                    receiptText.textContent = receipt.extracted_text;
                }
                
                if (this.receiptModal) {
                    this.receiptModal.classList.add('active');
                }
            }
            
            closeReceiptModal() {
                if (this.receiptModal) {
                    this.receiptModal.classList.remove('active');
                }
            }
            
            confirmDeleteReceipt(receiptId) {
                this.deleteReceiptId = receiptId;
                if (this.deleteModal) {
                    this.deleteModal.classList.add('active');
                }
            }
            
            closeDeleteModalHandler() {
                if (this.deleteModal) {
                    this.deleteModal.classList.remove('active');
                }
                this.deleteReceiptId = null;
            }
            
            async confirmDelete() {
                if (!this.deleteReceiptId) return;
                
                const confirmBtn = this.confirmDeleteBtn;
                if (!confirmBtn) return;
                
                const btnText = confirmBtn.querySelector('.btn-text');
                const btnLoader = confirmBtn.querySelector('.btn-loader');
                
                if (btnText) btnText.style.display = 'none';
                if (btnLoader) btnLoader.style.display = 'flex';
                confirmBtn.disabled = true;
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'delete_receipt');
                    formData.append('receipt_id', this.deleteReceiptId);
                    
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.showToast('Receipt deleted successfully', 'success');
                        this.closeDeleteModalHandler();
                        this.loadReceipts();
                    } else {
                        this.showToast(data.message || 'Failed to delete receipt', 'error');
                    }
                } catch (error) {
                    console.error('Error deleting receipt:', error);
                    this.showToast('Failed to delete receipt', 'error');
                } finally {
                    if (btnText) btnText.style.display = 'inline';
                    if (btnLoader) btnLoader.style.display = 'none';
                    confirmBtn.disabled = false;
                }
            }
            
            toggleReceiptSelection(receiptId, isSelected) {
                if (isSelected) {
                    this.selectedReceipts.add(receiptId);
                } else {
                    this.selectedReceipts.delete(receiptId);
                }
                
                // Update select all checkbox
                const totalCheckboxes = document.querySelectorAll('#receiptsTableBody input[type="checkbox"]').length;
                if (this.selectAllCheckbox) {
                    this.selectAllCheckbox.checked = this.selectedReceipts.size === totalCheckboxes;
                    this.selectAllCheckbox.indeterminate = this.selectedReceipts.size > 0 && this.selectedReceipts.size < totalCheckboxes;
                }
            }
            
            updatePagination(total, page, totalPages) {
                this.totalPages = totalPages;
                this.currentPage = page;
                
                if (this.currentPageSpan) this.currentPageSpan.textContent = page;
                if (this.totalPagesSpan) this.totalPagesSpan.textContent = totalPages;
                
                const itemsPerPage = 10;
                const showingFrom = total > 0 ? ((page - 1) * itemsPerPage) + 1 : 0;
                const showingTo = Math.min(page * itemsPerPage, total);
                
                if (this.showingFromSpan) this.showingFromSpan.textContent = showingFrom;
                if (this.showingToSpan) this.showingToSpan.textContent = showingTo;
                if (this.totalRecordsSpan) this.totalRecordsSpan.textContent = total;
                
                if (this.prevBtn) this.prevBtn.disabled = page <= 1;
                if (this.nextBtn) this.nextBtn.disabled = page >= totalPages;
            }
            
            updateStats(total) {
                const totalRecordsEl = document.getElementById('totalRecords');
                if (totalRecordsEl) {
                    totalRecordsEl.textContent = total;
                }
            }
            
            exportReceipts() {
                this.showToast('Export functionality would be implemented here', 'info');
            }
            
            showToast(message, type = 'info') {
                if (!this.toast) return;
                
                const toastMessage = this.toast.querySelector('.toast-message');
                const toastIcon = this.toast.querySelector('.toast-icon');
                
                if (toastMessage) toastMessage.textContent = message;
                
                // Set icon based on type
                let iconSvg = '';
                switch (type) {
                    case 'success':
                        iconSvg = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <polyline points="20,6 9,17 4,12"></polyline>
                        </svg>`;
                        this.toast.className = 'toast show success';
                        break;
                    case 'error':
                        iconSvg = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>`;
                        this.toast.className = 'toast show error';
                        break;
                    default:
                        iconSvg = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>`;
                        this.toast.className = 'toast show info';
                }
                
                if (toastIcon) toastIcon.innerHTML = iconSvg;
                
                setTimeout(() => {
                    this.toast.classList.remove('show');
                }, 5000);
                
                const toastClose = this.toast.querySelector('.toast-close');
                if (toastClose) {
                    toastClose.onclick = () => {
                        this.toast.classList.remove('show');
                    };
                }
            }
            
            // Utility methods
            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
            
            formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric' 
                });
            }
            
            formatTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleTimeString('en-US', { 
                    hour: '2-digit', 
                    minute: '2-digit'
                });
            }
            
            formatDateTime(dateString) {
                const date = new Date(dateString);
                return date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }
            
            truncateText(text, maxLength) {
                if (!text || text.length <= maxLength) return text || '';
                return text.substring(0, maxLength) + '...';
            }
            
            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text || '';
                return div.innerHTML;
            }
        }
        
        // Initialize the receipts manager when the page loads
        let receiptsManager;
        document.addEventListener('DOMContentLoaded', () => {
            console.log('DOM loaded, initializing receipts manager...');
            receiptsManager = new ReceiptsManager();
        });
    </script>
</body>
</html>
