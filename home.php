<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Fleetly - Smart fuel management solution for SMB fleets. Track fuel usage, reduce costs, and optimize fleet efficiency with real-time insights." />
  <meta name="keywords" content="fleet management, fuel tracking, logistics, vehicle management, fuel optimization" />
  <meta property="og:title" content="Fleetly - Smart Fuel Management" />
  <meta property="og:description" content="Reduce fleet fuel costs by up to 25% with intelligent tracking and analytics" />
  <meta property="og:image" content="https://your-domain.com/og-image.jpg" />
  <title>Fleetly - Smart Fuel Management for SMB Fleets</title>
  <link rel="stylesheet" href="homestyle.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/png" href="favicon.png" />
</head>
<body>
 

  <!-- Navbar -->
  <header class="navbar" id="navbar">
   <div class="logo">
    <img src="https://user-gen-media-assets.s3.amazonaws.com/gpt4o_images/34478828-c090-4c89-bb22-585e8ad4b437.png" 
         alt="Fleetly Logo" 
         class="logo-image">
  </div>


    <nav class="nav-links">
      <a href="#home" class="nav-link active">Home</a>
      <a href="#features" class="nav-link">Features</a>
      <a href="#about" class="nav-link">About</a>
      <a href="#pricing" class="nav-link">Pricing</a>
      <a href="#contact" class="nav-link">Contact</a>
    </nav>

    <div class="nav-buttons">
      <a href="signup.php" class="btn-outline">Sign In</a>
      <a href="login.php" class="btn-outline">Log In</a>
      <a href="#demo" class="btn-primary">Start Free Trial</a>
    </div>

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" aria-label="Toggle navigation">
      <span></span>
      <span></span>
      <span></span>
    </button>
  </header>

  <main>
    <!-- Hero Section -->
    <section id="home" class="hero">
      <div class="hero-content">
        <div class="hero-text">
          <h1>Reduce Fleet Fuel Costs by <span class="highlight">25%</span></h1>
          <p class="hero-subtitle">Smart fuel management for growing businesses. Track usage, prevent theft, and optimize routes with real-time insights and AI-powered analytics.</p>
          
          <div class="hero-stats">
            <div class="stat">
              <strong>500+</strong>
              <span>Companies Trust Us</span>
            </div>
            <div class="stat">
              <strong>$2M+</strong>
              <span>Fuel Costs Saved</span>
            </div>
            <div class="stat">
              <strong>98%</strong>
              <span>Customer Satisfaction</span>
            </div>
          </div>

          <div class="hero-actions">
            <a href="#demo" class="btn-hero-primary">Start 14-Day Free Trial</a>
            <a href="#demo-video" class="btn-hero-secondary">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M8 5v14l11-7z"/>
              </svg>
              Watch Demo
            </a>
          </div>

          <div class="trust-badges">
            <p>Trusted by industry leaders</p>
            <div class="badges">
              <span class="badge">SOC 2 Certified</span>
              <span class="badge">GDPR Compliant</span>
              <span class="badge">99.9% Uptime</span>
            </div>
          </div>
        </div>
        
        <div class="hero-visual">
          <div class="dashboard-preview">
            <img src="./img1.png" alt="Fleetly Dashboard Preview" loading="eager">
            <div class="floating-cards">
              <div class="card fuel-card">
                <h4>Fuel Efficiency</h4>
                <div class="metric">+18%</div>
              </div>
              <div class="card alert-card">
                <h4>Smart Alerts</h4>
                <div class="status">3 Active</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Social Proof / Counters -->
    <section class="social-proof">
      <div class="container">
        <h2>Delivering Results Across Industries</h2>
        <div class="counters">
          <div class="counter-box">
            <div class="counter-icon">⛽</div>
            <h3 id="fuelSaved">0</h3>
            <p>Liters of Fuel Saved</p>
          </div>
          <div class="counter-box">
            <div class="counter-icon">🚛</div>
            <h3 id="fleets">0</h3>
            <p>Active Fleet Vehicles</p>
          </div>
          <div class="counter-box">
            <div class="counter-icon">📊</div>
            <h3 id="reports">0</h3>
            <p>Analytics Reports Generated</p>
          </div>
          <div class="counter-box">
            <div class="counter-icon">💰</div>
            <h3 id="savings">0</h3>
            <p>Cost Savings (USD)</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
      <div class="container">
        <div class="section-header">
          <h2>Everything You Need to Optimize Your Fleet</h2>
          <p>Comprehensive fuel management tools designed for modern businesses</p>
        </div>

        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M9 19c-5 0-8-3-8-6s3-6 8-6 8 3 8 6-3 6-8 6z"/>
                <path d="M17 12h3l-3 9-3-9h3z"/>
              </svg>
            </div>
            <h3>Real-Time Fuel Tracking</h3>
            <p>Monitor fuel consumption across your entire fleet with live updates and instant notifications for unusual activity.</p>
            <ul class="feature-benefits">
              <li>GPS-enabled fuel monitoring</li>
              <li>Theft detection algorithms</li>
              <li>Mobile app integration</li>
            </ul>
          </div>

          <div class="feature-card featured">
            <div class="feature-badge">Most Popular</div>
            <div class="feature-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M3 3v18h18v-18z"/>
                <path d="M7 8h10"/>
                <path d="M7 16h6"/>
              </svg>
            </div>
            <h3>Advanced Analytics Dashboard</h3>
            <p>Make data-driven decisions with comprehensive reports, predictive analytics, and customizable KPI tracking.</p>
            <ul class="feature-benefits">
              <li>Custom report builder</li>
              <li>Predictive fuel forecasting</li>
              <li>Performance benchmarking</li>
            </ul>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.27,6.96 12,12.01 20.73,6.96"/>
                <line x1="12" y1="22.08" x2="12" y2="12"/>
              </svg>
            </div>
            <h3>Route Optimization</h3>
            <p>Reduce fuel costs by up to 20% with AI-powered route planning and traffic-aware scheduling.</p>
            <ul class="feature-benefits">
              <li>AI route optimization</li>
              <li>Traffic pattern analysis</li>
              <li>Fuel-efficient scheduling</li>
            </ul>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
            </div>
            <h3>Smart Alerts & Notifications</h3>
            <p>Stay informed with intelligent alerts for fuel theft, unusual consumption, and maintenance schedules.</p>
            <ul class="feature-benefits">
              <li>Anomaly detection</li>
              <li>Maintenance reminders</li>
              <li>Multi-channel notifications</li>
            </ul>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                <line x1="8" y1="21" x2="16" y2="21"/>
                <line x1="12" y1="17" x2="12" y2="21"/>
              </svg>
            </div>
            <h3>Driver Performance Monitoring</h3>
            <p>Improve driver behavior and fuel efficiency with detailed performance metrics and coaching tools.</p>
            <ul class="feature-benefits">
              <li>Driving behavior analysis</li>
              <li>Fuel efficiency scoring</li>
              <li>Training recommendations</li>
            </ul>
          </div>

          <div class="feature-card">
            <div class="feature-icon">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
            </div>
            <h3>Enterprise Security</h3>
            <p>Bank-level security with encrypted data transmission, role-based access, and compliance reporting.</p>
            <ul class="feature-benefits">
              <li>256-bit SSL encryption</li>
              <li>Role-based permissions</li>
              <li>Audit trail logging</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works -->
    <section id="about" class="how-it-works">
      <div class="container">
        <div class="section-header">
          <h2>Get Started in 3 Simple Steps</h2>
          <p>From setup to savings in less than 24 hours</p>
        </div>

        <div class="steps-timeline">
          <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">
              <h3>Quick Setup</h3>
              <p>Connect your vehicles with our plug-and-play IoT devices. No technical expertise required.</p>
              <div class="step-details">
                <span class="time-badge">⏱️ 15 minutes per vehicle</span>
              </div>
            </div>
          </div>

          <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">
              <h3>Data Collection</h3>
              <p>Our system automatically starts tracking fuel usage, routes, and driver behavior in real-time.</p>
              <div class="step-details">
                <span class="time-badge">📊 Instant data flow</span>
              </div>
            </div>
          </div>

          <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">
              <h3>Start Saving</h3>
              <p>Receive actionable insights and watch your fuel costs decrease while efficiency improves.</p>
              <div class="step-details">
                <span class="time-badge">💰 See results in 7 days</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials -->
    <section class="testimonials">
      <div class="container">
        <div class="section-header">
          <h2>Trusted by Fleet Managers Worldwide</h2>
        </div>

        <div class="testimonials-grid">
          <div class="testimonial-card">
            <div class="testimonial-content">
              <div class="stars">★★★★★</div>
              <p>"Fleetly reduced our fuel costs by 28% in the first quarter. The ROI was immediate and the insights are invaluable."</p>
            </div>
            <div class="testimonial-author">
              <img src="https://via.placeholder.com/60x60/1C4E80/FFFFFF?text=JD" alt="John Davis">
              <div>
                <strong>John Davis</strong>
                <span>Fleet Manager, LogiTrans Inc.</span>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="testimonial-content">
              <div class="stars">★★★★★</div>
              <p>"The theft detection feature alone saved us $15,000 in the first month. Incredible technology!"</p>
            </div>
            <div class="testimonial-author">
              <img src="https://via.placeholder.com/60x60/EA6A47/FFFFFF?text=SM" alt="Sarah Mitchell">
              <div>
                <strong>Sarah Mitchell</strong>
                <span>Operations Director, Swift Delivery</span>
              </div>
            </div>
          </div>

          <div class="testimonial-card">
            <div class="testimonial-content">
              <div class="stars">★★★★★</div>
              <p>"Easy to implement, powerful analytics, and outstanding customer support. Highly recommended!"</p>
            </div>
            <div class="testimonial-author">
              <img src="https://via.placeholder.com/60x60/0091D5/FFFFFF?text=MR" alt="Mike Rodriguez">
              <div>
                <strong>Mike Rodriguez</strong>
                <span>CEO, Metro Logistics</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing -->
    <section id="pricing" class="pricing">
      <div class="container">
        <div class="section-header">
          <h2>Simple, Transparent Pricing</h2>
          <p>Choose the plan that fits your fleet size and needs</p>
          
          <div class="pricing-toggle">
            <span>Monthly</span>
            <button class="toggle-switch" id="billing-toggle">
              <span class="toggle-slider"></span>
            </button>
            <span>Annual <span class="discount-badge">Save 20%</span></span>
          </div>
        </div>

        <div class="pricing-grid">
          <div class="pricing-card">
            <div class="plan-header">
              <h3>Starter</h3>
              <p>Perfect for small fleets</p>
            </div>
            <div class="plan-price">
              <span class="currency">$</span>
              <span class="amount" data-monthly="29" data-annual="23">29</span>
              <span class="period">/month</span>
            </div>
            <div class="plan-billing">
              <span class="annual-price">$276 billed annually</span>
            </div>
            <ul class="plan-features">
              <li>✅ Up to 10 vehicles</li>
              <li>✅ Real-time fuel tracking</li>
              <li>✅ Basic analytics</li>
              <li>✅ Email support</li>
              <li>✅ Mobile app access</li>
            </ul>
            <a href="#demo" class="plan-button">Start Free Trial</a>
          </div>

          <div class="pricing-card popular">
            <div class="plan-badge">Most Popular</div>
            <div class="plan-header">
              <h3>Professional</h3>
              <p>Ideal for growing businesses</p>
            </div>
            <div class="plan-price">
              <span class="currency">$</span>
              <span class="amount" data-monthly="99" data-annual="79">99</span>
              <span class="period">/month</span>
            </div>
            <div class="plan-billing">
              <span class="annual-price">$948 billed annually</span>
            </div>
            <ul class="plan-features">
              <li>✅ Up to 50 vehicles</li>
              <li>✅ Advanced analytics & AI insights</li>
              <li>✅ Route optimization</li>
              <li>✅ Driver performance monitoring</li>
              <li>✅ Priority phone support</li>
              <li>✅ Custom reporting</li>
              <li>✅ API access</li>
            </ul>
            <a href="#demo" class="plan-button primary">Start Free Trial</a>
          </div>

          <div class="pricing-card">
            <div class="plan-header">
              <h3>Enterprise</h3>
              <p>For large-scale operations</p>
            </div>
            <div class="plan-price">
              <span class="custom-price">Custom</span>
            </div>
            <div class="plan-billing">
              <span>Tailored to your needs</span>
            </div>
            <ul class="plan-features">
              <li>✅ Unlimited vehicles</li>
              <li>✅ White-label solutions</li>
              <li>✅ Dedicated account manager</li>
              <li>✅ Custom integrations</li>
              <li>✅ SLA guarantees</li>
              <li>✅ On-premise deployment</li>
              <li>✅ 24/7 premium support</li>
            </ul>
            <a href="#contact" class="plan-button">Contact Sales</a>
          </div>
        </div>

        <div class="pricing-footer">
          <p>All plans include a 14-day free trial • No setup fees • Cancel anytime</p>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>Ready to Transform Your Fleet Management?</h2>
          <p>Join 500+ companies already saving thousands on fuel costs</p>
          <div class="cta-buttons">
            <a href="#demo" class="btn-cta-primary">Start Your Free Trial</a>
            <a href="#contact" class="btn-cta-secondary">Schedule a Demo</a>
          </div>
          <div class="cta-guarantee">
            <p>✅ 14-day free trial • ✅ No credit card required • ✅ Setup in 24 hours</p>
          </div>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer id="contact" class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <div class="footer-logo">
    <img src="https://user-gen-media-assets.s3.amazonaws.com/gpt4o_images/34478828-c090-4c89-bb22-585e8ad4b437.png" 
         alt="Fleetly Logo" 
         class="logo-image">
  </div>
          <p>Smart fuel management for modern fleets. Reduce costs, improve efficiency, and drive your business forward.</p>
          <div class="social-links">
            <a href="#" aria-label="LinkedIn">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
              </svg>
            </a>
            <a href="#" aria-label="Twitter">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
              </svg>
            </a>
          </div>
        </div>

        <div class="footer-links">
          <h4>Product</h4>
          <ul>
            <li><a href="#features">Features</a></li>
            <li><a href="#pricing">Pricing</a></li>
            <li><a href="#">API Documentation</a></li>
            <li><a href="#">Integrations</a></li>
          </ul>
        </div>

        <div class="footer-links">
          <h4>Company</h4>
          <ul>
            <li><a href="#about">About Us</a></li>
            <li><a href="#">Careers</a></li>
            <li><a href="#">Press</a></li>
            <li><a href="#">Partners</a></li>
          </ul>
        </div>

        <div class="footer-links">
          <h4>Resources</h4>
          <ul>
            <li><a href="#">Help Center</a></li>
            <li><a href="#">Blog</a></li>
            <li><a href="#">Case Studies</a></li>
            <li><a href="#">Webinars</a></li>
          </ul>
        </div>

        <div class="footer-contact">
          <h4>Contact</h4>
          <div class="contact-info">
            <p>📧 hello@fleetly.com</p>
            <p>📞 +1 (555) 123-4567</p>
            <p>📍 San Francisco, CA</p>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="footer-legal">
          <p>&copy; 2025 Fleetly Inc. All rights reserved.</p>
          <div class="legal-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
            <a href="#">Cookie Policy</a>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <!-- Back to Top Button -->
  <button id="back-to-top" class="back-to-top" aria-label="Back to top">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
      <path d="M7 14l5-5 5 5"/>
    </svg>
  </button>

  <!-- Scripts -->
  <script src="script.js"></script>
</body>
</html>
