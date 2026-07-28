<?php

class Home extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = [
            'title' => 'Cyberpunk | Portafolio de Desarrollo',

            'description' => (
                'Portafolio profesional de desarrollo web, escritorio, '
                . 'redes, infraestructura y ciberseguridad.'
            ),

            'themeColor' => '#070711',

            'styles' => [
                'Assets/css/Principal/style.css',
            ],

            'scripts' => [
                'Assets/js/Principal/index.js',
                'Assets/js/Principal/site-config.js',
            ],
        ];

        $this->views->getView(
            'Principal/Home',
            'index',
            $data
        );
    }
}
