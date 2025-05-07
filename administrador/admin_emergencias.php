<!-- filepath: /c:/xampp/htdocs/QOSQO/administrador/admin_reportes.php -->
<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['cip'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Emergencias en Tiempo Real</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            padding: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .emergency-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .emergency-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .emergency-type {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.9em;
            color: white;
        }
        .emergency-medical { background: linear-gradient(90deg, #ff6b6b, #ff8787); }
        .emergency-security { background: linear-gradient(90deg, #4d96ff, #6ba6ff); }
        .emergency-fire { background: linear-gradient(90deg, #ff9f43, #ffbe76); }
        .estado-pendiente {
            background: linear-gradient(90deg, #ffd32a, #ffba08);
            color: #000;
        }
        .estado-atendido {
            background: linear-gradient(90deg, #20bf6b, #26de81);
            color: white;
        }
        .estado-select {
            background: transparent;
            border: none;
            color: inherit;
            -webkit-appearance: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
            padding: 5px;
        }
        .estado-select option {
            background: white;
            color: black;
        }
        .card-pendiente {
            border-left: 5px solid #ffd32a;
            background: rgba(255, 211, 42, 0.1);
        }
        
        .card-atendido {
            border-left: 5px solid #20bf6b;
            background: rgba(32, 191, 107, 0.1);
        }
        
        .emergency-card {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="animate__animated animate__fadeIn">
    <nav class="navbar">
        <div class="container-fluid">
            <h2 class="text-white mb-0">
                <i class="fas fa-ambulance mr-2"></i>
                Emergencias en Tiempo Real
            </h2>
            <div>
                <button id="toggleSound" class="btn btn-light mr-2">
                    <i class="fas fa-volume-up"></i>
                </button>
                <a href="admin_dashboard.php?action=dashboard" class="btn btn-light">
                    <i class="fas fa-arrow-left mr-2"></i>Volver
                </a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div id="emergencias-container" class="row">
            <!-- Las emergencias se cargarán aquí -->
        </div>
    </div>

    <audio id="notificationSound" preload="auto">
        <source src="../qosqo/assets/notification.mp3" type="audio/mp3">
    </audio>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

    <script>
        let emergenciasActuales = new Map(); // Para trackear las emergencias existentes
        let soundEnabled = true;
        let primeraCargar = true; // Flag para la primera carga

        function cargarEmergencias() {
            $.ajax({
                url: 'administrador/get_emergencias.php',
                type: 'GET',
                dataType: 'json',
                success: function(emergencias) {
                    let nuevasEmergencias = new Map();
                    
                    emergencias.forEach(function(emergencia) {
                        nuevasEmergencias.set(emergencia.id, emergencia);
                        
                        // Reproducir sonido solo si es una emergencia nueva y no es la primera carga
                        if (!emergenciasActuales.has(emergencia.id) && !primeraCargar && soundEnabled) {
                            document.getElementById('notificationSound').play();
                        }
                        
                        if (!emergenciasActuales.has(emergencia.id)) {
                            // Solo agregar nuevas emergencias
                            let nuevoElemento = `
                                <div id="emergencia-${emergencia.id}" class="col-md-4 animate__animated animate__fadeIn">
                                    <div class="card emergency-card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="emergency-type emergency-${emergencia.tipo_emergencia.toLowerCase()}">
                                                    ${emergencia.tipo_emergencia}
                                                </span>
                                                <small class="text-muted">
                                                    <i class="far fa-clock mr-1"></i>
                                                    ${new Date(emergencia.fecha).toLocaleString()}
                                                </small>
                                            </div>
                                            <h5 class="card-title">
                                                <i class="fas fa-user mr-2"></i>${emergencia.nombre}
                                            </h5>
                                            <p class="card-text">
                                                <i class="fas fa-id-card mr-2"></i>DNI: ${emergencia.dni}<br>
                                                <i class="fas fa-phone mr-2"></i>Celular: ${emergencia.celular}
                                            </p>
                                            <a href="https://www.google.com/maps?q=${emergencia.latitud},${emergencia.longitud}" 
                                               target="_blank" 
                                               class="btn btn-primary btn-block">
                                                <i class="fas fa-map-marked-alt mr-2"></i>Ver en Mapa
                                            </a>
                                            <div class="mt-3">
                                                ${emergencia.estado === 'PENDIENTE' ? `
                                                    <div class="emergency-type estado-pendiente">
                                                        <select class="estado-select" onchange="actualizarEstado(${emergencia.id}, this.value)">
                                                            <option value="PENDIENTE" selected>PENDIENTE</option>
                                                            <option value="ATENDIDO">ATENDIDO</option>
                                                        </select>
                                                    </div>
                                                ` : `
                                                    <div class="emergency-type estado-atendido">
                                                        <span class="d-block text-center">ATENDIDO</span>
                                                    </div>
                                                `}
                                                ${emergencia.estado === 'ATENDIDO' ? `
                                                    <div class="btn-group mt-2 w-100">
                                                        <button onclick="editarDescargo(${emergencia.id})" class="btn btn-sm btn-info">
                                                            <i class="fas fa-edit mr-1"></i>Editar
                                                        </button>
                                                        <a href="administrador/generar_pdf_descargo.php?id=${emergencia.id}" target="_blank" 
                                                           class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-file-pdf mr-1"></i>Ver PDF
                                                        </a>
                                                    </div>
                                                ` : ''}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('#emergencias-container').prepend(nuevoElemento);
                        }
                    });

                    // Eliminar emergencias que ya no existen
                    emergenciasActuales.forEach((value, key) => {
                        if (!nuevasEmergencias.has(key)) {
                            $(`#emergencia-${key}`).fadeOut(400, function() {
                                $(this).remove();
                            });
                        }
                    });

                    emergenciasActuales = nuevasEmergencias;
                    primeraCargar = false; // Desactivar el flag después de la primera carga
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }

        // Toggle sonido
        $('#toggleSound').click(function() {
            soundEnabled = !soundEnabled;
            $(this).find('i').toggleClass('fa-volume-up fa-volume-mute');
        });

        // Cargar emergencias cada segundo
        $(document).ready(function() {
            cargarEmergencias();
            setInterval(cargarEmergencias, 1000);
        });

        function actualizarEstado(id, estado) {
            if (estado === 'ATENDIDO') {
                Swal.fire({
                    title: 'Registrar Descargo',
                    html: `
                        <form id="descargoForm">
                            <div class="form-group">
                                <label>Placa del Vehículo *</label>
                                <input type="text" class="form-control" id="placa_vehiculo" 
                                       pattern="[A-Z0-9-]{6,10}" maxlength="10" required>
                                <small class="text-muted">Formato: ABC-123 o ABC123</small>
                            </div>
                            <div class="form-group">
                                <label>Medidas Adoptadas *</label>
                                <textarea class="form-control" id="medidas_adoptadas" 
                                        rows="4" required></textarea>
                            </div>
                        </form>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Registrar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#20bf6b',
                    preConfirm: () => {
                        const placa = document.getElementById('placa_vehiculo').value;
                        const medidas = document.getElementById('medidas_adoptadas').value;

                        if (!placa.trim()) {
                            Swal.showValidationMessage('La placa del vehículo es obligatoria');
                            return false;
                        }
                        if (!medidas.trim()) {
                            Swal.showValidationMessage('Las medidas adoptadas son obligatorias');
                            return false;
                        }
                        if (!placa.match(/^[A-Z0-9-]{6,10}$/)) {
                            Swal.showValidationMessage('Formato de placa inválido');
                            return false;
                        }

                        return {
                            placa_vehiculo: placa,
                            medidas_adoptadas: medidas
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'administrador/actualizar_estado_emergencia.php',
                            type: 'POST',
                            data: {
                                id: id,
                                estado: estado,
                                placa_vehiculo: result.value.placa_vehiculo,
                                medidas_adoptadas: result.value.medidas_adoptadas
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('¡Registrado!', 'La emergencia ha sido atendida', 'success');
                                    cargarEmergencias(); // Recargar las emergencias para actualizar la vista
                                } else {
                                    Swal.fire('Error', 'No se pudo actualizar el estado', 'error');
                                }
                            }
                        });
                    }
                });
            } else {
                $.ajax({
                    url: 'administrador/actualizar_estado_emergencia.php',
                    type: 'POST',
                    data: {
                        id: id,
                        estado: estado
                    },
                    success: function(response) {
                        if (response.success) {
                            // Actualizar la tarjeta completa
                            $(`#emergencia-${id} .card`)
                                .removeClass('card-atendido')
                                .addClass('card-pendiente');
                            // Actualizar el estado
                            $(`#emergencia-${id} .emergency-type.estado-atendido`)
                                .removeClass('estado-atendido')
                                .addClass('estado-pendiente');
                            // Actualizar el select
                            $(`#emergencia-${id} select.estado-select`).val('PENDIENTE');
                        } else {
                            alert('Error al actualizar el estado');
                        }
                    },
                    error: function() {
                        alert('Error al actualizar el estado');
                    }
                });
            }
        }

        function editarDescargo(id) {
            $.ajax({
                url: 'administrador/get_descargo.php',
                type: 'GET',
                data: { id: id },
                success: function(descargo) {
                    if (descargo.error) {
                        Swal.fire('Error', descargo.error, 'error');
                        return;
                    }

                    // Verificar si el admin actual puede editar
                    if (descargo.cip_administrador !== '<?php echo $_SESSION['cip']; ?>') {
                        Swal.fire('Error', 'Solo el administrador que registró el descargo puede editarlo', 'error');
                        return;
                    }

                    Swal.fire({
                        title: 'Editar Descargo',
                        html: `
                            <form id="editDescargoForm">
                                <div class="form-group">
                                    <label>Placa del Vehículo *</label>
                                    <input type="text" class="form-control" id="placa_vehiculo" 
                                        value="${descargo.placa_vehiculo}"
                                        pattern="[A-Z0-9-]{6,10}" maxlength="10" required>
                                    <small class="text-muted">Formato: ABC-123 o ABC123</small>
                                </div>
                                <div class="form-group">
                                    <label>Medidas Adoptadas *</label>
                                    <textarea class="form-control" id="medidas_adoptadas" 
                                        rows="4" required>${descargo.medidas_adoptadas}</textarea>
                                </div>
                            </form>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Actualizar',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#20bf6b',
                        preConfirm: () => {
                            const placa = document.getElementById('placa_vehiculo').value;
                            const medidas = document.getElementById('medidas_adoptadas').value;

                            if (!placa.trim() || !medidas.trim()) {
                                Swal.showValidationMessage('Todos los campos son obligatorios');
                                return false;
                            }
                            if (!placa.match(/^[A-Z0-9-]{6,10}$/)) {
                                Swal.showValidationMessage('Formato de placa inválido');
                                return false;
                            }

                            return {
                                placa_vehiculo: placa,
                                medidas_adoptadas: medidas
                            };
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: 'administrador/actualizar_descargo.php',
                                type: 'POST',
                                data: {
                                    id: id,
                                    placa_vehiculo: result.value.placa_vehiculo,
                                    medidas_adoptadas: result.value.medidas_adoptadas
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire('¡Actualizado!', 'El descargo ha sido actualizado', 'success');
                                    } else {
                                        Swal.fire('Error', response.error || 'No se pudo actualizar el descargo', 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error', 'Error de conexión', 'error');
                                }
                            });
                        }
                    });
                },
                error: function() {
                    Swal.fire('Error', 'Error al obtener el descargo', 'error');
                }
            });
        }
    </script>
</body>
</html>