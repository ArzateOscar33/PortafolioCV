<?php

class Sitio extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->configuracion();
    }

    public function configuracion()
    {
        $configuracion = $this->model->configuracionPublica();
        $redes = $this->model->redesPublicas();

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

        echo json_encode([
            'status' => true,
            'configuracion' => is_array($configuracion)
                ? $configuracion
                : [],
            'redes' => is_array($redes) ? $redes : [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        die();
    }
}
