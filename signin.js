class SigninForm {
  constructor() {
    this.form = document.getElementById('signinForm');
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.setupValidation();
    console.log('🚀 Signin form initialized');
  }

  setupEventListeners() {
    this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    
    // Real-time validation
    const inputs = this.form.querySelectorAll('input[required]');
    inputs.forEach(input => {
      input.addEventListener('blur', () => this.validateField(input));
      input.addEventListener('input', () => {
        if (input.classList.contains('error')) {
          this.validateField(input);
        }
      });
    });
  }

  setupValidation() {
    // Clear any existing alerts when user starts typing
    const inputs = this.form.querySelectorAll('input');
    inputs.forEach(input => {
      input.addEventListener('input', () => {
        this.hideAlert();
      });
    });
  }

  validateField(field) {
    const value = field.value.trim();
    const type = field.type;
    let isValid = true;
    let errorMessage = '';

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
    // Password validation
    else if (field.name === 'password' && value) {
      if (value.length < 6) {
        isValid = false;
        errorMessage = 'Password must be at least 6 characters long';
      }
    }

    this.updateFieldValidation(field, isValid, errorMessage);
    return isValid;
  }

  updateFieldValidation(field, isValid, errorMessage) {
    const errorElement = field.parentNode.parentNode.querySelector('.error-message');
    
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

  async handleSubmit(e) {
    e.preventDefault();
    
    // Validate all fields
    const requiredFields = this.form.querySelectorAll('input[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
      if (!this.validateField(field)) {
        isValid = false;
      }
    });

    if (!isValid) {
      this.showAlert('Please fix the errors above', 'error');
      return;
    }

    // Show loading state
    const submitBtn = this.form.querySelector('.btn-signin');
    const btnText = submitBtn.querySelector('.btn-text');
    const btnLoader = submitBtn.querySelector('.btn-loader');
    
    submitBtn.disabled = true;
    submitBtn.classList.add('loading');
    btnText.style.opacity = '0';
    btnLoader.style.display = 'flex';

    try {
      const formData = new FormData();
      formData.append('email', document.getElementById('email').value);
      formData.append('password', document.getElementById('password').value);
      formData.append('rememberMe', document.getElementById('rememberMe').checked ? 'true' : 'false');

      console.log('📤 Sending login request...');

      const response = await fetch('login.php', {
        method: 'POST',
        body: formData
      });

      const responseText = await response.text();
      console.log('📥 Server response:', responseText);

      if (!response.ok) {
        throw new Error(`Server error: ${responseText}`);
      }

      let result;
      try {
        result = JSON.parse(responseText);
      } catch (parseError) {
        throw new Error(`Invalid response format: ${responseText}`);
      }

      if (result.success) {
        this.showSuccessMessage();
        
        // Redirect after 2 seconds
        setTimeout(() => {
          window.location.href = result.redirect || 'dashboard.html';
        }, 2000);
        
      } else {
        throw new Error(result.message || 'Login failed');
      }

    } catch (error) {
      console.error('❌ Login error:', error);
      this.showAlert(error.message, 'error');
    } finally {
      // Reset button state
      submitBtn.disabled = false;
      submitBtn.classList.remove('loading');
      btnText.style.opacity = '1';
      btnLoader.style.display = 'none';
    }
  }

  showAlert(message, type = 'error') {
    const alertDiv = document.getElementById('alertMessage');
    const alertText = alertDiv.querySelector('.alert-text');
    const alertIcon = alertDiv.querySelector('.alert-icon');
    
    // Set icon based on type
    if (type === 'error') {
      alertIcon.textContent = '⚠️';
      alertDiv.className = 'alert error';
    } else if (type === 'success') {
      alertIcon.textContent = '✅';
      alertDiv.className = 'alert success';
    }
    
    alertText.textContent = message;
    alertDiv.style.display = 'block';
    
    // Auto hide after 5 seconds
    setTimeout(() => {
      this.hideAlert();
    }, 5000);
  }

  hideAlert() {
    const alertDiv = document.getElementById('alertMessage');
    alertDiv.style.display = 'none';
  }

  showSuccessMessage() {
    document.querySelector('.signin-form').style.display = 'none';
    document.getElementById('successMessage').style.display = 'block';
  }
}

// Global functions
function togglePassword() {
  const passwordInput = document.getElementById('password');
  const toggleBtn = document.querySelector('.password-toggle');
  
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

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  new SigninForm();
});
