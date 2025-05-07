document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registroForm');
    const claveInput = document.getElementById('clave');
    const confirmarClaveInput = document.getElementById('confirmar_clave');
    const dniInput = document.getElementById('dni');
    const celularInput = document.getElementById('celular');
    const diaSelect = document.getElementById('dia');
    const mesSelect = document.getElementById('mes');
    const anioSelect = document.getElementById('anio');
    const edadCalculadaDiv = document.getElementById('edad-calculada');

    // Función para calcular edad
    function calcularEdad(dia, mes, anio) {
        if (!dia || !mes || !anio) return 0;
        
        const fechaNac = new Date(anio, mes - 1, dia);
        const hoy = new Date();
        let edad = hoy.getFullYear() - fechaNac.getFullYear();
        const m = hoy.getMonth() - fechaNac.getMonth();
        
        if (m < 0 || (m === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }
        
        return edad;
    }

    // Función para validar y mostrar la edad
    function validarYMostrarEdad() {
        const dia = diaSelect.value;
        const mes = mesSelect.value;
        const anio = anioSelect.value;
        
        if (dia && mes && anio) {
            const edad = calcularEdad(dia, mes, anio);
            edadCalculadaDiv.textContent = `Edad calculada: ${edad} años`;
            edadCalculadaDiv.style.color = edad >= 14 ? 'var(--success-color)' : 'var(--error-color)';
            return edad >= 14;
        }
        return false;
    }

    // Evento para actualizar la edad cuando cambian los selectores
    [diaSelect, mesSelect, anioSelect].forEach(select => {
        select.addEventListener('change', validarYMostrarEdad);
    });

    // Función para validar DNI
    function validarDNI(dni) {
        return /^[0-9]{8}$/.test(dni);
    }

    // Función para validar el formato del celular
    function validarFormatoCelular(celular) {
        if (!celular.startsWith('9')) {
            return 'El número debe empezar con 9';
        }
        if (celular.length < 9) {
            return 'Faltan ' + (9 - celular.length) + ' dígitos';
        }
        if (celular.length > 9) {
            return 'El número debe tener exactamente 9 dígitos';
        }
        if (!/^[0-9]+$/.test(celular)) {
            return 'Solo se permiten números';
        }
        return '';
    }

    // Función para validar el formato de la contraseña
    function validarFormatoClave(clave) {
        const errores = [];
        if (clave.length < 8) errores.push('Mínimo 8 caracteres');
        if (clave.length > 20) errores.push('Máximo 20 caracteres');
        if (!/[A-Z]/.test(clave)) errores.push('Al menos una mayúscula');
        if (!/[0-9]/.test(clave)) errores.push('Al menos un número');
        if (!/[!@#$%^&*(),.?":{}|<>]/.test(clave)) errores.push('Al menos un carácter especial');
        return errores;
    }

    // Función para mostrar el nivel de seguridad de la contraseña
    function mostrarNivelSeguridad(clave) {
        const seguridad = document.getElementById('seguridadClave');
        if (!seguridad) return;

        const errores = validarFormatoClave(clave);
        let nivel = 'débil';
        let color = '#dc3545';

        if (errores.length <= 2) {
            nivel = 'media';
            color = '#ffc107';
        }
        if (errores.length === 0) {
            nivel = 'fuerte';
            color = '#28a745';
        }

        seguridad.textContent = `Seguridad: ${nivel}`;
        seguridad.style.color = color;
    }

    // Validación en tiempo real de la contraseña
    if (claveInput) {
        claveInput.addEventListener('input', function() {
            const errores = validarFormatoClave(this.value);
            const helpText = this.parentElement.querySelector('.input-help');
            
            if (errores.length > 0) {
                this.setCustomValidity(errores.join('\n'));
                if (helpText) {
                    helpText.innerHTML = errores.map(error => `<span class="text-danger">• ${error}</span>`).join('<br>');
                }
            } else {
                this.setCustomValidity('');
                if (helpText) {
                    helpText.innerHTML = '<span class="text-success">✓ Contraseña válida</span>';
                }
            }
            
            mostrarNivelSeguridad(this.value);
        });
    }

    // Validación en tiempo real de confirmación de contraseña
    if (confirmarClaveInput) {
        confirmarClaveInput.addEventListener('input', function() {
            if (this.value !== claveInput.value) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Validación en tiempo real del DNI con throttling
    let dniTimeout;
    if (dniInput) {
        const dniContainer = dniInput.closest('.form-group');
        const dniValidationMessage = dniContainer.querySelector('.validation-message');
        
        dniInput.addEventListener('input', function() {
            clearTimeout(dniTimeout);
            const dni = this.value;
            
            // Limpiar mensaje si el DNI no tiene 8 dígitos
            if (dni.length !== 8) {
                dniValidationMessage.textContent = '';
                dniValidationMessage.style.color = '';
                this.setCustomValidity('');
                return;
            }

            // Solo números
            if (!/^\d+$/.test(dni)) {
                dniValidationMessage.textContent = '❌ Solo se permiten números';
                dniValidationMessage.style.color = 'var(--error-color)';
                this.setCustomValidity('Solo se permiten números');
                return;
            }

            dniTimeout = setTimeout(() => {
                fetch('index.php?action=verificarDNI&dni=' + dni)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.exists) {
                            dniValidationMessage.textContent = '❌ Este DNI ya está registrado';
                            dniValidationMessage.style.color = 'var(--error-color)';
                            this.setCustomValidity('Este DNI ya está registrado');
                        } else {
                            dniValidationMessage.textContent = '✓ DNI disponible';
                            dniValidationMessage.style.color = 'var(--success-color)';
                            this.setCustomValidity('');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        dniValidationMessage.textContent = '❌ Error al verificar DNI';
                        dniValidationMessage.style.color = 'var(--error-color)';
                    });
            }, 500);
        });
    }

    // Validación en tiempo real del celular
    if (celularInput) {
        celularInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
            const helpText = this.parentElement.querySelector('.input-help');
            
            if (this.value && !this.value.startsWith('9')) {
                this.setCustomValidity('El número debe empezar con 9');
                if (helpText) helpText.textContent = '❌ El número debe empezar con 9';
            } else if (this.value.length !== 9) {
                this.setCustomValidity('El número debe tener 9 dígitos');
                if (helpText) helpText.textContent = `${this.value.length}/9 dígitos`;
            } else {
                this.setCustomValidity('');
                if (helpText) helpText.textContent = '✓ Número válido';
            }
        });
    }

    // Protección contra CSRF
    function agregarCSRFToken() {
        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = 'csrf_token';
        token.value = Math.random().toString(36).substr(2);
        form.appendChild(token);
    }

    // Validación del formulario
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const edad = calcularEdad(
                diaSelect.value,
                mesSelect.value,
                anioSelect.value
            );

            if (edad < 14) {
                alert('Debes ser mayor de 14 años para registrarte');
                return;
            }

            // Resto de validaciones
            const campos = form.querySelectorAll('input[required], select[required]');
            let isValid = true;
            
            campos.forEach(campo => {
                if (!campo.value.trim()) {
                    isValid = false;
                    mostrarError(campo, 'Este campo es requerido');
                } else if (campo.validationMessage) {
                    isValid = false;
                }
            });

            if (!isValid) {
                return;
            }

            // Combinar fecha de nacimiento
            const fechaNacimiento = `${anioSelect.value}-${mesSelect.value.padStart(2, '0')}-${diaSelect.value.padStart(2, '0')}`;
            document.getElementById('fecha_nacimiento').value = fechaNacimiento;

            // Validar DNI
            const dni = document.getElementById('dni').value;
            if (!validarDNI(dni)) {
                alert('El DNI debe contener exactamente 8 números');
                return;
            }

            // Validar celular
            const celular = document.getElementById('celular').value;
            const errorCelular = validarFormatoCelular(celular);
            if (errorCelular) {
                alert(errorCelular);
                return;
            }

            // Validar clave
            const clave = document.getElementById('clave').value;
            const errorClave = validarFormatoClave(clave).join('\n');
            if (errorClave) {
                alert(errorClave);
                return;
            }

            // Validar confirmación de clave
            const confirmarClave = document.getElementById('confirmar_clave').value;
            if (clave !== confirmarClave) {
                alert('Las claves no coinciden');
                return;
            }

            // Agregar token CSRF antes de enviar
            agregarCSRFToken();
            
            // Si todo está validado, enviar el formulario
            this.submit();
        });
    }

    // Función para mostrar/ocultar contraseña
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = input.nextElementSibling;
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    // Función para mostrar errores
    function mostrarError(input, mensaje) {
        const container = input.closest('.form-group');
        let errorDiv = container.querySelector('.error-message');
        
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            container.appendChild(errorDiv);
        }
        
        errorDiv.textContent = mensaje;
        input.classList.add('error');
    }

    // Recuperar valores de fecha si existen en los datos del formulario
    if (typeof fechaGuardada !== 'undefined' && fechaGuardada) {
        const fecha = new Date(fechaGuardada);
        document.getElementById('dia').value = ('0' + fecha.getDate()).slice(-2);
        document.getElementById('mes').value = ('0' + (fecha.getMonth() + 1)).slice(-2);
        document.getElementById('anio').value = fecha.getFullYear();
    }
});