<?php
class Query extends Conexion
{
    private $pdo, $con, $sql, $datos;

    public function __construct()
    {
        $this->pdo = new Conexion();
        $this->con = $this->pdo->conect();
    }

    public function select(string $sql, array $params = [])
    {
        try {
            $resul = $this->con->prepare($sql);
            $resul->execute($params);
            return $resul->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en select: ' . $e->getMessage());
            return false;
        }
    }

    public function selectAll(string $sql, array $params = [])
    {
        try {
            $resul = $this->con->prepare($sql);
            $resul->execute($params);
            return $resul->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error en selectAll: ' . $e->getMessage());
            return false;
        }
    }

    public function save(string $sql, array $datos)
    {
        try {
            $this->sql = $sql;
            $this->datos = $datos;
            $consulta = $this->con->prepare($this->sql);
            $resultado = $consulta->execute($this->datos);
            return $resultado ? 1 : 0;
        } catch (PDOException $e) {
            error_log('Error en save: ' . $e->getMessage());
            return 0;
        }
    }

    public function insertar(string $sql, array $datos)
    {
        try {
            $this->sql = $sql;
            $this->datos = $datos;
            $consulta = $this->con->prepare($this->sql);
            $resultado = $consulta->execute($this->datos);
            return $resultado ? $this->con->lastInsertId() : 0;
        } catch (PDOException $e) {
            error_log('Error en insertar: ' . $e->getMessage());
            return 0;
        }
    }

    public function ejecutar(string $sql, array $datos = []): array
    {
        try {
            $consulta = $this->con->prepare($sql);
            $consulta->execute($datos);

            return [
                'status' => true,
                'filas_afectadas' => $consulta->rowCount(),
                'error' => null,
            ];
        } catch (PDOException $e) {
            error_log(
                '[Query::ejecutar] SQL: ' . $sql
                    . ' | Datos: ' . json_encode(
                        $datos,
                        JSON_UNESCAPED_UNICODE
                    )
                    . ' | Error: ' . $e->getMessage()
            );

            return [
                'status' => false,
                'filas_afectadas' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function iniciarTransaccion()
    {
        return $this->con->beginTransaction();
    }

    public function confirmarTransaccion()
    {
        return $this->con->commit();
    }

    public function cancelarTransaccion()
    {
        if (!$this->con->inTransaction()) {
            return false;
        }

        return $this->con->rollBack();
    }
}
