<?php
class Goal {
    private $conn;
    private $table_name = "metas_de_ahorro";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByUser($user_id) {
        $query = "SELECT id_meta, nombre_meta, monto_objetivo, monto_actual, fecha_limite, porcentaje_ahorro as porcentaje_ingreso 
                  FROM " . $this->table_name . " WHERE id_usuario = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id_meta, $user_id) {
        $query = "SELECT *, porcentaje_ahorro as porcentaje_ingreso FROM " . $this->table_name . " WHERE id_meta = :id_meta AND id_usuario = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id_meta' => $id_meta, ':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($user_id, $nombre, $objetivo, $actual, $fecha_limite, $porcentaje = 0) {
        $ahorro_auto = $porcentaje > 0 ? 1 : 0;
        $query = "INSERT INTO " . $this->table_name . " (id_usuario, nombre_meta, monto_objetivo, monto_actual, fecha_limite, porcentaje_ahorro, ahorro_automatico) 
                  VALUES (:user_id, :nombre, :objetivo, :actual, :fecha_limite, :porcentaje, :ahorro_auto)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':nombre' => $nombre,
            ':objetivo' => $objetivo,
            ':actual' => $actual,
            ':fecha_limite' => $fecha_limite ?: null,
            ':porcentaje' => $porcentaje,
            ':ahorro_auto' => $ahorro_auto
        ]);
    }

    public function getActiveWithPercentage($user_id) {
        $query = "SELECT id_meta, nombre_meta, monto_actual, monto_objetivo, porcentaje_ahorro as porcentaje_ingreso 
                  FROM " . $this->table_name . " 
                  WHERE id_usuario = :user_id AND ahorro_automatico = 1 AND monto_actual < monto_objetivo";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
