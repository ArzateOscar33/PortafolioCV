<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Servicio SMTP para solicitudes del formulario público.
 *
 * Responsabilidades:
 * - Notificar al administrador.
 * - Enviar confirmación automática al visitante.
 * - Renderizar las plantillas HTML y su alternativa en texto plano.
 * - Registrar errores técnicos sin exponerlos en la respuesta pública.
 */
class CorreoContacto
{
    private string $root;

    public function __construct()
    {
        $this->root = defined('UPLOAD_ROOT')
            ? rtrim(UPLOAD_ROOT, '/\\')
            : dirname(__DIR__, 2);

        $autoload = $this->root . '/vendor/autoload.php';

        if (!is_file($autoload)) {
            throw new RuntimeException(
                'No se encontró vendor/autoload.php. Ejecuta composer require phpmailer/phpmailer.'
            );
        }

        require_once $autoload;
        $this->validarConfiguracion();
    }

    /**
     * Envía una confirmación de prueba desde la terminal.
     */
    public function enviarPrueba(string $destino): bool
    {
        $destino = trim($destino);

        if (!filter_var($destino, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(
                'El destinatario de prueba no es válido.'
            );
        }

        return $this->enviarConfirmacion(
            [
                'nombre_completo' => 'Prueba SMTP',
                'correo' => $destino,
                'telefono' => null,
                'nombre_empresa' => null,
                'asunto' => 'Verificación de PHPMailer',
                'mensaje' => 'Este mensaje confirma que la conexión SMTP, la plantilla HTML y los iconos incrustados funcionan correctamente.',
                'fecha_preferida' => null,
                'modalidad_preferida' => 'en_linea',
            ],
            999999,
            [
                'nombre' => 'Prueba de configuración',
            ]
        );
    }

    /**
     * Envía ambos correos sin deshacer el registro de la solicitud
     * cuando el servidor SMTP presenta un problema.
     */
    public function enviarSolicitud(
        array $datos,
        int $idSolicitud,
        ?array $servicio = null
    ): array {
        $resultado = [
            'administrador' => false,
            'confirmacion' => false,
        ];

        try {
            $resultado['administrador'] =
                $this->enviarAlAdministrador(
                    $datos,
                    $idSolicitud,
                    $servicio
                );
        } catch (Throwable $e) {
            $this->registrarError(
                'notificación al administrador',
                $e
            );
        }

        if ($this->autoRespuestaHabilitada()) {
            try {
                $resultado['confirmacion'] =
                    $this->enviarConfirmacion(
                        $datos,
                        $idSolicitud,
                        $servicio
                    );
            } catch (Throwable $e) {
                $this->registrarError(
                    'confirmación automática',
                    $e
                );
            }
        }

        return $resultado;
    }

    private function enviarAlAdministrador(
        array $datos,
        int $idSolicitud,
        ?array $servicio
    ): bool {
        $mail = $this->crearMailer();
        $this->incrustarIconos($mail);

        $mail->addAddress(
            $this->adminEmail(),
            $this->adminNombre()
        );

        /*
         * El remitente siempre pertenece al dominio autenticado.
         * El correo del visitante se utiliza solamente como Reply-To.
         */
        $mail->addReplyTo(
            (string) $datos['correo'],
            (string) $datos['nombre_completo']
        );

        $mail->Subject = sprintf(
            '[Portafolio #%d] %s',
            $idSolicitud,
            (string) $datos['asunto']
        );

        $mail->Body = $this->renderizar(
            'contacto-admin.php',
            [
                'datos' => $datos,
                'idSolicitud' => $idSolicitud,
                'servicio' => $servicio,
                'panelUrl' => BASE_URL . 'admin/mensajes',
            ]
        );

        $mail->AltBody = $this->textoAdministrador(
            $datos,
            $idSolicitud,
            $servicio
        );

        return $mail->send();
    }

    private function enviarConfirmacion(
        array $datos,
        int $idSolicitud,
        ?array $servicio
    ): bool {
        $mail = $this->crearMailer();
        $this->incrustarIconos($mail);

        $mail->addAddress(
            (string) $datos['correo'],
            (string) $datos['nombre_completo']
        );

        $mail->addReplyTo(
            $this->adminEmail(),
            $this->adminNombre()
        );

        $mail->Subject = sprintf(
            'Recibí tu solicitud #%d | %s',
            $idSolicitud,
            $this->remitenteNombre()
        );

        $mail->Body = $this->renderizar(
            'contacto-confirmacion.php',
            [
                'datos' => $datos,
                'idSolicitud' => $idSolicitud,
                'servicio' => $servicio,
                'sitioUrl' => BASE_URL,
                'correoRespuesta' => $this->adminEmail(),
            ]
        );

        $mail->AltBody = $this->textoConfirmacion(
            $datos,
            $idSolicitud,
            $servicio
        );

        return $mail->send();
    }

    private function crearMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->Encoding = PHPMailer::ENCODING_BASE64;
        $mail->isSMTP();

        $mail->Host = HOST_SMTP;
        $mail->SMTPAuth = true;
        $mail->Username = USER_SMTP;
        $mail->Password = PASS_SMTP;
        $mail->Port = (int) PUERTO_SMTP;
        $mail->Timeout = defined('SMTP_TIMEOUT')
            ? max(5, (int) SMTP_TIMEOUT)
            : 20;

        $seguridad = defined('SMTP_ENCRYPTION')
            ? strtolower((string) SMTP_ENCRYPTION)
            : ((int) PUERTO_SMTP === 465 ? 'smtps' : 'starttls');

        $mail->SMTPSecure = $seguridad === 'starttls'
            ? PHPMailer::ENCRYPTION_STARTTLS
            : PHPMailer::ENCRYPTION_SMTPS;

        $mail->SMTPDebug = $this->debugHabilitado()
            ? SMTP::DEBUG_SERVER
            : SMTP::DEBUG_OFF;

        /* Evita que la depuración SMTP rompa la respuesta JSON. */
        $mail->Debugoutput = static function (
            string $mensaje,
            int $nivel
        ): void {
            error_log(
                sprintf('[SMTP:%d] %s', $nivel, $mensaje)
            );
        };

        $mail->setFrom(
            $this->remitenteEmail(),
            $this->remitenteNombre()
        );

        $mail->isHTML(true);

        return $mail;
    }

