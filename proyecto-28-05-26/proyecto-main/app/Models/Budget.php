<?php
class Budget {
    private $conn;
    private $table_name = "presupuestos_mensuales";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getMonthlyBudget($user_id, $month, $year) {
        $query = "SELECT monto_limite FROM " . $this->table_name . " 
                  WHERE id_usuario = :user_id AND mes = :month AND anio = :year LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id, ':month' => $month, ':year' => $year]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (float)$row['monto_limite'] : 0;
    }

    public function setMonthlyBudget($user_id, $month, $year, $limit) {
        $query = "INSERT INTO " . $this->table_name . " (id_usuario, mes, anio, monto_limite) 
                  VALUES (:uid, :mes, :anio, :limit)
                  ON DUPLICATE KEY UPDATE monto_limite = :limit2";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':uid' => $user_id,
            ':mes' => $month,
            ':anio' => $year,
            ':limit' => $limit,
            ':limit2' => $limit
        ]);
    }
}
?>
