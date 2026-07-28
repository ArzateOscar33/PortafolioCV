<?php

class Configuracion extends Controller
{
    private const CAMPOS = [
        'nombre_sitio' => ['general', 'texto', 1, 120],
        'nombre_completo' => ['general', 'texto', 1, 150],
        'titulo_profesional' => ['general', 'texto', 1, 150],
        'descripcion_sitio' => ['general', 'texto', 1, 500],
        'footer_texto' => ['general', 'texto', 1, 255],

        'hero_kicker' => ['hero', 'texto', 1, 180],
        'hero_titulo_principal' => ['hero', 'texto', 1, 100],
        'hero_titulo_destacado' => ['hero', 'texto', 1, 100],
        'hero_descripcion' => ['hero', 'texto', 1, 1200],
        'hero_cta_principal_texto' => ['hero', 'texto', 1, 80],
        'hero_cta_principal_url' => ['hero', 'url', 1, 500],
        'hero_cta_secundario_texto' => ['hero', 'texto', 1, 80],
        'hero_cta_secundario_url' => ['hero', 'url', 1, 500],

        'acerca_titulo' => ['acerca', 'texto', 1, 120],
        'acerca_parrafo_1' => ['acerca', 'texto', 1, 2500],
        'acerca_parrafo_2' => ['acerca', 'texto', 1, 2500],
        'acerca_habilidades' => ['acerca', 'json', 1, 5000],
        'foto_perfil_ruta' => ['acerca', 'ruta', 1, 1000],
        'cv_ruta' => ['acerca', 'ruta', 1, 1000],

        'ubicacion' => ['contacto', 'texto', 1, 180],
        'correo_publico' => ['contacto', 'correo', 1, 150],
        'telefono_publico' => ['contacto', 'texto', 1, 50],
        'tiempo_respuesta' => ['contacto', 'texto', 1, 80],
        'disponibilidad_texto' => ['contacto', 'texto', 1, 180],
        'disponible_proyectos' => ['contacto', 'booleano', 1, 1],
        'mostrar_correo' => ['contacto', 'booleano', 1, 1],
        'mostrar_telefono' => ['contacto', 'booleano', 1, 1],

        'seo_titulo' => ['seo', 'texto', 1, 160],
        'seo_descripcion' => ['seo', 'texto', 1, 320],
        'seo_palabras_clave' => ['seo', 'texto', 1, 500],
        'seo_imagen_ruta' => ['seo', 'ruta', 1, 1000],
        'favicon_ruta' => ['seo', 'ruta', 1, 1000],

        'color_primario' => ['apariencia', 'color', 1, 7],
        'color_secundario' => ['apariencia', 'color', 1, 7],
        'color_acento' => ['apariencia', 'color', 1, 7],
    ];


    private const VALORES_PREDETERMINADOS = [
        'nombre_sitio' => 'Cyberpunk Portfolio',
        'nombre_completo' => 'Oscar Arzate',
        'titulo_profesional' => 'Ingeniero en Sistemas',
        'descripcion_sitio' => 'Portafolio profesional de desarrollo web, software, infraestructura, redes y ciberseguridad.',
        'footer_texto' => 'Oscar Arzate. Sistema operativo: estable.',
        'hero_kicker' => 'Ingeniería de software + infraestructura',
        'hero_titulo_principal' => 'Código',
        'hero_titulo_destacado' => 'sin límites',
        'hero_descripcion' => 'Diseño y construyo soluciones digitales desde la arquitectura y la base de datos hasta la interfaz, el despliegue y la infraestructura.',
        'hero_cta_principal_texto' => 'Explorar proyectos',
        'hero_cta_principal_url' => '#proyectos',
        'hero_cta_secundario_texto' => 'Iniciar conexión',
        'hero_cta_secundario_url' => '#contacto',
        'acerca_titulo' => 'Acerca de mí',
        'acerca_parrafo_1' => 'Soy ingeniero en sistemas enfocado en convertir necesidades operativas en soluciones funcionales. Trabajo desde la arquitectura y la base de datos hasta la interfaz, el despliegue y la administración de infraestructura.',
        'acerca_parrafo_2' => 'Mi experiencia combina desarrollo web con PHP y JavaScript, administración Linux, contenedores Docker, redes y automatización. Me interesa construir sistemas mantenibles, seguros y preparados para crecer.',
        'acerca_habilidades' => [
            'Desarrollo de sistemas empresariales con arquitectura MVC.',
            'Despliegue y operación de servicios sobre Linux y Docker.',
            'Diseño de bases de datos, flujos operativos e integraciones.',
            'Seguridad, trazabilidad, respaldo y mantenibilidad.',
        ],
        'foto_perfil_ruta' => null,
        'cv_ruta' => null,
        'ubicacion' => 'Tijuana, Baja California, México',
        'correo_publico' => 'correo@ejemplo.com',
        'telefono_publico' => '',
        'tiempo_respuesta' => '24-48 horas',
        'disponibilidad_texto' => 'Disponible para nuevos proyectos',
        'disponible_proyectos' => true,
        'mostrar_correo' => true,
        'mostrar_telefono' => false,
        'seo_titulo' => 'Cyberpunk | Portafolio de Desarrollo',
        'seo_descripcion' => 'Portafolio profesional de desarrollo web, escritorio, redes, infraestructura y ciberseguridad.',
        'seo_palabras_clave' => 'PHP, JavaScript, MySQL, Docker, Linux, redes, ciberseguridad, Tijuana',
        'seo_imagen_ruta' => null,
        'favicon_ruta' => null,
        'color_primario' => '#00F6FF',
        'color_secundario' => '#FF2BD6',
        'color_acento' => '#F8F32B',
    ];

