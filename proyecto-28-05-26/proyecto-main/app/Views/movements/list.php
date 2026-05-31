<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinSight — <?php echo $title; ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Mono:wght@400;500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="shell">
  <?php 
    include 'app/Views/partials/sidebar.php'; 
  ?>

  <header class="topbar">
    <div class="topbar-left">
      <h1><?php echo $title; ?></h1>
      <p>Listado completo de ingresos y gastos registrados</p>
    </div>
    <div class="topbar-right">
      <?php if ($user_role_id == 3): ?>
      <button class="btn-add" onclick="openMovementModal('<?php echo ($current_action == 'ingresos' ? 'ingreso' : ''); ?>')">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M8 2v12M2 8h12"/>
        </svg>
        Añadir <?php echo ($current_action == 'ingresos' ? 'ingreso' : 'movimiento'); ?>
      </button>
      <?php endif; ?>
      <?php if (isset($user_role_id) && ($user_role_id == 1 || $user_role_id == 2)): ?>
        <a href="index.php?action=export_movements" class="btn-add" style="background:var(--accent); color:white;">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 10l-4 4-4-4M8 2v12"/></svg>
          Exportar CSV
        </a>
      <?php endif; ?>
    </div>
  </header>

  <main class="main">
    <?php if ($current_action == 'ingresos' && !empty($ingresos_programados)): ?>
    <div class="card" style="margin-bottom: 25px; border-left: 4px solid var(--accent2);">
      <div class="card-header">
        <div>
          <div class="card-title">Ingresos Programados</div>
          <div class="card-title-sub">Tus fuentes de ingresos recurrentes</div>
        </div>
      </div>
      <div style="margin-top:15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
        <?php foreach ($ingresos_programados as $ip): ?>
          <div style="background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; border: 1px solid var(--border);">
            <div style="display:flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
              <span style="font-weight: 600; font-size: 15px;"><?php echo htmlspecialchars($ip['nombre']); ?></span>
              <span class="pill" style="font-size: 10px; background: var(--accent2); color: #000;"><?php echo $ip['intervalo']; ?></span>
            </div>
            <div style="font-family: 'DM Mono'; font-size: 18px; color: var(--accent2); font-weight: 600; margin-bottom: 10px;">
              Bs. <?php echo number_format($ip['monto'], 2); ?>
            </div>
            <div style="font-size: 12px; color: var(--muted);">
              <div>Duración: <?php echo htmlspecialchars($ip['duracion']); ?></div>
              <?php if ($ip['intervalo'] == 'Personalizado'): ?>
                <div>Frecuencia: Cada <?php echo $ip['intervalo_personalizado']; ?> días</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title"><?php echo ($current_action == 'ingresos' ? 'Historial de Ingresos' : 'Registros Financieros'); ?></div>
          <div class="card-title-sub"><?php echo ($current_action == 'ingresos' ? 'Lista de todos los ingresos percibidos' : 'Historial detallado de transacciones'); ?></div>
        </div>
      </div>
      <div style="margin-top:20px; overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
          <thead>
            <tr style="text-align:left; color:var(--muted); border-bottom:1px solid var(--border);">
              <th style="padding:12px;">Fecha</th>
              <th style="padding:12px;">Descripción</th>
              <th style="padding:12px;">Categoría</th>
              <th style="padding:12px;">Tipo</th>
              <?php if (isset($user_role_id) && ($user_role_id == 1 || $user_role_id == 2)): ?>
              <th style="padding:12px;">Usuario</th>
              <?php endif; ?>
              <th style="padding:12px; text-align:right;">Monto</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($movimientos)): ?>
              <tr><td colspan="<?php echo (isset($user_role_id) && ($user_role_id == 1 || $user_role_id == 2)) ? '6' : '5'; ?>" style="text-align:center; color:var(--muted); padding:40px;">No se encontraron movimientos.</td></tr>
            <?php else: ?>
              <?php foreach ($movimientos as $m): ?>
                <tr style="border-bottom:1px solid var(--border);">
                  <td style="padding:16px 12px;"><?php echo date('d/m/Y H:i', strtotime($m['fecha_hora'])); ?></td>
                  <td style="padding:16px 12px; font-weight:500;"><?php echo htmlspecialchars($m['notas']); ?></td>
                  <td style="padding:16px 12px;"><span class="pill" style="background:rgba(255,255,255,0.05);"><?php echo htmlspecialchars($m['nombre_categoria']); ?></span></td>
                  <td style="padding:16px 12px;"><?php echo $m['tipo_movimiento']; ?></td>
                  <?php if (isset($user_role_id) && ($user_role_id == 1 || $user_role_id == 2)): ?>
                  <td style="padding:16px 12px; font-weight:600; color:var(--accent2);"><?php echo htmlspecialchars($m['nombre_usuario'] ?? 'N/A'); ?></td>
                  <?php endif; ?>
                  <td style="padding:16px 12px; text-align:right; font-family:'DM Mono'; font-weight:500; color:<?php echo $m['tipo_movimiento'] == 'Gasto' ? 'var(--danger)' : 'var(--accent2)'; ?>;">
                    <?php echo $m['tipo_movimiento'] == 'Gasto' ? '−' : '+'; ?> Bs. <?php echo number_format($m['monto_real'], 2); ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<?php 
if ($user_role_id == 3) {
    // We need $all_categories for the modal
    require_once 'app/Models/Category.php';
    $categoryModel = new Category((new Database())->getConnection());
    $all_categories = $categoryModel->getByUser($_SESSION['user_id']);
    include 'app/Views/partials/modal_movement.php'; 
}
?>
</body>
</html>
