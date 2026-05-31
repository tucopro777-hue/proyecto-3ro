<?php
require_once 'config/Database.php';
require_once 'app/Models/User.php';

$database = new Database();
$conn = $database->getConnection();

// Limpiar tablas para evitar duplicados y conflictos de datos
try {
    $conn->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $conn->exec("TRUNCATE TABLE movimientos_diarios;");
    $conn->exec("TRUNCATE TABLE presupuestos;");
    $conn->exec("TRUNCATE TABLE metas_de_ahorro;");
    $conn->exec("TRUNCATE TABLE alertas;");
    $conn->exec("TRUNCATE TABLE cuentas;");
    $conn->exec("TRUNCATE TABLE categorias;");
    $conn->exec("TRUNCATE TABLE usuarios;");
    $conn->exec("SET FOREIGN_KEY_CHECKS = 1;");
} catch (Exception $e) {
    // Silencioso para permitir redirección
}

try {
    $userModel = new User($conn);

    // 1. Crear Usuarios de Prueba con Roles Específicos usando el nuevo modelo
    $usuarios = [
        ['nombre' => 'Sebastian', 'email' => 'sebastian@test.com', 'pass' => 'super123', 'rol' => 1],
        ['nombre' => 'Brando', 'email' => 'brando@test.com', 'pass' => 'admin123', 'rol' => 2],
        ['nombre' => 'Samuel Cliente', 'email' => 'samuel@test.com', 'pass' => '12345678', 'rol' => 3],
    ];

    foreach ($usuarios as $u) {
        $userModel->create($u['nombre'], $u['email'], $u['pass'], $u['rol']);
    }

    // Obtener todos los usuarios recién creados
    $stmt = $conn->query("SELECT id_usuario FROM usuarios");
    $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Categorías base (si no existen)
    $categorias_base = [
        ['nombre' => 'Alimentación', 'tipo' => 'Gasto', 'color' => '#f46060'],
        ['nombre' => 'Transporte', 'tipo' => 'Gasto', 'color' => '#f4a24a'],
        ['nombre' => 'Sueldo', 'tipo' => 'Ingreso', 'color' => '#4af4c2'],
        ['nombre' => 'Educación', 'tipo' => 'Gasto', 'color' => '#4ab4f4'],
        ['nombre' => 'Ocio', 'tipo' => 'Gasto', 'color' => '#b07af4'],
    ];

    foreach ($userIds as $uid) {
        foreach ($categorias_base as $cat) {
            $stmt = $conn->prepare("INSERT INTO categorias (id_usuario, nombre_categoria, tipo_movimiento, color_hex) VALUES (?, ?, ?, ?)");
            $stmt->execute([$uid, $cat['nombre'], $cat['tipo'], $cat['color']]);
        }

        // 3. Generar Movimientos (Ingresos y Gastos)
        $stmt = $conn->prepare("SELECT id_categoria, nombre_categoria, tipo_movimiento FROM categorias WHERE id_usuario = ?");
        $stmt->execute([$uid]);
        $userCats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $catIds = [];
        foreach($userCats as $row) $catIds[$row['nombre_categoria']] = $row;

        // Ingreso principal
        $stmt = $conn->prepare("INSERT INTO movimientos_diarios (id_usuario, id_categoria, monto_real, tipo_movimiento, notas, fecha_hora) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uid, $catIds['Sueldo']['id_categoria'], 5000.00, 'Ingreso', 'Sueldo Mensual Mayo', '2026-05-01 09:00:00']);

        // Gastos aleatorios
        $stmt_g = $conn->prepare("INSERT INTO movimientos_diarios (id_usuario, id_categoria, monto_real, tipo_movimiento, notas, fecha_hora) VALUES (?, ?, ?, ?, ?, ?)");
        $gastos = [
            ['cat' => 'Alimentación', 'monto' => 35.50, 'nota' => 'Almuerzo ejecutivo'],
            ['cat' => 'Transporte', 'monto' => 10.00, 'nota' => 'Pasajes trufi'],
        ];
        foreach ($gastos as $g) {
            $stmt_g->execute([$uid, $catIds[$g['cat']]['id_categoria'], $g['monto'], 'Gasto', $g['nota'], date('Y-m-d H:i:s')]);
        }

        // 4. Metas de Ahorro
        $stmt = $conn->prepare("INSERT INTO metas_de_ahorro (id_usuario, nombre_meta, monto_objetivo, monto_actual, fecha_limite) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$uid, 'Fondo de Emergencia', 2000.00, 500.00, '2026-12-31']);
    }

    // Redirección directa al login tras el éxito
    header("Location: index.php?action=login");
    exit;

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
