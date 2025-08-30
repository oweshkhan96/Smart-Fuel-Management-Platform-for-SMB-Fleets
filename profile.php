<?php
// profile.php - Driver Profile View
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

// Fetch current driver info
$current_driver_id = $_SESSION['driver_id'] ?? 'DRV001';
$stmt = $pdo->prepare("SELECT * FROM drivers WHERE driver_id = ?");
$stmt->execute([$current_driver_id]);
$current_driver = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current_driver) {
    header('Location: login.php');
    exit;
}

// Helper function to format dates
function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

// Helper function to calculate age
function calculateAge($birthDate) {
    $today = new DateTime();
    $birthDate = new DateTime($birthDate);
    $age = $today->diff($birthDate);
    return $age->y;
}

// Helper function to check license expiry status
function getLicenseStatus($expiryDate) {
    $today = new DateTime();
    $expiry = new DateTime($expiryDate);
    $diff = $today->diff($expiry);
    
    if ($expiry < $today) {
        return ['status' => 'expired', 'text' => 'Expired', 'class' => 'danger'];
    } elseif ($diff->days <= 30) {
        return ['status' => 'expiring', 'text' => 'Expires in ' . $diff->days . ' days', 'class' => 'warning'];
    } else {
        return ['status' => 'valid', 'text' => 'Valid', 'class' => 'success'];
    }
}