    private const ARCHIVOS = [
        'foto_perfil_archivo' => [
            'clave' => 'foto_perfil_ruta',
            'prefijo' => 'perfil',
            'maximo' => 5242880,
            'mimes' => [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ],
        ],
        'cv_archivo' => [
            'clave' => 'cv_ruta',
            'prefijo' => 'cv',
            'maximo' => 10485760,
            'mimes' => [
                'application/pdf' => 'pdf',
            ],
        ],
        'seo_imagen_archivo' => [
            'clave' => 'seo_imagen_ruta',
            'prefijo' => 'seo',
            'maximo' => 5242880,
            'mimes' => [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
            ],
        ],
        'favicon_archivo' => [
            'clave' => 'favicon_ruta',
            'prefijo' => 'favicon',
            'maximo' => 2097152,
            'mimes' => [
                'image/png' => 'png',
                'image/x-icon' => 'ico',
                'image/vnd.microsoft.icon' => 'ico',
            ],
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
        header('Location: ' . BASE_URL . 'admin/configuracion');
        exit;
    }

    public function obtener()
    {
        $configuracion = $this->model->obtenerConfiguracion();
        $redes = $this->model->listarRedes();
        $resumen = $this->model->estadisticas();

        $this->json([
            'status' => true,

            /*
     * Permite sincronizar el formulario cuando la página
     * se restauró desde caché o la sesión fue renovada.
     */
            'csrf_token' => (string) (
                $_SESSION['admin_csrf_token'] ?? ''
            ),

            'configuracion' => array_merge(
                self::VALORES_PREDETERMINADOS,
                $this->valoresPlanos($configuracion)
            ),
            'configuracion' => array_merge(
                self::VALORES_PREDETERMINADOS,
                $this->valoresPlanos($configuracion)
            ),
            'metadatos' => $configuracion,
            'redes' => is_array($redes) ? $redes : [],
            'resumen' => [
                'total_configuraciones' => (int) ($resumen['total_configuraciones'] ?? 0),
                'publicas' => (int) ($resumen['publicas'] ?? 0),
                'total_redes' => (int) ($resumen['total_redes'] ?? 0),
                'redes_activas' => (int) ($resumen['redes_activas'] ?? 0),
                'redes_inactivas' => (int) ($resumen['redes_inactivas'] ?? 0),
                'ultima_actualizacion' => $resumen['ultima_actualizacion'] ?? null,
            ],
        ]);
    }

    public function guardar()
    {
        $entrada = $_POST;
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idUsuario = (int) ($_SESSION['usuario']['id_usuario'] ?? 0);
        $configuracionAnterior = $this->model->obtenerConfiguracion();
        $valoresAnteriores = $this->valoresPlanos($configuracionAnterior);
        $valores = [];

        foreach (self::CAMPOS as $clave => $regla) {
            [$grupo, $tipo, $publica, $maximo] = $regla;
            $valor = $entrada[$clave] ?? '';

            if ($tipo === 'booleano') {
                $valor = filter_var(
                    $valor,
                    FILTER_VALIDATE_BOOLEAN
                ) ? '1' : '0';
            } elseif ($tipo === 'json' && $clave === 'acerca_habilidades') {
                $valor = json_encode(
                    $this->normalizarHabilidades((string) $valor),
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
            } else {
                $valor = trim((string) $valor);
            }

            $error = $this->validarValor(
                $clave,
                $valor,
                $tipo,
                $maximo
            );

            if ($error !== '') {
                $this->json([
                    'status' => false,
                    'msg' => $error,
                    'icono' => 'warning',
                ], 422);
            }

            $valores[$clave] = [
                'grupo' => $grupo,
                'clave' => $clave,
                'valor' => $valor !== '' ? $valor : null,
                'tipo_valor' => $tipo,
                'publica' => $publica,
            ];
        }

        $archivosNuevos = [];
        $archivosParaEliminar = [];

        foreach (self::ARCHIVOS as $campoArchivo => $reglaArchivo) {
            $clave = $reglaArchivo['clave'];
            $rutaAnterior = (string) (
                $configuracionAnterior[$clave]['valor'] ?? ''
            );

            $banderaEliminar = filter_var(
                $entrada['eliminar_' . $clave] ?? false,
                FILTER_VALIDATE_BOOLEAN
            );

            if ($banderaEliminar) {
                $valores[$clave]['valor'] = null;

                if ($this->esArchivoLocal($rutaAnterior)) {
                    $archivosParaEliminar[] = $rutaAnterior;
                }
            }

            $archivo = $_FILES[$campoArchivo] ?? null;

            if (
                !is_array($archivo)
                || (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE)
                === UPLOAD_ERR_NO_FILE
            ) {
                if (!$banderaEliminar) {
                    $valores[$clave]['valor'] = $rutaAnterior !== ''
                        ? $rutaAnterior
                        : null;
                }

                continue;
            }

            $resultadoArchivo = $this->guardarArchivo(
                $archivo,
                $reglaArchivo
            );

            if (!$resultadoArchivo['status']) {
                $this->eliminarArchivosCreados($archivosNuevos);

                $this->json([
                    'status' => false,
                    'msg' => $resultadoArchivo['msg'],
                    'icono' => 'warning',
                ], 422);
            }

            $nuevaRuta = $resultadoArchivo['ruta'];
            $archivosNuevos[] = $nuevaRuta;
            $valores[$clave]['valor'] = $nuevaRuta;

            if (
                $rutaAnterior !== ''
                && $rutaAnterior !== $nuevaRuta
                && $this->esArchivoLocal($rutaAnterior)
            ) {
                $archivosParaEliminar[] = $rutaAnterior;
            }
        }

        if (!$this->model->guardarConfiguracion(
            array_values($valores),
            $idUsuario
        )) {
            $this->eliminarArchivosCreados($archivosNuevos);

            $this->json([
                'status' => false,
                'msg' => 'No fue posible guardar la configuración.',
                'icono' => 'error',
            ], 500);
        }

        foreach (array_unique($archivosParaEliminar) as $ruta) {
            $this->eliminarArchivoLocal($ruta);
        }

        $configuracionNueva = $this->model->obtenerConfiguracion();
        $valoresNuevos = $this->valoresPlanos($configuracionNueva);

        $this->model->registrarAuditoria(
            $idUsuario,
            'configuracion_actualizada',
            'configuracion_sitio',
            0,
            $valoresAnteriores,
            $valoresNuevos,
            $this->ipCliente(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        $this->json([
            'status' => true,
            'msg' => 'Configuración guardada correctamente.',
            'icono' => 'success',
            'configuracion' => $valoresNuevos,
        ]);
    }

    public function obtenerRed($idRedSocial)
    {
        $idRedSocial = (int) $idRedSocial;
        $red = $this->model->obtenerRed($idRedSocial);

        if (empty($red)) {
            $this->json([
                'status' => false,
                'msg' => 'La red social no existe.',
                'icono' => 'error',
            ], 404);
        }

        $this->json($red);
    }

    public function guardarRed()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idRedSocial = max(
            0,
            (int) ($entrada['id_red_social'] ?? 0)
        );

        $redAnterior = $idRedSocial > 0
            ? $this->model->obtenerRed($idRedSocial)
            : null;

        if ($idRedSocial > 0 && empty($redAnterior)) {
            $this->json([
                'status' => false,
                'msg' => 'La red social no existe.',
                'icono' => 'error',
            ], 404);
        }

        $orden = $entrada['orden_visualizacion'] ?? '';

        if ($orden === '' || $orden === null) {
            $siguiente = $this->model->siguienteOrdenRed();
            $orden = (int) ($siguiente['siguiente_orden'] ?? 1);
        } else {
            $orden = max(0, (int) $orden);
        }

        $datos = [
            'plataforma' => trim($entrada['plataforma'] ?? ''),
            'etiqueta' => trim($entrada['etiqueta'] ?? ''),
            'url' => trim($entrada['url'] ?? ''),
            'clase_icono' => trim($entrada['clase_icono'] ?? ''),
            'color_hexadecimal' => strtoupper(
                trim($entrada['color_hexadecimal'] ?? '')
            ),
            'orden_visualizacion' => $orden,
            'activa' => filter_var(
                $entrada['activa'] ?? false,
                FILTER_VALIDATE_BOOLEAN
            ) ? 1 : 0,
        ];

        $error = $this->validarRed($datos);

        if ($error !== '') {
            $this->json([
                'status' => false,
                'msg' => $error,
                'icono' => 'warning',
            ], 422);
        }

        if (!empty($this->model->existeUrlRed(
            $datos['url'],
            $idRedSocial
        ))) {
            $this->json([
                'status' => false,
                'msg' => 'Ya existe una red social con esa URL.',
                'icono' => 'warning',
            ], 409);
        }

        foreach (['etiqueta', 'clase_icono', 'color_hexadecimal'] as $campo) {
            $datos[$campo] = $datos[$campo] !== ''
                ? $datos[$campo]
                : null;
        }

        if ($idRedSocial === 0) {
            $resultado = $this->model->registrarRed($datos);
            $correcto = (int) $resultado > 0;
            $idGuardado = (int) $resultado;
            $accion = 'red_social_creada';
            $mensaje = 'Red social registrada correctamente.';
        } else {
            $correcto = $this->model->modificarRed(
                $datos,
                $idRedSocial
            );
            $idGuardado = $idRedSocial;
            $accion = 'red_social_actualizada';
            $mensaje = 'Red social modificada correctamente.';
        }

        if (!$correcto) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible guardar la red social.',
                'icono' => 'error',
            ], 500);
        }

        $redNueva = $this->model->obtenerRed($idGuardado);
        $idUsuario = (int) ($_SESSION['usuario']['id_usuario'] ?? 0);

        $this->model->registrarAuditoria(
            $idUsuario,
            $accion,
            'redes_sociales',
            $idGuardado,
            $redAnterior,
            $redNueva,
            $this->ipCliente(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        $this->json([
            'status' => true,
            'msg' => $mensaje,
            'icono' => 'success',
            'id_red_social' => $idGuardado,
        ]);
    }

    public function cambiarEstadoRed()
    {
        $entrada = $this->entrada();
        $this->validarCsrf($entrada['csrf_token'] ?? '');

        $idRedSocial = (int) ($entrada['id_red_social'] ?? 0);
        $activa = filter_var(
            $entrada['activa'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        ) ? 1 : 0;

        $redAnterior = $this->model->obtenerRed($idRedSocial);

        if (empty($redAnterior)) {
            $this->json([
                'status' => false,
                'msg' => 'La red social no existe.',
                'icono' => 'error',
            ], 404);
        }

        if (!$this->model->cambiarEstadoRed(
            $idRedSocial,
            $activa
        )) {
            $this->json([
                'status' => false,
                'msg' => 'No fue posible cambiar el estado.',
                'icono' => 'error',
            ], 500);
        }

        $redNueva = $this->model->obtenerRed($idRedSocial);
        $idUsuario = (int) ($_SESSION['usuario']['id_usuario'] ?? 0);

        $this->model->registrarAuditoria(
            $idUsuario,
            $activa ? 'red_social_activada' : 'red_social_desactivada',
            'redes_sociales',
            $idRedSocial,
            $redAnterior,
            $redNueva,
            $this->ipCliente(),
            $_SERVER['HTTP_USER_AGENT'] ?? null
        );

        $this->json([
            'status' => true,
            'msg' => $activa
                ? 'Red social activada correctamente.'
                : 'Red social dada de baja correctamente.',
            'icono' => 'success',
        ]);
    }

    private function validarValor(
        $clave,
        $valor,
        $tipo,
        $maximo
    ) {
        if (
            $tipo !== 'booleano'
            && $tipo !== 'json'
            && strlen((string) $valor) > $maximo
        ) {
            return 'El campo ' . str_replace('_', ' ', $clave)
                . ' supera la longitud permitida.';
        }

        if (
            $tipo === 'correo'
            && $valor !== ''
            && !filter_var($valor, FILTER_VALIDATE_EMAIL)
        ) {
            return 'El correo público no es válido.';
        }

        if (
            $tipo === 'url'
            && $valor !== ''
            && !$this->esUrlFlexibleValida($valor)
        ) {
            return 'Una de las URLs de los botones no es válida.';
        }

        if (
            $tipo === 'color'
            && $valor !== ''
            && !preg_match('/^#[0-9A-Fa-f]{6}$/', $valor)
        ) {
            return 'Los colores deben usar el formato #RRGGBB.';
        }

        if (
            $tipo === 'json'
            && $valor !== ''
            && json_decode($valor, true) === null
            && strtolower($valor) !== 'null'
        ) {
            return 'La lista de habilidades no pudo procesarse.';
        }

        return '';
    }

    private function validarRed(array $datos)
    {
        if ($datos['plataforma'] === '' || $datos['url'] === '') {
            return 'La plataforma y la URL son obligatorias.';
        }

        if (strlen($datos['plataforma']) > 60) {
            return 'La plataforma no puede superar 60 caracteres.';
        }

        if (
            $datos['etiqueta'] !== ''
            && strlen($datos['etiqueta']) > 100
        ) {
            return 'La etiqueta no puede superar 100 caracteres.';
        }

        if (
            strlen($datos['url']) > 1000
            || !filter_var($datos['url'], FILTER_VALIDATE_URL)
        ) {
            return 'La URL de la red social no es válida.';
        }

        $esquema = strtolower((string) parse_url(
            $datos['url'],
            PHP_URL_SCHEME
        ));

        if (!in_array($esquema, ['http', 'https'], true)) {
            return 'La URL debe utilizar http o https.';
        }

        if (
            $datos['clase_icono'] !== ''
            && strlen($datos['clase_icono']) > 150
        ) {
            return 'La clase del icono es demasiado larga.';
        }

        if (
            $datos['color_hexadecimal'] !== ''
            && !preg_match(
                '/^#[0-9A-F]{6}$/',
                $datos['color_hexadecimal']
            )
        ) {
            return 'El color de la red social no es válido.';
        }

        return '';
    }

    private function guardarArchivo(array $archivo, array $regla)
    {
        $error = (int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error !== UPLOAD_ERR_OK) {
            return [
                'status' => false,
                'msg' => $this->mensajeErrorArchivo($error),
            ];
        }

        $temporal = (string) ($archivo['tmp_name'] ?? '');
        $tamano = (int) ($archivo['size'] ?? 0);

        if (
            $temporal === ''
            || !is_uploaded_file($temporal)
        ) {
            return [
                'status' => false,
                'msg' => 'El archivo recibido no es válido.',
            ];
        }

        if ($tamano <= 0 || $tamano > $regla['maximo']) {
            return [
                'status' => false,
                'msg' => 'El archivo supera el tamaño permitido.',
            ];
        }

        $mime = $this->detectarMime($temporal);
        $extension = $regla['mimes'][$mime] ?? null;

        if ($extension === null) {
            return [
                'status' => false,
                'msg' => 'El tipo real del archivo no está permitido.',
            ];
        }

        $directorioRelativo = 'Assets/uploads/configuracion';
        $directorioAbsoluto = rtrim(UPLOAD_ROOT, '/\\')
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $directorioRelativo);

        if (
            !is_dir($directorioAbsoluto)
            && !mkdir($directorioAbsoluto, 0755, true)
            && !is_dir($directorioAbsoluto)
        ) {
            return [
                'status' => false,
                'msg' => 'No fue posible crear el directorio de configuración.',
            ];
        }

        $nombre = $regla['prefijo']
            . '-'
            . bin2hex(random_bytes(12))
            . '.'
            . $extension;

        $destino = $directorioAbsoluto
            . DIRECTORY_SEPARATOR
            . $nombre;

        if (!move_uploaded_file($temporal, $destino)) {
            return [
                'status' => false,
                'msg' => 'No fue posible almacenar el archivo.',
            ];
        }

        @chmod($destino, 0644);

        return [
            'status' => true,
            'ruta' => $directorioRelativo . '/' . $nombre,
        ];
    }

    private function detectarMime($ruta)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            if ($finfo !== false) {
                $mime = finfo_file($finfo, $ruta);
                finfo_close($finfo);

                if (is_string($mime) && $mime !== '') {
                    return strtolower($mime);
                }
            }
        }

        if (function_exists('mime_content_type')) {
            $mime = mime_content_type($ruta);

            if (is_string($mime)) {
                return strtolower($mime);
            }
        }

        return '';
    }

