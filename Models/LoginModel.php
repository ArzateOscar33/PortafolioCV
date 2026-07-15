<?php

/**
 * Modelo de autenticación del panel administrativo.
 *
 * Responsabilidades:
 * - Consultar usuarios por nombre de usuario o correo.
 * - Controlar intentos fallidos y bloqueos temporales.
 * - Registrar accesos exitosos.
 * - Crear, consultar y revocar sesiones persistentes.
 * - Registrar eventos de autenticación en la bitácora.
 *
 * La contraseña se valida en el controlador mediante password_verify().
 */
class LoginModel extends Query
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Busca un usuario por nombre de usuario o correo electrónico.
     *
     * No filtra por estado para que el controlador pueda distinguir entre:
     * activo, inactivo y bloqueado.
     *
     * @return array|false
     */
    public function obtenerUsuarioPorIdentificador(string $identificador)
    {
        $identificador = trim($identificador);

        if ($identificador === '') {
            return false;
        }

        $sql = "SELECT
                    u.id_usuario,
                    u.id_rol,
                    r.nombre AS nombre_rol,
                    u.nombre_usuario,
                    u.correo,
                    u.contrasena_hash,
                    u.nombre_mostrar,
                    u.ruta_avatar,
                    u.estado,
                    u.intentos_fallidos,
                    u.bloqueado_hasta,
                    u.ultimo_acceso_en,
                    u.creado_en,
                    u.actualizado_en
                FROM usuarios AS u
                INNER JOIN roles AS r
                    ON r.id_rol = u.id_rol
                   AND r.activo = 1
                WHERE (
                    u.nombre_usuario = :nombre_usuario
                    OR u.correo = :correo
                )
                  AND u.eliminado_en IS NULL
                LIMIT 1";

        return $this->select($sql, [
            ':nombre_usuario' => $identificador,
            ':correo' => $identificador,
        ]);
    }

    /**
     * Obtiene los datos básicos de un usuario por su ID.
     *
     * Este método puede usarse para reconstruir o validar una sesión.
     *
     * @return array|false
     */
    public function obtenerUsuarioPorId(int $idUsuario)
    {
        if ($idUsuario <= 0) {
            return false;
        }

        $sql = "SELECT
                    u.id_usuario,
                    u.id_rol,
                    r.nombre AS nombre_rol,
                    u.nombre_usuario,
                    u.correo,
                    u.nombre_mostrar,
                    u.ruta_avatar,
                    u.estado,
                    u.intentos_fallidos,
                    u.bloqueado_hasta,
                    u.ultimo_acceso_en
                FROM usuarios AS u
                INNER JOIN roles AS r
                    ON r.id_rol = u.id_rol
                   AND r.activo = 1
                WHERE u.id_usuario = :id_usuario
                  AND u.eliminado_en IS NULL
                LIMIT 1";

        return $this->select($sql, [
            ':id_usuario' => $idUsuario,
        ]);
    }

    /**
     * Libera automáticamente un bloqueo temporal que ya venció.
     *
     * No desbloquea cuentas bloqueadas manualmente, porque esas cuentas
     * tendrían bloqueado_hasta en NULL.
     */
    public function liberarBloqueoExpirado(int $idUsuario): bool
    {
        if ($idUsuario <= 0) {
            return false;
        }

        $sql = "UPDATE usuarios
                SET estado = 'activo',
                    intentos_fallidos = 0,
                    bloqueado_hasta = NULL,
                    actualizado_en = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario
                  AND estado = 'bloqueado'
                  AND bloqueado_hasta IS NOT NULL
                  AND bloqueado_hasta <= CURRENT_TIMESTAMP
                  AND eliminado_en IS NULL";

        return $this->save($sql, [
            ':id_usuario' => $idUsuario,
        ]) === 1;
    }

    /**
     * Incrementa los intentos fallidos y bloquea temporalmente la cuenta
     * cuando alcanza el máximo permitido.
     */
    public function registrarIntentoFallido(
        int $idUsuario,
        int $maximoIntentos = 5,
        int $minutosBloqueo = 15
    ): bool {
        if (
            $idUsuario <= 0
            || $maximoIntentos < 1
            || $minutosBloqueo < 1
        ) {
            return false;
        }

        $bloqueadoHasta = (new DateTimeImmutable())
            ->modify('+' . $minutosBloqueo . ' minutes')
            ->format('Y-m-d H:i:s');

        $sql = "UPDATE usuarios
                SET intentos_fallidos = intentos_fallidos + 1,
                    bloqueado_hasta = CASE
                        WHEN intentos_fallidos + 1 >= :maximo_bloqueo
                        THEN :bloqueado_hasta
                        ELSE bloqueado_hasta
                    END,
                    estado = CASE
                        WHEN intentos_fallidos + 1 >= :maximo_estado
                        THEN 'bloqueado'
                        ELSE estado
                    END,
                    actualizado_en = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario
                  AND estado = 'activo'
                  AND eliminado_en IS NULL";

        return $this->save($sql, [
            ':maximo_bloqueo' => $maximoIntentos,
            ':bloqueado_hasta' => $bloqueadoHasta,
            ':maximo_estado' => $maximoIntentos,
            ':id_usuario' => $idUsuario,
        ]) === 1;
    }

    /**
     * Reinicia los intentos fallidos y registra la fecha del acceso.
     *
     * Debe ejecutarse solamente después de password_verify().
     */
    public function registrarAccesoExitoso(int $idUsuario): bool
    {
        if ($idUsuario <= 0) {
            return false;
        }

        $sql = "UPDATE usuarios
                SET intentos_fallidos = 0,
                    bloqueado_hasta = NULL,
                    ultimo_acceso_en = CURRENT_TIMESTAMP,
                    actualizado_en = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario
                  AND estado = 'activo'
                  AND eliminado_en IS NULL";

        return $this->save($sql, [
            ':id_usuario' => $idUsuario,
        ]) === 1;
    }

    /**
     * Actualiza el hash de contraseña después de password_needs_rehash().
     */
    public function actualizarHashContrasena(
        int $idUsuario,
        string $nuevoHash
    ): bool {
        $nuevoHash = trim($nuevoHash);

        if ($idUsuario <= 0 || $nuevoHash === '') {
            return false;
        }

        $sql = "UPDATE usuarios
                SET contrasena_hash = :contrasena_hash,
                    actualizado_en = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario
                  AND eliminado_en IS NULL";

        return $this->save($sql, [
            ':contrasena_hash' => $nuevoHash,
            ':id_usuario' => $idUsuario,
        ]) === 1;
    }

    /**
     * Crea una sesión persistente.
     *
     * El controlador debe generar un token aleatorio, guardar solamente
     * hash('sha256', $token) y enviar el token original en una cookie segura.
     *
     * @return int ID de sesión creado, o 0 si falla.
     */
    public function crearSesion(
        int $idUsuario,
        string $tokenHash,
        ?string $direccionIp,
        ?string $agenteUsuario,
        DateTimeInterface $expiraEn
    ): int {
        $tokenHash = strtolower(trim($tokenHash));

        if (
            $idUsuario <= 0
            || preg_match('/^[a-f0-9]{64}$/', $tokenHash) !== 1
        ) {
            return 0;
        }

        $direccionIp = $this->limitarTexto($direccionIp, 45);
        $agenteUsuario = $this->limitarTexto($agenteUsuario, 500);

        $sql = "INSERT INTO sesiones_usuario (
                    id_usuario,
                    token_hash,
                    direccion_ip,
                    agente_usuario,
                    ultima_actividad_en,
                    expira_en,
                    creada_en
                ) VALUES (
                    :id_usuario,
                    :token_hash,
                    :direccion_ip,
                    :agente_usuario,
                    CURRENT_TIMESTAMP,
                    :expira_en,
                    CURRENT_TIMESTAMP
                )";

        return (int) $this->insertar($sql, [
            ':id_usuario' => $idUsuario,
            ':token_hash' => $tokenHash,
            ':direccion_ip' => $direccionIp,
            ':agente_usuario' => $agenteUsuario,
            ':expira_en' => $expiraEn->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Busca una sesión válida mediante el hash del token.
     *
     * @return array|false
     */
    public function obtenerSesionActiva(string $tokenHash)
    {
        $tokenHash = strtolower(trim($tokenHash));

        if (preg_match('/^[a-f0-9]{64}$/', $tokenHash) !== 1) {
            return false;
        }

        $sql = "SELECT
                    s.id_sesion,
                    s.id_usuario,
                    s.token_hash,
                    s.direccion_ip,
                    s.agente_usuario,
                    s.ultima_actividad_en,
                    s.expira_en,
                    u.id_rol,
                    r.nombre AS nombre_rol,
                    u.nombre_usuario,
                    u.correo,
                    u.nombre_mostrar,
                    u.ruta_avatar
                FROM sesiones_usuario AS s
                INNER JOIN usuarios AS u
                    ON u.id_usuario = s.id_usuario
                   AND u.estado = 'activo'
                   AND u.eliminado_en IS NULL
                INNER JOIN roles AS r
                    ON r.id_rol = u.id_rol
                   AND r.activo = 1
                WHERE s.token_hash = :token_hash
                  AND s.revocada_en IS NULL
                  AND s.expira_en > CURRENT_TIMESTAMP
                LIMIT 1";

        return $this->select($sql, [
            ':token_hash' => $tokenHash,
        ]);
    }

    /**
     * Actualiza la última actividad de una sesión válida.
     */
    public function actualizarActividadSesion(int $idSesion): bool
    {
        if ($idSesion <= 0) {
            return false;
        }

        $sql = "UPDATE sesiones_usuario
                SET ultima_actividad_en = CURRENT_TIMESTAMP
                WHERE id_sesion = :id_sesion
                  AND revocada_en IS NULL
                  AND expira_en > CURRENT_TIMESTAMP";

        return $this->save($sql, [
            ':id_sesion' => $idSesion,
        ]) === 1;
    }

    /**
     * Revoca una sesión a partir de su hash.
     */
    public function revocarSesion(string $tokenHash): bool
    {
        $tokenHash = strtolower(trim($tokenHash));

        if (preg_match('/^[a-f0-9]{64}$/', $tokenHash) !== 1) {
            return false;
        }

        $sql = "UPDATE sesiones_usuario
                SET revocada_en = CURRENT_TIMESTAMP
                WHERE token_hash = :token_hash
                  AND revocada_en IS NULL";

        return $this->save($sql, [
            ':token_hash' => $tokenHash,
        ]) === 1;
    }

    /**
     * Revoca todas las sesiones abiertas de un usuario.
     */
    public function revocarSesionesUsuario(int $idUsuario): bool
    {
        if ($idUsuario <= 0) {
            return false;
        }

        $sql = "UPDATE sesiones_usuario
                SET revocada_en = CURRENT_TIMESTAMP
                WHERE id_usuario = :id_usuario
                  AND revocada_en IS NULL";

        return $this->save($sql, [
            ':id_usuario' => $idUsuario,
        ]) === 1;
    }

    /**
     * Elimina sesiones antiguas que ya expiraron o fueron revocadas.
     *
     * Puede ejecutarse ocasionalmente al iniciar sesión o mediante cron.
     */
    public function eliminarSesionesInactivas(int $diasConservacion = 30): bool
    {
        if ($diasConservacion < 1) {
            return false;
        }

        $fechaLimite = (new DateTimeImmutable())
            ->modify('-' . $diasConservacion . ' days')
            ->format('Y-m-d H:i:s');

        $sql = "DELETE FROM sesiones_usuario
                WHERE (
                    expira_en < :fecha_limite_expiracion
                    OR (
                        revocada_en IS NOT NULL
                        AND revocada_en < :fecha_limite_revocacion
                    )
                )";

        return $this->save($sql, [
            ':fecha_limite_expiracion' => $fechaLimite,
            ':fecha_limite_revocacion' => $fechaLimite,
        ]) === 1;
    }

    /**
     * Registra una acción de autenticación en la bitácora.
     *
     * Nunca deben enviarse contraseñas, tokens originales ni cookies
     * dentro de valoresAnteriores o valoresNuevos.
     */
    public function registrarAuditoria(
        ?int $idUsuario,
        string $accion,
        string $tipoEntidad = 'usuarios',
        ?int $idEntidad = null,
        ?array $valoresAnteriores = null,
        ?array $valoresNuevos = null,
        ?string $direccionIp = null,
        ?string $agenteUsuario = null
    ): bool {
        $accion = trim($accion);
        $tipoEntidad = trim($tipoEntidad);

        if ($accion === '' || $tipoEntidad === '') {
            return false;
        }

        $sql = "INSERT INTO bitacora_auditoria (
                    id_usuario,
                    accion,
                    tipo_entidad,
                    id_entidad,
                    valores_anteriores,
                    valores_nuevos,
                    direccion_ip,
                    agente_usuario,
                    creado_en
                ) VALUES (
                    :id_usuario,
                    :accion,
                    :tipo_entidad,
                    :id_entidad,
                    :valores_anteriores,
                    :valores_nuevos,
                    :direccion_ip,
                    :agente_usuario,
                    CURRENT_TIMESTAMP
                )";

        return $this->insertar($sql, [
            ':id_usuario' => $idUsuario,
            ':accion' => $this->limitarTexto($accion, 60),
            ':tipo_entidad' => $this->limitarTexto($tipoEntidad, 80),
            ':id_entidad' => $idEntidad,
            ':valores_anteriores' => $this->codificarJson($valoresAnteriores),
            ':valores_nuevos' => $this->codificarJson($valoresNuevos),
            ':direccion_ip' => $this->limitarTexto($direccionIp, 45),
            ':agente_usuario' => $this->limitarTexto($agenteUsuario, 500),
        ]) > 0;
    }

    /**
     * Limita texto opcional al tamaño permitido por la columna.
     */
    private function limitarTexto(?string $texto, int $longitud): ?string
    {
        if ($texto === null) {
            return null;
        }

        $texto = trim($texto);

        if ($texto === '') {
            return null;
        }

        if (function_exists('mb_substr')) {
            return mb_substr($texto, 0, $longitud, 'UTF-8');
        }

        return substr($texto, 0, $longitud);
    }

    /**
     * Convierte arreglos a JSON para las columnas de auditoría.
     */
    private function codificarJson(?array $datos): ?string
    {
        if ($datos === null) {
            return null;
        }

        $json = json_encode(
            $datos,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        return $json === false ? null : $json;
    }
}
