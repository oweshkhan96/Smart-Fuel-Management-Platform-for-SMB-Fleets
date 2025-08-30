class SignupForm {
  constructor() {
    this.currentStep = 1;
    this.totalSteps = 3;
    this.formData = {};
    this.selectedFeatures = [];
    this.selectedBusinessType = null;
    this.businessTypes = [];
    
    this.init();
  }
  
  async init() {
    console.log('🚀 Initializing signup form...');
    this.setupEventListeners();
    this.setupFeatureSelection();
    this.setupPasswordStrength();
    this.setupFormValidation();
    this.updateCountryCode();
    
    // Load business types from backend
    await this.loadBusinessTypes();
    
    console.log('✅ Signup form initialized successfully');
  }
  
  setupEventListeners() {
    const form = document.getElementById('signupForm');
    form.addEventListener('submit', (e) => this.handleSubmit(e));
    
    // Country change handler
    const countrySelect = document.getElementById('country');
    if (countrySelect) {
      countrySelect.addEventListener('change', () => this.updateCountryCode());
    }

    // Business type selection handler
    document.addEventListener('click', (e) => {
      if (e.target.closest('.business-type-option')) {
        this.selectBusinessType(e.target.closest('.business-type-option'));
      }
    });
  }
  
  async loadBusinessTypes() {
    try {
      console.log('📡 Loading business types...');
      const response = await fetch('get_business_types.php');
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const result = await response.json();
      console.log('📡 Response:', result);
      
      if (!result.success) {
        throw new Error(result.message || 'Failed to load business types');
      }
      
      this.businessTypes = result.data;
      this.renderBusinessTypes();
      console.log('✅ Business types loaded:', this.businessTypes.length);
      
    } catch (error) {
      console.error('❌ Error loading business types:', error);
      this.showBusinessTypeError();
    }
  }

  renderBusinessTypes() {
    const container = document.getElementById('businessTypeContainer');
    if (!container) return;

    container.innerHTML = '';
    
    this.businessTypes.forEach(type => {
      const typeElement = document.createElement('div');
      typeElement.className = 'business-type-option';
      typeElement.setAttribute('data-type', type.name);
      typeElement.innerHTML = `
        <div class="business-type-icon">
          <i class="icon-${type.icon}"></i>
        </div>
        <h4>${this.capitalize(type.name.replace('-', ' '))}</h4>
        <p>${type.description}</p>
      `;
      container.appendChild(typeElement);
    });
  }

  showBusinessTypeError() {
    const container = document.getElementById('businessTypeContainer');
    if (container) {
      container.innerHTML = `
        <div class="error-message show">
          <p>Unable to load business types. Please refresh the page and try again.</p>
          <button type="button" onclick="location.reload()" class="btn-retry">Retry</button>
        </div>
      `;
    }
  }

  selectBusinessType(element) {
    console.log('🎯 Selecting business type...');
    // Remove previous selection
    document.querySelectorAll('.business-type-option').forEach(el => {
      el.classList.remove('selected');
    });
    
    // Add selection to clicked element
    element.classList.add('selected');
    this.selectedBusinessType = element.getAttribute('data-type');
    
    // Enable continue button for step 2
    const continueBtn = document.querySelector('[data-step="2"] .btn-next');
    if (continueBtn) {
      continueBtn.disabled = false;
    }
    console.log('✅ Selected business type:', this.selectedBusinessType);
  }
  
  setupFeatureSelection() {
    document.addEventListener('click', (e) => {
      if (e.target.closest('.feature-option')) {
        const option = e.target.closest('.feature-option');
        this.toggleFeature(option);
      }
    });
  }

  toggleFeature(option) {
    option.classList.toggle('selected');
    const feature = option.dataset.feature;
    
    if (option.classList.contains('selected')) {
      if (!this.selectedFeatures.includes(feature)) {
        this.selectedFeatures.push(feature);
      }
    } else {
      this.selectedFeatures = this.selectedFeatures.filter(f => f !== feature);
    }
    
    console.log('Selected features:', this.selectedFeatures);
  }
  
  setupPasswordStrength() {
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
      passwordInput.addEventListener('input', () => this.checkPasswordStrength());
    }
  }
  
  checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthFill = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text span');
    
    // Safety check - if elements don't exist, skip
    if (!strengthFill || !strengthText) {
      console.log('⚠️ Password strength elements not found, skipping...');
      return;
    }

    let strength = 0;
    let label = 'Weak';
    
    // Check password criteria
    if (password.length >= 8) strength += 1;
    if (/[a-z]/.test(password)) strength += 1;
    if (/[A-Z]/.test(password)) strength += 1;
    if (/[0-9]/.test(password)) strength += 1;
    if (/[^A-Za-z0-9]/.test(password)) strength += 1;
    
    // Remove all strength classes
    strengthFill.className = 'strength-fill';
    
    if (strength >= 4) {
      strengthFill.classList.add('strong');
      label = 'Strong';
    } else if (strength >= 3) {
      strengthFill.classList.add('good');
      label = 'Good';
    } else if (strength >= 2) {
      strengthFill.classList.add('fair');
      label = 'Fair';
    } else if (strength >= 1) {
      strengthFill.classList.add('weak');
      label = 'Weak';
    }
    
    strengthText.textContent = label;
  }
  
  setupFormValidation() {
    const inputs = document.querySelectorAll('input, select');
    inputs.forEach(input => {
      input.addEventListener('blur', () => this.validateField(input));
      input.addEventListener('input', () => {
        if (input.classList.contains('error')) {
          this.validateField(input);
        }
      });
    });
  }
  
  validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    const name = field.name;
    let isValid = true;
    let errorMessage = '';
    
    // Skip validation if field is not visible or not in current step
    const currentStepElement = document.querySelector(`.form-step[data-step="${this.currentStep}"]`);
    if (!currentStepElement || !currentStepElement.contains(field)) {
      return true; // Don't validate fields not in current step
    }
    
    // Required field validation
    if (field.hasAttribute('required') && !value) {
      isValid = false;
      errorMessage = 'This field is required';
    }
    
    // Email validation
    else if (type === 'email' && value) {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(value)) {
        isValid = false;
        errorMessage = 'Please enter a valid email address';
      }
    }
    
    // Phone validation
    else if (type === 'tel' && value) {
      const phoneRegex = /^[0-9]{10}$/;
      if (!phoneRegex.test(value.replace(/\s/g, ''))) {
        isValid = false;
        errorMessage = 'Please enter a valid 10-digit phone number';
      }
    }
    
    // Password validation
    else if (name === 'password' && value) {
      if (value.length < 8) {
        isValid = false;
        errorMessage = 'Password must be at least 8 characters long';
      }
    }
    
    // Confirm password validation
    else if (name === 'confirmPassword' && value) {
      const password = document.getElementById('password').value;
      if (value !== password) {
        isValid = false;
        errorMessage = 'Passwords do not match';
      }
    }
    
    this.updateFieldValidation(field, isValid, errorMessage);
    return isValid;
  }
  
  updateFieldValidation(field, isValid, errorMessage) {
    const errorElement = field.parentNode.querySelector('.error-message');
    
    if (isValid) {
      field.classList.remove('error');
      field.classList.add('valid');
      if (errorElement) {
        errorElement.classList.remove('show');
        setTimeout(() => {
          errorElement.textContent = '';
        }, 300);
      }
    } else {
      field.classList.add('error');
      field.classList.remove('valid');
      
      if (errorElement) {
        errorElement.textContent = errorMessage;
        errorElement.classList.add('show');
      }
    }
  }
  
  updateCountryCode() {
    const countrySelect = document.getElementById('country');
    const flagIcon = document.querySelector('.flag-icon');
    const countryCode = document.querySelector('.country-code span');
    
    if (!countrySelect || !flagIcon || !countryCode) return;

    const countryCodes = {
      'India': { flag: 'in', code: '+91' },
      'United States': { flag: 'us', code: '+1' },
      'United Kingdom': { flag: 'gb', code: '+44' },
      'Canada': { flag: 'ca', code: '+1' },
      'Australia': { flag: 'au', code: '+61' },
      'Germany': { flag: 'de', code: '+49' },
      'France': { flag: 'fr', code: '+33' },
      'Singapore': { flag: 'sg', code: '+65' },
      'UAE': { flag: 'ae', code: '+971' }
    };
    
    const selectedCountry = countrySelect.value;
    if (countryCodes[selectedCountry]) {
      flagIcon.src = `https://flagcdn.com/w20/${countryCodes[selectedCountry].flag}.png`;
      flagIcon.alt = selectedCountry;
      countryCode.textContent = countryCodes[selectedCountry].code;
    }
  }
  
  nextStep() {
    console.log('🚀 Moving to next step from:', this.currentStep);
    
    if (this.validateCurrentStep()) {
      console.log('✅ Validation passed');
      this.saveCurrentStepData();
      
      if (this.currentStep < this.totalSteps) {
        this.currentStep++;
        console.log('📈 Updated to step:', this.currentStep);
        this.updateStep();
      }
    } else {
      console.log('❌ Validation failed');
    }
  }
  
  previousStep() {
    if (this.currentStep > 1) {
      this.currentStep--;
      this.updateStep();
    }
  }
  
  validateCurrentStep() {
    console.log('🔍 Validating step:', this.currentStep);
    
    const currentStepElement = document.querySelector(`.form-step[data-step="${this.currentStep}"]`);
    if (!currentStepElement) {
      console.error('❌ Current step element not found');
      return false;
    }
    
    const requiredFields = currentStepElement.querySelectorAll('input[required], select[required]');
    console.log('📋 Required fields found:', requiredFields.length);
    
    let isValid = true;
    
    requiredFields.forEach(field => {
      const fieldValid = this.validateField(field);
      console.log(`Field ${field.name}: ${fieldValid ? 'VALID' : 'INVALID'} (value: "${field.value}")`);
      if (!fieldValid) {
        isValid = false;
      }
    });
    
    // Special validation for step 2 (business type selection)
    if (this.currentStep === 2 && !this.selectedBusinessType) {
      console.log('❌ Business type not selected');
      this.showError('Please select a business type to continue.');
      isValid = false;
    }
    
    // Special validation for step 3
    if (this.currentStep === 3) {
      const termsCheckbox = document.getElementById('terms');
      if (termsCheckbox && !termsCheckbox.checked) {
        console.log('❌ Terms not accepted');
        this.showError('You must accept the terms and conditions to continue.');
        isValid = false;
      }
    }
    
    console.log('📊 Overall validation result:', isValid);
    return isValid;
  }
  
  saveCurrentStepData() {
    const currentStepElement = document.querySelector(`.form-step[data-step="${this.currentStep}"]`);
    const inputs = currentStepElement.querySelectorAll('input, select');
    
    inputs.forEach(input => {
      if (input.type === 'checkbox') {
        this.formData[input.name] = input.checked;
      } else {
        this.formData[input.name] = input.value;
      }
    });
    
    // Save additional data based on step
    if (this.currentStep === 2) {
      this.formData.businessType = this.selectedBusinessType;
    }
    
    if (this.currentStep === 3) {
      this.formData.selectedFeatures = this.selectedFeatures;
    }
    
    console.log('💾 Saved form data:', this.formData);
  }
  
  updateStep() {
    // Update form steps
    document.querySelectorAll('.form-step').forEach(step => {
      step.classList.remove('active');
    });
    document.querySelector(`.form-step[data-step="${this.currentStep}"]`).classList.add('active');
    
    // Update progress indicator
    document.querySelectorAll('.progress-step').forEach((step, index) => {
      const stepNumber = index + 1;
      step.classList.remove('active', 'completed');
      
      if (stepNumber < this.currentStep) {
        step.classList.add('completed');
      } else if (stepNumber === this.currentStep) {
        step.classList.add('active');
      }
    });
    
    console.log('🎯 UI updated for step:', this.currentStep);
  }
  
  async handleSubmit(e) {
    e.preventDefault();
    
    if (!this.validateCurrentStep()) {
      return;
    }
    
    this.saveCurrentStepData();
    
    const submitBtn = document.querySelector('.btn-submit');
    const originalText = submitBtn.textContent;
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Creating Account...';
    
    try {
      const success = await this.submitToBackend();
      
      if (success) {
        this.showSuccessMessage();
        this.trackSignup();
      }
      
    } catch (error) {
      console.error('Signup error:', error);
      this.showError('Registration failed: ' + error.message);
    } finally {
      submitBtn.classList.remove('loading');
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  }

  async submitToBackend() {
    const formData = new FormData();
    
    // Add all form data
    formData.append('email', this.formData.email || document.getElementById('email').value);
    formData.append('fullName', this.formData.fullName || document.getElementById('fullName').value);
    formData.append('country', this.formData.country || document.getElementById('country').value);
    formData.append('phone', this.formData.phone || document.getElementById('phone').value);
    formData.append('company', this.formData.company || document.getElementById('company').value);
    formData.append('password', this.formData.password || document.getElementById('password').value);
    formData.append('businessType', this.selectedBusinessType);
    formData.append('fleetSize', this.formData.fleetSize || document.getElementById('fleetSize').value);
    formData.append('selectedFeatures', JSON.stringify(this.selectedFeatures));
    
    // Convert boolean to string for terms
    const termsChecked = this.formData.terms || document.getElementById('terms').checked;
    formData.append('terms', termsChecked ? 'true' : 'false');
    
    console.log('📤 Data being sent:', {
        email: formData.get('email'),
        fullName: formData.get('fullName'),
        businessType: formData.get('businessType'),
        terms: formData.get('terms'),
        phone: formData.get('phone'),
        company: formData.get('company')
    });
    
    try {
        const response = await fetch('register.php', {
            method: 'POST',
            body: formData
        });
        
        // Get the response text to see what server actually returned
        const responseText = await response.text();
        console.log('📥 Server response status:', response.status);
        console.log('📥 Server response body:', responseText);
        
        if (!response.ok) {
            // Show the actual server error message
            throw new Error(`Server Error (${response.status}): ${responseText}`);
        }
        
        // Try to parse as JSON
        const result = JSON.parse(responseText);
        
        if (!result.success) {
            throw new Error(result.message || 'Registration failed');
        }
        
        // Store company ID for display
        this.companyId = result.data ? result.data.company_id : result.company_id;
        
        return true;
        
    } catch (error) {
        console.error('❌ Registration error:', error);
        throw error;
    }
}


  
  showSuccessMessage() {
    document.querySelector('.signup-form').style.display = 'none';
    document.querySelector('.progress-indicator').style.display = 'none';
    
    const successMessage = document.getElementById('successMessage');
    const companyIdElement = document.getElementById('companyId');
    
    if (companyIdElement && this.companyId) {
      companyIdElement.textContent = this.companyId;
    }
    
    successMessage.style.display = 'block';
  }

  showError(message) {
    alert(message);
  }
  
  trackSignup() {
    if (typeof gtag !== 'undefined') {
      gtag('event', 'sign_up', {
        method: 'email',
        features_selected: this.selectedFeatures.join(','),
        fleet_size: this.formData.fleetSize,
        business_type: this.selectedBusinessType
      });
    }
    
    console.log('📊 Signup completed:', {
      ...this.formData,
      businessType: this.selectedBusinessType,
      selectedFeatures: this.selectedFeatures,
      companyId: this.companyId
    });
  }

  capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
}

// Global functions for button clicks
function nextStep() {
  console.log('🔘 nextStep button clicked');
  if (window.signupForm) {
    window.signupForm.nextStep();
  } else {
    console.error('❌ signupForm not initialized');
  }
}

function previousStep() {
  console.log('🔙 previousStep button clicked');
  if (window.signupForm) {
    window.signupForm.previousStep();
  } else {
    console.error('❌ signupForm not initialized');
  }
}

function togglePassword() {
  const passwordInput = document.getElementById('password');
  const toggleBtn = document.querySelector('.password-toggle');
  
  if (!passwordInput || !toggleBtn) return;
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    toggleBtn.innerHTML = `
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
        <line x1="1" y1="1" x2="23" y2="23"/>
      </svg>
    `;
  } else {
    passwordInput.type = 'password';
    toggleBtn.innerHTML = `
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
        <circle cx="12" cy="12" r="3"/>
      </svg>
    `;
  }
}

function goToDashboard() {
  window.location.href = 'dashboard.html';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  console.log('🌟 DOM loaded, initializing SignupForm...');
  window.signupForm = new SignupForm();
});
