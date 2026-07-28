<?php

require_once __DIR__ . '/Config/Config.php';
require_once __DIR__ . '/Config/App/Autoload.php';
require_once __DIR__ . '/Config/Helpers.php';

$destino = 'arzateoscar33@gmail.com';

try {
    $correo = new CorreoContacto();
    $resultado = $correo->enviarPrueba($destino);

    echo $resultado
        ? "Correo enviado correctamente.\n"
        : "PHPMailer devolvió false.\n";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
