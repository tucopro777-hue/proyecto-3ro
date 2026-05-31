<?php
require_once 'config/Database.php';
require_once 'app/Controllers/AuthController.php';
require_once 'app/Controllers/DashboardController.php';
require_once 'app/Controllers/MovementController.php';

$database = new Database();
$db = $database->getConnection();

$action = isset($_GET['action']) ? $_GET['action'] : 'login';

switch ($action) {
    case 'login':
        $controller = new AuthController($db);
        $controller->login();
        break;
    case 'register':
        $controller = new AuthController($db);
        $controller->register();
        break;
    case 'register_controlled_user':
        $controller = new AuthController($db);
        $controller->registerControlledUser();
        break;
    case 'logout':
        $controller = new AuthController($db);
        $controller->logout();
        break;
    case 'dashboard':
        $controller = new DashboardController($db);
        $controller->index();
        break;
    case 'alerts':
        $controller = new DashboardController($db);
        $controller->alerts();
        break;
    case 'mark_alert_read':
        $controller = new MovementController($db);
        $controller->markAlertRead();
        break;
    case 'delete_alert':
        $controller = new MovementController($db);
        $controller->deleteAlert();
        break;
    case 'movimientos':
        $controller = new MovementController($db);
        $controller->list();
        break;
    case 'gastos_fijos':
        $controller = new MovementController($db);
        $controller->fixedExpenses();
        break;
    case 'pay_fixed_expense':
        $controller = new MovementController($db);
        $controller->payFixedExpense();
        break;
    case 'ingresos':
        $controller = new MovementController($db);
        $controller->incomes();
        break;
    case 'metas':
        $controller = new MovementController($db);
        $controller->goals();
        break;
    case 'grupos':
        $controller = new MovementController($db);
        $controller->groups();
        break;
    case 'add_group':
        $controller = new MovementController($db);
        $controller->addGroup();
        break;
    case 'add_group_member':
        $controller = new MovementController($db);
        $controller->addGroupMember();
        break;
    case 'reports':
        $controller = new MovementController($db);
        $controller->reports();
        break;
    case 'audit':
        $controller = new MovementController($db);
        $controller->audit();
        break;
    case 'export_movements':
        $controller = new MovementController($db);
        $controller->exportMovements();
        break;
    case 'add_movement':
        $controller = new MovementController($db);
        $controller->addMovement();
        break;
    case 'add_fixed_expense':
        $controller = new MovementController($db);
        $controller->addFixedExpense();
        break;
    case 'add_category':
        $controller = new MovementController($db);
        $controller->addCategory();
        break;
    case 'set_budget':
        $controller = new MovementController($db);
        $controller->setBudget();
        break;
    case 'mark_alerts_read':
        $controller = new MovementController($db);
        $controller->markAlertsRead();
        break;
    case 'add_goal':
        $controller = new MovementController($db);
        $controller->addGoal();
        break;
    case 'delete_user':
        $controller = new AuthController($db);
        $controller->deleteUser();
        break;
    default:
        $controller = new AuthController($db);
        $controller->login();
        break;
}
?>
