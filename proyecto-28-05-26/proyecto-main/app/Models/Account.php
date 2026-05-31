<?php
class Account {
    private $conn;
    private $table_name = "cuentas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByUser($user_id) {
        $query = "SELECT c.*, m.codigo, m.nombre_moneda, m.tipo_cambio_bs 
                  FROM " . $this->table_name . " c
                  JOIN monedas m ON c.id_moneda = m.id_moneda
                  WHERE c.id_usuario = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($user_id, $nombre, $id_moneda, $saldo_inicial = 0) {
        $query = "INSERT INTO " . $this->table_name . " (id_usuario, nombre_cuenta, id_moneda, saldo_actual) 
                  VALUES (:user_id, :nombre, :id_moneda, :saldo)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':nombre' => $nombre,
            ':id_moneda' => $id_moneda,
            ':saldo' => $saldo_inicial
        ]);
    }
}
?>
