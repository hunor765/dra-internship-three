class CookieBannerAccordion {
  constructor() {
    this.cookies = {
      essential: true,
      personalization: false,
      targeting: false,
      measurement: false
    };
    this.expandedSections = new Set();
    this.init();
  }

  init() {
    this.loadConsent();
    if (!this.hasConsent()) {
      this.showBanner();
    }
  }

  loadConsent() {
    const cookies = this.parseCookies();
    if (cookies.consent_essential !== undefined) {
      this.cookies = {
        essential: cookies.consent_essential === 'true',
        personalization: cookies.consent_personalization === 'true',
        targeting: cookies.consent_targeting === 'true',
        measurement: cookies.consent_measurement === 'true'
      };
    }
  }

  parseCookies() {
    const cookies = {};
    document.cookie.split(';').forEach(c => {
      const [key, value] = c.trim().split('=');
      if (key && value) cookies[key] = value;
    });
    return cookies;
  }

  hasConsent() {
    const cookies = this.parseCookies();
    return cookies.consent_essential !== undefined;
  }

  showBanner() {
    const banner = document.createElement('div');
    banner.id = 'cookie-accordion-banner';
    banner.innerHTML = `
      <div class="cookie-top-bar">
        <div class="cookie-top-content">
          <div class="cookie-title">
            <span class="cookie-emoji">🍪</span>
            <span>We respect your privacy</span>
          </div>
          <p>Our website uses cookies to enhance your experience. Show more to customize.</p>

          <div class="cookie-accordion-container">
            <div class="accordion-item">
              <button class="accordion-trigger" data-section="essential">
                <span>Essential Cookies</span>
                <span class="accordion-icon">+</span>
              </button>
              <div class="accordion-content" data-section="essential">
                <p>Required for basic site functionality like navigation and security. Cannot be disabled.</p>
                <label class="checkbox-item">
                  <input type="checkbox" class="cookie-check" data-type="essential" checked disabled>
                  <span class="check-text">Always enabled</span>
                </label>
              </div>
            </div>

            <div class="accordion-item">
              <button class="accordion-trigger" data-section="personalization">
                <span>Personalization</span>
                <span class="accordion-icon">+</span>
              </button>
              <div class="accordion-content" data-section="personalization">
                <p>Remembers your preferences and settings for a better experience.</p>
                <label class="checkbox-item">
                  <input type="checkbox" class="cookie-check" data-type="personalization">
                  <span class="check-text">Enable personalization</span>
                </label>
              </div>
            </div>

            <div class="accordion-item">
              <button class="accordion-trigger" data-section="targeting">
                <span>Targeting & Advertising</span>
                <span class="accordion-icon">+</span>
              </button>
              <div class="accordion-content" data-section="targeting">
                <p>Used to show you relevant ads and marketing content.</p>
                <label class="checkbox-item">
                  <input type="checkbox" class="cookie-check" data-type="targeting">
                  <span class="check-text">Allow targeted ads</span>
                </label>
              </div>
            </div>

            <div class="accordion-item">
              <button class="accordion-trigger" data-section="measurement">
                <span>Measurement & Analytics</span>
                <span class="accordion-icon">+</span>
              </button>
              <div class="accordion-content" data-section="measurement">
                <p>Helps us understand how you use our site and improve it.</p>
                <label class="checkbox-item">
                  <input type="checkbox" class="cookie-check" data-type="measurement">
                  <span class="check-text">Enable analytics</span>
                </label>
              </div>
            </div>
          </div>

          <div class="cookie-actions">
            <button class="btn-decline" id="cookie-decline-all">Decline Non-Essential</button>
            <button class="btn-accept-all" id="cookie-accept-all">Accept All</button>
          </div>
        </div>
      </div>
    `;

    document.body.insertBefore(banner, document.body.firstChild);
    this.attachEventListeners();
  }

  attachEventListeners() {
    document.querySelectorAll('.accordion-trigger').forEach(button => {
      button.addEventListener('click', (e) => this.toggleSection(e.currentTarget));
    });

    document.querySelectorAll('.cookie-check:not([disabled])').forEach(checkbox => {
      checkbox.addEventListener('change', (e) => {
        this.cookies[e.target.dataset.type] = e.target.checked;
      });
    });

    document.getElementById('cookie-decline-all').addEventListener('click', () => {
      this.cookies = { essential: true, personalization: false, targeting: false, measurement: false };
      this.saveAndClose();
    });

    document.getElementById('cookie-accept-all').addEventListener('click', () => {
      this.cookies = { essential: true, personalization: true, targeting: true, measurement: true };
      this.saveAndClose();
    });
  }

  toggleSection(button) {
    const section = button.dataset.section;
    const content = document.querySelector(`.accordion-content[data-section="${section}"]`);
    const icon = button.querySelector('.accordion-icon');

    if (this.expandedSections.has(section)) {
      content.style.maxHeight = '0';
      icon.textContent = '+';
      this.expandedSections.delete(section);
    } else {
      content.style.maxHeight = content.scrollHeight + 'px';
      icon.textContent = '−';
      this.expandedSections.add(section);
    }
  }

  saveAndClose() {
    this.setServerCookies();
    const banner = document.getElementById('cookie-accordion-banner');
    banner.classList.add('banner-closing');
    setTimeout(() => {
      if (banner && banner.parentNode) {
        banner.remove();
      }
    }, 300);
  }

  setServerCookies() {
    Object.entries(this.cookies).forEach(([key, value]) => {
      document.cookie = `consent_${key}=${value}; path=/; max-age=${365 * 24 * 60 * 60}; SameSite=Lax`;
    });
  }

  getConsent(type) {
    return this.cookies[type] || false;
  }

  readCookies() {
    return this.cookies;
  }
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.cookieBanner = new CookieBannerAccordion();
  });
} else {
  window.cookieBanner = new CookieBannerAccordion();
}
