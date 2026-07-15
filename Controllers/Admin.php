<?php

class Admin extends Controller
{
    private array $menu = [
        'dashboard' => [
            'label' => 'Dashboard',
            'route' => 'admin/index',
            'icon' => 'fa-chart-line',
        ],
        'proyectos' => [
            'label' => 'Proyectos',
            'route' => 'admin/proyectos',
            'icon' => 'fa-diagram-project',
        ],
        'tecnologias' => [
            'label' => 'Tecnologías',
            'route' => 'admin/tecnologias',
            'icon' => 'fa-microchip',
        ],
        'categorias' => [
            'label' => 'Categorías',
            'route' => 'admin/categorias',
            'icon' => 'fa-tags',
        ],
        'multimedia' => [
            'label' => 'Multimedia',
            'route' => 'admin/multimedia',
            'icon' => 'fa-photo-film',
        ],
        'mensajes' => [
            'label' => 'Mensajes',
            'route' => 'admin/mensajes',
            'icon' => 'fa-envelope',
            'badge' => '0',
        ],
        'configuracion' => [
            'label' => 'Configuración',
            'route' => 'admin/configuracion',
            'icon' => 'fa-sliders',
        ],
    ];

    private array $moduleMeta = [
        'proyectos' => [
            'eyebrow' => 'PORTFOLIO_CORE',
            'title' => 'Gestión de proyectos',
            'description' => 'Administra los proyectos que se mostrarán en el portafolio público.',
            'features' => ['Alta y edición de proyectos', 'Asignación de categorías y tecnologías', 'Galerías de imágenes y videos'],
            'action' => 'Nuevo proyecto',
            'icon' => 'fa-diagram-project',
        ],
        'tecnologias' => [
            'eyebrow' => 'TECH_STACK',
            'title' => 'Catálogo de tecnologías',
            'description' => 'Registra tecnologías con nombre, color hexadecimal e icono identificador.',
            'features' => ['Nombre de tecnología', 'Color hexadecimal', 'Icono o imagen representativa'],
            'action' => 'Nueva tecnología',
            'icon' => 'fa-microchip',
        ],
        'categorias' => [
            'eyebrow' => 'PROJECT_CLASS',
            'title' => 'Categorías de proyectos',
            'description' => 'Organiza el portafolio por tipo de solución o área técnica.',
            'features' => ['Web', 'Escritorio y móvil', 'Redes, infraestructura y ciberseguridad'],
            'action' => 'Nueva categoría',
            'icon' => 'fa-tags',
        ],
        'multimedia' => [
            'eyebrow' => 'MEDIA_VAULT',
            'title' => 'Biblioteca multimedia',
            'description' => 'Centraliza las fotografías, capturas y videos utilizados por los proyectos.',
            'features' => ['Carga múltiple', 'Vista previa', 'Asociación con proyectos'],
            'action' => 'Subir archivo',
            'icon' => 'fa-photo-film',
        ],
        'mensajes' => [
            'eyebrow' => 'INBOX_LINK',
            'title' => 'Mensajes de contacto',
            'description' => 'Consulta las solicitudes enviadas desde el formulario público.',
            'features' => ['Bandeja de entrada', 'Estado leído o pendiente', 'Datos de contacto del remitente'],
            'action' => 'Actualizar bandeja',
            'icon' => 'fa-envelope',
        ],
        'configuracion' => [
            'eyebrow' => 'SYSTEM_CONFIG',
            'title' => 'Configuración del portafolio',
            'description' => 'Ajusta la identidad, enlaces y preferencias generales del sitio.',
            'features' => ['Datos personales', 'Redes y enlaces externos', 'Preferencias del sistema'],
            'action' => 'Guardar cambios',
            'icon' => 'fa-sliders',
        ],
    ];

    public function __construct()
    {
        SessionManager::start();
        parent::__construct();

        $this->requireAuthentication();

        if (empty($_SESSION['admin_csrf_token'])) {
            $_SESSION['admin_csrf_token'] = bin2hex(
                random_bytes(32)
            );
        }
    }
    private function requireAuthentication(): void
    {
        $authenticated = !empty($_SESSION['autenticado']);
        $hasUser = !empty($_SESSION['usuario']['id_usuario']);

        if (!$authenticated || !$hasUser) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $_SESSION['ultima_actividad'] = time();
    }

    public function index(): void
    {
        $this->render('dashboard', 'Panel de control');
    }

    public function proyectos(): void
    {
        $this->render('proyectos', 'Proyectos');
    }

    public function tecnologias(): void
    {
        $this->render('tecnologias', 'Tecnologías');
    }

    public function categorias(): void
    {
        $this->render('categorias', 'Categorías');
    }

    public function multimedia(): void
    {
        $this->render('multimedia', 'Multimedia');
    }

    public function mensajes(): void
    {
        $this->render('mensajes', 'Mensajes');
    }

    public function configuracion(): void
    {
        $this->render('configuracion', 'Configuración');
    }



    private function render(string $activeModule, string $pageTitle): void
    {
        $sessionUser = $_SESSION['usuario'] ?? [];

        $userName = $sessionUser['nombre_mostrar']
            ?? $sessionUser['nombre_usuario']
            ?? 'Administrador';

        $userRole = $sessionUser['nombre_rol']
            ?? 'Sin rol';

        $data = [
            'title' => $pageTitle . ' | ' . TITLE,
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'menu' => $this->menu,
            'moduleMeta' => $this->moduleMeta[$activeModule] ?? null,
            'csrfToken' => $_SESSION['admin_csrf_token'],
            'csrfTokenSesion' => $_SESSION['csrf_token_sesion'] ?? '',
            'user' => [
                'name' => $userName,
                'role' => $userRole,
                'initials' => $this->getInitials($userName),
            ],
            'stats' => [
                ['label' => 'Proyectos', 'value' => '0', 'icon' => 'fa-diagram-project', 'accent' => 'cyan'],
                ['label' => 'Tecnologías', 'value' => '0', 'icon' => 'fa-microchip', 'accent' => 'pink'],
                ['label' => 'Categorías', 'value' => '0', 'icon' => 'fa-tags', 'accent' => 'yellow'],
                ['label' => 'Mensajes pendientes', 'value' => '0', 'icon' => 'fa-envelope', 'accent' => 'green'],
            ],
        ];

        $this->views->getView('Admin/Admin', 'index', $data);
    }

    private function getInitials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name)) ?: [];
        $initials = '';

        foreach (array_slice($words, 0, 2) as $word) {
            if ($word !== '') {
                $firstCharacter = function_exists('mb_substr')
                    ? mb_substr($word, 0, 1, 'UTF-8')
                    : substr($word, 0, 1);

                $initials .= function_exists('mb_strtoupper')
                    ? mb_strtoupper($firstCharacter, 'UTF-8')
                    : strtoupper($firstCharacter);
            }
        }

        return $initials !== '' ? $initials : 'AD';
    }
}
