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
    <title>Reportes en Tiempo Real - PNP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --pnp-green: #0A5C36;
        }
        body {
            background: var(--pnp-green);
            min-height: 100vh;
        }
        .navbar {
            background: rgba(0,0,0,0.2);
        }
        .card {
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: all 0.3s ease;
            border: 3px solid transparent;
            background: white; /* Aseguramos fondo blanco para la card */
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-pendiente {
            border-color: #ffc107;
            background: rgba(255, 193, 7, 0.1);
        }
        .card-atendido {
            border-color: var(--pnp-green);
            background: rgba(10, 92, 54, 0.1);
        }
        .reporte-img {
            width: 100%;
            height: 300px; /* Aumentamos la altura */
            object-fit: contain; /* Cambiamos de 'cover' a 'contain' */
            background: #f8f9fa; /* Fondo claro para imágenes */
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            padding: 10px; /* Agregamos padding para que no toque los bordes */
        }
        .estado-select {
            color: inherit;
            -webkit-appearance: none;
            cursor: pointer;
            width: 100%;
            text-align: center;
            padding: 5px;
            border: 2px solid #ddd;
            border-radius: 50px;
        }
        .estado-select option {
            background: white;
            color: black;
        }
        .badge-pendiente {
            background: #ffc107;
            color: #000;
        }
        .badge-atendido {
            background: var(--pnp-green);
            color: #fff;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="animate__animated animate__fadeIn">
    <nav class="navbar">
        <div class="container-fluid">
            <h2 class="text-white mb-0">
                <i class="fas fa-clipboard-list mr-2"></i>
                Reportes en Tiempo Real
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
        <div id="reportes-container" class="row">
            <!-- Los reportes se cargarán aquí -->
        </div>
    </div>

    <audio id="notificationSound" preload="auto">
        <source src="../qosqo/assets/notification.mp3" type="audio/mp3">
    </audio>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let lastReportCount = 0;
        let soundEnabled = true;
        let reportesActuales = new Map();

        function cargarReportes() {
            $.ajax({
                url: 'administrador/get_reportes.php',
                type: 'GET',
                dataType: 'json',
                success: function(reportes) {
                    let nuevosReportes = new Map();
                    let hayNuevosReportes = false;
                    
                    reportes.forEach(function(reporte) {
                        nuevosReportes.set(reporte.id, reporte);
                        
                        // Verificar si es un reporte nuevo
                        if (!reportesActuales.has(reporte.id)) {
                            hayNuevosReportes = true;
                            let nuevoElemento = `
                                <div id="reporte-${reporte.id}" class="col-md-4 animate__animated animate__fadeIn">
                                    <div class="card card-${reporte.estado.toLowerCase()}">
                                        <img src="uploads/${reporte.imagen}" class="reporte-img" alt="Imagen del reporte">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="badge badge-${reporte.estado.toLowerCase()}">
                                                    ${reporte.estado}
                                                </span>
                                                <small class="text-muted">
                                                    <i class="far fa-clock mr-1"></i>
                                                    ${new Date(reporte.fecha).toLocaleString()}
                                                </small>
                                            </div>
                                            <p class="card-text">${reporte.descripcion}</p>
                                            <hr>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user mr-1"></i>${reporte.nombre}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-id-card mr-1"></i>DNI: ${reporte.dni}
                                                    </small>
                                                </div>
                                                ${reporte.estado === 'PENDIENTE' ? `
                                                    <select class="estado-select" onchange="actualizarEstado(${reporte.id}, this.value)">
                                                        <option value="PENDIENTE" selected>PENDIENTE</option>
                                                        <option value="ATENDIDO">ATENDIDO</option>
                                                    </select>
                                                ` : `
                                                    <div class="btn-group">
                                                        <button onclick="editarDescargo(${reporte.id})" class="btn btn-sm btn-info">
                                                            <i class="fas fa-edit mr-1"></i>Editar
                                                        </button>
                                                        <a href="administrador/generar_pdf_reporte.php?id=${reporte.id}" 
                                                           target="_blank" class="btn btn-sm btn-secondary">
                                                            <i class="fas fa-file-pdf mr-1"></i>PDF
                                                        </a>
                                                    </div>
                                                `}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            $('#reportes-container').prepend(nuevoElemento);
                        }
                    });

                    // Reproducir sonido solo si hay nuevos reportes y no es la primera carga
                    if (hayNuevosReportes && reportesActuales.size > 0 && soundEnabled) {
                        document.getElementById('notificationSound').play();
                    }

                    // Actualizar el mapa de reportes actuales
                    reportesActuales = nuevosReportes;
                }
            });
        }

        $('#toggleSound').click(function() {
            soundEnabled = !soundEnabled;
            $(this).find('i').toggleClass('fa-volume-up fa-volume-mute');
        });

        $(document).ready(function() {
            cargarReportes();
            setInterval(cargarReportes, 1000);
        });

        function actualizarEstado(id, estado) {
            if (estado === 'ATENDIDO') {
                Swal.fire({
                    title: 'Registrar Descargo del Reporte',
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
                            url: 'administrador/actualizar_estado_reporte.php',
                            type: 'POST',
                            data: {
                                id: id,
                                estado: estado,
                                placa_vehiculo: result.value.placa_vehiculo,
                                medidas_adoptadas: result.value.medidas_adoptadas
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('¡Registrado!', 'El reporte ha sido atendido', 'success');
                                    cargarReportes();
                                } else {
                                    Swal.fire('Error', response.error || 'No se pudo actualizar el estado', 'error');
                                }
                            }
                        });
                    }
                });
            }
        }

        function editarDescargo(id) {
            $.ajax({
                url: 'administrador/get_descargo_reporte.php',
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
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: 'administrador/actualizar_descargo_reporte.php',
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