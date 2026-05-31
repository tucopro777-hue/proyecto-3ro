<?php
require_once 'config/Database.php';

echo "<h1>Prueba de Conexión a la Base de Datos</h1>";

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "<p style='color: green; font-weight: bold;'>¡Conexión exitosa!</p>";
        echo "<ul>";
        echo "<li><b>Host:</b> localhost</li>";
        echo "<li><b>Base de datos:</b> finsight_db</li>";
        echo "</ul>";
        
        // Intentar una consulta simple para verificar que las tablas existen
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($tables) > 0) {
            echo "<p>Tablas encontradas en <b>finsight_db</b>:</p><ul>";
            foreach ($tables as $table) {
                echo "<li>$table</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color: orange;'>La conexión funciona, pero no se encontraron tablas. Asegúrate de haber importado el archivo SQL.</p>";
        }
    } else {
        echo "<p style='color: red;'>Error: No se pudo establecer la conexión. Revisa los logs de Laragon.</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<br><a href='index.php'>Volver al Inicio</a>";
?>
