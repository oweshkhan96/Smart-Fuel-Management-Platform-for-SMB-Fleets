<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - Fleetly Smart Fuel Management</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="favicon.png" />
  <link rel="stylesheet" href="styles.css">
</head>
<body class="signup-page">
  <!-- Background Image -->
  <div class="signup-background">
    <div class="background-overlay"></div>
  </div>

  <!-- Signup Container -->
  <div class="signup-container">
    <!-- Left Side - Background Image -->
    <div class="signup-left">
      <div class="background-content">
        <div class="logo-section">
          <svg width="150" height="50" viewBox="0 0 150 50" fill="none">
            <rect width="150" height="50" rx="12" fill="#1C4E80"/>
            <text x="15" y="32" fill="white" font-family="Inter" font-weight="700" font-size="20">Fleetly</text>
          </svg>
          <p class="tagline">Smart Fuel Management</p>
        </div>
        
        <div class="benefits-list">
          <div class="benefit-item">
            <div class="benefit-icon">✓</div>
            <div>
              <h4>Real-Time Tracking</h4>
              <p>Monitor your fleet's fuel consumption in real-time</p>
            </div>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon">✓</div>
            <div>
              <h4>Cost Reduction</h4>
              <p>Reduce fuel costs by up to 25% with smart insights</p>
            </div>
          </div>
          <div class="benefit-item">
            <div class="benefit-icon">✓</div>
            <div>
              <h4>Easy Integration</h4>
              <p>Get started in minutes with our simple setup</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right Side - Signup Form -->
    <div class="signup-right">
      <div class="signup-form-container">
        <!-- Progress Indicator -->
        <div class="progress-indicator">
          <div class="progress-step active" data-step="1">
            <div class="step-number">1</div>
            <span>Personal Info</span>
          </div>
          <div class="progress-line"></div>
          <div class="progress-step" data-step="2">
            <div class="step-number">2</div>
            <span>Business Type</span>
          </div>
          <div class="progress-line"></div>
          <div class="progress-step" data-step="3">
            <div class="step-number">3</div>
            <span>Business Details</span>
          </div>
        </div>

        <!-- Signup Form -->
        <form id="signupForm" class="signup-form" novalidate>
          <!-- Step 1: Personal Information -->
          <div class="form-step active" data-step="1">
            <div class="step-header">
              <h2>Create an account</h2>
              <p class="step-subtitle">Step 1: Personal Info</p>
            </div>

            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" id="email" name="email" required placeholder="Enter your email">
              <span class="error-message"></span>
            </div>

            <div class="form-group">
              <label for="fullName">Full Name</label>
              <input type="text" id="fullName" name="fullName" required placeholder="Enter your full name">
              <span class="error-message"></span>
            </div>

            <div class="form-group">
              <label for="country">Country</label>
              <select id="country" name="country" required>
                <option value="">Select your country</option>
                <option value="India" selected>India</option>
                <option value="United States">United States</option>
                <option value="United Kingdom">United Kingdom</option>
                <option value="Canada">Canada</option>
                <option value="Australia">Australia</option>
                <option value="Germany">Germany</option>
                <option value="France">France</option>
                <option value="Singapore">Singapore</option>
                <option value="UAE">UAE</option>
                <option value="Other">Other</option>
              </select>
              <span class="error-message"></span>
            </div>

            <div class="form-group phone-group">
              <label for="phone">Phone Number</label>
              <div class="phone-input">
                <div class="country-code">
                  <img src="https://flagcdn.com/w20/in.png" alt="India" class="flag-icon">
                  <span>+91</span>
                </div>
                <input type="tel" id="phone" name="phone" required placeholder="Enter phone number">
              </div>
              <span class="error-message"></span>
            </div>

            <div class="form-group">
              <label for="company">Company Name</label>
              <input type="text" id="company" name="company" required placeholder="Enter your company name">
              <span class="error-message"></span>
            </div>

            <div class="form-group">
  <label for="password">Create Password</label>
  <div class="password-input">
    <input type="password" id="password" name="password" required placeholder="Create a strong password">
    <button type="button" class="password-toggle" onclick="togglePassword()">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
    </button>
  </div>
  <!-- ADD THIS PART -->
  <div class="password-strength">
    <div class="strength-bar">
      <div class="strength-fill"></div>
    </div>
    <p class="strength-text">Password strength: <span>Weak</span></p>
  </div>
  <!-- END ADD -->
  <span class="error-message"></span>
