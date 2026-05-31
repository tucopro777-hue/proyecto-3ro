<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinSight — Dashboard</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Mono:wght@400;500&family=Outfit:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="shell">

  <?php 
    $current_action = 'dashboard';
    include 'app/Views/partials/sidebar.php'; 
  ?>

  <!-- ── TOPBAR ── -->
  <header class="topbar">
    <div class="topbar-left">
      <h1>Dashboard</h1>
      <p><?php echo ($user_role_id == 3) ? 'Resumen financiero' : 'Panel de Control de Supervisor'; ?> · Mayo 2026</p>
    </div>
    <div class="topbar-right">
      <?php if ($user_role_id == 3 || $user_role_id == 2): ?>
      <button class="btn-add" id="btn-open-modal" onclick="openMovementModal()">
        <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2.5">
          <path d="M8 2v12M2 8h12"/>
        </svg>
        Añadir movimiento
      </button>
      <?php endif; ?>
      <div class="month-selector">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="2" y="3" width="12" height="11" rx="2"/><path d="M2 7h12M5 1v4M11 1v4"/>
        </svg>
        <select id="mes-select">
          <?php
            $months = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
            $current_y = (int)date('Y');
            for ($m=1; $m<=12; $m++) {
                $sel = ($m == $month) ? 'selected' : '';
                echo "<option value='$m' $sel>".$months[$m-1]." $current_y</option>";
            }
          ?>
        </select>
      </div>
      <button class="btn-notif" title="Notificaciones">
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
          <path d="M8 1a5 5 0 015 5v2l1.5 2.5h-13L3 8V6a5 5 0 015-5z"/><path d="M6.5 13a1.5 1.5 0 003 0"/>
        </svg>
        <?php if (!empty($active_alerts)): ?>
        <span class="notif-badge"><?php echo count($active_alerts); ?></span>
        <?php endif; ?>
      </button>
    </div>
  </header>

  <!-- ── MAIN ── -->
  <main class="main">

    <!-- ALERTA TIPO 1: BANNER DE PRESUPUESTO EXCEDIDO -->
    <?php 
    $presupuesto_alerta = array_filter($active_alerts, function($a) { 
        return stripos($a['mensaje'], 'superado tu presupuesto') !== false; 
    });
    if (!empty($presupuesto_alerta)): ?>
    <div class="banner-danger">
        <div class="banner-icon">⚠️</div>
        <div class="banner-text"><?php echo end($presupuesto_alerta)['mensaje']; ?></div>
    </div>
    <?php endif; ?>

    <!-- KPIs: RESUMEN GLOBAL (Restaurado a dashboard anterior) -->
    <div class="kpi-grid">
      <div class="kpi" style="--kpi-color: var(--accent2)">
        <div class="kpi-header"><span class="kpi-label">Ingresos del mes</span><span class="kpi-badge up">Real</span></div>
        <div class="kpi-value"><span class="currency">Bs.</span><?php echo number_format($total_ingresos, 2); ?></div>
        <div class="kpi-sub">Total acumulado de ingresos</div>
      </div>
      <div class="kpi" style="--kpi-color: var(--danger)">
        <div class="kpi-header"><span class="kpi-label">Gastos del mes</span><span class="kpi-badge down">Real</span></div>
        <div class="kpi-value"><span class="currency">Bs.</span><?php echo number_format($total_gastos, 2); ?></div>
        <div class="kpi-sub">Total acumulado de gastos</div>
      </div>
      <div class="kpi" style="--kpi-color: var(--accent)">
        <div class="kpi-header"><span class="kpi-label">Balance neto</span><span class="kpi-badge up">Real</span></div>
        <div class="kpi-value"><span class="currency">Bs.</span><?php echo number_format($balance_neto, 2); ?></div>
        <div class="kpi-sub">Diferencia entre ingresos y gastos</div>
      </div>
      <div class="kpi" style="--kpi-color: var(--accent3)">
        <div class="kpi-header">
          <span class="kpi-label">Límite mensual</span>
          <a href="javascript:void(0)" id="btn-edit-budget" style="font-size:10px; color:var(--accent2); text-decoration:none;">Configurar</a>
        </div>
        <div class="kpi-value"><span class="currency">Bs.</span><?php echo number_format($monthly_limit, 0); ?></div>
        <div class="kpi-progress">
          <?php $pct_budget = $monthly_limit > 0 ? min(100, ($total_gastos / $monthly_limit) * 100) : 0; ?>
          <div class="progress-track"><div class="progress-fill <?php echo ($pct_budget > 90) ? 'danger' : (($pct_budget > 70) ? 'warn' : ''); ?>" id="limite-fill" style="width:<?php echo $pct_budget; ?>%"></div></div>
          <div class="progress-labels"><span>Bs. <?php echo number_format($total_gastos, 0); ?> gastado</span><span>Bs. <?php echo number_format(max(0, $monthly_limit - $total_gastos), 0); ?> libre</span></div>
        </div>
      </div>
    </div>

    <!-- MID GRID -->
    <div class="mid-grid" style="margin-top: 32px;">
      <div class="card">
        <div class="card-header">
          <div><div class="card-title">Gasto por categoría</div><div class="card-title-sub">Distribución mensual</div></div>
          <span class="pill active">Este mes</span>
        </div>
        <div class="cat-list" id="cat-list"></div>
      </div>
      <div class="card" style="animation-delay:0.3s">
        <div class="card-header">
          <div><div class="card-title">Metas de ahorro</div><div class="card-title-sub">Progreso acumulado</div></div>
          <a href="index.php?action=metas" class="pill" style="text-decoration:none">+ Gestionar</a>
        </div>
        <div class="metas-list" id="metas-list"></div>
      </div>
    </div>

    <!-- BOTTOM GRID -->
    <div class="bot-grid">
      <div class="card" style="animation-delay:0.35s">
        <div class="card-header">
          <div><div class="card-title">Movimientos recientes</div><div class="card-title-sub">Últimos registros</div></div>
          <a href="index.php?action=movimientos" class="pill" style="text-decoration:none">Ver todos</a>
        </div>
        <div class="tx-list" id="tx-list"></div>
      </div>

      <div style="display:flex;flex-direction:column;gap:16px">
        <div class="card" style="animation-delay:0.40s">
          <div class="card-header">
            <div><div class="card-title">Alertas activas</div><div class="card-title-sub">Sin leer</div></div>
            <span class="pill" id="btn-clear-alerts">Limpiar</span>
          </div>
          <div class="alert-list" id="alert-list">
            <?php if (empty($active_alerts)): ?>
              <div style="padding:16px;text-align:center;color:var(--muted);font-size:13px">Sin alertas.</div>
            <?php else: ?>
              <?php foreach ($active_alerts as $alert): ?>
                <div class="alert-item <?php echo strtolower($alert['prioridad']); ?>">
                  <div class="alert-msg"><?php echo htmlspecialchars($alert['mensaje']); ?></div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </main>
