<?php
require_once 'app/Models/Movement.php';
require_once 'app/Models/Category.php';
require_once 'app/Models/Goal.php';
require_once 'app/Models/Budget.php';
require_once 'app/Models/Alert.php';
require_once 'app/Models/FixedExpense.php';
require_once 'app/Models/Account.php';
require_once 'app/Models/Currency.php';

class DashboardController {
    private $db;
    private $movementModel;
    private $categoryModel;
    private $goalModel;
    private $budgetModel;
    private $alertModel;
    private $fixedExpenseModel;
    private $accountModel;
    private $currencyModel;

    public function __construct($db) {
        $this->db = $db;
        $this->movementModel = new Movement($db);
        $this->categoryModel = new Category($db);
        $this->goalModel = new Goal($db);
        $this->budgetModel = new Budget($db);
        $this->alertModel = new Alert($db);
        $this->fixedExpenseModel = new FixedExpense($db);
        $this->accountModel = new Account($db);
        $this->currencyModel = new Currency($db);
    }

    public function index() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?action=login");
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $user_name = $_SESSION['user_name'];
        $user_role_id = $_SESSION['user_role_id'];
        $user_role_name = $_SESSION['user_role_name'];
        $current_action = 'dashboard';

        // Get Month and Year from GET or default to current
        $month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

        $kpis = $this->movementModel->getKPIs($user_id, $month, $year);
        $total_ingresos = (float)($kpis['total_ingresos'] ?? 0);
        $total_gastos = (float)($kpis['total_gastos'] ?? 0);
        $balance_neto = $total_ingresos - $total_gastos;

        // Budget info
        $monthly_limit = $this->budgetModel->getMonthlyBudget($user_id, $month, $year);
        if ($monthly_limit <= 0) $monthly_limit = 4000.00; // Default fallback

        $categorias_gastos = $this->categoryModel->getExpensesByCategory($user_id, $month, $year);
        $metas = $this->goalModel->getByUser($user_id);
        $movimientos = $this->movementModel->getRecent($user_id, 10, $month, $year);
        $all_categories = $this->categoryModel->getByUser($user_id);
        $active_alerts = $this->alertModel->getActiveByUser($user_id);
        $gastos_fijos = $this->fixedExpenseModel->getAll($user_id);
        
        // Multi-currency Support
        $cuentas = $this->accountModel->getByUser($user_id);
        $monedas = $this->currencyModel->getAll();

        // --- ALERTA TIPO 2: Vencimiento de Gasto Fijo ---
        $avisos_pago = [];
        $today_day = (int)date('d');
        foreach ($gastos_fijos as $g) {
            if ($g['estado'] === 'Pendiente') {
                $dias_faltantes = $g['dia_pago'] - $today_day;
                if ($dias_faltantes >= 0 && $dias_faltantes <= 2) {
                    $msg = ($dias_faltantes == 0) ? "vence HOY" : "vence en $dias_faltantes días";
                    $avisos_pago[] = [
                        'nombre' => $g['nombre_servicio'],
                        'monto' => $g['monto_fijo'],
                        'mensaje' => "Tu pago de " . $g['nombre_servicio'] . " por Bs. " . $g['monto_fijo'] . " $msg."
                    ];
                }
            }
        }

        $all_users = [];
        if ($user_role_id == 1 || $user_role_id == 2) {
            $userModel = new User($this->db);
            $all_users = $userModel->getAllWithSummaries();
        }

        // Lógica de Alertas Automáticas
        foreach ($gastos_fijos as $g) {
            if ($g['estado'] === 'Pendiente') {
                $today = (int)date('d');
                if ($g['dia_pago'] - $today <= 3 && $g['dia_pago'] >= $today) {
                    $this->alertModel->create($user_id, "Recordatorio: El pago de " . $g['nombre_servicio'] . " (Bs. " . $g['monto_fijo'] . ") vence pronto.", 'Media');
                }
            }
        }

        foreach ($metas as $m) {
            if ($m['monto_actual'] >= $m['monto_objetivo']) {
                $this->alertModel->create($user_id, "¡Felicidades! Has alcanzado tu meta: " . $m['nombre_meta'], 'Baja');
            }
        }

        // Recargar alertas después de generar las automáticas
        $active_alerts = $this->alertModel->getActiveByUser($user_id);

        include 'app/Views/dashboard/index.php';
    }

    public function alerts() {
        session_start();
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?action=login"); exit; }
        $user_id = $_SESSION['user_id'];
        $user_role_id = $_SESSION['user_role_id'];
        $user_role_name = $_SESSION['user_role_name'];
        $current_action = 'alerts';

        $todas_las_alertas = $this->alertModel->getAllByUser($user_id);
        $active_alerts = $this->alertModel->getActiveByUser($user_id);

        include 'app/Views/dashboard/alerts.php';
    }
}
?>
