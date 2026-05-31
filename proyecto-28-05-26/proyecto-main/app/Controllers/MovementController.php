<?php
require_once 'app/Models/Movement.php';
require_once 'app/Models/Category.php';
require_once 'app/Models/Budget.php';
require_once 'app/Models/Alert.php';
require_once 'app/Models/Goal.php';
require_once 'app/Models/FixedExpense.php';
require_once 'app/Models/Audit.php';
require_once 'app/Models/ScheduledIncome.php';
require_once 'app/Models/Account.php';
require_once 'app/Models/Group.php';

class MovementController {
    private $db;
    private $movementModel;
    private $categoryModel;
    private $budgetModel;
    private $alertModel;
    private $goalModel;
    private $fixedExpenseModel;
    private $auditModel;
    private $scheduledIncomeModel;
    private $accountModel;
    private $groupModel;

    public function __construct($db) {
        $this->db = $db;
        $this->movementModel = new Movement($db);
        $this->categoryModel = new Category($db);
        $this->budgetModel = new Budget($db);
        $this->alertModel = new Alert($db);
        $this->goalModel = new Goal($db);
        $this->fixedExpenseModel = new FixedExpense($db);
        $this->auditModel = new Audit($db);
        $this->scheduledIncomeModel = new ScheduledIncome($db);
        $this->accountModel = new Account($db);
        $this->groupModel = new Group($db);
    }
// ... (rest of constructor)
    public function groups() {
        session_start();
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?action=login"); exit; }
        $user_id = $_SESSION['user_id'];
        $user_role_id = $_SESSION['user_role_id'];
        $current_action = 'grupos';

        // Solo supervisores o admins pueden ver grupos
        if ($user_role_id > 2) { header("Location: index.php?action=dashboard"); exit; }

        $mis_grupos = $this->groupModel->getBySupervisor($user_id);
        
        $grupos_con_miembros = [];
        foreach ($mis_grupos as $g) {
            $g['miembros'] = $this->groupModel->getMembers($g['id_grupo']);
            $grupos_con_miembros[] = $g;
        }

        include 'app/Views/movements/groups.php';
    }

