
<?php
require 'header.php';  // Como está en el mismo directorio
?>

<!-- filepath: /c:/xampp/htdocs/QOSQO/views/emergencias.php -->
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
    <title>Emergencias</title>
    <script>
        let countdown;
        let interval;
        let isCancelled = false;

        function startCountdown(tipoEmergencia) {
            countdown = 5;
            isCancelled = false;
            interval = setInterval(() => {
                document.getElementById('countdown').innerText = countdown;
                countdown--;
                if (countdown < 0) {
                    clearInterval(interval);
                    if (!isCancelled) {
                        getLocation(tipoEmergencia);
                    }
                }
            }, 1000);
        }

        function cancelCountdown() {
            clearInterval(interval);
            isCancelled = true;
            document.getElementById('countdown').innerText = 'Cancelado';
        }

        function getLocation(tipoEmergencia) {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const latitud = position.coords.latitude;
                    const longitud = position.coords.longitude;
                    document.getElementById('latitud').value = latitud;
                    document.getElementById('longitud').value = longitud;
                    document.getElementById('tipo_emergencia').value = tipoEmergencia;
                    document.getElementById('emergenciaForm').submit();
                });
            } else {
                alert("Geolocalización no es soportada por este navegador.");
            }
        }
    </script>
</head>
<body>
    <h2>Emergencias</h2>
    <p>Seleccione una opción de emergencia:</p>
    <button onclick="startCountdown('VIOLENCIA FAMILIAR')">VIOLENCIA FAMILIAR</button>
    <button onclick="startCountdown('ACCIDENTE DE TRANSITO')">ACCIDENTE DE TRANSITO</button>
    <button onclick="startCountdown('ROBO Y/O HURTO')">ROBO Y/O HURTO</button>
    <button onclick="startCountdown('SECUESTRO')">SECUESTRO</button>
    <button onclick="startCountdown('OTROS')">OTROS</button>
    <button onclick="cancelCountdown()">CANCELAR ENVIO</button>
    <p>Contador: <span id="countdown">5</span></p>
    <form id="emergenciaForm" action="index.php?action=emergencias" method="post">
        <input type="hidden" id="latitud" name="latitud">
        <input type="hidden" id="longitud" name="longitud">
        <input type="hidden" id="tipo_emergencia" name="tipo_emergencia">
    </form>
</body>
</html>