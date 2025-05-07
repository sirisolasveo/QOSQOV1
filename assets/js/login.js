document.addEventListener('DOMContentLoaded', function() {
    // Función para alternar la visibilidad de la contraseña
    const togglePassword = document.querySelector('.toggle-password');
    const passwordInput = document.querySelector('#clave');
    const dniInput = document.querySelector('#dni');
    const loginForm = document.querySelector('#loginForm');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Cambiar el ícono
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    // Mostrar mensaje de error
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        let errorDiv = formGroup.querySelector('.error-message');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            formGroup.appendChild(errorDiv);
        }
        
        errorDiv.textContent = message;
        input.classList.add('error');
    }

    // Limpiar mensaje de error
    function clearError(input) {
        const formGroup = input.closest('.form-group');
        const errorDiv = formGroup.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
        input.classList.remove('error');
    }

    // Validación en tiempo real del DNI
    if (dniInput) {
        dniInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
            
            if (this.value.length > 0) {
                if (this.value.length !== 8) {
                    showError(this, 'El DNI debe tener exactamente 8 números');
                } else {
                    clearError(this);
                }
            } else {
                clearError(this);
            }
        });
    }

    // Validación del formulario al enviar
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let isValid = true;

            // Validar DNI
            if (!dniInput.value) {
                showError(dniInput, 'El DNI es requerido');
                isValid = false;
            } else if (!/^\d{8}$/.test(dniInput.value)) {
                showError(dniInput, 'El DNI debe contener exactamente 8 números');
                isValid = false;
            }

            // Validar contraseña
            if (!passwordInput.value.trim()) {
                showError(passwordInput, 'La contraseña es requerida');
                isValid = false;
            }

            if (!isValid) {
                e.preventDefault();
            }
        });
    }

    // Manejar los mensajes de alerta
    const messages = document.querySelectorAll('.mensaje-exito, .mensaje-error, .mensaje-warning');
    messages.forEach(message => {
        // Añadir transición CSS
        message.style.transition = 'opacity 0.5s ease-in-out';
        
        // Mantener el mensaje por 10 segundos antes de comenzar a desvanecerlo
        setTimeout(() => {
            message.style.opacity = '0';
            // Esperar a que termine la transición antes de remover el elemento
            setTimeout(() => {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 500);
        }, 10000); // 10 segundos
    });
});