/**
 * UI Toasts - Classic Script
 * Handles toast notifications with accessibility features
 */

(function(){
	class UIToasts {
		constructor() {
			this.container = null;
			this.toasts = new Map();
			this.init();
		}

		init() {
			this.createContainer();
			this.setupKeyboardNavigation();
		}

		/**
		 * Create toast container
		 */
		createContainer() {
			this.container = document.createElement('div');
			this.container.className = 'toast-container';
			this.container.setAttribute('aria-live', 'polite');
			this.container.setAttribute('aria-label', 'Notifications');
			document.body.appendChild(this.container);
		}

		/**
		 * Show success toast
		 */
		success(message, title = 'Success', duration = 5000) {
			return this.show('success', message, title, duration);
		}

		/**
		 * Show error toast
		 */
		error(message, title = 'Error', duration = 7000) {
			return this.show('error', message, title, duration);
		}

		/**
		 * Show warning toast
		 */
		warning(message, title = 'Warning', duration = 6000) {
			return this.show('warning', message, title, duration);
		}

		/**
		 * Show info toast
		 */
		info(message, title = 'Info', duration = 5000) {
			return this.show('info', message, title, duration);
		}

		/**
		 * Show toast notification
		 */
		show(type, message, title, duration) {
			const toastId = `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
			
			const toast = this.createToast(toastId, type, message, title);
			this.container.appendChild(toast);
			this.toasts.set(toastId, toast);

			// Trigger animation
			requestAnimationFrame(() => {
				toast.classList.add('show');
			});

			// Auto-remove after duration
			if (duration > 0) {
				setTimeout(() => {
					this.hide(toastId);
				}, duration);
			}

			return toastId;
		}

		/**
		 * Create toast element
		 */
		createToast(id, type, message, title) {
			const toast = document.createElement('div');
			toast.id = id;
			toast.className = `toast-merlaws toast-merlaws-${type} slide-in-right`;
			toast.setAttribute('role', 'alert');
			toast.setAttribute('aria-live', 'assertive');
			toast.setAttribute('aria-atomic', 'true');

			toast.innerHTML = `
				<div class="toast-merlaws-header">
					<div class="toast-icon">
						${this.getIcon(type)}
					</div>
					<div class="toast-title">${this.escapeHtml(title)}</div>
					<button type="button" class="toast-close" aria-label="Close notification">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="toast-merlaws-body">
					${this.escapeHtml(message)}
				</div>
			`;

			// Add close functionality
			const closeBtn = toast.querySelector('.toast-close');
			closeBtn.addEventListener('click', () => {
				this.hide(id);
			});

			return toast;
		}

		/**
		 * Hide toast
		 */
		hide(toastId) {
			const toast = this.toasts.get(toastId);
			if (!toast) return;

			toast.classList.remove('show');
			toast.classList.add('fade-out');

			setTimeout(() => {
				if (toast.parentElement) {
					toast.parentElement.removeChild(toast);
				}
				this.toasts.delete(toastId);
			}, 300);
		}

		/**
		 * Hide all toasts
		 */
		hideAll() {
			this.toasts.forEach((toast, id) => {
				this.hide(id);
			});
		}

		/**
		 * Get icon for toast type
		 */
		getIcon(type) {
			const icons = {
				success: '',
				error: '',
				warning: '',
				info: 'ℹ'
			};
			return icons[type] || icons.info;
		}

		/**
		 * Escape HTML to prevent XSS
		 */
		escapeHtml(text) {
			const div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}

		/**
		 * Setup keyboard navigation
		 */
		setupKeyboardNavigation() {
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape') {
					this.hideAll();
				}
			});
		}
	}

	// Global instance
	window.uiToasts = new UIToasts();
})();
