  1 <?php
    2 require_once 'proyecto-main/config/Database.php';
    3 session_start();
    4
    5 if (!isset($_SESSION['user_id'])) {
    6     die("Primero inicia sesión en la aplicación.");
    7 }
    8
    9 $db = (new Database())->getConnection();
   10 $user_id = $_SESSION['user_id'];
   11
   12 // Convertir al usuario actual en Supervisor (Rol 2)
   13 $db->exec("UPDATE usuarios SET id_rol = 2 WHERE id_usuario = $user_id");
   14 $_SESSION['user_role_id'] = 2;
   15 $_SESSION['user_role_name'] = 'Supervisor';
   16
   17 echo "<h3>✅ ¡Ascenso exitoso!</h3>";
   18 echo "Ahora eres <b>Supervisor</b>. <a href='index.php?action=dashboard'>Vuelve al Dashboard</a> y verás las nuevas
      opciones en la sidebar.";
   19 ?>