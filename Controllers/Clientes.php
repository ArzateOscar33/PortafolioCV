<?php

class Clientes extends Controller
{
    private const TIPOS = [
        'empresa',
        'persona',
        'organizacion',
    ];

    public function __construct()
    {
        SessionManager::start();
        parent::__construct();

        if (
            empty($_SESSION['autenticado'])
            || empty($_SESSION['usuario']['id_usuario'])
        ) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->json([
                    'status' => false,
                    'msg' => 'La sesión ha expirado.',
                    'icono' => 'warning',
                ], 401);
            }

            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $_SESSION['ultima_actividad'] = time();

        if (empty($_SESSION['admin_csrf_token'])) {
            $_SESSION['admin_csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }
    }

    public function index()
    {
        header('Location: ' . BASE_URL . 'admin/clientes');
        exit;
    }

    public function listar()
    {
        $busqueda = trim($_GET['busqueda'] ?? '');
        $tipo = $_GET['tipo'] ?? 'all';
        $estado = $_GET['estado'] ?? 'all';
        $registro = $_GET['registro'] ?? 'active';

        if (
            $tipo !== 'all'
            && !in_array($tipo, self::TIPOS, true)
        ) {
            $tipo = 'all';
        }

        if (!in_array($estado, ['all', 'active', 'inactive'], true)) {
            $estado = 'all';
        }

        if (!in_array($registro, ['active', 'deleted', 'all'], true)) {
            $registro = 'active';
        }

        $clientes = $this->model->listar(
            $busqueda,
            $tipo,
            $estado,
            $registro
        );

        $resumen = $this->model->estadisticas();

        $this->json([
            'status' => true,
            'clientes' => is_array($clientes) ? $clientes : [],
            'resumen' => [
                'total' => (int) ($resumen['total'] ?? 0),
                'activos' => (int) ($resumen['activos'] ?? 0),
                'inactivos' => (int) ($resumen['inactivos'] ?? 0),
                'bajas' => (int) ($resumen['bajas'] ?? 0),
            ],
        ]);
    }

    public function obtener($idCliente)
    {
        $idCliente = (int) $idCliente;

        if ($idCliente <= 0) {
            $this->json([
                'status' => false,
                'msg' => 'El identificador no es válido.',
                'icono' => 'error',
            ], 400);
        }

        $cliente = $this->model->obtener($idCliente);

        if (empty($cliente)) {
            $this->json([
                'status' => false,
                'msg' => 'El cliente no existe.',
                'icono' => 'error',
            ], 404);
        }

        $this->json($cliente);
    }

    public function guardar()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idCliente = max(0, (int) ($entrada['id_cliente'] ?? 0));

        if (
            $idCliente > 0
            && empty($this->model->obtener($idCliente))
        ) {
            $this->json([
                'status' => false,
                'msg' => 'El cliente no existe.',
                'icono' => 'error',
            ], 404);
        }

        $datos = [
            'tipo_cliente' => strtolower(
                trim($entrada['tipo_cliente'] ?? 'empresa')
            ),
            'nombre_mostrar' => trim(
                $entrada['nombre_mostrar'] ?? ''
            ),
            'razon_social' => trim(
                $entrada['razon_social'] ?? ''
            ),
            'correo' => strtolower(
                trim($entrada['correo'] ?? '')
            ),
            'telefono' => trim(
                $entrada['telefono'] ?? ''
            ),
            'sitio_web' => trim(
                $entrada['sitio_web'] ?? ''
            ),
            'ruta_logotipo' => trim(
                $entrada['ruta_logotipo'] ?? ''
            ),
            'notas' => trim(
                $entrada['notas'] ?? ''
            ),
            'activo' => filter_var(
                $entrada['activo'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) ? 1 : 0,
        ];

        $error = $this->validarDatos($datos);

        if ($error !== '') {
            $this->json([
                'status' => false,
                'msg' => $error,
                'icono' => 'warning',
            ], 422);
        }

        foreach ([
            'razon_social',
            'correo',
            'telefono',
            'sitio_web',
            'ruta_logotipo',
            'notas',
        ] as $campo) {
            $datos[$campo] = $this->nullable($datos[$campo]);
        }

        if ($idCliente === 0) {
            $resultado = $this->model->registrar($datos);
            $correcto = (int) $resultado > 0;
            $idGuardado = (int) $resultado;
            $mensaje = 'Cliente registrado correctamente.';
        } else {
            $correcto = $this->model->modificar(
                $datos,
                $idCliente
            );
            $idGuardado = $idCliente;
            $mensaje = 'Cliente modificado correctamente.';
        }

        if (!$correcto) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible guardar el cliente.',
                'icono' => 'error',
            ], 500);
        }

        $this->json([
            'status' => true,
            'msg' => $mensaje,
            'icono' => 'success',
            'id_cliente' => $idGuardado,
        ]);
    }

    public function cambiarEstado()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idCliente = (int) ($entrada['id_cliente'] ?? 0);
        $activo = filter_var(
            $entrada['activo'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        $cliente = $this->model->obtener($idCliente);

        if (empty($cliente)) {
            $this->json([
                'status' => false,
                'msg' => 'El cliente no existe.',
                'icono' => 'error',
            ], 404);
        }

        if (!empty($cliente['eliminado_en'])) {
            $this->json([
                'status' => false,
                'msg' => 'Restaura el cliente antes de cambiar su estado.',
                'icono' => 'warning',
            ], 409);
        }

        if (!$this->model->cambiarEstado($idCliente, $activo)) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible cambiar el estado.',
                'icono' => 'error',
            ], 500);
        }

        $this->json([
            'status' => true,
            'msg' => $activo
                ? 'Cliente activado correctamente.'
                : 'Cliente desactivado correctamente.',
            'icono' => 'success',
        ]);
    }

    public function cambiarBaja()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idCliente = (int) ($entrada['id_cliente'] ?? 0);
        $accion = $entrada['accion'] ?? '';
        $cliente = $this->model->obtener($idCliente);

        if (empty($cliente)) {
            $this->json([
                'status' => false,
                'msg' => 'El cliente no existe.',
                'icono' => 'error',
            ], 404);
        }

        if ($accion === 'baja') {
            if (!empty($cliente['eliminado_en'])) {
                $this->json([
                    'status' => false,
                    'msg' => 'El cliente ya fue dado de baja.',
                    'icono' => 'warning',
                ], 409);
            }

            $correcto = $this->model->darBaja($idCliente);
            $mensaje = 'Cliente dado de baja correctamente.';
        } elseif ($accion === 'restaurar') {
            if (empty($cliente['eliminado_en'])) {
                $this->json([
                    'status' => false,
                    'msg' => 'El cliente no está dado de baja.',
                    'icono' => 'warning',
                ], 409);
            }

            $correcto = $this->model->restaurar($idCliente);
            $mensaje = 'Cliente restaurado correctamente.';
        } else {
            $this->json([
                'status' => false,
                'msg' => 'La acción solicitada no es válida.',
                'icono' => 'warning',
            ], 422);
        }

        if (!$correcto) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible actualizar el cliente.',
                'icono' => 'error',
            ], 500);
        }

        $this->json([
            'status' => true,
            'msg' => $mensaje,
            'icono' => 'success',
        ]);
    }

    private function validarDatos(array $datos)
    {
        if (!in_array($datos['tipo_cliente'], self::TIPOS, true)) {
            return 'Selecciona un tipo de cliente válido.';
        }

        if ($datos['nombre_mostrar'] === '') {
            return 'El nombre del cliente es obligatorio.';
        }

        if (strlen($datos['nombre_mostrar']) > 150) {
            return 'El nombre no puede superar los 150 caracteres.';
        }

        if (strlen($datos['razon_social']) > 180) {
            return 'La razón social no puede superar los 180 caracteres.';
        }

        if (strlen($datos['correo']) > 150) {
            return 'El correo no puede superar los 150 caracteres.';
        }

        if (
            $datos['correo'] !== ''
            && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)
        ) {
            return 'El correo electrónico no es válido.';
        }

        if (strlen($datos['telefono']) > 30) {
            return 'El teléfono no puede superar los 30 caracteres.';
        }

        if (strlen($datos['sitio_web']) > 500) {
            return 'El sitio web no puede superar los 500 caracteres.';
        }

        if ($datos['sitio_web'] !== '') {
            if (!filter_var($datos['sitio_web'], FILTER_VALIDATE_URL)) {
                return 'El sitio web no es una URL válida.';
            }

            $esquema = strtolower((string) parse_url(
                $datos['sitio_web'],
                PHP_URL_SCHEME
            ));

            if (!in_array($esquema, ['http', 'https'], true)) {
                return 'El sitio web debe utilizar http o https.';
            }
        }

        if (strlen($datos['ruta_logotipo']) > 255) {
            return 'La ruta del logotipo no puede superar los 255 caracteres.';
        }

        if (str_contains($datos['ruta_logotipo'], '..')) {
            return 'La ruta del logotipo contiene segmentos no permitidos.';
        }

        if (strlen($datos['notas']) > 5000) {
            return 'Las notas no pueden superar los 5000 caracteres.';
        }

        return '';
    }

    private function entrada()
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contenido = file_get_contents('php://input');

        if ($contenido === false || trim($contenido) === '') {
            return [];
        }

        $datos = json_decode($contenido, true);

        return is_array($datos) ? $datos : [];
    }

    private function validarCsrf($token)
    {
        $tokenSesion = $_SESSION['admin_csrf_token'] ?? '';

        if (
            $token === ''
            || $tokenSesion === ''
            || !hash_equals($tokenSesion, $token)
        ) {
            $this->json([
                'status' => false,
                'msg' => 'La solicitud no es válida. Actualiza la página.',
                'icono' => 'warning',
            ], 419);
        }
    }

    private function nullable($valor)
    {
        return $valor === '' ? null : $valor;
    }

    private function json($respuesta, $codigoHttp = 200)
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        die();
    }
}
