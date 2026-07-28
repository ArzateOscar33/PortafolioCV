<?php

class Proyectos extends Controller
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
        header('Location: ' . BASE_URL . 'admin/proyectos');
        exit;
    }

    public function catalogos()
    {
        $catalogos = $this->model->catalogos();

        $this->json([
            'status' => true,
            'estados' => is_array($catalogos['estados'] ?? null)
                ? $catalogos['estados']
                : [],
            'categorias' => is_array($catalogos['categorias'] ?? null)
                ? $catalogos['categorias']
                : [],
            'tecnologias' => is_array($catalogos['tecnologias'] ?? null)
                ? $catalogos['tecnologias']
                : [],
            'clientes' => is_array($catalogos['clientes'] ?? null)
                ? $catalogos['clientes']
                : [],
        ]);
    }

    public function listar()
    {
        $busqueda = trim($_GET['busqueda'] ?? '');
        $idEstado = max(0, (int) ($_GET['estado'] ?? 0));
        $registro = $_GET['registro'] ?? 'active';

        if (!in_array($registro, ['active', 'deleted', 'all'], true)) {
            $registro = 'active';
        }

        $proyectos = $this->model->listar(
            $busqueda,
            $idEstado,
            $registro
        );

        $resumen = $this->model->estadisticas();

        $this->json([
            'status' => true,
            'proyectos' => is_array($proyectos) ? $proyectos : [],
            'resumen' => [
                'total' => (int) ($resumen['total'] ?? 0),
                'activos' => (int) ($resumen['activos'] ?? 0),
                'publicados' => (int) ($resumen['publicados'] ?? 0),
                'bajas' => (int) ($resumen['bajas'] ?? 0),
            ],
        ]);
    }

    public function obtener($idProyecto)
    {
        $idProyecto = (int) $idProyecto;

        if ($idProyecto <= 0) {
            $this->json([
                'status' => false,
                'msg' => 'El identificador no es válido.',
                'icono' => 'error',
            ], 400);
        }

        $proyecto = $this->model->obtener($idProyecto);

        if (empty($proyecto)) {
            $this->json([
                'status' => false,
                'msg' => 'El proyecto no existe.',
                'icono' => 'error',
            ], 404);
        }

        $this->json($proyecto);
    }

    public function guardar()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idProyecto = max(0, (int) ($entrada['id_proyecto'] ?? 0));
        $existente = $idProyecto > 0
            ? $this->model->obtener($idProyecto)
            : null;

        if ($idProyecto > 0 && empty($existente)) {
            $this->json([
                'status' => false,
                'msg' => 'El proyecto no existe.',
                'icono' => 'error',
            ], 404);
        }

        $orden = $entrada['orden_visualizacion'] ?? '';
        $categorias = $this->ids($entrada['categorias'] ?? []);
        $tecnologias = $this->ids($entrada['tecnologias'] ?? []);

        $datos = [
            'id_estado_proyecto' => (int) (
                $entrada['id_estado_proyecto'] ?? 0
            ),
            'id_cliente' => (int) ($entrada['id_cliente'] ?? 0),
            'creado_por' => (int) $_SESSION['usuario']['id_usuario'],
            'slug' => strtolower(trim($entrada['slug'] ?? '')),
            'titulo' => trim($entrada['titulo'] ?? ''),
            'resumen_corto' => trim($entrada['resumen_corto'] ?? ''),
            'descripcion' => trim($entrada['descripcion'] ?? ''),
            'reto_tecnico' => trim($entrada['reto_tecnico'] ?? ''),
            'resultado' => trim($entrada['resultado'] ?? ''),
            'url_github' => trim($entrada['url_github'] ?? ''),
            'github_privado' => filter_var(
                $entrada['github_privado'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) ? 1 : 0,
            'fecha_inicio' => trim($entrada['fecha_inicio'] ?? ''),
            'fecha_fin' => trim($entrada['fecha_fin'] ?? ''),
            'destacado' => filter_var(
                $entrada['destacado'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) ? 1 : 0,
            'orden_visualizacion' => 0,
            'publicado_en' => $existente['publicado_en'] ?? null,
        ];

        $error = $this->validarDatos(
            $datos,
            $categorias,
            $tecnologias,
            $orden
        );

        if ($error !== '') {
            $this->json([
                'status' => false,
                'msg' => $error,
                'icono' => 'warning',
            ], 422);
        }

        $estado = $this->model->obtenerEstado(
            $datos['id_estado_proyecto']
        );

        if (empty($estado)) {
            $this->json([
                'status' => false,
                'msg' => 'El estado seleccionado no existe.',
                'icono' => 'warning',
            ], 422);
        }

        if (
            $datos['id_cliente'] > 0
            && empty($this->model->clienteValido($datos['id_cliente']))
        ) {
            $this->json([
                'status' => false,
                'msg' => 'El cliente seleccionado no está disponible.',
                'icono' => 'warning',
            ], 422);
        }

        if (!$this->model->relacionesValidas('categorias', $categorias)) {
            $this->json([
                'status' => false,
                'msg' => 'Una de las categorías seleccionadas no existe.',
                'icono' => 'warning',
            ], 422);
        }

        if (!$this->model->relacionesValidas('tecnologias', $tecnologias)) {
            $this->json([
                'status' => false,
                'msg' => 'Una de las tecnologías seleccionadas no existe.',
                'icono' => 'warning',
            ], 422);
        }

        if ($this->model->existeSlug($datos['slug'], $idProyecto)) {
            $this->json([
                'status' => false,
                'msg' => 'Ya existe un proyecto con ese slug.',
                'icono' => 'warning',
            ], 409);
        }

        if ($orden === '' || $orden === null) {
            $siguiente = $this->model->siguienteOrden();
            $datos['orden_visualizacion'] = (int) (
                $siguiente['siguiente_orden'] ?? 1
            );
        } else {
            $datos['orden_visualizacion'] = max(0, (int) $orden);
        }

        $datos['id_cliente'] = $datos['id_cliente'] > 0
            ? $datos['id_cliente']
            : null;
        $datos['reto_tecnico'] = $this->nullable($datos['reto_tecnico']);
        $datos['resultado'] = $this->nullable($datos['resultado']);
        $datos['url_github'] = $this->nullable($datos['url_github']);
        $datos['fecha_inicio'] = $this->nullable($datos['fecha_inicio']);
        $datos['fecha_fin'] = $this->nullable($datos['fecha_fin']);

        if (
            ($estado['slug'] ?? '') === 'publicado'
            && empty($datos['publicado_en'])
        ) {
            $datos['publicado_en'] = date('Y-m-d H:i:s');
        }

        if ($idProyecto === 0) {
            $resultado = $this->model->registrar(
                $datos,
                $categorias,
                $tecnologias
            );
            $correcto = $resultado > 0;
            $idGuardado = (int) $resultado;
            $mensaje = 'Proyecto registrado correctamente.';
        } else {
            $correcto = $this->model->modificar(
                $datos,
                $idProyecto,
                $categorias,
                $tecnologias
            );
            $idGuardado = $idProyecto;
            $mensaje = 'Proyecto modificado correctamente.';
        }

        $this->json([
            'status' => $correcto,
            'msg' => $correcto
                ? $mensaje
                : 'No fue posible guardar el proyecto.',
            'icono' => $correcto ? 'success' : 'error',
            'id_proyecto' => $correcto ? $idGuardado : 0,
        ], $correcto ? 200 : 500);
    }

    public function cambiarBaja()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idProyecto = (int) ($entrada['id_proyecto'] ?? 0);
        $activo = filter_var(
            $entrada['activo'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        if ($idProyecto <= 0 || empty($this->model->obtener($idProyecto))) {
            $this->json([
                'status' => false,
                'msg' => 'Selecciona un proyecto válido.',
                'icono' => 'error',
            ], 404);
        }

        $correcto = $this->model->cambiarBaja($idProyecto, $activo);

        $this->json([
            'status' => $correcto,
            'msg' => $correcto
                ? ($activo
                    ? 'Proyecto restaurado correctamente.'
                    : 'Proyecto dado de baja correctamente.')
                : 'No fue posible cambiar la disponibilidad del proyecto.',
            'icono' => $correcto ? 'success' : 'error',
        ], $correcto ? 200 : 500);
    }

    private function validarDatos(
        array $datos,
        array $categorias,
        array $tecnologias,
        $orden
    ) {
        if (
            $datos['id_estado_proyecto'] <= 0
            || $datos['titulo'] === ''
            || $datos['slug'] === ''
            || $datos['resumen_corto'] === ''
            || $datos['descripcion'] === ''
        ) {
            return 'Completa el estado, título, slug, resumen y descripción.';
        }

        if (
            strlen($datos['titulo']) > 180
            || strlen($datos['slug']) > 180
            || strlen($datos['resumen_corto']) > 350
        ) {
            return 'El título, slug o resumen superan la longitud permitida.';
        }

        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $datos['slug'])) {
            return 'El slug solo puede contener minúsculas, números y guiones.';
        }

        if ($datos['url_github'] !== '') {
            if (strlen($datos['url_github']) > 1000) {
                return 'El enlace de GitHub supera la longitud permitida.';
            }

            if (!filter_var($datos['url_github'], FILTER_VALIDATE_URL)) {
                return 'El enlace de GitHub no es una URL válida.';
            }

            $esquemaGithub = strtolower((string) parse_url(
                $datos['url_github'],
                PHP_URL_SCHEME
            ));
            $hostGithub = strtolower((string) parse_url(
                $datos['url_github'],
                PHP_URL_HOST
            ));

            if (!in_array($esquemaGithub, ['http', 'https'], true)) {
                return 'El enlace de GitHub debe utilizar http o https.';
            }

            if (!in_array($hostGithub, ['github.com', 'www.github.com'], true)) {
                return 'El enlace debe pertenecer al dominio github.com.';
            }
        }

        if (empty($categorias)) {
            return 'Selecciona al menos una categoría.';
        }

        if (empty($tecnologias)) {
            return 'Selecciona al menos una tecnología.';
        }

        if ($orden !== '' && $orden !== null && !is_numeric($orden)) {
            return 'El orden de visualización debe ser numérico.';
        }

        foreach (['fecha_inicio', 'fecha_fin'] as $campo) {
            if ($datos[$campo] !== '' && !$this->fechaValida($datos[$campo])) {
                return 'Las fechas no tienen un formato válido.';
            }
        }

        if (
            $datos['fecha_inicio'] !== ''
            && $datos['fecha_fin'] !== ''
            && $datos['fecha_fin'] < $datos['fecha_inicio']
        ) {
            return 'La fecha de finalización no puede ser anterior al inicio.';
        }

        return '';
    }

    private function ids($valores)
    {
        if (!is_array($valores)) {
            $valores = [$valores];
        }

        $ids = array_map('intval', $valores);
        $ids = array_filter($ids, static fn($id) => $id > 0);

        return array_values(array_unique($ids));
    }

    private function fechaValida($fecha)
    {
        $objeto = DateTime::createFromFormat('Y-m-d', $fecha);

        return $objeto && $objeto->format('Y-m-d') === $fecha;
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
