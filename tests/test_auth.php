<?php
require_once '../config/init.php';

// Test 1: Verificar sesión sin autenticación
echo "Test 1: Verificar sesión sin autenticación\n";
if (!$auth->verificarAutenticacion()) {
    echo "✅ Correcto: Usuario no autenticado\n";
} else {
    echo "❌ Error: Usuario no debería estar autenticado\n";
}

// Test 2: Probar remember token
echo "\nTest 2: Probar remember token\n";
$user = new User($conn);
$resultado = $user->login('12345678', 'clave123', true);
if (isset($resultado['remember_token'])) {
    echo "✅ Correcto: Token generado\n";
    echo "Token: " . $resultado['remember_token'] . "\n";
} else {
    echo "❌ Error: No se generó el token\n";
}

// Test 3: Verificar cookie
echo "\nTest 3: Verificar cookie\n";
if (isset($_COOKIE['remember_token'])) {
    echo "✅ Correcto: Cookie establecida\n";
} else {
    echo "❌ Error: Cookie no establecida\n";
}