    private function incrustarIconos(PHPMailer $mail): void
    {
        $directorio = $this->root . '/Assets/email/icons';

        $iconos = [
            'fa-bolt' => 'bolt.png',
            'fa-envelope' => 'envelope.png',
            'fa-circle-check' => 'circle-check.png',
            'fa-calendar-days' => 'calendar-days.png',
            'fa-paper-plane' => 'paper-plane.png',
        ];

        foreach ($iconos as $cid => $archivo) {
            $ruta = $directorio . '/' . $archivo;

            if (!is_file($ruta)) {
                throw new RuntimeException(
                    'No se encontró el icono de correo: ' . $archivo
                );
            }

            $mail->addEmbeddedImage(
                $ruta,
                $cid,
                $archivo,
                PHPMailer::ENCODING_BASE64,
                'image/png'
            );
        }
    }

    private function renderizar(
        string $plantilla,
        array $variables
    ): string {
        $ruta = $this->root
            . '/Views/Emails/'
            . basename($plantilla);

        if (!is_file($ruta)) {
            throw new RuntimeException(
                'No se encontró la plantilla de correo: '
                . $plantilla
            );
        }

        extract($variables, EXTR_SKIP);

        ob_start();
        require $ruta;
        $contenido = ob_get_clean();

        if ($contenido === false) {
            throw new RuntimeException(
                'No fue posible renderizar la plantilla de correo.'
            );
        }

        return $contenido;
    }

