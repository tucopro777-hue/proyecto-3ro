<?php
class Movement {
    private $conn;
    private $table_name = "movimientos_diarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getKPIs($user_id, $month = null, $year = null) {
        $query = "SELECT 
            SUM(CASE WHEN tipo_movimiento = 'Ingreso' THEN monto_real ELSE 0 END) as total_ingresos,
            SUM(CASE WHEN tipo_movimiento = 'Gasto' THEN monto_real ELSE 0 END) as total_gastos
            FROM " . $this->table_name . " WHERE id_usuario = :user_id";
        
        if ($month && $year) {
            $query .= " AND MONTH(fecha_hora) = :month AND YEAR(fecha_hora) = :year";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        if ($month && $year) {
            $stmt->bindParam(':month', $month);
            $stmt->bindParam(':year', $year);
        }
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRecent($user_id, $limit = 10, $month = null, $year = null) {
        $query = "SELECT m.*, c.nombre_categoria, acc.nombre_cuenta, cur.codigo as moneda_codigo
            FROM " . $this->table_name . " m 
            LEFT JOIN categorias c ON m.id_categoria = c.id_categoria 
            JOIN cuentas acc ON m.id_cuenta = acc.id_cuenta
            JOIN monedas cur ON acc.id_moneda = cur.id_moneda
            WHERE m.id_usuario = :user_id";
        
        if ($month && $year) {
            $query .= " AND MONTH(m.fecha_hora) = :month AND YEAR(m.fecha_hora) = :year";
        }
        
        $query .= " ORDER BY m.fecha_hora DESC LIMIT :limit";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        if ($month && $year) {
            $stmt->bindParam(':month', $month);
            $stmt->bindParam(':year', $year);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll($user_id) {
        $query = "SELECT m.*, c.nombre_categoria, acc.nombre_cuenta, cur.codigo as moneda_codigo
            FROM " . $this->table_name . " m 
            LEFT JOIN categorias c ON m.id_categoria = c.id_categoria 
            JOIN cuentas acc ON m.id_cuenta = acc.id_cuenta
            JOIN monedas cur ON acc.id_moneda = cur.id_moneda
            WHERE m.id_usuario = :user_id 
            ORDER BY m.fecha_hora DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($user_id, $tipo, $monto, $categoria_id, $es_ocasional, $notas, $id_cuenta) {
        $query = "INSERT INTO " . $this->table_name . " (id_usuario, id_cuenta, tipo_movimiento, monto_real, id_categoria, es_ocasional, notas) 
                  VALUES (:user_id, :id_cuenta, :tipo, :monto, :categoria_id, :es_ocasional, :notas)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':id_cuenta' => $id_cuenta,
            ':tipo' => $tipo,
            ':monto' => $monto,
            ':categoria_id' => $categoria_id,
            ':es_ocasional' => $es_ocasional,
            ':notas' => $notas
        ]);
    }
    
    public function getByType($user_id, $tipo) {
        $query = "SELECT m.*, c.nombre_categoria, acc.nombre_cuenta, cur.codigo as moneda_codigo
            FROM " . $this->table_name . " m 
            LEFT JOIN categorias c ON m.id_categoria = c.id_categoria 
            JOIN cuentas acc ON m.id_cuenta = acc.id_cuenta
            JOIN monedas cur ON acc.id_moneda = cur.id_moneda
            WHERE m.id_usuario = :user_id AND m.tipo_movimiento = :tipo 
            ORDER BY m.fecha_hora DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id, ':tipo' => $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllSystem() {
        $query = "SELECT m.*, c.nombre_categoria, u.nombre as nombre_usuario, acc.nombre_cuenta
            FROM " . $this->table_name . " m 
            LEFT JOIN categorias c ON m.id_categoria = c.id_categoria 
            JOIN usuarios u ON m.id_usuario = u.id_usuario
            JOIN cuentas acc ON m.id_cuenta = acc.id_cuenta
            ORDER BY m.fecha_hora DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