</div>


            <div class="form-group">
              <label for="confirmPassword">Confirm Password</label>
              <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Confirm your password">
              <span class="error-message"></span>
            </div>

            <button type="button" class="btn-next" onclick="nextStep()">Continue</button>

            <div class="form-footer">
              <p>Already have an account? <a href="signin.html">Sign in</a></p>
            </div>
          </div>

          <!-- Step 2: Business Type Selection -->
          <div class="form-step" data-step="2">
            <div class="step-header">
              <h2>Business Type</h2>
              <p class="step-subtitle">Step 2: What type of business do you have?</p>
            </div>

            <div id="businessTypeContainer" class="business-type-grid">
              <!-- Business types will be loaded dynamically -->
            </div>

            <div class="form-actions">
              <button type="button" class="btn-back" onclick="previousStep()">Back</button>
              <button type="button" class="btn-next" onclick="nextStep()" disabled>Continue</button>
            </div>
          </div>

          <!-- Step 3: Business Details -->
          <div class="form-step" data-step="3">
            <div class="step-header">
              <h2>Business Details</h2>
              <p class="step-subtitle">Step 3: Tell us more about your business</p>
            </div>

            <div class="features-selection">
              <label>What features are you looking for?</label>
              <div class="feature-grid">
                <div class="feature-option" data-feature="trip-management">
                  <div class="feature-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                      <circle cx="12" cy="10" r="3"/>
                    </svg>
                  </div>
                  <h4>Trip Management</h4>
                  <p>Track and manage fleet trips</p>
                </div>

                <div class="feature-option" data-feature="gps-tracking">
                  <div class="feature-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <circle cx="12" cy="12" r="10"/>
                      <path d="M12 6v6l4 2"/>
                    </svg>
                  </div>
                  <h4>GPS Tracking</h4>
                  <p>Real-time vehicle location</p>
                </div>

                <div class="feature-option" data-feature="inventory">
                  <div class="feature-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                      <path d="M9 9h6v6H9z"/>
                    </svg>
                  </div>
                  <h4>Inventory</h4>
                  <p>Manage vehicle inventory</p>
                </div>

                <div class="feature-option" data-feature="invoicing">
                  <div class="feature-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                      <polyline points="14,2 14,8 20,8"/>
                      <line x1="16" y1="13" x2="8" y2="13"/>
                      <line x1="16" y1="17" x2="8" y2="17"/>
                    </svg>
                  </div>
                  <h4>Invoicing</h4>
                  <p>Automated billing system</p>
                </div>

                <div class="feature-option" data-feature="maintenance">
                  <div class="feature-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                    </svg>
                  </div>
                  <h4>Fleet Maintenance</h4>
                  <p>Schedule and track maintenance</p>
                </div>

                <div class="feature-option" data-feature="documentation">
                  <div class="feature-icon">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                      <polyline points="14,2 14,8 20,8"/>
                    </svg>
                  </div>
                  <h4>Documentation</h4>
                  <p>Digital document management</p>
                </div>
              </div>
            </div>

            <div class="form-group">
              <label for="fleetSize">Fleet Size</label>
              <select id="fleetSize" name="fleetSize" required>
                <option value="">Select your fleet size</option>
                <option value="1-5">1-5 vehicles</option>
                <option value="6-10">6-10 vehicles</option>
                <option value="11-25">11-25 vehicles</option>
                <option value="26-50">26-50 vehicles</option>
                <option value="50+">50+ vehicles</option>
              </select>
              <span class="error-message"></span>
            </div>

            <div class="form-group checkbox-group">
              <input type="checkbox" id="terms" name="terms" required>
              <label for="terms">
                I agree to the <a href="#" target="_blank">Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
              </label>
              <span class="error-message"></span>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-back" onclick="previousStep()">Back</button>
              <button type="submit" class="btn-submit">Complete Registration</button>
            </div>

            <div class="form-footer">
              <p>Already have an account? <a href="signin.html">Sign in</a></p>
            </div>
          </div>
        </form>

        <!-- Success Message -->
        <div class="success-message" id="successMessage" style="display: none;">
          <div class="success-icon">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
              <polyline points="22,4 12,14.01 9,11.01"/>
            </svg>
          </div>
          <h3>Welcome to Fleetly!</h3>
          <p>Your account has been created successfully. Your Company ID is: <strong id="companyId"></strong></p>
          <button class="btn-dashboard" onclick="goToDashboard()">Go to Dashboard</button>
        </div>
      </div>
    </div>
  </div>

  <script src="signup.js"></script>
</body>
</html>
