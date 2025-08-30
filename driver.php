<?php
// driver_dashboard.php - Mobile-Optimized Driver Dashboard
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

// Configuration
$GEOAPIFY_API_KEY = '053f0cbc8d894135bd0fdb09c21d1620';
$OPENROUTER_API_KEY = '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';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Driver Dashboard - Smart Fleet Management</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
            height: 100%;
            overflow: hidden;
            position: fixed;
            width: 100%;
            -webkit-font-smoothing: antialiased;
        }

        /* Header - Mobile Optimized */
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 100;
            min-height: 60px;
        }

        .header-left {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .header h1 {
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Profile Icon */
        .profile-icon {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }

        .profile-icon:hover {
            background: rgba(255, 255, 255, 0.25);
            border-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.05);
        }

        .profile-icon i {
            font-size: 20px;
            color: white;
        }

        /* Status indicator */
        .status-dot {
            position: absolute;
            top: -2px;
            right: -2px;
            width: 12px;
            height: 12px;
            background: #4ade80;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 0 1px rgba(0,0,0,0.1);
        }

        .status-dot.inactive {
            background: #f87171;
        }

        /* Driver name tooltip */
        .driver-tooltip {
            position: absolute;
            bottom: -35px;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            pointer-events: none;
            z-index: 1000;
        }

        .profile-icon:hover .driver-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(-5px);
        }

        /* Main Content */
        .main-content {
            height: calc(100vh - 130px);
            position: relative;
            overflow: hidden;
        }

        #map {
            width: 100%;
            height: 100%;
            background: #f0f0f0;
        }

        /* Upload Modal - Mobile Optimized */
        .upload-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.85);
            display: none;
            z-index: 2000;
            padding: 0;
            align-items: flex-end;
            justify-content: center;
        }

        .upload-modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px 20px 0 0;
            padding: 20px;
            width: 100%;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
            }
            to {
                transform: translateY(0);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-header h3 {
            color: #2c3e50;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        .close-modal {
            background: #f8f9fa;
            border: none;
            font-size: 18px;
            color: #666;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .close-modal:hover {
            background: #e9ecef;
            color: #333;
        }

        /* Upload Area - Mobile Friendly */
        .upload-area {
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 30px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
            background: #fafbfc;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .upload-area:active {
            transform: scale(0.98);
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: #667eea;
            background: #f8f9ff;
            transform: translateY(-2px);
        }

        .upload-icon {
            font-size: 40px;
            color: #667eea;
            margin-bottom: 12px;
        }

        .upload-text {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .upload-subtext {
            font-size: 13px;
            color: #718096;
            line-height: 1.4;
        }

        .file-selected {
            background: #e6fffa;
            border-color: #38b2ac;
            color: #234e52;
        }

        .file-selected .upload-icon {
            color: #38b2ac;
        }

        /* Buttons - Touch Friendly */
        .btn {
            padding: 14px 24px;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            justify-content: center;
            min-width: 130px;
            min-height: 48px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn:active {
            transform: scale(0.96);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
        }

        .btn-success:hover:not(:disabled) {
            box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #718096;
            color: white;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
        }

        /* OCR Results */
        .ocr-results {
            background: black;
            border-radius: 10px;
            padding: 16px;
            margin: 20px 0;
            max-height: 180px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
        }

        .ocr-results pre {
            white-space: pre-wrap;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            margin: 0;
            color: #fff;
            line-height: 1.5;
            font-size: 14px;
        }

        /* Bottom Navigation - Enhanced */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e9ecef;
            box-shadow: 0 -2px 20px rgba(0,0,0,0.08);
            z-index: 200;
            height: 70px;
            padding-bottom: env(safe-area-inset-bottom);
        }

        .nav-items {
            display: flex;
            height: 100%;
            align-items: center;
        }

        .nav-item {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #8e8e93;
            text-decoration: none;
            font-size: 11px;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
            padding: 8px 4px;
            border-radius: 8px;
            margin: 0 2px;
            min-height: 54px;
            position: relative;
        }

        .nav-item:active {
            transform: scale(0.95);
        }

        .nav-item.active {
            color: #667eea;
        }

        .nav-item i {
            font-size: 22px;
            margin-bottom: 2px;
        }

        .nav-item.receipt-btn {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            margin: 0 8px;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.3);
            font-size: 10px;
            transform: translateY(-8px);
        }

        .nav-item.receipt-btn:hover {
            box-shadow: 0 6px 20px rgba(72, 187, 120, 0.4);
            color: white;
        }

        .nav-item.receipt-btn i {
            font-size: 26px;
            margin-bottom: 0;
        }

        /* Loading and Alerts */
        .loading {
            opacity: 0.7;
            pointer-events: none;
        }

        .spinner {
            border: 2px solid transparent;
            border-top: 2px solid currentColor;
            border-radius: 50%;
            width: 16px;
            height: 16px;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            border: 1px solid;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .alert-success {
            background: #f0fff4;
            border-color: #9ae6b4;
            color: #276749;
        }

        .alert-error {
            background: #fed7d7;
            border-color: #fc8181;
            color: #742a2a;
        }

        .alert-info {
            background: #ebf8ff;
            border-color: #90cdf4;
            color: #2c5282;
        }

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .header {
                padding: 10px 16px;
                min-height: 56px;
            }

            .header h1 {
                font-size: 16px;
            }

            .profile-icon {
                width: 38px;
                height: 38px;
            }

            .profile-icon i {
                font-size: 18px;
            }

            .main-content {
                height: calc(100vh - 126px);
            }

            .modal-content {
                padding: 16px;
                border-radius: 16px 16px 0 0;
            }

            .upload-area {
                padding: 24px 16px;
                min-height: 120px;
            }

            .upload-icon {
                font-size: 36px;
            }

            .nav-item {
                font-size: 10px;
                padding: 6px 2px;
            }

            .nav-item i {
                font-size: 20px;
            }

            .nav-item.receipt-btn {
                width: 52px;
                height: 52px;
                transform: translateY(-6px);
            }

            .nav-item.receipt-btn i {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .header h1 {
                font-size: 15px;
            }

            .main-content {
                height: calc(100vh - 124px);
            }

            .modal-content {
                padding: 14px;
            }

            .btn {
                padding: 12px 20px;
                font-size: 14px;
            }

            .nav-item {
                font-size: 9px;
            }

            .nav-item i {
                font-size: 18px;
            }
        }

        /* Utility Classes */
        .hidden {
            display: none !important;
        }

        .text-center {
            text-align: center;
        }

        /* Prevent zoom on input focus */
        input[type="file"] {
            font-size: 16px;
        }

        /* Safe area support for notched devices */
        @supports (padding: env(safe-area-inset-bottom)) {
            .bottom-nav {
                padding-bottom: env(safe-area-inset-bottom);
                height: calc(70px + env(safe-area-inset-bottom));
            }

            body {
                padding-bottom: calc(70px + env(safe-area-inset-bottom));
            }

            .main-content {
                height: calc(100vh - 126px - env(safe-area-inset-bottom));
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .modal-content {
                background: #1a1a1a;
                color: white;
            }

            .modal-header h3 {
                color: white;
            }

            .upload-area {
                background: #2a2a2a;
                border-color: #404040;
            }

            .upload-text {
                color: white;
            }

            .ocr-results {
                background: #2a2a2a;
                border-color: #404040;
                color: white;
            }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="header-left">
        <h1>
            <i class="fas fa-tachometer-alt"></i> 
            Driver Dashboard
        </h1>
    </div>
    
    <!-- Profile Icon -->
    <div class="profile-icon" onclick="redirectToProfile()">
        <i class="fas fa-user"></i>
        <div class="status-dot <?php echo $current_driver['status'] !== 'Active' ? 'inactive' : ''; ?>"></div>
        <div class="driver-tooltip">
            <?php echo htmlspecialchars($current_driver['full_name']); ?>
            <br>
            <small><?php echo $current_driver['status']; ?></small>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <div id="map"></div>
</div>

<!-- Receipt Upload Modal -->
<div class="upload-modal" id="uploadModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>
                <i class="fas fa-receipt"></i>
                Upload Receipt
            </h3>
            <button class="close-modal" onclick="closeUploadModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Upload Area -->
        <div class="upload-area" id="uploadArea" onclick="document.getElementById('receiptFile').click()">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <div class="upload-text">Tap to upload receipt</div>
            <div class="upload-subtext">JPG, PNG, PDF supported<br>Maximum size: 10MB</div>
        </div>

        <input type="file" id="receiptFile" accept="image/*,.pdf" style="display: none;">

        <!-- Process Button -->
        <div class="text-center">
            <button id="processBtn" class="btn btn-primary" disabled onclick="processReceipt()">
                <i class="fas fa-magic"></i>
                Extract Text
            </button>
        </div>

        <!-- Results Area -->
        <div id="resultsSection" class="hidden">
            <div id="ocrResults" class="ocr-results"></div>
            <div class="btn-group">
                <button id="saveBtn" class="btn btn-success" onclick="saveReceipt()">
                    <i class="fas fa-save"></i>
                    Save Receipt
                </button>
                <button id="clearBtn" class="btn btn-secondary" onclick="clearResults()">
                    <i class="fas fa-times"></i>
                    Clear Results
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
    <div class="nav-items">
        <a href="#" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        
        <a href="routes_dashboard.php" class="nav-item">
            <i class="fas fa-route"></i>
            <span>Routes</span>
        </a>
        
        <div class="nav-item receipt-btn" onclick="openUploadModal()">
            <i class="fas fa-receipt"></i>
            <span>Receipt</span>
        </div>
        
        <a href="#" class="nav-item">
            <i class="fas fa-history"></i>
            <span>History</span>
        </a>
        
        <a href="profile.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </div>
</div>

<!-- Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Global variables
let selectedFile = null;
let map = null;

// Initialize map
function initMap() {
    try {
        map = L.map('map', {
            zoomControl: false,
            attributionControl: false
        }).setView([26.2389, 73.0243], 12);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            maxZoom: 19,
        }).addTo(map);

        // Add zoom control to bottom right
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Add driver location marker
        const driverMarker = L.marker([26.2389, 73.0243])
            .bindPopup(`
                <div style="text-align: center; padding: 8px;">
                    <strong><?php echo htmlspecialchars($current_driver['full_name']); ?></strong><br>
                    <small style="color: #666;">Current Location</small>
                </div>
            `)
            .addTo(map);

        // Handle map resize
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    } catch (error) {
        console.error('Map initialization error:', error);
    }
}

// Profile redirect
function redirectToProfile() {
    window.location.href = 'profile.php';
}

// Modal functions
function openUploadModal() {
    document.getElementById('uploadModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.remove('active');
    document.body.style.overflow = '';
    clearResults();
}

// Initialize everything
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    setupFileHandling();
    setupModalHandling();
});

function setupFileHandling() {
    const fileInput = document.getElementById('receiptFile');
    const uploadArea = document.getElementById('uploadArea');
    
    fileInput.addEventListener('change', function(e) {
        handleFileSelect(e.target.files[0]);
    });

    // Touch-friendly drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        if (!uploadArea.contains(e.relatedTarget)) {
            uploadArea.classList.remove('dragover');
        }
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        const file = e.dataTransfer.files[0];
        if (file) {
            handleFileSelect(file);
        }
    });
}

