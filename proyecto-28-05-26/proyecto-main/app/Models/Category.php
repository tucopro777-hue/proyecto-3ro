<?php
class Category {
    private $conn;
    private $table_name = "categorias";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_usuario = :user_id OR id_usuario IS NULL ORDER BY nombre_categoria ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($user_id, $nombre, $tipo, $color = '#c8f064') {
        $query = "INSERT INTO " . $this->table_name . " (id_usuario, nombre_categoria, tipo_categoria, color_hex) 
                  VALUES (:user_id, :nombre, :tipo, :color)";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([
            ':user_id' => $user_id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':color' => $color
        ])) {
            return ['success' => true, 'id' => $this->conn->lastInsertId(), 'nombre' => $nombre];
        }
        return false;
    }

    public function getExpensesByCategory($user_id, $month, $year) {
        $query = "SELECT c.nombre_categoria, c.color_hex, SUM(m.monto_real) as total
                  FROM movimientos_diarios m
                  JOIN categorias c ON m.id_categoria = c.id_categoria
                  WHERE m.id_usuario = :user_id 
                  AND m.tipo_movimiento = 'Gasto'
                  AND MONTH(m.fecha_hora) = :month
                  AND YEAR(m.fecha_hora) = :year
                  GROUP BY c.id_categoria";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id, ':month' => $month, ':year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