</div>

<?php 
  include 'app/Views/partials/modal_movement.php'; 
?>

<!-- MODAL: CONFIGURAR PRESUPUESTO -->
<div class="modal-overlay" id="modal-budget" role="dialog" aria-modal="true">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title">Configurar Presupuesto</div>
        <div class="modal-subtitle">Establece tu límite de gasto mensual</div>
      </div>
      <button class="modal-close" onclick="document.getElementById('modal-budget').classList.remove('open')">✕</button>
    </div>
    <div class="field-group">
      <label class="field-label">Límite Máximo (Bs.)</label>
      <input id="budget-limit" type="number" class="ff-input" value="<?php echo $monthly_limit; ?>" />
    </div>
    <button type="button" class="btn-submit" id="btn-save-budget">Guardar Presupuesto</button>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
<script>
document.getElementById('mes-select').addEventListener('change', function() {
  const m = this.value;
  const y = <?php echo $year; ?>;
  window.location.href = `index.php?action=dashboard&month=${m}&year=${y}`;
});

document.getElementById('btn-edit-budget').addEventListener('click', () => {
  document.getElementById('modal-budget').classList.add('open');
});

document.getElementById('btn-save-budget').addEventListener('click', async () => {
  const limit = document.getElementById('budget-limit').value;
  const formData = new FormData();
  formData.append('limit', limit);
  formData.append('month', <?php echo $month; ?>);
  formData.append('year', <?php echo $year; ?>);

  const response = await fetch('index.php?action=set_budget', {
    method: 'POST',
    body: formData
  });
  const result = await response.json();
  if (result.success) {
    location.reload();
  } else {
    alert("Error al actualizar presupuesto.");
  }
});

document.getElementById('btn-clear-alerts').addEventListener('click', async () => {
  const response = await fetch('index.php?action=mark_alerts_read');
  const result = await response.json();
  if (result.success) {
    location.reload();
  }
});

