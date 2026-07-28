<?php

class Contacto extends Controller
{
    private const SERVICIOS_COMPATIBLES = [
        'web' => 'sistemas-web',
        'desktop' => 'aplicaciones-escritorio',
        'mobile' => 'aplicaciones-moviles',
        'infrastructure' => 'infraestructura-redes',
        'security' => 'ciberseguridad',
        'consulting' => 'consultoria-tecnica',
    ];

    private const MODALIDADES = [
        'online' => 'en_linea',
        'onsite' => 'presencial',
        'either' => 'cualquiera',
        'en_linea' => 'en_linea',
        'presencial' => 'presencial',
        'cualquiera' => 'cualquiera',
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        header('Location: ' . BASE_URL . '#contacto');
        exit;
    }

    public function guardar()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->json([
                'status' => false,
                'msg' => 'Método no permitido.',
            ], 405);
        }

        $entrada = $this->entrada();

        /*
         * Campo trampa. A los visitantes reales se les oculta.
         * Los bots suelen llenarlo automáticamente.
         */
        if (trim($entrada['website'] ?? '') !== '') {
            $this->respuestaGenerica();
        }

        $inicioFormulario = (int) (
            $entrada['form_started_at'] ?? 0
        );

        /*
         * Un envío realizado en menos de dos segundos suele
         * ser automatizado. Se responde como éxito para no
         * revelar la protección al bot.
         */
        if (
            $inicioFormulario > 0
            && (time() - $inicioFormulario) < 2
        ) {
            $this->respuestaGenerica();
        }

        $datos = [
            'nombre_completo' => trim(
                $entrada['nombre_completo']
                    ?? $entrada['name']
                    ?? ''
            ),
            'correo' => strtolower(trim(
                $entrada['correo']
                    ?? $entrada['email']
                    ?? ''
            )),
            'telefono' => trim(
                $entrada['telefono']
                    ?? $entrada['phone']
                    ?? ''
            ),
            'nombre_empresa' => trim(
                $entrada['nombre_empresa']
                    ?? $entrada['company']
                    ?? ''
            ),
            'asunto' => trim(
                $entrada['asunto']
                    ?? $entrada['subject']
                    ?? ''
            ),
            'mensaje' => trim(
                $entrada['mensaje']
                    ?? $entrada['message']
                    ?? ''
            ),
            'fecha_preferida' => trim(
                $entrada['fecha_preferida']
                    ?? $entrada['meetingDate']
                    ?? ''
            ),
            'modalidad_preferida' => strtolower(trim(
                $entrada['modalidad_preferida']
                    ?? $entrada['meetingMode']
                    ?? ''
            )),
        ];

        $servicioEntrada = trim(
            $entrada['id_servicio']
                ?? $entrada['servicio']
                ?? $entrada['service']
                ?? ''
        );

        $servicio = $this->resolverServicio(
            $servicioEntrada
        );

        if ($servicioEntrada !== '' && empty($servicio)) {
            $this->json([
                'status' => false,
                'msg' => 'Selecciona un servicio válido.',
            ], 422);
        }

        $datos['id_servicio'] = !empty(
            $servicio['id_servicio']
        )
            ? (int) $servicio['id_servicio']
            : null;

        if ($datos['asunto'] === '') {
            $datos['asunto'] = !empty($servicio['nombre'])
                ? 'Solicitud sobre ' . $servicio['nombre']
                : 'Nueva solicitud desde el portafolio';
        }

        $datos['modalidad_preferida'] =
            self::MODALIDADES[$datos['modalidad_preferida']]
            ?? '';

        $error = $this->validarDatos($datos);

        if ($error !== '') {
            $this->json([
                'status' => false,
                'msg' => $error,
            ], 422);
        }

        foreach ([
            'telefono',
            'nombre_empresa',
            'fecha_preferida',
            'modalidad_preferida',
        ] as $campo) {
            $datos[$campo] = $this->nullable(
                $datos[$campo]
            );
        }

        $direccionIp = $this->direccionIp();

        if (
            $direccionIp !== ''
            && $this->model->enviosRecientes(
                $direccionIp,
                10
            ) >= 5
        ) {
            $this->json([
                'status' => false,
                'msg' => 'Se alcanzó el límite temporal de solicitudes. Intenta nuevamente más tarde.',
            ], 429);
        }

        if ($this->model->esDuplicadoReciente(
            $datos['correo'],
            $datos['mensaje'],
            2
        )) {
            $this->respuestaGenerica();
        }

        $contacto = $this->model->buscarContactoPorCorreo(
            $datos['correo']
        );

        $datos['id_contacto_cliente'] = !empty(
            $contacto['id_contacto_cliente']
        )
            ? (int) $contacto['id_contacto_cliente']
            : null;

        $datos['estado'] = 'nueva';
        $datos['origen'] = 'formulario_web';
        $datos['direccion_ip'] = $direccionIp !== ''
            ? $direccionIp
            : null;

        $idSolicitud = (int) $this->model->registrar(
            $datos
        );

        if ($idSolicitud <= 0) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible registrar la solicitud. Intenta nuevamente.',
            ], 500);
        }

        /*
         * El registro en base de datos ya fue confirmado.
         * Los fallos SMTP se registran en error_log y no eliminan
         * la solicitud ni muestran información sensible al visitante.
         */
        $resultadoCorreo = [
            'administrador' => false,
            'confirmacion' => false,
        ];

        try {
            $correo = new CorreoContacto();
            $resultadoCorreo = $correo->enviarSolicitud(
                $datos,
                $idSolicitud,
                $servicio
            );
        } catch (Throwable $e) {
            error_log(
                '[Contacto] No fue posible iniciar el servicio de correo: '
                . $e->getMessage()
            );
        }

        $confirmacionEnviada = !empty(
            $resultadoCorreo['confirmacion']
        );

        $this->json([
            'status' => true,
            'msg' => $confirmacionEnviada
                ? 'Tu solicitud fue registrada correctamente. También envié una confirmación a tu correo.'
                : 'Tu solicitud fue registrada correctamente. Me pondré en contacto contigo.',
            'id_solicitud_contacto' => $idSolicitud,
            'correo_confirmacion_enviado' => $confirmacionEnviada,
        ], 201);
    }

    private function resolverServicio($valor)
    {
        if ($valor === '') {
            return null;
        }

        if (ctype_digit((string) $valor)) {
            return $this->model->servicioPorId(
                (int) $valor
            );
        }

        $slug = strtolower(trim($valor));
        $slug = self::SERVICIOS_COMPATIBLES[$slug]
            ?? $slug;

        return $this->model->servicioPorSlug($slug);
    }

    private function validarDatos(array $datos)
    {
        if ($datos['nombre_completo'] === '') {
            return 'Escribe tu nombre.';
        }

        if ($this->longitud($datos['nombre_completo']) > 150) {
            return 'El nombre no puede superar los 150 caracteres.';
        }

        if (
            $datos['correo'] === ''
            || !filter_var(
                $datos['correo'],
                FILTER_VALIDATE_EMAIL
            )
        ) {
            return 'Escribe un correo electrónico válido.';
        }

        if ($this->longitud($datos['correo']) > 150) {
            return 'El correo no puede superar los 150 caracteres.';
        }

        if ($this->longitud($datos['telefono']) > 30) {
            return 'El teléfono no puede superar los 30 caracteres.';
        }

        if ($this->longitud($datos['nombre_empresa']) > 150) {
            return 'El nombre de la empresa es demasiado largo.';
        }

        if (
            $datos['asunto'] === ''
            || $this->longitud($datos['asunto']) > 180
        ) {
            return 'El asunto debe contener entre 1 y 180 caracteres.';
        }

        $longitudMensaje = $this->longitud($datos['mensaje']);

        if ($longitudMensaje < 20) {
            return 'Describe tu solicitud con al menos 20 caracteres.';
        }

        if ($longitudMensaje > 10000) {
            return 'El mensaje no puede superar los 10,000 caracteres.';
        }

        if ($datos['fecha_preferida'] !== '') {
            $fecha = DateTimeImmutable::createFromFormat(
                'Y-m-d',
                $datos['fecha_preferida']
            );

            if (
                $fecha === false
                || $fecha->format('Y-m-d')
                    !== $datos['fecha_preferida']
            ) {
                return 'La fecha preferida no es válida.';
            }

            $hoy = new DateTimeImmutable('today');
            $limite = $hoy->modify('+1 year');

            if ($fecha < $hoy) {
                return 'La fecha preferida no puede estar en el pasado.';
            }

            if ($fecha > $limite) {
                return 'La fecha preferida no puede superar un año.';
            }
        }

        return '';
    }


    private function longitud($valor)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($valor, 'UTF-8');
        }

        return strlen($valor);
    }

    private function direccionIp()
    {
        $direccion = trim(
            $_SERVER['REMOTE_ADDR'] ?? ''
        );

        return substr($direccion, 0, 45);
    }

    private function nullable($valor)
    {
        return $valor !== '' ? $valor : null;
    }

    private function entrada()
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contenido = file_get_contents('php://input');

        if (
            $contenido === false
            || trim($contenido) === ''
        ) {
            return [];
        }

        $datos = json_decode($contenido, true);

        return is_array($datos) ? $datos : [];
    }

    private function respuestaGenerica()
    {
        $this->json([
            'status' => true,
            'msg' => 'Tu solicitud fue registrada correctamente. Me pondré en contacto contigo.',
        ], 201);
    }

    private function json($respuesta, $codigo = 200)
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
        );

        exit;
    }
}
