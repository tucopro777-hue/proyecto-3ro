<?php
class FixedExpense {
    private $conn;
    private $table_name = "gastos_programados";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_usuario = :user_id ORDER BY dia_pago ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($user_id, $nombre, $monto, $dia) {
        $query = "INSERT INTO " . $this->table_name . " (id_usuario, nombre_servicio, monto_fijo, dia_pago, estado) 
                  VALUES (:user_id, :nombre, :monto, :dia, 'Pendiente')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':dia', $dia);
        return $stmt->execute();
    }

    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_gasto_fijo = :id AND id_usuario = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }

    public function markAsPaid($id, $user_id) {
        $query = "UPDATE " . $this->table_name . " SET estado = 'Pagado' WHERE id_gasto_fijo = :id AND id_usuario = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
}
?>
