<?php
class Group {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        $this->ensureTablesExist();
    }

    private function ensureTablesExist() {
        $sql = "CREATE TABLE IF NOT EXISTS `grupos` (
            `id_grupo` int NOT NULL AUTO_INCREMENT,
            `id_supervisor` int NOT NULL,
            `nombre_grupo` varchar(100) NOT NULL,
            `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_grupo`),
            CONSTRAINT `grupos_ibfk_1` FOREIGN KEY (`id_supervisor`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

        CREATE TABLE IF NOT EXISTS `miembros_grupo` (
            `id_miembro` int NOT NULL AUTO_INCREMENT,
            `id_grupo` int NOT NULL,
            `id_usuario` int NOT NULL,
            `rango` varchar(50) DEFAULT 'Miembro',
            `fecha_union` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_miembro`),
            UNIQUE KEY `idx_user_group` (`id_grupo`, `id_usuario`),
            CONSTRAINT `miembros_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupos` (`id_grupo`) ON DELETE CASCADE,
            CONSTRAINT `miembros_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        try {
            $this->conn->exec($sql);
        } catch (PDOException $e) {}
    }

    public function create($id_supervisor, $nombre) {
        $query = "INSERT INTO grupos (id_supervisor, nombre_grupo) VALUES (:sup, :nombre)";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute([':sup' => $id_supervisor, ':nombre' => $nombre])) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getBySupervisor($id_supervisor) {
        $query = "SELECT * FROM grupos WHERE id_supervisor = :sup";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':sup' => $id_supervisor]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMembers($id_grupo) {
        $query = "SELECT u.id_usuario, u.nombre, u.email, m.rango, m.fecha_union,
                  COALESCE((SELECT SUM(monto_real) FROM movimientos_diarios WHERE id_usuario = u.id_usuario AND tipo_movimiento = 'Ingreso' AND MONTH(fecha_hora) = MONTH(CURDATE())), 0) as ingresos_mes,
                  COALESCE((SELECT SUM(monto_real) FROM movimientos_diarios WHERE id_usuario = u.id_usuario AND tipo_movimiento = 'Gasto' AND MONTH(fecha_hora) = MONTH(CURDATE())), 0) as gastos_mes
                  FROM miembros_grupo m
                  JOIN usuarios u ON m.id_usuario = u.id_usuario
                  WHERE m.id_grupo = :gid";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':gid' => $id_grupo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addMember($id_grupo, $identifier, $rango = 'Miembro') {
        // Buscar usuario por email o nombre
        $query_u = "SELECT id_usuario FROM usuarios WHERE email = :id OR nombre = :id LIMIT 1";
        $stmt_u = $this->conn->prepare($query_u);
        $stmt_u->execute([':id' => $identifier]);
        $user = $stmt_u->fetch(PDO::FETCH_ASSOC);

        if (!$user) return ['success' => false, 'message' => 'Usuario no encontrado.'];

        $query = "INSERT INTO miembros_grupo (id_grupo, id_usuario, rango) VALUES (:gid, :uid, :rango)";
        $stmt = $this->conn->prepare($query);
        try {
            return ['success' => $stmt->execute([':gid' => $id_grupo, ':uid' => $user['id_usuario'], ':rango' => $rango])];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'El usuario ya pertenece a este grupo.'];
        }
    }
}
?>