    private function normalizarHabilidades($texto)
    {
        $lineas = preg_split('/\R+/', trim($texto)) ?: [];
        $habilidades = [];

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if ($linea === '') {
                continue;
            }

            if (strlen($linea) > 180) {
                $linea = substr($linea, 0, 180);
            }

            $habilidades[] = $linea;
        }

        return array_slice(array_values(array_unique($habilidades)), 0, 12);
    }

    private function valoresPlanos(array $configuracion)
    {
        $valores = [];

        foreach ($configuracion as $clave => $registro) {
            $tipo = (string) ($registro['tipo_valor'] ?? 'texto');
            $valor = $registro['valor'] ?? null;

            if ($tipo === 'booleano') {
                $valor = (string) $valor === '1';
            } elseif ($tipo === 'numero') {
                $valor = is_numeric($valor) ? $valor + 0 : 0;
            } elseif ($tipo === 'json') {
                $decodificado = json_decode((string) $valor, true);
                $valor = is_array($decodificado) ? $decodificado : [];
            }

            $valores[$clave] = $valor;
        }

        return $valores;
    }

    private function esUrlFlexibleValida($valor)
    {
        if (preg_match('/^#[A-Za-z0-9_-]+$/', $valor)) {
            return true;
        }

        if (str_starts_with($valor, '/')) {
            return true;
        }

        if (!filter_var($valor, FILTER_VALIDATE_URL)) {
            return false;
        }

        $esquema = strtolower((string) parse_url(
            $valor,
            PHP_URL_SCHEME
        ));

        return in_array(
            $esquema,
            ['http', 'https', 'mailto', 'tel'],
            true
        );
    }

    private function esArchivoLocal($ruta)
    {
        return $ruta !== ''
            && str_starts_with($ruta, 'Assets/uploads/configuracion/');
    }

    private function eliminarArchivoLocal($ruta)
    {
        if (!$this->esArchivoLocal($ruta)) {
            return;
        }

        $absoluta = rtrim(UPLOAD_ROOT, '/\\')
            . DIRECTORY_SEPARATOR
            . str_replace('/', DIRECTORY_SEPARATOR, $ruta);

        if (is_file($absoluta)) {
            @unlink($absoluta);
        }
    }

    private function eliminarArchivosCreados(array $rutas)
    {
        foreach ($rutas as $ruta) {
            $this->eliminarArchivoLocal($ruta);
        }
    }

    private function mensajeErrorArchivo($error)
    {
        $mensajes = [
            UPLOAD_ERR_INI_SIZE => 'El archivo supera el límite configurado en PHP.',
            UPLOAD_ERR_FORM_SIZE => 'El archivo supera el límite del formulario.',
            UPLOAD_ERR_PARTIAL => 'El archivo se recibió de forma incompleta.',
            UPLOAD_ERR_NO_TMP_DIR => 'No existe un directorio temporal en el servidor.',
            UPLOAD_ERR_CANT_WRITE => 'El servidor no pudo escribir el archivo.',
            UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la carga.',
        ];

        return $mensajes[$error] ?? 'No fue posible recibir el archivo.';
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
                'msg' => 'La sesión cambió y el formulario fue actualizado. Presiona Guardar nuevamente.',
                'icono' => 'warning',

                /*
     * Se devuelve el token actual para que JavaScript
     * pueda corregir el formulario sin recargarlo.
     */
                'csrf_token' => (string) $tokenSesion,
            ], 419);
        }
    }

    private function ipCliente()
    {
        return trim((string) ($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    private function json($respuesta, $codigoHttp = 200)
    {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode(
            $respuesta,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        die();
    }
}
