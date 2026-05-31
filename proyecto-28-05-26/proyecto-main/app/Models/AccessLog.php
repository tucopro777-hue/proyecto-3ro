<?php
class AccessLog {
    private $conn;
    private $table_name = "bitacora_accesos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function register($user_id) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $browser = $_SERVER['HTTP_USER_AGENT'];
        $query = "INSERT INTO " . $this->table_name . " (id_usuario, ip_origen, navegador) VALUES (:uid, :ip, :browser)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':uid' => $user_id, ':ip' => $ip, ':browser' => $browser]);
    }
}
?>