    private function textoAdministrador(
        array $datos,
        int $idSolicitud,
        ?array $servicio
    ): string {
        return implode("\n", [
            'Nueva solicitud desde el portafolio',
            'Referencia: #' . $idSolicitud,
            'Nombre: ' . $datos['nombre_completo'],
            'Correo: ' . $datos['correo'],
            'Teléfono: ' . ($datos['telefono'] ?? 'No proporcionado'),
            'Empresa: ' . ($datos['nombre_empresa'] ?? 'No proporcionada'),
            'Servicio: ' . ($servicio['nombre'] ?? 'No especificado'),
            'Fecha preferida: ' . ($datos['fecha_preferida'] ?? 'No especificada'),
            'Modalidad: ' . ($datos['modalidad_preferida'] ?? 'No especificada'),
            'Asunto: ' . $datos['asunto'],
            '',
            'Mensaje:',
            $datos['mensaje'],
            '',
            'Abrir bandeja: ' . BASE_URL . 'admin/mensajes',
        ]);
    }

    private function textoConfirmacion(
        array $datos,
        int $idSolicitud,
        ?array $servicio
    ): string {
        return implode("\n", [
            'Hola ' . $datos['nombre_completo'] . ',',
            '',
            'Recibí correctamente tu solicitud.',
            'Referencia: #' . $idSolicitud,
            'Servicio: ' . ($servicio['nombre'] ?? 'No especificado'),
            'Asunto: ' . $datos['asunto'],
            '',
            'Revisaré la información y responderé al correo que proporcionaste.',
            '',
            'Tu mensaje:',
            $datos['mensaje'],
            '',
            $this->remitenteNombre(),
            $this->adminEmail(),
        ]);
    }

    private function validarConfiguracion(): void
    {
        foreach ([
            'HOST_SMTP',
            'USER_SMTP',
            'PASS_SMTP',
            'PUERTO_SMTP',
        ] as $constante) {
            if (
                !defined($constante)
                || trim((string) constant($constante)) === ''
            ) {
                throw new RuntimeException(
                    'Falta configurar ' . $constante . '.'
                );
            }
        }

        if (
            !filter_var(
                $this->remitenteEmail(),
                FILTER_VALIDATE_EMAIL
            )
            || !filter_var(
                $this->adminEmail(),
                FILTER_VALIDATE_EMAIL
            )
        ) {
            throw new RuntimeException(
                'MAIL_FROM_EMAIL o MAIL_ADMIN_EMAIL no es válido.'
            );
        }
    }

    private function remitenteEmail(): string
    {
        return defined('MAIL_FROM_EMAIL')
            ? (string) MAIL_FROM_EMAIL
            : (string) USER_SMTP;
    }

    private function remitenteNombre(): string
    {
        return defined('MAIL_FROM_NAME')
            ? (string) MAIL_FROM_NAME
            : 'Oscar Arzate | Portafolio';
    }

    private function adminEmail(): string
    {
        return defined('MAIL_ADMIN_EMAIL')
            ? (string) MAIL_ADMIN_EMAIL
            : (string) USER_SMTP;
    }

    private function adminNombre(): string
    {
        return defined('MAIL_ADMIN_NAME')
            ? (string) MAIL_ADMIN_NAME
            : 'Oscar Arzate';
    }

    private function autoRespuestaHabilitada(): bool
    {
        return !defined('MAIL_AUTO_REPLY_ENABLED')
            || MAIL_AUTO_REPLY_ENABLED === true;
    }

    private function debugHabilitado(): bool
    {
        return defined('SMTP_DEBUG')
            && SMTP_DEBUG === true;
    }

    private function registrarError(
        string $operacion,
        Throwable $e
    ): void {
        error_log(sprintf(
            '[CorreoContacto] Error al enviar %s: %s',
            $operacion,
            $e->getMessage()
        ));
    }
}