const REAL_DATA = {
  categorias: <?php 
    $cat_js = array_map(function($c) {
        return [
            'nombre' => $c['nombre_categoria'],
            'monto' => (float)$c['total'],
            'max' => 5000, 
            'color' => $c['color_hex'] ?? '#c8f064'
        ];
    }, $categorias_gastos);
    echo json_encode($cat_js); 
  ?>,
  metas: <?php 
    $metas_js = array_map(function($m) {
        return [
            'nombre' => $m['nombre_meta'],
            'objetivo' => (float)$m['monto_objetivo'],
            'actual' => (float)$m['monto_actual'],
            'limite' => $m['fecha_limite'] ? date('M Y', strtotime($m['fecha_limite'])) : 'Sin fecha',
            'porcentaje' => (float)$m['porcentaje_ingreso'],
            'color' => '#4af4c2'
        ];
    }, $metas);
    echo json_encode($metas_js); 
  ?>,
  movimientos: <?php 
    $movs_js = array_map(function($m) {
        return [
            'notas' => $m['notas'],
            'tipo_movimiento' => $m['tipo_movimiento'],
            'monto_real' => (float)$m['monto_real'],
            'nombre_categoria' => $m['nombre_categoria'],
            'fecha_hora' => $m['fecha_hora'],
            'es_ocasional' => (bool)$m['es_ocasional']
        ];
    }, $movimientos);
    echo json_encode($movs_js); 
  ?>
};

function renderRealData() {
  const catList = document.getElementById('cat-list');
  if (catList) {
    catList.innerHTML = '';
    if (REAL_DATA.categorias.length === 0) {
      catList.innerHTML = '<div style="padding:20px;text-align:center;color:var(--muted)">Sin gastos registrados.</div>';
    }
    REAL_DATA.categorias.forEach(c => {
      const pct = Math.round((c.monto / c.max) * 100);
      catList.innerHTML += `
        <div class="cat-row">
          <div class="cat-name"><span class="cat-dot" style="background:${c.color}"></span>${c.nombre}</div>
          <div class="cat-bar-track"><div class="cat-bar-fill" data-pct="${pct}" style="width:0%;background:${c.color}"></div></div>
          <div class="cat-amount">Bs. ${c.monto.toLocaleString()}</div>
        </div>`;
    });
  }

  const metasList = document.getElementById('metas-list');
  if (metasList) {
    metasList.innerHTML = '';
    if (REAL_DATA.metas.length === 0) {
      metasList.innerHTML = '<div style="padding:20px;text-align:center;color:var(--muted)">Sin metas de ahorro.</div>';
    }
    REAL_DATA.metas.forEach(m => {
      const pct = Math.round((m.actual / m.objetivo) * 100);
      const autoSave = m.porcentaje > 0 ? `<div style="font-size:10px; color:var(--accent2); margin-top:2px;">⚡ Auto: ${m.porcentaje}%</div>` : '';
      metasList.innerHTML += `
        <div class="meta-item" onclick="window.location.href='index.php?action=metas'">
          <div class="meta-top">
            <div>
                <div class="meta-name">${m.nombre}</div>
                ${autoSave}
            </div>
            <span class="meta-pct" style="background:${m.color}18;color:${m.color}">${pct}%</span>
          </div>
          <div class="meta-amounts">Bs. ${m.actual.toLocaleString()} de Bs. ${m.objetivo.toLocaleString()}</div>
          <div class="meta-track"><div class="meta-fill" data-pct="${pct}" style="width:0%;background:${m.color}"></div></div>
          <div class="meta-deadline">📅 Límite: ${m.limite}</div>
        </div>`;
    });
  }

  const txList = document.getElementById('tx-list');
  if (txList) {
    txList.innerHTML = '';
    if (REAL_DATA.movimientos.length === 0) {
      txList.innerHTML = '<div style="padding:20px;text-align:center;color:var(--muted)">Sin movimientos.</div>';
    }
    REAL_DATA.movimientos.forEach(t => {
      const esGasto  = t.tipo_movimiento === 'Gasto';
      const signo    = esGasto ? '−' : '+';
      const abs      = Math.abs(t.monto_real).toFixed(2);
      const cat      = t.nombre_categoria || 'Sin categoría';
      const fecha    = new Date(t.fecha_hora).toLocaleDateString('es-BO', { day: '2-digit', month: 'short' });
      const emoji    = esGasto ? '💸' : '💰';
      
      txList.innerHTML += `
        <div class="tx-row">
          <div class="tx-icon" style="background:rgba(255,255,255,0.05)">${emoji}</div>
          <div class="tx-info">
            <div class="tx-name">${t.notas}</div>
            <div class="tx-date">${cat} · ${fecha}</div>
          </div>
          <div class="tx-amount ${esGasto ? 'gasto' : 'ingreso'}">${signo} Bs. ${abs}</div>
        </div>`;
    });
  }
}

function animateBars() {
  document.querySelectorAll('.cat-bar-fill').forEach(el => {
    setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 200);
  });
  document.querySelectorAll('.meta-fill').forEach(el => {
    setTimeout(() => { el.style.width = el.dataset.pct + '%'; }, 400);
  });
}

(async () => {
  renderRealData();
  animateBars();
})();
</script>
</body>
</html>
