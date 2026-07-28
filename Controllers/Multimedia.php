<?php

class Multimedia extends Controller
{
    private const ALMACENAMIENTOS = [
        'local',
        'externo',
    ];

    private const MIME_PERMITIDOS = [
        'image/jpeg' => [
            'extension' => 'jpg',
            'tipo' => 'imagen',
            'maximo' => 12582912,
        ],
        'image/png' => [
            'extension' => 'png',
            'tipo' => 'imagen',
            'maximo' => 12582912,
        ],
        'image/webp' => [
            'extension' => 'webp',
            'tipo' => 'imagen',
            'maximo' => 12582912,
        ],
        'image/gif' => [
            'extension' => 'gif',
            'tipo' => 'imagen',
            'maximo' => 12582912,
        ],
        'video/mp4' => [
            'extension' => 'mp4',
            'tipo' => 'video',
            'maximo' => 104857600,
        ],
        'video/webm' => [
            'extension' => 'webm',
            'tipo' => 'video',
            'maximo' => 104857600,
        ],
        'video/quicktime' => [
            'extension' => 'mov',
            'tipo' => 'video',
            'maximo' => 104857600,
        ],
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
        header('Location: ' . BASE_URL . 'admin/multimedia');
        exit;
    }

    public function catalogos()
    {
        $catalogos = $this->model->catalogos();

        $this->json([
            'status' => true,
            'proyectos' => is_array($catalogos['proyectos'] ?? null)
                ? $catalogos['proyectos']
                : [],
            'tipos' => is_array($catalogos['tipos'] ?? null)
                ? $catalogos['tipos']
                : [],
        ]);
    }

    public function listar()
    {
        $busqueda = trim($_GET['busqueda'] ?? '');
        $idProyecto = max(0, (int) ($_GET['id_proyecto'] ?? 0));
        $tipo = $_GET['tipo'] ?? 'all';
        $estado = $_GET['estado'] ?? 'all';

        if (!in_array($tipo, ['all', 'imagen', 'video'], true)) {
            $tipo = 'all';
        }

        if (!in_array($estado, ['all', 'active', 'inactive'], true)) {
            $estado = 'all';
        }

        $registros = $this->model->listar(
            $busqueda,
            $idProyecto,
            $tipo,
            $estado
        );

        $resumen = $this->model->estadisticas();

        $this->json([
            'status' => true,
            'multimedia' => is_array($registros) ? $registros : [],
            'resumen' => [
                'total' => (int) ($resumen['total'] ?? 0),
                'imagenes' => (int) ($resumen['imagenes'] ?? 0),
                'videos' => (int) ($resumen['videos'] ?? 0),
                'inactivos' => (int) ($resumen['inactivos'] ?? 0),
            ],
        ]);
    }

    public function obtener($idMultimedia)
    {
        $idMultimedia = (int) $idMultimedia;

        if ($idMultimedia <= 0) {
            $this->json([
                'status' => false,
                'msg' => 'El identificador no es válido.',
                'icono' => 'error',
            ], 400);
        }

        $registro = $this->model->obtener($idMultimedia);

        if (empty($registro)) {
            $this->json([
                'status' => false,
                'msg' => 'El archivo multimedia no existe.',
                'icono' => 'error',
            ], 404);
        }

        $this->json($registro);
    }

    public function guardar()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idMultimedia = max(
            0,
            (int) ($entrada['id_multimedia'] ?? 0)
        );

        $existente = $idMultimedia > 0
            ? $this->model->obtener($idMultimedia)
            : null;

        if ($idMultimedia > 0 && empty($existente)) {
            $this->json([
                'status' => false,
                'msg' => 'El archivo multimedia no existe.',
                'icono' => 'error',
            ], 404);
        }

        $idProyecto = (int) ($entrada['id_proyecto'] ?? 0);
        $almacenamiento = strtolower(
            trim($entrada['tipo_almacenamiento'] ?? 'local')
        );

        if (empty($this->model->proyectoExiste($idProyecto))) {
            $this->json([
                'status' => false,
                'msg' => 'Selecciona un proyecto válido.',
                'icono' => 'warning',
            ], 422);
        }

        if (!in_array(
            $almacenamiento,
            self::ALMACENAMIENTOS,
            true
        )) {
            $this->json([
                'status' => false,
                'msg' => 'El tipo de almacenamiento no es válido.',
                'icono' => 'warning',
            ], 422);
        }

        $nuevoArchivo = null;
        $rutaAnterior = null;

        if (
            is_array($existente)
            && ($existente['tipo_almacenamiento'] ?? '') === 'local'
        ) {
            $rutaAnterior = $existente['ruta_archivo'] ?? null;
        }

