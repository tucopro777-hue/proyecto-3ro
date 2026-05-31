<?php
require_once 'app/Models/User.php';
require_once 'app/Models/AccessLog.php';

class AuthController {
    private $db;
    private $userModel;
    private $accessLogModel;

    public function __construct($db) {
        $this->db = $db;
        $this->userModel = new User($db);
        $this->accessLogModel = new AccessLog($db);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userModel->getByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                session_start();
                $_SESSION['user_id'] = $user['id_usuario'];
                $_SESSION['user_name'] = $user['nombre'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role_id'] = $user['id_rol'];
                $_SESSION['user_role_name'] = $user['nombre_rol'];

                // Registrar acceso en bitácora
                $this->accessLogModel->register($user['id_usuario']);

                echo json_encode(['success' => true, 'message' => 'Acceso correcto.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
            }
            exit;
        }
        include 'app/Views/auth/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            if ($this->userModel->getByEmail($email)) {
                echo json_encode(['success' => false, 'message' => 'Este correo ya tiene una cuenta.']);
            } else {
                if ($this->userModel->create($nombre, $email, $password)) {
                    echo json_encode(['success' => true, 'message' => 'Cuenta creada correctamente.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al crear la cuenta.']);
                }
            }
            exit;
        }
        include 'app/Views/auth/register.php';
    }

    public function registerControlledUser() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['user_role_id'] <= 2) {
            $nombre = trim($_POST['nombre']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $id_grupo = (int)$_POST['id_grupo'];

            if ($this->userModel->getByEmail($email)) {
                echo json_encode(['success' => false, 'message' => 'El email ya está registrado.']);
                exit;
            }

            // Crear usuario con rol Estándar (3)
            if ($this->userModel->create($nombre, $email, $password)) {
                $new_user_id = $this->db->lastInsertId();
                
                // Vincular al grupo como miembro con rango 'Controlado'
                require_once 'app/Models/Group.php';
                $groupModel = new Group($this->db);
                $groupModel->addMember($id_grupo, $email, 'Controlado');
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear usuario.']);
            }
            exit;
        }
    }

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
        header("Location: index.php?action=login");
    }

    public function deleteUser() {
        session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role_id'] != 1) {
            echo json_encode(['success' => false, 'message' => 'No autorizado.']);
            exit;
        }
        $id = $_GET['id'];
        if ($this->userModel->delete($id)) {
            header("Location: index.php?action=dashboard");
        }
    }
}
?>