$licenseStatus = getLicenseStatus($current_driver['license_expiry']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Driver Profile - <?php echo htmlspecialchars($current_driver['full_name']); ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
            -webkit-tap-highlight-color: transparent;
        }
        
        html, body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
            min-height: 100%;
            -webkit-font-smoothing: antialiased;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 100;
            min-height: 60px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            padding: 8px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 40px;
            height: 40px;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(-2px);
        }

        .back-btn:active {
            transform: scale(0.95) translateX(-2px);
        }

        .header-title {
            flex: 1;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Main Content */
        .main-content {
            padding: 20px 16px;
            max-width: 600px;
            margin: 0 auto;
            padding-bottom: 40px;
        }

        /* Profile Header Card */
        .profile-header {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            position: relative;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .profile-avatar i {
            font-size: 32px;
            color: white;
        }

        .status-badge {
            position: absolute;
            bottom: 2px;
            right: 2px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid white;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .status-badge.active {
            background: #4ade80;
        }

        .status-badge.inactive {
            background: #f87171;
        }

        .status-badge.on-leave {
            background: #fbbf24;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .profile-id {
            color: #64748b;
            font-size: 14px;
            font-weight: 500;
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 16px;
        }

        .profile-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .profile-status.active {
            background: #dcfce7;
            color: #166534;
        }

        .profile-status.inactive {
            background: #fef2f2;
            color: #991b1b;
        }

        .profile-status.on-leave {
            background: #fefce8;
            color: #a16207;
        }

        /* Info Cards */
        .info-section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }

        .info-grid {
            display: grid;
            gap: 16px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .info-label {
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            word-break: break-word;
        }

        .info-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background: #f1f5f9;
            border-radius: 8px;
            color: #667eea;
            font-size: 14px;
            margin-right: 12px;
            flex-shrink: 0;
        }

        /* License Status */
        .license-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
        }

        .license-status.success {
            background: #dcfce7;
            color: #166534;
        }

        .license-status.warning {
            background: #fefce8;
            color: #a16207;
        }

        .license-status.danger {
            background: #fef2f2;
            color: #991b1b;
        }

        /* Company Badge */
        .company-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            margin-top: 8px;
        }

        /* Responsive Grid */
        @media (min-width: 640px) {
            .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .main-content {
                padding: 16px 12px;
            }

            .profile-header {
                padding: 20px;
                margin-bottom: 16px;
            }

            .profile-avatar {
                width: 70px;
                height: 70px;
            }

            .profile-avatar i {
                font-size: 28px;
            }

            .profile-name {
                font-size: 22px;
            }

            .header-title {
                font-size: 16px;
            }

            .section-title {
                font-size: 16px;
            }

            .info-card {
                padding: 16px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 12px 8px;
            }

            .profile-header {
                padding: 16px;
            }

            .profile-name {
                font-size: 20px;
            }

            .info-card {
                padding: 14px;
            }

            .info-value {
                font-size: 15px;
            }
        }

        /* Animations */
        .info-card {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .info-card:nth-child(1) { animation-delay: 0.1s; }
        .info-card:nth-child(2) { animation-delay: 0.2s; }
        .info-card:nth-child(3) { animation-delay: 0.3s; }
        .info-card:nth-child(4) { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-header {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background: #1a1a1a;
                color: white;
            }

            .info-card, .profile-header {
                background: #2a2a2a;
                border-color: #404040;
            }

            .profile-name {
                color: white;
            }

            .info-value {
                color: white;
            }

            .info-icon {
                background: #404040;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <button class="back-btn" onclick="goBack()">
        <i class="fas fa-arrow-left"></i>
    </button>
    <div class="header-title">
        <i class="fas fa-user"></i>
        Driver Profile
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-avatar">
            <i class="fas fa-user"></i>
            <div class="status-badge <?php echo strtolower(str_replace(' ', '-', $current_driver['status'])); ?>">
                <i class="fas fa-circle" style="font-size: 8px;"></i>
            </div>
        </div>
        
        <h1 class="profile-name"><?php echo htmlspecialchars($current_driver['full_name']); ?></h1>
        <div class="profile-id">ID: <?php echo htmlspecialchars($current_driver['driver_id']); ?></div>
        
        <div class="profile-status <?php echo strtolower(str_replace(' ', '-', $current_driver['status'])); ?>">
            <i class="fas fa-circle" style="font-size: 8px;"></i>
            <?php echo htmlspecialchars($current_driver['status']); ?>
        </div>
        
        <div class="company-badge">
            <i class="fas fa-building"></i>
            Company: <?php echo htmlspecialchars($current_driver['company_id']); ?>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="info-section">
        <h2 class="section-title">
            <i class="fas fa-address-card"></i>
            Contact Information
        </h2>
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Email Address</div>
                    <div class="info-value">
                        <i class="fas fa-envelope info-icon"></i>
                        <?php echo htmlspecialchars($current_driver['email']); ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value">
                        <i class="fas fa-phone info-icon"></i>
                        <?php echo htmlspecialchars($current_driver['phone']); ?>
                    </div>
                </div>
                
                <?php if ($current_driver['address']): ?>
                <div class="info-item" style="grid-column: 1 / -1;">
                    <div class="info-label">Address</div>
                    <div class="info-value">
                        <i class="fas fa-map-marker-alt info-icon"></i>
                        <?php echo nl2br(htmlspecialchars($current_driver['address'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Personal Information -->
    <div class="info-section">
        <h2 class="section-title">
            <i class="fas fa-user-circle"></i>
            Personal Information
        </h2>
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value">
                        <i class="fas fa-birthday-cake info-icon"></i>
                        <?php echo formatDate($current_driver['date_of_birth']); ?>
                        <small style="color: #64748b; display: block; margin-top: 4px;">
                            Age: <?php echo calculateAge($current_driver['date_of_birth']); ?> years
                        </small>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Member Since</div>
                    <div class="info-value">
                        <i class="fas fa-calendar-plus info-icon"></i>
                        <?php echo formatDate($current_driver['created_at']); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- License Information -->
    <div class="info-section">
        <h2 class="section-title">
            <i class="fas fa-id-card"></i>
            License Information
        </h2>
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">License Number</div>
                    <div class="info-value">
                        <i class="fas fa-id-badge info-icon"></i>
                        <?php echo htmlspecialchars($current_driver['license_number']); ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Expiry Date</div>
                    <div class="info-value">
                        <i class="fas fa-calendar-times info-icon"></i>
                        <?php echo formatDate($current_driver['license_expiry']); ?>
                        <div class="license-status <?php echo $licenseStatus['class']; ?>">
                            <i class="fas fa-<?php echo $licenseStatus['status'] === 'valid' ? 'check-circle' : ($licenseStatus['status'] === 'expiring' ? 'exclamation-triangle' : 'times-circle'); ?>"></i>
                            <?php echo $licenseStatus['text']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Account Information -->
    <div class="info-section">
        <h2 class="section-title">
            <i class="fas fa-cog"></i>
            Account Information
        </h2>
        <div class="info-card">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Last Updated</div>
                    <div class="info-value">
                        <i class="fas fa-clock info-icon"></i>
                        <?php echo date('M j, Y \a\t g:i A', strtotime($current_driver['updated_at'])); ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Account Status</div>
                    <div class="info-value">
                        <i class="fas fa-shield-alt info-icon"></i>
                        Verified Account
                        <small style="color: #059669; display: block; margin-top: 4px;">
                            <i class="fas fa-check-circle" style="font-size: 12px;"></i>
                            Profile Complete
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function goBack() {
    // Check if there's a previous page in history
    if (document.referrer && document.referrer !== '') {
        window.history.back();
    } else {
        // Fallback to driver dashboard
        window.location.href = 'driver_dashboard.php';
    }
}

// Add touch feedback for buttons
document.addEventListener('touchstart', function(e) {
    if (e.target.closest('.back-btn')) {
        e.target.closest('.back-btn').style.transform = 'scale(0.95) translateX(-2px)';
    }
});

document.addEventListener('touchend', function(e) {
    if (e.target.closest('.back-btn')) {
        setTimeout(() => {
            e.target.closest('.back-btn').style.transform = '';
        }, 150);
    }
});

// Prevent double-tap zoom
document.addEventListener('touchend', function(event) {
    if (event.target.closest('.back-btn')) {
        event.preventDefault();
        event.target.closest('.back-btn').click();
    }
});
</script>

</body>
</html>
