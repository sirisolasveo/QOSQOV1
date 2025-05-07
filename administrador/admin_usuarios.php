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
    <title>Lista de Usuarios</title>
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
        .navbar h2 {
            color: white;
            margin: 0;
        }
        .container {
            padding: 2rem;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            background: white;
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        .table {
            margin: 0;
        }
        .table thead th {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px;
        }
        .table td {
            vertical-align: middle;
            padding: 15px;
        }
        .btn {
            border-radius: 50px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .btn-dashboard {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            margin-bottom: 20px;
        }
        .btn-dashboard:hover {
            color: white;
            background: linear-gradient(90deg, #764ba2 0%, #667eea 100%);
        }
        .badge {
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }
        .badge:hover {
            transform: scale(1.1);
            cursor: pointer;
        }
        .badge-success {
            background: linear-gradient(90deg, #00b09b, #96c93d);
        }
        .badge-warning {
            background: linear-gradient(90deg, #f6d365, #fda085);
        }
        .modal-content {
            border-radius: 15px;
        }
        .modal-header {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .close {
            color: white;
        }
        .toast {
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .loading-spinner {
            width: 3rem;
            height: 3rem;
        }
        tr {
            transition: all 0.3s ease;
        }
        tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        .btn-group-sm > .btn {
            margin: 0 2px;
        }
    </style>
</head>
<body class="animate__animated animate__fadeIn">
    <nav class="navbar">
        <div class="container-fluid">
            <h2 class="animate__animated animate__slideInLeft">
                <i class="fas fa-user-shield mr-2"></i>
                Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre']); ?>
            </h2>
            <a href="admin_dashboard.php?action=dashboard" class="btn btn-light animate__animated animate__slideInRight">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="card animate__animated animate__fadeInUp">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <i class="fas fa-users mr-2"></i>
                    Lista de Usuarios
                </h2>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Edad</th>
                                <th>DNI</th>
                                <th>Celular</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usuarios-lista">
                            <!-- Los usuarios se cargarán aquí mediante AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar estado -->
    <div class="modal fade" id="cambiarEstadoModal">
        <div class="modal-dialog">
            <div class="modal-content animate__animated animate__fadeInDown">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit mr-2"></i>
                        Cambiar Estado de Usuario
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formCambiarEstado">
                        <input type="hidden" id="usuarioId">
                        <div class="form-group">
                            <label>Estado:</label>
                            <select class="form-control" id="estadoUsuario">
                                <option value="PENDIENTE">PENDIENTE</option>
                                <option value="ACTIVO">ACTIVO</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarEstado()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast actualizado -->
    <div class="position-fixed bottom-0 right-0 p-3" style="z-index: 5; right: 0; bottom: 0;">
        <div id="statusToast" class="toast hide animate__animated animate__fadeInRight">
            <div class="toast-body">
                <i class="fas fa-check-circle text-success mr-2"></i>
                Estado actualizado exitosamente
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function() {
            cargarUsuarios(); // Carga inicial

            // Actualizar cada 10 segundos (10000 milisegundos)
            setInterval(cargarUsuarios, 1000);

            function cargarUsuarios() {
                $('#loading').removeClass('d-none');
                $.ajax({
                    url: 'administrador/get_usuarios.php', // Corregimos la ruta ya que get_usuarios.php está en la misma carpeta
                    type: 'GET',
                    dataType: 'json',
                    success: function(usuarios) {
                        let html = '';
                        if (Array.isArray(usuarios) && usuarios.length > 0) {
                            usuarios.forEach(function(usuario) {
                                let estadoClass = usuario.estado === 'ACTIVO' ? 'success' : 'warning';
                                html += `
                                    <tr>
                                        <td>${usuario.id}</td>
                                        <td>${usuario.nombre}</td>
                                        <td>${usuario.apellidos}</td>
                                        <td>${usuario.edad}</td>
                                        <td>${usuario.dni}</td>
                                        <td>${usuario.celular}</td>
                                        <td>
                                            <span class="badge badge-${estadoClass}" style="cursor: pointer" 
                                                  onclick="abrirModalEstado(${usuario.id}, '${usuario.estado}')">
                                                ${usuario.estado}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="admin_dashboard.php?action=editarUsuario&id=${usuario.id}" class="btn btn-primary btn-sm">Editar</a>
                                            <a href="admin_dashboard.php?action=eliminarUsuario&id=${usuario.id}" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este usuario?')">Eliminar</a>
                                        </td>
                                    </tr>
                                `;
                            });
                        } else {
                            html = '<tr><td colspan="8" class="text-center">No hay usuarios registrados</td></tr>';
                        }
                        $('#usuarios-lista').html(html);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar usuarios:', error);
                        $('#usuarios-lista').html('<tr><td colspan="8" class="text-center text-danger">Error al cargar los usuarios</td></tr>');
                    },
                    complete: function() {
                        $('#loading').addClass('d-none');
                    }
                });
            }

            window.eliminarUsuario = function(id) {
                if (confirm('¿Está seguro de eliminar este usuario?')) {
                    // Implementaremos la eliminación más adelante
                    console.log('Eliminar usuario:', id);
                }
            };
        });

        function abrirModalEstado(id, estado) {
            $('#usuarioId').val(id);
            $('#estadoUsuario').val(estado);
            $('#cambiarEstadoModal').modal('show');
        }

        function guardarEstado() {
            const id = $('#usuarioId').val();
            const estado = $('#estadoUsuario').val();
            
            $.ajax({
                url: 'administrador/crud/actualizar_estado.php', // Ruta relativa correcta
                type: 'POST',
                data: {
                    id: id,
                    estado: estado
                },
                success: function(response) {
                    $('#cambiarEstadoModal').modal('hide');
                    cargarUsuarios();
                    $('#statusToast').toast('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                }
            });
        }
    </script>
</body>
</html>