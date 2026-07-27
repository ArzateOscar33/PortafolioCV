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