        try {
            $datosArchivo = $this->resolverArchivo(
                $almacenamiento,
                $idProyecto,
                $entrada,
                $existente
            );

            $nuevoArchivo = $datosArchivo['nuevo_archivo'] ?? null;

            $orden = $entrada['orden_visualizacion'] ?? '';

            if ($orden === '' || $orden === null) {
                if (is_array($existente)) {
                    $orden = (int) ($existente['orden_visualizacion'] ?? 0);
                } else {
                    $siguiente = $this->model->siguienteOrden($idProyecto);
                    $orden = (int) ($siguiente['siguiente_orden'] ?? 1);
                }
            } else {
                $orden = max(0, (int) $orden);
            }

            $activo = filter_var(
                $entrada['activo'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) ? 1 : 0;

            $esPortada = filter_var(
                $entrada['es_portada'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) ? 1 : 0;

            if ($activo === 0) {
                $esPortada = 0;
            }

            if (
                $esPortada === 1
                && $datosArchivo['tipo_slug'] !== 'imagen'
            ) {
                throw new InvalidArgumentException(
                    'Solo una imagen activa puede utilizarse como portada.'
                );
            }

            $datos = [
                'id_proyecto' => $idProyecto,
                'id_tipo_multimedia' => $datosArchivo['id_tipo_multimedia'],
                'tipo_almacenamiento' => $almacenamiento,
                'ruta_archivo' => $datosArchivo['ruta_archivo'],
                'url_externa' => $datosArchivo['url_externa'],
                'ruta_miniatura' => $this->nullable(
                    trim($entrada['ruta_miniatura'] ?? '')
                ),
                'titulo' => $this->nullable(
                    trim($entrada['titulo'] ?? '')
                ),
                'texto_alternativo' => $this->nullable(
                    trim($entrada['texto_alternativo'] ?? '')
                ),
                'descripcion' => $this->nullable(
                    trim($entrada['descripcion'] ?? '')
                ),
                'tipo_mime' => $datosArchivo['tipo_mime'],
                'tamano_bytes' => $datosArchivo['tamano_bytes'],
                'orden_visualizacion' => $orden,
                'es_portada' => $esPortada,
                'activo' => $activo,
            ];

            $error = $this->validarDatos($datos);

            if ($error !== '') {
                throw new InvalidArgumentException($error);
            }

            if ($idMultimedia === 0) {
                $resultado = $this->model->registrar($datos);
                $correcto = (int) $resultado > 0;
                $idGuardado = (int) $resultado;
                $mensaje = 'Archivo multimedia registrado correctamente.';
            } else {
                $correcto = $this->model->modificar(
                    $datos,
                    $idMultimedia
                );
                $idGuardado = $idMultimedia;
                $mensaje = 'Archivo multimedia modificado correctamente.';
            }

            if (!$correcto) {
                throw new RuntimeException(
                    'No fue posible guardar el archivo multimedia.'
                );
            }

            if (
                $nuevoArchivo !== null
                && $rutaAnterior !== null
                && $rutaAnterior !== $datos['ruta_archivo']
            ) {
                $this->eliminarArchivoLocal($rutaAnterior);
            }

            if (
                $almacenamiento === 'externo'
                && $rutaAnterior !== null
            ) {
                $this->eliminarArchivoLocal($rutaAnterior);
            }

            $this->json([
                'status' => true,
                'msg' => $mensaje,
                'icono' => 'success',
                'id_multimedia' => $idGuardado,
            ]);
        } catch (InvalidArgumentException $e) {
            if ($nuevoArchivo !== null) {
                $this->eliminarArchivoLocal($nuevoArchivo);
            }

            $this->json([
                'status' => false,
                'msg' => $e->getMessage(),
                'icono' => 'warning',
            ], 422);
        } catch (Throwable $e) {
            if ($nuevoArchivo !== null) {
                $this->eliminarArchivoLocal($nuevoArchivo);
            }

            error_log('Error en Multimedia::guardar: ' . $e->getMessage());

            $this->json([
                'status' => false,
                'msg' => 'No fue posible guardar el archivo multimedia.',
                'icono' => 'error',
            ], 500);
        }
    }

    public function cambiarEstado()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idMultimedia = (int) ($entrada['id_multimedia'] ?? 0);
        $activo = filter_var(
            $entrada['activo'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        if (empty($this->model->obtener($idMultimedia))) {
            $this->json([
                'status' => false,
                'msg' => 'El archivo multimedia no existe.',
                'icono' => 'error',
            ], 404);
        }

        if (!$this->model->cambiarEstado($idMultimedia, $activo)) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible cambiar el estado.',
                'icono' => 'error',
            ], 500);
        }

