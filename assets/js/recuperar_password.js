document.addEventListener('DOMContentLoaded', function() {
    // Manejo de contraseñas
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Cambiar el ícono
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    });

    // Validación de DNI
    const dniInput = document.getElementById('dni');
    if (dniInput) {
        dniInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8);
            
            if (this.value.length > 0 && this.value.length !== 8) {
                this.setCustomValidity('El DNI debe tener exactamente 8 números');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Validación de nueva contraseña
    const formNuevaClave = document.getElementById('formNuevaClave');
    if (formNuevaClave) {
        const nuevaClaveInput = document.getElementById('nueva_clave');
        const confirmarClaveInput = document.getElementById('confirmar_clave');

        function validarClave(clave) {
            const errores = [];
            if (clave.length < 8) errores.push('La contraseña debe tener al menos 8 caracteres');
            if (clave.length > 20) errores.push('La contraseña no puede tener más de 20 caracteres');
            if (!/[A-Z]/.test(clave)) errores.push('Debe incluir al menos una mayúscula');
            if (!/[0-9]/.test(clave)) errores.push('Debe incluir al menos un número');
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(clave)) errores.push('Debe incluir al menos un carácter especial');
            return errores;
        }

        nuevaClaveInput.addEventListener('input', function() {
            const errores = validarClave(this.value);
            if (errores.length > 0) {
                this.setCustomValidity(errores.join('\n'));
            } else {
                this.setCustomValidity('');
            }
        });

        confirmarClaveInput.addEventListener('input', function() {
            if (this.value !== nuevaClaveInput.value) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });

        formNuevaClave.addEventListener('submit', function(e) {
            const erroresNuevaClave = validarClave(nuevaClaveInput.value);
            
            if (erroresNuevaClave.length > 0) {
                e.preventDefault();
                alert(erroresNuevaClave.join('\n'));
                return;
            }

            if (nuevaClaveInput.value !== confirmarClaveInput.value) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return;
            }
        });
    }
});