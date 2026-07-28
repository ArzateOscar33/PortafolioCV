<?php

class ConfiguracionModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    public function obtenerConfiguracion()
    {
        $registros = $this->selectAll(
            "SELECT
                id_configuracion,
                grupo,
                clave,
                valor,
                tipo_valor,
                publica,
                actualizada_por,
                actualizada_en
             FROM configuracion_sitio
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

            $configuracion[$clave] = [
                'id_configuracion' => (int) ($registro['id_configuracion'] ?? 0),
                'grupo' => (string) ($registro['grupo'] ?? ''),
                'clave' => $clave,
                'valor' => $registro['valor'] ?? null,
                'tipo_valor' => (string) ($registro['tipo_valor'] ?? 'texto'),
                'publica' => (int) ($registro['publica'] ?? 0),
                'actualizada_por' => isset($registro['actualizada_por'])
                    ? (int) $registro['actualizada_por']
                    : null,
                'actualizada_en' => $registro['actualizada_en'] ?? null,
            ];
        }

        return $configuracion;
    }

    public function guardarConfiguracion(array $items, $idUsuario)
    {
        if (empty($items)) {
            return true;
        }

        $idUsuario = (int) $idUsuario;

        /*
     * Se utilizan UPDATE e INSERT explícitos.
     * Esto evita depender del ON DUPLICATE KEY UPDATE y permite
     * identificar exactamente cuál configuración produjo el error.
     */
        $sqlExiste = "SELECT id_configuracion
                  FROM configuracion_sitio
                  WHERE clave = ?
                  LIMIT 1";

        $sqlActualizar = "UPDATE configuracion_sitio
                      SET grupo = ?,
                          valor = ?,
                          tipo_valor = ?,
                          publica = ?,
                          actualizada_por = ?,
                          actualizada_en = CURRENT_TIMESTAMP
                      WHERE clave = ?";

        $sqlInsertar = "INSERT INTO configuracion_sitio (
                        grupo,
                        clave,
                        valor,
                        tipo_valor,
                        publica,
                        actualizada_por
                    ) VALUES (?, ?, ?, ?, ?, ?)";

        try {
            if (!$this->iniciarTransaccion()) {
                error_log(
                    '[ConfiguracionModel::guardarConfiguracion] '
                        . 'No fue posible iniciar la transacción.'
                );

                return false;
            }

            foreach ($items as $item) {
                $clave = (string) ($item['clave'] ?? '');

                if ($clave === '') {
                    error_log(
                        '[ConfiguracionModel::guardarConfiguracion] '
                            . 'Se recibió una configuración sin clave.'
                    );

                    $this->cancelarTransaccion();
                    return false;
                }

                $registroExistente = $this->select(
                    $sqlExiste,
                    [$clave]
                );

                if (
                    is_array($registroExistente)
                    && !empty($registroExistente['id_configuracion'])
                ) {
                    $resultado = $this->ejecutar(
                        $sqlActualizar,
                        [
                            $item['grupo'],
                            $item['valor'],
                            $item['tipo_valor'],
                            $item['publica'],
                            $idUsuario,
                            $clave,
                        ]
                    );
                } else {
                    $resultado = $this->ejecutar(
                        $sqlInsertar,
                        [
                            $item['grupo'],
                            $clave,
                            $item['valor'],
                            $item['tipo_valor'],
                            $item['publica'],
                            $idUsuario,
                        ]
                    );
                }

                if (empty($resultado['status'])) {
                    error_log(
                        '[ConfiguracionModel::guardarConfiguracion] '
                            . 'Clave: ' . $clave
                            . ' | Error: '
                            . ($resultado['error'] ?? 'Error SQL desconocido')
                    );

                    $this->cancelarTransaccion();
                    return false;
                }
            }

            if (!$this->confirmarTransaccion()) {
                error_log(
                    '[ConfiguracionModel::guardarConfiguracion] '
                        . 'No fue posible confirmar la transacción.'
                );

                return false;
            }

            return true;
        } catch (Throwable $e) {
            $this->cancelarTransaccion();

            error_log(
                '[ConfiguracionModel::guardarConfiguracion] '
                    . $e->getMessage()
            );

            return false;
        }
    }

    public function listarRedes()
    {
        $sql = "SELECT
                    id_red_social,
                    plataforma,
                    etiqueta,
                    url,
                    clase_icono,
                    color_hexadecimal,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM redes_sociales
                ORDER BY orden_visualizacion ASC, plataforma ASC";

        return $this->selectAll($sql);
    }

    public function obtenerRed($idRedSocial)
    {
        $sql = "SELECT
                    id_red_social,
                    plataforma,
                    etiqueta,
                    url,
                    clase_icono,
                    color_hexadecimal,
                    orden_visualizacion,
                    activa,
                    creada_en,
                    actualizada_en
                FROM redes_sociales
                WHERE id_red_social = ?";

        return $this->select($sql, [$idRedSocial]);
    }

    public function existeUrlRed($url, $idRedSocial = 0)
    {
        $sql = "SELECT id_red_social
                FROM redes_sociales
                WHERE url = ?";

        $parametros = [$url];

        if ($idRedSocial > 0) {
            $sql .= " AND id_red_social != ?";
            $parametros[] = $idRedSocial;
        }

        return $this->select($sql, $parametros);
    }

    public function registrarRed(array $datos)
    {
        $sql = "INSERT INTO redes_sociales (
                    plataforma,
                    etiqueta,
                    url,
                    clase_icono,
                    color_hexadecimal,
                    orden_visualizacion,
                    activa
                ) VALUES (?, ?, ?, ?, ?, ?, ?)";

        return $this->insertar($sql, [
            $datos['plataforma'],
            $datos['etiqueta'],
            $datos['url'],
            $datos['clase_icono'],
            $datos['color_hexadecimal'],
            $datos['orden_visualizacion'],
            $datos['activa'],
        ]);
    }

    public function modificarRed(array $datos, $idRedSocial)
    {
        $sql = "UPDATE redes_sociales
                SET plataforma = ?,
                    etiqueta = ?,
                    url = ?,
                    clase_icono = ?,
                    color_hexadecimal = ?,
                    orden_visualizacion = ?,
                    activa = ?
                WHERE id_red_social = ?";

        return $this->save($sql, [
            $datos['plataforma'],
            $datos['etiqueta'],
            $datos['url'],
            $datos['clase_icono'],
            $datos['color_hexadecimal'],
            $datos['orden_visualizacion'],
            $datos['activa'],
            $idRedSocial,
        ]) === 1;
    }

    public function cambiarEstadoRed($idRedSocial, $activa)
    {
        $sql = "UPDATE redes_sociales
                SET activa = ?
                WHERE id_red_social = ?";

        return $this->save($sql, [$activa, $idRedSocial]) === 1;
    }

    public function siguienteOrdenRed()
    {
        return $this->select(
            "SELECT COALESCE(MAX(orden_visualizacion), 0) + 1 AS siguiente_orden
             FROM redes_sociales"
        );
    }

    public function estadisticas()
    {
        $configuracion = $this->select(
            "SELECT
                COUNT(*) AS total_configuraciones,
                COALESCE(SUM(publica = 1), 0) AS publicas,
                MAX(actualizada_en) AS ultima_actualizacion
             FROM configuracion_sitio"
        );

        $redes = $this->select(
            "SELECT
                COUNT(*) AS total_redes,
                COALESCE(SUM(activa = 1), 0) AS redes_activas,
                COALESCE(SUM(activa = 0), 0) AS redes_inactivas
             FROM redes_sociales"
        );

        return array_merge(
            is_array($configuracion) ? $configuracion : [],
            is_array($redes) ? $redes : []
        );
    }

    public function registrarAuditoria(
        $idUsuario,
        $accion,
        $tipoEntidad,
        $idEntidad,
        $valoresAnteriores,
        $valoresNuevos,
        $direccionIp,
        $agenteUsuario
    ) {
        $sql = "INSERT INTO bitacora_auditoria (
                    id_usuario,
                    accion,
                    tipo_entidad,
                    id_entidad,
                    valores_anteriores,
                    valores_nuevos,
                    direccion_ip,
                    agente_usuario
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        return $this->save($sql, [
            $idUsuario > 0 ? $idUsuario : null,
            $accion,
            $tipoEntidad,
            $idEntidad > 0 ? $idEntidad : null,
            $valoresAnteriores !== null
                ? json_encode($valoresAnteriores, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
            $valoresNuevos !== null
                ? json_encode($valoresNuevos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
            $direccionIp ?: null,
            $agenteUsuario ?: null,
        ]) === 1;
    }
}
