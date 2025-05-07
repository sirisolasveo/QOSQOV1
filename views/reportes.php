<?php
require 'header.php';  // Como está en el mismo directorio
?>

<?php
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }
// if (!isset($_SESSION['dni'])) {
//     header("Location: index.php?action=login");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ciudadano - PNP</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --pnp-green: #0A5C36;
            --pnp-light-green: #157348;
            --pnp-gold: #D4AF37;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e3e3e3 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .navbar-pnp {
            background: var(--pnp-green);
            padding: 15px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: var(--pnp-gold) !important;
            font-weight: bold;
            font-size: 1.5em;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            border-top: 5px solid var(--pnp-green);
        }

        .card:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }

        .card-title {
            color: var(--pnp-green);
            font-weight: bold;
        }

        .btn-pnp {
            background: var(--pnp-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-pnp:hover {
            background: var(--pnp-light-green);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(10, 92, 54, 0.3);
        }

        .preview-image {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-top: 10px;
            border: 3px solid var(--pnp-green);
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px;
        }

        .form-control:focus {
            border-color: var(--pnp-green);
            box-shadow: 0 0 0 0.2rem rgba(10, 92, 54, 0.25);
        }

        .badge-pnp {
            background: var(--pnp-green);
            color: white;
            padding: 8px 15px;
            border-radius: 50px;
        }

        .escudo-pnp {
            width: 80px;
            margin-bottom: 20px;
        }

        .upload-area {
            border: 2px dashed var(--pnp-green);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            background: rgba(10, 92, 54, 0.1);
        }

        .upload-icon {
            font-size: 3em;
            color: var(--pnp-green);
            margin-bottom: 10px;
        }

        .preview-container {
            position: relative;
            display: inline-block;
            margin: 10px 0;
        }

        .delete-preview {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            line-height: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .delete-preview:hover {
            transform: scale(1.1);
            background: #c82333;
        }

        #imagen {
            display: none;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-pnp">
        <div class="container">
            <span class="navbar-brand">
                <i class="fas fa-shield-alt mr-2"></i>
                Reporte Ciudadano - PNP
            </span>
        </div>
    </nav>

    <div class="container">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card animate__animated animate__fadeIn">
                    <div class="card-body text-center">
                        <img src="assets/escudo-pnp.png" alt="Escudo PNP" class="escudo-pnp">
                        <h3 class="card-title mb-4">
                            <i class="fas fa-file-alt mr-2"></i>Nuevo Reporte Ciudadano
                        </h3>
                        
                        <form method="POST" action="index.php?action=reportes" enctype="multipart/form-data">
                            <div class="form-group">
                                <div id="uploadArea" class="upload-area">
                                    <input type="file" class="form-control-file" id="imagen" 
                                        name="imagen" accept="image/png, image/jpeg, image/jpg" required>
                                    <i class="fas fa-camera upload-icon"></i>
                                    <p class="mb-0">Haz clic aquí para subir una foto</p>
                                    <small class="text-muted">Solo se permiten imágenes JPG, JPEG y PNG</small>
                                </div>
                                <div id="previewContainer" class="preview-container d-none">
                                    <img id="preview" class="preview-image">
                                    <div class="delete-preview" onclick="eliminarImagen()">
                                        <i class="fas fa-times"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="descripcion">
                                    <i class="fas fa-pen mr-2"></i>Descripción del Hecho
                                </label>
                                <textarea class="form-control" id="descripcion" name="descripcion" 
                                    rows="4" required placeholder="Describa el incidente con el mayor detalle posible..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-pnp btn-block">
                                <i class="fas fa-paper-plane mr-2"></i>Enviar Reporte
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['reporte_status'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if($_SESSION['reporte_status'] == 'success'): ?>
                Swal.fire({
                    title: '¡Reporte Enviado!',
                    text: 'Tu reporte ha sido registrado exitosamente',
                    icon: 'success',
                    confirmButtonColor: '#0A5C36',
                    confirmButtonText: 'Aceptar'
                });
            <?php else: ?>
                Swal.fire({
                    title: '¡Error!',
                    text: 'No se pudo enviar el reporte. Inténtalo nuevamente',
                    icon: 'error',
                    confirmButtonColor: '#0A5C36',
                    confirmButtonText: 'Aceptar'
                });
            <?php endif; ?>
        });
    </script>
    <?php unset($_SESSION['reporte_status']); ?>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        const uploadArea = document.getElementById('uploadArea');
        const inputImagen = document.getElementById('imagen');
        const preview = document.getElementById('preview');
        const previewContainer = document.getElementById('previewContainer');

        uploadArea.onclick = () => inputImagen.click();

        inputImagen.onchange = function() {
            const file = this.files[0];
            if (file) {
                // Validar tipo de archivo
                if (!file.type.match('image/(jpeg|jpg|png)')) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Solo se permiten imágenes JPG, JPEG y PNG',
                        icon: 'error',
                        confirmButtonColor: '#0A5C36'
                    });
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('d-none');
                    uploadArea.classList.add('d-none');
                }
                reader.readAsDataURL(file);
            }
        };

        function eliminarImagen() {
            inputImagen.value = '';
            previewContainer.classList.add('d-none');
            uploadArea.classList.remove('d-none');
            preview.src = '';
        }
    </script>
</body>
</html>