    public function addGroup() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $nombre = trim($_POST['nombre']);
            if ($this->groupModel->create($_SESSION['user_id'], $nombre)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }
    }

    public function addGroupMember() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $id_grupo = (int)$_POST['id_grupo'];
            $identifier = trim($_POST['identificador']);
            $rango = trim($_POST['rango']);
            $res = $this->groupModel->addMember($id_grupo, $identifier, $rango);
            echo json_encode($res);
            exit;
        }
    }

    public function addMovement() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id = $_SESSION['user_id'];
            $tipo = $_POST['tipo'];
            $descripcion = trim($_POST['descripcion']);
            $monto = (float)$_POST['monto'];
            $categoria_id = (int)$_POST['categoria_id'];
            $id_cuenta = (int)$_POST['id_cuenta'];
            $es_ocasional = isset($_POST['es_ocasional']) ? (int)$_POST['es_ocasional'] : 0;
            $es_fijo = isset($_POST['es_fijo']) ? (int)$_POST['es_fijo'] : 0;

            if ($this->movementModel->create($user_id, $tipo, $monto, $categoria_id, $es_ocasional, $descripcion, $id_cuenta)) {
                
                // --- PILAR 1: CONTROLADOR DE TRANSACCIONES INTELIGENTE ---
                if ($tipo === 'Ingreso') {
                    $activeGoals = $this->goalModel->getActiveWithPercentage($user_id);
                    foreach ($activeGoals as $goal) {
                        $savingAmount = $monto * ($goal['porcentaje_ingreso'] / 100);
                        if ($savingAmount > 0) {
                            // 1. Actualizar monto de la meta
                            $newTotal = $goal['monto_actual'] + $savingAmount;
                            $this->goalModel->updateAmount($goal['id_meta'], $user_id, $newTotal);

                            // 2. Registrar un gasto virtual de "Ahorro" (Categoría Global Ahorro)
                            $stmt_cat = $this->db->prepare("SELECT id_categoria FROM categorias WHERE nombre_categoria = 'Ahorro' LIMIT 1");
                            $stmt_cat->execute();
                            $cat = $stmt_cat->fetch(PDO::FETCH_ASSOC);
                            $ahorro_cat_id = $cat ? $cat['id_categoria'] : $categoria_id;

                            $this->movementModel->create(
                                $user_id, 
                                'Gasto', 
                                $savingAmount, 
                                $ahorro_cat_id, 
                                0, 
                                "Ahorro Automático: " . $goal['nombre_meta'],
                                $id_cuenta
                            );
                        }
                    }
                }

                // Gasto Fijo Recurrente
                if ($es_fijo && $tipo === 'Gasto') {
                    $dia_pago = isset($_POST['dia_pago']) ? (int)$_POST['dia_pago'] : (int)date('d');
                    $this->fixedExpenseModel->create($user_id, $descripcion, $monto, $dia_pago);
                }

                // Ingreso Programado
                if ($tipo === 'Ingreso' && isset($_POST['programar_ingreso']) && $_POST['programar_ingreso'] == 1) {
                    $intervalo = $_POST['intervalo'];
                    $fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : null;
                    $this->scheduledIncomeModel->create($user_id, $descripcion, $monto, $intervalo, null, $fecha_limite);
                }

                // --- PILAR 3: SISTEMA DE ALERTAS POR PRESUPUESTO ---
                if ($tipo === 'Gasto') {
                    $month = (int)date('m');
                    $year = (int)date('Y');
                    $limit = $this->budgetModel->getMonthlyBudget($user_id, $month, $year);
                    
                    $kpis = $this->movementModel->getKPIs($user_id, $month, $year);
                    $total_gastos = (float)($kpis['total_gastos'] ?? 0);

                    if ($limit > 0 && $total_gastos > $limit) {
                        $diff = $total_gastos - $limit;
                        $this->alertModel->create($user_id, "¡Alerta Presupuestaria! Has sobrepasado tu límite mensual por Bs. " . number_format($diff, 2), 'Alta');
                    }
                }

                echo json_encode(['success' => true, 'message' => 'Movimiento registrado correctamente.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar el movimiento.']);
            }
            exit;
        }
    }

    public function list() {
        session_start();
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?action=login"); exit; }
        $user_id = $_SESSION['user_id'];
        $user_role_id = $_SESSION['user_role_id'];
        $current_action = 'movimientos';
        $movimientos = ($user_role_id == 1 || $user_role_id == 2) ? $this->movementModel->getAllSystem() : $this->movementModel->getAll($user_id);
        $title = "Todos los Movimientos";
        include 'app/Views/movements/list.php';
    }

    public function incomes() {
        session_start();
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?action=login"); exit; }
        $user_id = $_SESSION['user_id'];
        $current_action = 'ingresos';
        $movimientos = $this->movementModel->getByType($user_id, 'Ingreso');
        $ingresos_programados = $this->scheduledIncomeModel->getByUser($user_id);
        $title = "Mis Ingresos";
        include 'app/Views/movements/list.php';
    }

    public function goals() {
        session_start();
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?action=login"); exit; }
        $user_id = $_SESSION['user_id'];
        $current_action = 'metas';
        $metas = $this->goalModel->getByUser($user_id);
        include 'app/Views/movements/goals.php';
    }

    public function addGoal() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id = $_SESSION['user_id'];
            $nombre = trim($_POST['nombre']);
            $objetivo = (float)$_POST['objetivo'];
            $actual = (float)$_POST['actual'];
            $fecha_limite = !empty($_POST['fecha_limite']) ? $_POST['fecha_limite'] : null;
            $porcentaje = isset($_POST['porcentaje']) ? (float)$_POST['porcentaje'] : 0;
            if ($this->goalModel->create($user_id, $nombre, $objetivo, $actual, $fecha_limite, $porcentaje)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear meta.']);
            }
            exit;
        }
    }

    public function deleteGoal() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $user_id = $_SESSION['user_id'];
            $id_meta = (int)$_POST['id_meta'];
            $devolver = isset($_POST['devolver']) && $_POST['devolver'] == '1';
            $goal = $this->goalModel->getById($id_meta, $user_id);
            if ($goal) {
                if ($devolver && $goal['monto_actual'] > 0) {
                    // Buscar cuenta principal o primera cuenta
                    $cuentas = $this->accountModel->getByUser($user_id);
                    $id_cuenta = !empty($cuentas) ? $cuentas[0]['id_cuenta'] : 1;
                    $this->movementModel->create($user_id, 'Ingreso', $goal['monto_actual'], 1, 0, "Retorno de Ahorro: " . $goal['nombre_meta'], $id_cuenta);
                }
                $this->goalModel->delete($id_meta, $user_id);
                echo json_encode(['success' => true]);
            }
            exit;
        }
    }

    public function fixedExpenses() {
        session_start();
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?action=login"); exit; }
        $user_id = $_SESSION['user_id'];
        $user_role_id = $_SESSION['user_role_id'];
        $current_action = 'gastos_fijos';
        $month = (int)date('m');
        $year = (int)date('Y');
        $gastos_fijos = $this->fixedExpenseModel->getAll($user_id);
        
        // Verificar pagos en el mes actual
        foreach ($gastos_fijos as &$g) {
            $query = "SELECT id_movimiento FROM movimientos_diarios 
                      WHERE id_usuario = :user_id 
                      AND notas LIKE :notas 
                      AND MONTH(fecha_hora) = :month 
                      AND YEAR(fecha_hora) = :year 
                      LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':user_id' => $user_id, ':notas' => "%" . $g['nombre_servicio'] . "%", ':month' => $month, ':year' => $year]);
            $g['estado'] = $stmt->fetch() ? 'Pagado' : 'Pendiente';
        }
        include 'app/Views/movements/fixed_expenses.php';
    }

    public function payFixedExpense() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $id_gasto_fijo = (int)$_POST['id'];
            $id_cuenta = (int)$_POST['id_cuenta'];
            
            $query = "SELECT * FROM gastos_programados WHERE id_gasto_fijo = :id AND id_usuario = :user_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':id' => $id_gasto_fijo, ':user_id' => $user_id]);
            $expense = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($expense) {
                $this->movementModel->create($user_id, 'Gasto', (float)$expense['monto_fijo'], 1, 0, "Pago Gasto Fijo: " . $expense['nombre_servicio'], $id_cuenta);
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gasto no encontrado.']);
            }
            exit;
        }
    }

    public function addFixedExpense() {
        session_start();
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            $nombre = trim($_POST['nombre']);
            $monto = (float)$_POST['monto'];
            $dia = (int)$_POST['dia'];
            if ($this->fixedExpenseModel->create($user_id, $nombre, $monto, $dia)) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }
    }

    public function markAlertsRead() {
        session_start();
        if (isset($_SESSION['user_id'])) {
            if ($this->alertModel->markAllAsRead($_SESSION['user_id'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }
    }

    public function markAlertRead() {
        session_start();
        if (isset($_SESSION['user_id']) && isset($_GET['id'])) {
            if ($this->alertModel->markAsRead((int)$_GET['id'], $_SESSION['user_id'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }
    }

    public function deleteAlert() {
        session_start();
        if (isset($_SESSION['user_id']) && isset($_GET['id'])) {
            if ($this->alertModel->delete((int)$_GET['id'], $_SESSION['user_id'])) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false]);
            }
            exit;
        }
    }
}
?>
