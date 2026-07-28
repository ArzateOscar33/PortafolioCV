<?php

class SitioModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function configuracionPublica()
    {
        $registros = $this->selectAll(
            "SELECT clave, valor, tipo_valor
             FROM configuracion_sitio
             WHERE publica = 1
             ORDER BY grupo ASC, clave ASC"
        );

        if (!is_array($registros)) {
            return [];
        }

        $configuracion = [];

        foreach ($registros as $registro) {
            $clave = (string) ($registro['clave'] ?? '');

            if ($clave === '') {
                continue;
            }

            $configuracion[$clave] = $this->convertirValor(
                $registro['valor'] ?? null,
                (string) ($registro['tipo_valor'] ?? 'texto')
            );
        }

        return $configuracion;
    }

    public function redesPublicas()
    {
        $sql = "SELECT
                    id_red_social,
                    plataforma,
                    etiqueta,
                    url,
                    clase_icono,
                    color_hexadecimal,
                    orden_visualizacion
                FROM redes_sociales
                WHERE activa = 1
                ORDER BY orden_visualizacion ASC, plataforma ASC";

        return $this->selectAll($sql);
    }

    private function convertirValor($valor, $tipo)
    {
        if ($tipo === 'booleano') {
            return (string) $valor === '1';
        }

        if ($tipo === 'numero') {
            return is_numeric($valor) ? $valor + 0 : 0;
        }

        if ($tipo === 'json') {
            $decodificado = json_decode((string) $valor, true);
            return is_array($decodificado) ? $decodificado : [];
        }

        return $valor;
    }
}
