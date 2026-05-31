<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinSight — Auditoría de Sistema</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
  .action-pill { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
  .action-insert { background: rgba(74,244,194,0.1); color: var(--accent2); }
  .action-update { background: rgba(200,240,100,0.1); color: var(--accent); }
  .action-delete { background: rgba(244,96,96,0.1); color: var(--danger); }
</style>
</head>
<body>
<div class="shell">
  <?php 
    include 'app/Views/partials/sidebar.php'; 
  ?>

  <header class="topbar">
    <div class="topbar-left">
      <h1>Auditoría de Movimientos</h1>
      <p>Historial de cambios en el sistema</p>
    </div>
    <div class="topbar-right">
      <span class="pill active">Super Admin</span>
    </div>
  </header>

  <main class="main">
    <div class="card">
      <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; font-size:14px;">
          <thead>
            <tr style="text-align:left; color:var(--muted); border-bottom:1px solid var(--border);">
              <th style="padding:12px;">Fecha y Hora</th>
              <th style="padding:12px;">Acción</th>
              <th style="padding:12px;">Usuario Responsable</th>
              <th style="padding:12px;">Detalle del Movimiento</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($logs)): ?>
              <tr><td colspan="4" style="text-align:center; color:var(--muted); padding:40px;">No hay registros de auditoría.</td></tr>
            <?php else: ?>
              <?php foreach ($logs as $l): ?>
                <tr style="border-bottom:1px solid var(--border);">
                  <td style="padding:16px 12px;"><?php echo date('d/m/Y H:i:s', strtotime($l['fecha_accion'])); ?></td>
                  <td style="padding:16px 12px;">
                    <span class="action-pill action-<?php echo strtolower($l['accion']); ?>">
                      <?php echo $l['accion']; ?>
                    </span>
                  </td>
                  <td style="padding:16px 12px; font-weight:600; color:var(--accent2);"><?php echo htmlspecialchars($l['nombre']); ?></td>
                  <td style="padding:16px 12px; color:var(--muted);"><?php echo htmlspecialchars($l['notas']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</body>
</html>
