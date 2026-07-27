<?php

class Categorias extends Controller
{
    public function __construct()
    {
        SessionManager::start();

        parent::__construct();

        /*
         * Verificar sesión administrativa.
         */
        if (
            empty($_SESSION['autenticado'])
            || empty($_SESSION['usuario']['id_usuario'])
        ) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->respuestaJson(
                    [
                        'status' => false,
                        'msg' => 'La sesión ha expirado.',
                        'icono' => 'warning'
                    ],
                    401
                );
            }

            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        /*
         * Crear token CSRF si todavía no existe.
         */
        if (empty($_SESSION['admin_csrf_token'])) {
            $_SESSION['admin_csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }
    }

    /**
     * Redireccionar al módulo administrativo.
     */
    public function index()
    {
        header(
            'Location: '
                . BASE_URL
                . 'admin/categorias'
        );

        exit;
    }

    /**
     * Listar categorías.
     *
     * Parámetros GET:
     *
     * estado:
     * - all
     * - active
     * - inactive
     *
     * busqueda:
     * - Nombre o slug.
     */
    public function listar()
    {
        $estado = $_GET['estado'] ?? 'all';

        $busqueda = trim(
            $_GET['busqueda'] ?? ''
        );

        /*
         * Listado sin búsqueda.
         */
        if ($busqueda === '') {
            if ($estado === 'active') {
                $categorias =
                    $this->model->getCategoriasEstado(1);
            } elseif ($estado === 'inactive') {
                $categorias =
                    $this->model->getCategoriasEstado(0);
            } else {
                $categorias =
                    $this->model->getCategorias();
            }
        } else {
            /*
             * Listado con búsqueda.
             */
            if ($estado === 'active') {
                $categorias =
                    $this->model->buscarCategoriasEstado(
                        $busqueda,
                        1
                    );
            } elseif ($estado === 'inactive') {
                $categorias =
                    $this->model->buscarCategoriasEstado(
                        $busqueda,
                        0
                    );
            } else {
                $categorias =
                    $this->model->buscarCategorias(
                        $busqueda
                    );
            }
        }

        $resumen = $this->model->getEstadisticas();

        if (!is_array($categorias)) {
            $categorias = [];
        }

        if (!is_array($resumen)) {
            $resumen = [
                'total' => 0,
                'activas' => 0,
                'inactivas' => 0
            ];
        }

        $respuesta = [
            'status' => true,
            'categorias' => $categorias,
            'resumen' => [
                'total' => (int) (
                    $resumen['total'] ?? 0
                ),
                'activas' => (int) (
                    $resumen['activas'] ?? 0
                ),
                'inactivas' => (int) (
                    $resumen['inactivas'] ?? 0
                )
            ]
        ];

        $this->respuestaJson($respuesta);
    }

    /**
     * Registrar o modificar una categoría.
     */
    public function guardar()
    {
        $datos = $this->obtenerDatosSolicitud();

        /*
         * Validar token CSRF.
         */
        if (
            !$this->validarToken(
                $datos['csrf_token'] ?? ''
            )
        ) {
            $this->respuestaJson(
                [
                    'status' => false,
                    'msg' => 'La solicitud no es válida. Actualiza la página.',
                    'icono' => 'warning'
                ],
                419
            );
        }

        $idCategoria = intval(
            $datos['id_categoria'] ?? 0
        );

        $nombre = trim(
            $datos['nombre'] ?? ''
        );

        $slug = strtolower(
            trim(
                $datos['slug'] ?? ''
            )
        );

        $descripcion = trim(
            $datos['descripcion'] ?? ''
        );

        $colorHexadecimal = strtoupper(
            trim(
                $datos['color_hexadecimal'] ?? ''
            )
        );

        $claseIcono = trim(
            $datos['clase_icono'] ?? ''
        );

        $ordenVisualizacion = $datos['orden_visualizacion'] ?? '';

        $activa = filter_var(
            $datos['activa'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        /*
         * Validar campos obligatorios.
         */
        if (
            $nombre === ''
            || $slug === ''
        ) {
            $respuesta = [
                'status' => false,
                'msg' => 'El nombre y el slug son obligatorios.',
                'icono' => 'warning'
            ];

            $this->respuestaJson($respuesta);
        }

        /*
         * Validar longitudes según la base de datos.
         */
        if (strlen($nombre) > 100) {
            $this->respuestaJson([
                'status' => false,
                'msg' => 'El nombre no puede superar los 100 caracteres.',
                'icono' => 'warning'
            ]);
        }

        if (strlen($slug) > 120) {
            $this->respuestaJson([
                'status' => false,
                'msg' => 'El slug no puede superar los 120 caracteres.',
                'icono' => 'warning'
            ]);
        }

        if (strlen($descripcion) > 500) {
            $this->respuestaJson([
                'status' => false,
                'msg' => 'La descripción no puede superar los 500 caracteres.',
                'icono' => 'warning'
            ]);
        }

        if (strlen($claseIcono) > 150) {
            $this->respuestaJson([
                'status' => false,
                'msg' => 'La clase del icono es demasiado larga.',
                'icono' => 'warning'
            ]);
        }

        /*
         * Validar formato del slug.
         */
        if (
            !preg_match(
                '/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                $slug
            )
        ) {
            $this->respuestaJson([
                'status' => false,
                'msg' => 'El slug solo puede contener letras minúsculas, números y guiones.',
                'icono' => 'warning'
            ]);
        }

        /*
         * Validar color hexadecimal si fue enviado.
         */
        if (
            $colorHexadecimal !== ''
            && !preg_match(
                '/^#[0-9A-F]{6}$/',
                $colorHexadecimal
            )
        ) {
            $this->respuestaJson([
                'status' => false,
                'msg' => 'El color hexadecimal no es válido.',
                'icono' => 'warning'
            ]);
        }

        /*
         * Convertir campos opcionales vacíos en NULL.
         */
        $descripcion = $descripcion !== ''
            ? $descripcion
            : null;

        $colorHexadecimal = $colorHexadecimal !== ''
            ? $colorHexadecimal
            : null;

        $claseIcono = $claseIcono !== ''
            ? $claseIcono
            : null;

        /*
         * Obtener orden automático si está vacío.
         */
        if (
            $ordenVisualizacion === ''
            || $ordenVisualizacion === null
        ) {
            $siguienteOrden =
                $this->model->getSiguienteOrden();

            $ordenVisualizacion = intval(
                $siguienteOrden['siguiente_orden']
                    ?? 1
            );
        } else {
            $ordenVisualizacion = intval(
                $ordenVisualizacion
            );
        }

        if ($ordenVisualizacion < 0) {
            $ordenVisualizacion = 0;
        }

        /*
         * Registrar categoría.
         */
        if ($idCategoria === 0) {
            $nombreExiste =
                $this->model->verificarNombre(
                    $nombre
                );

            if (!empty($nombreExiste)) {
                $this->respuestaJson([
                    'status' => false,
                    'msg' => 'Ya existe una categoría con ese nombre.',
                    'icono' => 'warning'
                ]);
            }

            $slugExiste =
                $this->model->verificarSlug(
                    $slug
                );

            if (!empty($slugExiste)) {
                $this->respuestaJson([
                    'status' => false,
                    'msg' => 'Ya existe una categoría con ese slug.',
                    'icono' => 'warning'
                ]);
            }

            $resultado = $this->model->registrar(
                $nombre,
                $slug,
                $descripcion,
                $colorHexadecimal,
                $claseIcono,
                $ordenVisualizacion,
                $activa
            );

            if ($resultado > 0) {
                $respuesta = [
                    'status' => true,
                    'msg' => 'Categoría registrada correctamente.',
                    'icono' => 'success',
                    'id_categoria' => intval($resultado)
                ];
            } else {
                $respuesta = [
                    'status' => false,
                    'msg' => 'Error al registrar la categoría.',
                    'icono' => 'error'
                ];
            }
        } else {
            /*
             * Modificar categoría.
             */
            $categoria =
                $this->model->getCategoria(
                    $idCategoria
                );

            if (empty($categoria)) {
                $this->respuestaJson(
                    [
                        'status' => false,
                        'msg' => 'La categoría no existe.',
                        'icono' => 'error'
                    ],
                    404
                );
            }

            $nombreExiste =
                $this->model->verificarNombreEditar(
                    $nombre,
                    $idCategoria
                );

            if (!empty($nombreExiste)) {
                $this->respuestaJson([
                    'status' => false,
                    'msg' => 'Ya existe otra categoría con ese nombre.',
                    'icono' => 'warning'
                ]);
            }

            $slugExiste =
                $this->model->verificarSlugEditar(
                    $slug,
                    $idCategoria
                );

            if (!empty($slugExiste)) {
                $this->respuestaJson([
                    'status' => false,
                    'msg' => 'Ya existe otra categoría con ese slug.',
                    'icono' => 'warning'
                ]);
            }

            $resultado = $this->model->modificar(
                $nombre,
                $slug,
                $descripcion,
                $colorHexadecimal,
                $claseIcono,
                $ordenVisualizacion,
                $activa,
                $idCategoria
            );

            if ($resultado == 1) {
                $respuesta = [
                    'status' => true,
                    'msg' => 'Categoría modificada correctamente.',
                    'icono' => 'success'
                ];
            } else {
                $respuesta = [
                    'status' => false,
                    'msg' => 'Error al modificar la categoría.',
                    'icono' => 'error'
                ];
            }
        }

        $this->respuestaJson($respuesta);
    }

    /**
     * Obtener una categoría para editar.
     *
     * Ejemplo:
     * categorias/obtener/5
     */
    public function obtener($idCategoria)
    {
        if (!is_numeric($idCategoria)) {
            $this->respuestaJson([
                'status' => false,
                'msg' => 'El identificador no es válido.',
                'icono' => 'error'
            ]);
        }

        $categoria = $this->model->getCategoria(
            intval($idCategoria)
        );

        if (empty($categoria)) {
            $this->respuestaJson(
                [
                    'status' => false,
                    'msg' => 'La categoría no existe.',
                    'icono' => 'error'
                ],
                404
            );
        }

        /*
         * Igual que en institutovc, se devuelve directamente
         * el registro que JavaScript colocará en el formulario.
         */
        $this->respuestaJson($categoria);
    }

    /**
     * Activar o desactivar una categoría.
     */
    public function cambiarEstado()
    {
        $datos = $this->obtenerDatosSolicitud();

        if (
            !$this->validarToken(
                $datos['csrf_token'] ?? ''
            )
        ) {
            $this->respuestaJson(
                [
                    'status' => false,
                    'msg' => 'La solicitud no es válida. Actualiza la página.',
                    'icono' => 'warning'
                ],
                419
            );
        }

        $idCategoria = intval(
            $datos['id_categoria'] ?? 0
        );

        if ($idCategoria <= 0) {
            $this->respuestaJson([
                'status' => false,
                'msg' => 'Selecciona una categoría válida.',
                'icono' => 'error'
            ]);
        }

        $categoria = $this->model->getCategoria(
            $idCategoria
        );

        if (empty($categoria)) {
            $this->respuestaJson(
                [
                    'status' => false,
                    'msg' => 'La categoría no existe.',
                    'icono' => 'error'
                ],
                404
            );
        }

        /*
         * JavaScript envía el nuevo estado.
         */
        $estado = filter_var(
            $datos['activa'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        $resultado =
            $this->model->cambiarEstado(
                $estado,
                $idCategoria
            );

        if ($resultado == 1) {
            $respuesta = [
                'status' => true,
                'msg' => $estado == 1
                    ? 'Categoría activada correctamente.'
                    : 'Categoría dada de baja correctamente.',
                'icono' => 'success'
            ];
        } else {
            $respuesta = [
                'status' => false,
                'msg' => 'Error al cambiar el estado de la categoría.',
                'icono' => 'error'
            ];
        }

        $this->respuestaJson($respuesta);
    }

    /**
     * Leer información enviada mediante FormData o JSON.
     */
    private function obtenerDatosSolicitud()
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $contenido = file_get_contents(
            'php://input'
        );

        if (
            $contenido === false
            || trim($contenido) === ''
        ) {
            return [];
        }

        $datos = json_decode(
            $contenido,
            true
        );

        return is_array($datos)
            ? $datos
            : [];
    }

    /**
     * Validar token CSRF.
     */
    private function validarToken($token)
    {
        $tokenSesion =
            $_SESSION['admin_csrf_token']
            ?? '';

        return $token !== ''
            && $tokenSesion !== ''
            && hash_equals(
                $tokenSesion,
                $token
            );
    }

    /**
     * Imprimir una respuesta JSON.
     */
    private function respuestaJson(
        $respuesta,
        $codigoHttp = 200
    ) {
        http_response_code($codigoHttp);

        header(
            'Content-Type: application/json; charset=utf-8'
        );

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        die();
    }
}
