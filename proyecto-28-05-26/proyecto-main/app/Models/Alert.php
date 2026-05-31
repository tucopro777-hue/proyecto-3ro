<?php
class Alert {
    private $conn;
    private $table_name = "alertas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getActiveByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_usuario = :user_id AND leido = 0 
                  ORDER BY fecha_alerta DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByUser($user_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE id_usuario = :user_id 
                  ORDER BY fecha_alerta DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($user_id, $mensaje, $prioridad = 'Media') {
        // Evitar duplicados exactos en el mismo día
        $query_check = "SELECT id_alerta FROM " . $this->table_name . " 
                        WHERE id_usuario = :user_id AND mensaje = :mensaje 
                        AND DATE(fecha_alerta) = CURDATE() AND leido = 0 LIMIT 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->execute([':user_id' => $user_id, ':mensaje' => $mensaje]);
        if ($stmt_check->rowCount() > 0) return true;

        $query = "INSERT INTO " . $this->table_name . " (id_usuario, mensaje, prioridad) 
                  VALUES (:user_id, :mensaje, :prioridad)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':mensaje' => $mensaje,
            ':prioridad' => $prioridad
        ]);
    }

    public function markAllAsRead($user_id) {
        $query = "UPDATE " . $this->table_name . " SET leido = 1 WHERE id_usuario = :user_id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':user_id' => $user_id]);
    }

    public function markAsRead($id_alerta, $user_id) {
        $query = "UPDATE " . $this->table_name . " SET leido = 1 WHERE id_alerta = :id AND id_usuario = :uid";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id_alerta, ':uid' => $user_id]);
    }

    public function delete($id_alerta, $user_id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_alerta = :id AND id_usuario = :uid";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id_alerta, ':uid' => $user_id]);
    }
}
?>