        $this->json([
            'status' => true,
            'msg' => $activo
                ? 'Archivo multimedia activado correctamente.'
                : 'Archivo multimedia dado de baja correctamente.',
            'icono' => 'success',
        ]);
    }

    public function cambiarPortada()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idMultimedia = (int) ($entrada['id_multimedia'] ?? 0);
        $esPortada = filter_var(
            $entrada['es_portada'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        $registro = $this->model->obtener($idMultimedia);

        if (empty($registro)) {
            $this->json([
                'status' => false,
                'msg' => 'El archivo multimedia no existe.',
                'icono' => 'error',
            ], 404);
        }

        if (
            $esPortada === 1
            && (
                ($registro['tipo_slug'] ?? '') !== 'imagen'
                || (int) ($registro['activo'] ?? 0) !== 1
            )
        ) {
            $this->json([
                'status' => false,
                'msg' => 'Solo una imagen activa puede ser portada.',
                'icono' => 'warning',
            ], 422);
        }

        if (!$this->model->cambiarPortada(
            $idMultimedia,
            $esPortada
        )) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible modificar la portada.',
                'icono' => 'error',
            ], 500);
        }

        $this->json([
            'status' => true,
            'msg' => $esPortada
                ? 'Portada del proyecto actualizada.'
                : 'La imagen dejó de ser portada.',
            'icono' => 'success',
        ]);
    }

    private function resolverArchivo(
        $almacenamiento,
        $idProyecto,
        array $entrada,
        $existente
    ) {
        if ($almacenamiento === 'externo') {
            $url = trim($entrada['url_externa'] ?? '');
            $idTipo = (int) ($entrada['id_tipo_multimedia'] ?? 0);
            $tipo = $this->model->tipoExiste($idTipo);

            if ($url === '' || !$this->esUrlHttpValida($url)) {
                throw new InvalidArgumentException(
                    'Escribe una URL externa válida con http o https.'
                );
            }

            if (strlen($url) > 1000) {
                throw new InvalidArgumentException(
                    'La URL externa supera los 1000 caracteres.'
                );
            }

            if (empty($tipo)) {
                throw new InvalidArgumentException(
                    'Selecciona si el recurso externo es imagen o video.'
                );
            }

            return [
                'id_tipo_multimedia' => (int) $tipo['id_tipo_multimedia'],
                'tipo_slug' => $tipo['slug'],
                'ruta_archivo' => null,
                'url_externa' => $url,
                'tipo_mime' => null,
                'tamano_bytes' => null,
                'nuevo_archivo' => null,
            ];
        }

        $archivo = $_FILES['archivo'] ?? null;
        $hayArchivo = is_array($archivo)
            && (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE)
                !== UPLOAD_ERR_NO_FILE;

        if ($hayArchivo) {
            $guardado = $this->guardarArchivoLocal(
                $archivo,
                $idProyecto
            );

            $tipo = $this->model->tipoPorSlug(
                $guardado['tipo_slug']
            );

            if (empty($tipo)) {
                $this->eliminarArchivoLocal($guardado['ruta_archivo']);

                throw new RuntimeException(
                    'No se encontró el tipo multimedia correspondiente.'
                );
            }

            return [
                'id_tipo_multimedia' => (int) $tipo['id_tipo_multimedia'],
                'tipo_slug' => $guardado['tipo_slug'],
                'ruta_archivo' => $guardado['ruta_archivo'],
                'url_externa' => null,
                'tipo_mime' => $guardado['tipo_mime'],
                'tamano_bytes' => $guardado['tamano_bytes'],
                'nuevo_archivo' => $guardado['ruta_archivo'],
            ];
        }

        if (
            is_array($existente)
            && ($existente['tipo_almacenamiento'] ?? '') === 'local'
            && !empty($existente['ruta_archivo'])
        ) {
            return [
                'id_tipo_multimedia' => (int) $existente['id_tipo_multimedia'],
                'tipo_slug' => $existente['tipo_slug'],
                'ruta_archivo' => $existente['ruta_archivo'],
                'url_externa' => null,
                'tipo_mime' => $existente['tipo_mime'],
                'tamano_bytes' => $existente['tamano_bytes'],
                'nuevo_archivo' => null,
            ];
        }

        throw new InvalidArgumentException(
            'Selecciona una imagen o un video para subir.'
        );
    }

    private function guardarArchivoLocal(array $archivo, $idProyecto)
    {
        $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException(
                $this->mensajeErrorSubida($error)
            );
        }

        $temporal = $archivo['tmp_name'] ?? '';
        $tamano = (int) ($archivo['size'] ?? 0);

        if (
            $temporal === ''
            || !is_uploaded_file($temporal)
            || $tamano <= 0
        ) {
            throw new InvalidArgumentException(
                'El archivo recibido no es válido.'
            );
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($temporal);
        $configuracion = self::MIME_PERMITIDOS[$mime] ?? null;

        if ($configuracion === null) {
            throw new InvalidArgumentException(
                'Formato no permitido. Usa JPG, PNG, WEBP, GIF, MP4, WEBM o MOV.'
            );
        }

        if ($tamano > $configuracion['maximo']) {
            $limite = $configuracion['tipo'] === 'imagen'
                ? '12 MB'
                : '100 MB';

            throw new InvalidArgumentException(
                'El archivo supera el límite permitido de ' . $limite . '.'
            );
        }

        $rutaRelativa = 'Assets/uploads/proyectos/'
            . $idProyecto;

        $directorio = rtrim(UPLOAD_ROOT, '/\\')
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);

        if (
            !is_dir($directorio)
            && !mkdir($directorio, 0775, true)
            && !is_dir($directorio)
        ) {
            throw new RuntimeException(
                'No fue posible crear el directorio de almacenamiento.'
            );
        }

        if (!is_writable($directorio)) {
            throw new RuntimeException(
                'El directorio de multimedia no tiene permisos de escritura.'
            );
        }

        $nombre = bin2hex(random_bytes(20))
            . '.'
            . $configuracion['extension'];

        $destino = $directorio
            . DIRECTORY_SEPARATOR
            . $nombre;

        if (!move_uploaded_file($temporal, $destino)) {
            throw new RuntimeException(
                'No fue posible almacenar el archivo en el servidor.'
            );
        }

        return [
            'ruta_archivo' => $rutaRelativa . '/' . $nombre,
            'tipo_mime' => $mime,
            'tamano_bytes' => $tamano,
            'tipo_slug' => $configuracion['tipo'],
        ];
    }

    private function validarDatos(array $datos)
    {
        if (strlen((string) $datos['titulo']) > 180) {
            return 'El título no puede superar los 180 caracteres.';
        }

        if (strlen((string) $datos['texto_alternativo']) > 255) {
            return 'El texto alternativo no puede superar los 255 caracteres.';
        }

        if (strlen((string) $datos['descripcion']) > 500) {
            return 'La descripción no puede superar los 500 caracteres.';
        }

        if (strlen((string) $datos['ruta_miniatura']) > 500) {
            return 'La ruta de miniatura no puede superar los 500 caracteres.';
        }

        if (
            $datos['ruta_miniatura'] !== null
            && !$this->esRutaOUrlValida($datos['ruta_miniatura'])
        ) {
            return 'La miniatura debe ser una ruta local o URL válida.';
        }

        return '';
    }

    private function esUrlHttpValida($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $esquema = strtolower(
            (string) parse_url($url, PHP_URL_SCHEME)
        );

        return in_array($esquema, ['http', 'https'], true);
    }

    private function esRutaOUrlValida($valor)
    {
        if ($this->esUrlHttpValida($valor)) {
            return true;
        }

        return preg_match(
            '#^[A-Za-z0-9_./-]+$#',
            $valor
        ) === 1;
    }

    private function eliminarArchivoLocal($rutaRelativa)
    {
        if (
            !is_string($rutaRelativa)
            || !str_starts_with(
                $rutaRelativa,
                'Assets/uploads/proyectos/'
            )
        ) {
            return false;
        }

        $ruta = rtrim(UPLOAD_ROOT, '/\\')
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $rutaRelativa);

        if (!is_file($ruta)) {
            return true;
        }

        return unlink($ruta);
    }

    private function mensajeErrorSubida($error)
    {
        $mensajes = [
            UPLOAD_ERR_INI_SIZE => 'El archivo supera upload_max_filesize del servidor.',
            UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño permitido por el formulario.',
            UPLOAD_ERR_PARTIAL => 'El archivo se recibió de forma incompleta.',
            UPLOAD_ERR_NO_FILE => 'Selecciona un archivo para subir.',
            UPLOAD_ERR_NO_TMP_DIR => 'El servidor no tiene directorio temporal.',
            UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida.',
        ];

        return $mensajes[$error]
            ?? 'Ocurrió un error durante la subida del archivo.';
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

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE
        );

        die();
    }
}
