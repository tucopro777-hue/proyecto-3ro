<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinSight — Reportes Administrativos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  :root {
    --bg: #0b0e14; --bg2: #111520; --bg3: #181e2e; --card: #161b28;
    --border: rgba(255,255,255,0.07); --accent: #1a7226; --accent2: #4af4c2;
    --danger: #f46060; --text: #e8ecf5; --muted: #6b7494;
    --font-head: 'DM Serif Display', serif; --font-body: 'Outfit', sans-serif;
  }
  body { background: var(--bg); color: var(--text); font-family: var(--font-body); min-height: 100vh; display: grid; grid-template-columns: 220px 1fr; }
  
  .sidebar { background: var(--bg2); border-right: 1px solid var(--border); padding: 28px 0; }
  .logo { padding: 0 24px 32px; display: flex; align-items: center; gap: 10px; text-decoration: none; }
  .logo-icon { width: 36px; height: 36px; background: var(--accent); border-radius: 10px; display: grid; place-items: center; }
  .logo-name { font-family: var(--font-head); font-size: 22px; color: var(--text); }
  .logo-name span { color: var(--accent); }
  .nav-item { display: flex; align-items: center; gap: 10px; padding: 12px 24px; color: var(--muted); text-decoration: none; font-size: 14px; transition: 0.2s; }
  .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.05); color: var(--text); }
  
  .main { padding: 40px; }
  .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
  .header h1 { font-family: var(--font-head); font-size: 32px; font-weight: 400; }
  
  .filters-card { background: var(--card); border: 1px solid var(--border); border-radius: 20px; padding: 24px; margin-bottom: 32px; }
  .filter-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px; align-items: end; }
  .filter-group { display: flex; flex-direction: column; gap: 8px; }
  .filter-group label { font-size: 12px; color: var(--muted); text-transform: uppercase; font-weight: 600; }
  .filter-group input, .filter-group select { 
    background: var(--bg); border: 1px solid var(--border); border-radius: 10px; padding: 10px; color: var(--text); font-family: var(--font-body); outline: none;
  }
  .btn-filter { background: var(--accent); color: #fff; border: none; border-radius: 10px; padding: 10px 20px; cursor: pointer; font-weight: 600; transition: 0.2s; }
  .btn-filter:hover { background: #228b31; }
  .btn-export { background: transparent; border: 1px solid var(--accent2); color: var(--accent2); border-radius: 10px; padding: 10px 20px; cursor: pointer; font-weight: 600; text-decoration: none; text-align: center; }
  .btn-export:hover { background: rgba(74,244,194,0.1); }

  .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
  .summary-card { background: var(--card); border: 1px solid var(--border); border-radius: 20px; padding: 24px; }
  .summary-label { font-size: 14px; color: var(--muted); margin-bottom: 8px; }
  .summary-value { font-size: 28px; font-weight: 600; font-family: var(--font-head); }
  .summary-value.income { color: var(--accent2); }
  .summary-value.expense { color: var(--danger); }

  .table-card { background: var(--card); border: 1px solid var(--border); border-radius: 20px; padding: 24px; overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; }
  th { text-align: left; padding: 12px; color: var(--muted); font-size: 12px; text-transform: uppercase; border-bottom: 1px solid var(--border); }
  td { padding: 16px 12px; font-size: 14px; border-bottom: 1px solid var(--border); }
  .type-pill { padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; }
  .type-ingreso { background: rgba(74,244,194,0.1); color: var(--accent2); }
  .type-gasto { background: rgba(244,96,96,0.1); color: var(--danger); }
</style>
</head>
<body>
  <?php 
    include 'app/Views/partials/sidebar.php'; 
    
    $total_in = 0;
    $total_out = 0;
    foreach ($movimientos as $m) {
        if ($m['tipo_movimiento'] === 'Ingreso') $total_in += $m['monto_real'];
        else $total_out += $m['monto_real'];
    }
    $balance = $total_in - $total_out;
  ?>

  <main class="main">
    <div class="header">
      <h1>Reportes Financieros</h1>
      <a href="index.php?action=export_movements&<?php echo $_SERVER['QUERY_STRING']; ?>" class="btn-export">Exportar CSV</a>
    </div>

    <div class="filters-card">
      <form action="index.php" method="GET" class="filter-form">
        <input type="hidden" name="action" value="reports">
        
        <div class="filter-group">
          <label>Usuario</label>
          <select name="user_filter">
            <option value="">Todos los usuarios</option>
            <?php foreach ($users as $u): ?>
              <option value="<?php echo $u['id_usuario']; ?>" <?php echo $filters['user_id'] == $u['id_usuario'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($u['nombre']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="filter-group">
          <label>Desde</label>
          <input type="date" name="date_from" value="<?php echo $filters['date_from']; ?>">
        </div>

        <div class="filter-group">
          <label>Hasta</label>
          <input type="date" name="date_to" value="<?php echo $filters['date_to']; ?>">
        </div>

        <div class="filter-group">
          <label>Tipo</label>
          <select name="tipo_filter">
            <option value="">Todos</option>
            <option value="Ingreso" <?php echo $filters['tipo'] == 'Ingreso' ? 'selected' : ''; ?>>Ingresos</option>
            <option value="Gasto" <?php echo $filters['tipo'] == 'Gasto' ? 'selected' : ''; ?>>Gastos</option>
          </select>
        </div>

        <button type="submit" class="btn-filter">Filtrar</button>
      </form>
    </div>

    <div class="summary-grid">
      <div class="summary-card">
        <div class="summary-label">Total Ingresos</div>
        <div class="summary-value income">Bs. <?php echo number_format($total_in, 2); ?></div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Total Gastos</div>
        <div class="summary-value expense">Bs. <?php echo number_format($total_out, 2); ?></div>
      </div>
      <div class="summary-card">
        <div class="summary-label">Balance Neto</div>
        <div class="summary-value">Bs. <?php echo number_format($balance, 2); ?></div>
      </div>
    </div>

    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Usuario</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Tipo</th>
            <th>Monto</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($movimientos)): ?>
            <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--muted);">No se encontraron movimientos con los filtros seleccionados.</td></tr>
          <?php else: ?>
            <?php foreach ($movimientos as $m): ?>
              <tr>
                <td><?php echo date('d/m/Y', strtotime($m['fecha_hora'])); ?></td>
                <td style="font-weight:600;"><?php echo htmlspecialchars($m['nombre_usuario']); ?></td>
                <td><?php echo htmlspecialchars($m['nombre_categoria']); ?></td>
                <td style="color:var(--muted);"><?php echo htmlspecialchars($m['notas']); ?></td>
                <td>
                  <span class="type-pill type-<?php echo strtolower($m['tipo_movimiento']); ?>">
                    <?php echo $m['tipo_movimiento']; ?>
                  </span>
                </td>
                <td style="font-weight:600;">Bs. <?php echo number_format($m['monto_real'], 2); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