function setupModalHandling() {
    document.getElementById('uploadModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeUploadModal();
        }
    });

    // Prevent body scroll when modal is open
    document.getElementById('uploadModal').addEventListener('touchmove', function(e) {
        if (e.target === this) {
            e.preventDefault();
        }
    }, { passive: false });
}

function handleFileSelect(file) {
    if (!file) return;

    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!validTypes.includes(file.type)) {
        showAlert('Please select a valid image file (JPG, PNG) or PDF', 'error');
        return;
    }

    if (file.size > 10 * 1024 * 1024) {
        showAlert('File size must be less than 10MB', 'error');
        return;
    }

    selectedFile = file;
    document.getElementById('processBtn').disabled = false;
    
    const uploadArea = document.getElementById('uploadArea');
    uploadArea.classList.add('file-selected');
    uploadArea.innerHTML = `
        <div class="upload-icon">
            <i class="fas fa-file-check"></i>
        </div>
        <div class="upload-text">${file.name}</div>
        <div class="upload-subtext">Ready to process</div>
    `;

    // Add haptic feedback if available
    if (navigator.vibrate) {
        navigator.vibrate(50);
    }
}

async function processReceipt() {
    if (!selectedFile) return;

    const processBtn = document.getElementById('processBtn');
    const originalText = processBtn.innerHTML;
    
    processBtn.innerHTML = '<div class="spinner"></div>Processing...';
    processBtn.classList.add('loading');

    try {
        const formData = new FormData();
        formData.append('receipt', selectedFile);

        const response = await fetch('process_receipt.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            displayResults(result.extracted_text);
            if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
        } else {
            showAlert('Error processing receipt: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('OCR Error:', error);
        showAlert('Failed to process receipt. Please check your connection and try again.', 'error');
    } finally {
        processBtn.innerHTML = originalText;
        processBtn.classList.remove('loading');
    }
}

function displayResults(extractedText) {
    const resultsSection = document.getElementById('resultsSection');
    const resultsDiv = document.getElementById('ocrResults');
    
    resultsDiv.innerHTML = `<pre>${extractedText}</pre>`;
    resultsSection.classList.remove('hidden');
    
    showAlert('Text extracted successfully!', 'success');
    
    // Scroll to results
    setTimeout(() => {
        resultsSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);
}

async function saveReceipt() {
    const extractedText = document.querySelector('#ocrResults pre').textContent;
    
    const saveBtn = document.getElementById('saveBtn');
    const originalText = saveBtn.innerHTML;
    
    saveBtn.innerHTML = '<div class="spinner"></div>Saving...';
    saveBtn.classList.add('loading');
    
    try {
        const response = await fetch('save_receipt.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                driver_id: '<?php echo $current_driver_id; ?>',
                extracted_text: extractedText,
                file_name: selectedFile.name
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success) {
            showAlert('Receipt saved successfully!', 'success');
            if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
            
            setTimeout(() => {
                closeUploadModal();
            }, 1500);
        } else {
            showAlert('Error saving receipt: ' + result.error, 'error');
        }
    } catch (error) {
        console.error('Save Error:', error);
        showAlert('Failed to save receipt. Please check your connection and try again.', 'error');
    } finally {
        saveBtn.innerHTML = originalText;
        saveBtn.classList.remove('loading');
    }
}

function clearResults() {
    document.getElementById('resultsSection').classList.add('hidden');
    document.getElementById('processBtn').disabled = true;
    selectedFile = null;
    
    const uploadArea = document.getElementById('uploadArea');
    uploadArea.classList.remove('file-selected');
    uploadArea.innerHTML = `
        <div class="upload-icon">
            <i class="fas fa-cloud-upload-alt"></i>
        </div>
        <div class="upload-text">Tap to upload receipt</div>
        <div class="upload-subtext">JPG, PNG, PDF supported<br>Maximum size: 10MB</div>
    `;
    
    document.getElementById('receiptFile').value = '';
}

function showAlert(message, type) {
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        ${message}
    `;
    
    const modalContent = document.querySelector('.modal-content');
    modalContent.insertBefore(alert, modalContent.firstChild);
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

// Handle orientation changes
window.addEventListener('orientationchange', function() {
    setTimeout(() => {
        if (map) {
            map.invalidateSize();
        }
    }, 100);
});

// Handle window resize
window.addEventListener('resize', function() {
    if (map) {
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    }
});

// Prevent double-tap zoom on buttons
document.addEventListener('touchend', function(event) {
    if (event.target.closest('.btn') || event.target.closest('.nav-item')) {
        event.preventDefault();
        event.target.click();
    }
});
</script>

</body>
</html>
