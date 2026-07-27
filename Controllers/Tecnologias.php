<?php

class Tecnologias extends Controller
{
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
            $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function index()
    {
        header('Location: ' . BASE_URL . 'admin/tecnologias');
        exit;
    }

    public function listar()
    {
        $busqueda = trim($_GET['busqueda'] ?? '');
        $filtro = $_GET['estado'] ?? 'all';
        $estado = null;

        if ($filtro === 'active') {
            $estado = 1;
        } elseif ($filtro === 'inactive') {
            $estado = 0;
        }

        $tecnologias = $this->model->listar($busqueda, $estado);
        $resumen = $this->model->estadisticas();

        $this->json([
            'status' => true,
            'tecnologias' => is_array($tecnologias) ? $tecnologias : [],
            'resumen' => [
                'total' => (int) ($resumen['total'] ?? 0),
                'activas' => (int) ($resumen['activas'] ?? 0),
                'inactivas' => (int) ($resumen['inactivas'] ?? 0),
            ],
        ]);
    }

    public function obtener($idTecnologia)
    {
        $idTecnologia = (int) $idTecnologia;

        if ($idTecnologia <= 0) {
            $this->json([
                'status' => false,
                'msg' => 'El identificador no es válido.',
                'icono' => 'error',
            ], 400);
        }

        $tecnologia = $this->model->obtener($idTecnologia);

        if (empty($tecnologia)) {
            $this->json([
                'status' => false,
                'msg' => 'La tecnología no existe.',
                'icono' => 'error',
            ], 404);
        }

        $this->json($tecnologia);
    }

    public function guardar()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idTecnologia = (int) ($entrada['id_tecnologia'] ?? 0);
        $orden = $entrada['orden_visualizacion'] ?? '';

        $datos = [
            'nombre' => trim($entrada['nombre'] ?? ''),
            'slug' => strtolower(trim($entrada['slug'] ?? '')),
            'color_hexadecimal' => strtoupper(trim(
                $entrada['color_hexadecimal'] ?? ''
            )),
            'tipo_icono' => trim($entrada['tipo_icono'] ?? 'fontawesome'),
            'valor_icono' => trim($entrada['valor_icono'] ?? ''),
            'nivel_o_area' => trim($entrada['nivel_o_area'] ?? ''),
            'orden_visualizacion' => 0,
            'activa' => filter_var(
                $entrada['activa'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) ? 1 : 0,
        ];

        $error = $this->validarDatos($datos, $orden);

        if ($error !== '') {
            $this->json([
                'status' => false,
                'msg' => $error,
                'icono' => 'warning',
            ], 422);
        }

        if ($orden === '' || $orden === null) {
            $siguiente = $this->model->siguienteOrden();
            $datos['orden_visualizacion'] = (int) (
                $siguiente['siguiente_orden'] ?? 1
            );
        } else {
            $datos['orden_visualizacion'] = max(0, (int) $orden);
        }

        $datos['nivel_o_area'] = $datos['nivel_o_area'] !== ''
            ? $datos['nivel_o_area']
            : null;

        if ($idTecnologia > 0 && empty($this->model->obtener($idTecnologia))) {
            $this->json([
                'status' => false,
                'msg' => 'La tecnología no existe.',
                'icono' => 'error',
            ], 404);
        }

        if ($this->model->existe('nombre', $datos['nombre'], $idTecnologia)) {
            $this->json([
                'status' => false,
                'msg' => 'Ya existe una tecnología con ese nombre.',
                'icono' => 'warning',
            ], 409);
        }

        if ($this->model->existe('slug', $datos['slug'], $idTecnologia)) {
            $this->json([
                'status' => false,
                'msg' => 'Ya existe una tecnología con ese slug.',
                'icono' => 'warning',
            ], 409);
        }

        if ($idTecnologia === 0) {
            $resultado = $this->model->registrar($datos);
            $correcto = $resultado > 0;
            $mensaje = 'Tecnología registrada correctamente.';
        } else {
            $correcto = $this->model->modificar($datos, $idTecnologia);
            $mensaje = 'Tecnología modificada correctamente.';
        }

        $this->json([
            'status' => $correcto,
            'msg' => $correcto
                ? $mensaje
                : 'No fue posible guardar la tecnología.',
            'icono' => $correcto ? 'success' : 'error',
        ], $correcto ? 200 : 500);
    }

    public function cambiarEstado()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idTecnologia = (int) ($entrada['id_tecnologia'] ?? 0);
        $estado = filter_var(
            $entrada['activa'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        if ($idTecnologia <= 0 || empty($this->model->obtener($idTecnologia))) {
            $this->json([
                'status' => false,
                'msg' => 'Selecciona una tecnología válida.',
                'icono' => 'error',
            ], 404);
        }

        $correcto = $this->model->cambiarEstado($idTecnologia, $estado);

        $this->json([
            'status' => $correcto,
            'msg' => $correcto
                ? ($estado
                    ? 'Tecnología activada correctamente.'
                    : 'Tecnología dada de baja correctamente.')
                : 'No fue posible cambiar el estado.',
            'icono' => $correcto ? 'success' : 'error',
        ], $correcto ? 200 : 500);
    }

    private function validarDatos(array $datos, $orden)
    {
        if ($datos['nombre'] === '' || $datos['slug'] === '') {
            return 'El nombre y el slug son obligatorios.';
        }

        if (strlen($datos['nombre']) > 100 || strlen($datos['slug']) > 120) {
            return 'El nombre o el slug superan la longitud permitida.';
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $datos['slug'])) {
            return 'El slug solo puede contener minúsculas, números y guiones.';
        }

        if (!preg_match('/^#[0-9A-F]{6}$/', $datos['color_hexadecimal'])) {
            return 'El color hexadecimal debe usar el formato #RRGGBB.';
        }

        if (!in_array($datos['tipo_icono'], ['fontawesome', 'imagen'], true)) {
            return 'El tipo de icono no es válido.';
        }

        if ($datos['valor_icono'] === '' || strlen($datos['valor_icono']) > 500) {
            return 'Indica una clase de icono o una ruta de imagen válida.';
        }

        if (strlen($datos['nivel_o_area']) > 80) {
            return 'El área no puede superar los 80 caracteres.';
        }

        if ($orden !== '' && $orden !== null && !is_numeric($orden)) {
            return 'El orden de visualización debe ser numérico.';
        }

        return '';
    }

    private function entrada()
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contenido = file_get_contents('php://input');
        $datos = json_decode($contenido ?: '', true);

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

    private function json($respuesta, $codigoHttp = 200)
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        die();
    }
}
