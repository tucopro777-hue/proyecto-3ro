<?php
class ScheduledIncome {
    private $db;
    private $table = 'ingresos_programados';

    public function __construct($db) {
        $this->db = $db;
        $this->ensureTableExists();
    }

    private function ensureTableExists() {
        $sql = "CREATE TABLE IF NOT EXISTS " . $this->table . " (
            id_ingreso_programado int NOT NULL AUTO_INCREMENT,
            id_usuario int DEFAULT NULL,
            nombre varchar(100) DEFAULT NULL,
            monto decimal(15,2) DEFAULT NULL,
            intervalo enum('Semanal','Quincenal','Mensual','Personalizado') DEFAULT 'Mensual',
            intervalo_personalizado int DEFAULT NULL,
            fecha_inicio date DEFAULT NULL,
            fecha_limite date DEFAULT NULL,
            estado enum('Activo','Inactivo') DEFAULT 'Activo',
            fecha_registro timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id_ingreso_programado),
            KEY id_usuario (id_usuario)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        try {
            $this->db->exec($sql);
            
            // Migration: Check if duracion exists and convert to fecha_limite if necessary
            // (Simplified for this exercise: we just ensure the column exists)
            $checkCol = "SHOW COLUMNS FROM " . $this->table . " LIKE 'fecha_limite'";
            $res = $this->db->query($checkCol)->fetch();
            if (!$res) {
                $this->db->exec("ALTER TABLE " . $this->table . " ADD COLUMN fecha_limite DATE AFTER fecha_inicio");
                $this->db->exec("ALTER TABLE " . $this->table . " DROP COLUMN duracion");
            }
        } catch (PDOException $e) {}
    }

    public function create($user_id, $nombre, $monto, $intervalo, $intervalo_personalizado = null, $fecha_limite = null) {
        $query = "INSERT INTO " . $this->table . " (id_usuario, nombre, monto, intervalo, intervalo_personalizado, fecha_limite) 
                  VALUES (:user_id, :nombre, :monto, :intervalo, :intervalo_personalizado, :fecha_limite)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':nombre' => $nombre,
            ':monto' => $monto,
            ':intervalo' => $intervalo,
            ':intervalo_personalizado' => $intervalo_personalizado,
            ':fecha_limite' => $fecha_limite
        ]);
    }

    public function getByUser($user_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id_usuario = :user_id AND estado = 'Activo' ORDER BY id_ingreso_programado DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
