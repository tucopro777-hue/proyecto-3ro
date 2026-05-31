<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByEmail($email) {
        $query = "SELECT u.id_usuario, u.nombre, u.email, u.password, u.id_rol, r.nombre_rol 
                  FROM " . $this->table_name . " u 
                  LEFT JOIN roles r ON u.id_rol = r.id_rol 
                  WHERE u.email = :email LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($nombre, $email, $password, $rol_id = 2) {
        try {
            $this->conn->beginTransaction();

            // rol_id = 2 es el Supervisor, que ahora es el usuario común por defecto
            $query = "INSERT INTO " . $this->table_name . " (nombre, email, password, id_rol) VALUES (:nombre, :email, :password, :rol_id)";
            $stmt = $this->conn->prepare($query);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':rol_id', $rol_id);
            $stmt->execute();
            
            $new_user_id = $this->conn->lastInsertId();

            // Crear cuenta predeterminada para el nuevo usuario
            $query_cuenta = "INSERT INTO cuentas (id_usuario, nombre_cuenta, id_moneda, saldo_actual) VALUES (:user_id, 'Efectivo', 1, 0.00)";
            $stmt_c = $this->conn->prepare($query_cuenta);
            $stmt_c->execute([':user_id' => $new_user_id]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function getAll() {
        $query = "SELECT u.id_usuario, u.nombre, u.email, r.nombre_rol, u.id_rol 
                  FROM " . $this->table_name . " u 
                  JOIN roles r ON u.id_rol = r.id_rol 
                  ORDER BY u.id_usuario DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllWithSummaries() {
        $query = "SELECT u.id_usuario, u.nombre, u.email, r.nombre_rol, u.id_rol,
                  COALESCE((SELECT SUM(monto_real) FROM movimientos_diarios WHERE id_usuario = u.id_usuario AND tipo_movimiento = 'Ingreso'), 0) as total_ingresos,
                  COALESCE((SELECT SUM(monto_real) FROM movimientos_diarios WHERE id_usuario = u.id_usuario AND tipo_movimiento = 'Gasto'), 0) as total_gastos,
                  (SELECT COUNT(*) FROM gastos_programados WHERE id_usuario = u.id_usuario) as total_gastos_fijos
                  FROM " . $this->table_name . " u 
                  JOIN roles r ON u.id_rol = r.id_rol 
                  ORDER BY u.id_usuario DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_usuario = :id AND id_rol != 1";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }
}
?>
