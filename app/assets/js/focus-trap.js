/**
 * Focus Trap Module - ES2023
 * Handles focus management for accessibility
 */

export class FocusTrap {
  constructor(element) {
    this.element = element;
    this.focusableElements = [];
    this.firstFocusableElement = null;
    this.lastFocusableElement = null;
    this.previousActiveElement = null;
  }

  /**
   * Initialize focus trap
   */
  init() {
    this.previousActiveElement = document.activeElement;
    this.updateFocusableElements();
    this.setupKeyboardNavigation();
    
    // Focus first element
    if (this.firstFocusableElement) {
      this.firstFocusableElement.focus();
    }
  }

  /**
   * Update focusable elements
   */
  updateFocusableElements() {
    const focusableSelectors = [
      'button:not([disabled])',
      'input:not([disabled])',
      'select:not([disabled])',
      'textarea:not([disabled])',
      'a[href]',
      'area[href]',
      '[tabindex]:not([tabindex="-1"])',
      '[contenteditable="true"]'
    ];

    this.focusableElements = Array.from(
      this.element.querySelectorAll(focusableSelectors.join(', '))
    ).filter(element => {
      return element.offsetParent !== null; // Visible elements only
    });

    this.firstFocusableElement = this.focusableElements[0] || null;
    this.lastFocusableElement = this.focusableElements[this.focusableElements.length - 1] || null;
  }

  /**
   * Setup keyboard navigation
   */
  setupKeyboardNavigation() {
    this.element.addEventListener('keydown', this.handleKeyDown.bind(this));
  }

  /**
   * Handle keydown events
   */
  handleKeyDown(event) {
    if (event.key === 'Tab') {
      this.handleTabKey(event);
    } else if (event.key === 'Escape') {
      this.handleEscapeKey(event);
    }
  }

  /**
   * Handle Tab key
   */
  handleTabKey(event) {
    if (this.focusableElements.length === 0) {
      event.preventDefault();
      return;
    }

    if (event.shiftKey) {
      // Shift + Tab (backwards)
      if (document.activeElement === this.firstFocusableElement) {
        event.preventDefault();
        this.lastFocusableElement.focus();
      }
    } else {
      // Tab (forwards)
      if (document.activeElement === this.lastFocusableElement) {
        event.preventDefault();
        this.firstFocusableElement.focus();
      }
    }
  }

  /**
   * Handle Escape key
   */
  handleEscapeKey(event) {
    event.preventDefault();
    this.destroy();
  }

  /**
   * Destroy focus trap
   */
  destroy() {
    this.element.removeEventListener('keydown', this.handleKeyDown.bind(this));
    
    // Return focus to previous element
    if (this.previousActiveElement && this.previousActiveElement.focus) {
      this.previousActiveElement.focus();
    }
  }
}

/**
 * Utility function to create focus trap
 */
export function createFocusTrap(element) {
  return new FocusTrap(element);
}
