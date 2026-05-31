<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinSight — Gastos Fijos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
  .checklist-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: var(--bg3);
    border: 1px solid var(--border);
    border-radius: 12px;
    margin-bottom: 12px;
    transition: 0.2s;
  }
  .checklist-item:hover { border-color: var(--accent2); }
  .checklist-item.paid { opacity: 0.6; background: rgba(74,244,194,0.05); }
  
  .item-info { display: flex; align-items: center; gap: 15px; }
  .item-day {
    width: 40px; height: 40px; border-radius: 10px;
    background: var(--bg); display: grid; place-items: center;
    font-size: 14px; font-weight: 600; color: var(--accent);
  }
  .item-text h4 { font-size: 15px; font-weight: 500; }
  .item-text p { font-size: 12px; color: var(--muted); }
  
  .item-actions { display: flex; align-items: center; gap: 20px; }
  .item-amount { font-family: var(--font-mono); font-size: 16px; font-weight: 500; color: var(--text); }
  
  .btn-pay {
    background: var(--accent2); color: #0b0e14; border: none;
    padding: 6px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: 0.2s;
  }
  .btn-pay:hover { opacity: 0.8; transform: translateY(-1px); }
  .btn-pay.disabled { background: var(--muted); cursor: not-allowed; opacity: 1; transform: none; }

  .filter-tabs { display: flex; gap: 10px; margin-bottom: 24px; }
  .tab { 
    padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; 
    cursor: pointer; background: var(--bg3); color: var(--muted); border: 1px solid var(--border);
  }
  .tab.active { background: rgba(200,240,100,0.1); color: var(--accent); border-color: var(--accent); }
</style>
</head>
<body>
<div class="shell">
  <?php 
    include 'app/Views/partials/sidebar.php'; 
  ?>

  <header class="topbar">
    <div class="topbar-left">
      <h1>Gastos Fijos</h1>
      <p>Gestión de pagos recurrentes</p>
    </div>
    <div class="topbar-right">
      <button class="btn-add" onclick="openFixedExpenseModal()">+ Nuevo Gasto</button>
    </div>
  </header>

  <main class="main">
    <div class="filter-tabs">
      <div class="tab active" data-filter="all">Todos</div>
      <div class="tab" data-filter="today">Vencen hoy</div>
      <div class="tab" data-filter="week">Esta semana</div>
      <div class="tab" data-filter="pending">Pendientes</div>
    </div>

    <div class="card">
      <div class="card-header">
        <div>
          <div class="card-title">Checklist de Pagos</div>
          <div class="card-title-sub">Confirma tus gastos fijos del mes</div>
        </div>
      </div>

      <div id="checklist-container">
        <?php if (empty($gastos_fijos)): ?>
          <p style="color:var(--muted); padding:20px; text-align:center;">No tienes gastos fijos programados.</p>
        <?php else: ?>
          <?php foreach ($gastos_fijos as $g): ?>
            <?php 
              $is_paid = ($g['estado'] === 'Pagado');
              $today = (int)date('d');
              $is_today = ($g['dia_pago'] == $today);
              $is_week = ($g['dia_pago'] >= $today && $g['dia_pago'] <= $today + 7);
            ?>
            <div class="checklist-item <?php echo $is_paid ? 'paid' : ''; ?>" 
                 data-day="<?php echo $g['dia_pago']; ?>"
                 data-status="<?php echo strtolower($g['estado']); ?>"
                 data-today="<?php echo $is_today ? '1' : '0'; ?>"
                 data-week="<?php echo $is_week ? '1' : '0'; ?>">
              <div class="item-info">
                <div class="item-day"><?php echo $g['dia_pago']; ?></div>
                <div class="item-text">
                  <h4><?php echo htmlspecialchars($g['nombre_servicio']); ?></h4>
                  <p>Frecuencia: Mensual</p>
                </div>
              </div>
              <div class="item-actions">
                <div class="item-amount">Bs. <?php echo number_format($g['monto_fijo'], 2); ?></div>
                <?php if ($is_paid): ?>
                  <button class="btn-pay disabled" disabled>✓ Pagado</button>
                <?php else: ?>
                  <button class="btn-pay" onclick="payExpense(<?php echo $g['id_gasto_fijo']; ?>, this)">Confirmar Pago</button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<!-- ══════════════════════════════════════════
     MODAL: AÑADIR GASTO FIJO
══════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-fixed-expense" role="dialog" aria-modal="true">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title">Nuevo Gasto Fijo</div>
        <div class="modal-subtitle">Programa un pago mensual recurrente</div>
      </div>
      <button class="modal-close" onclick="closeFixedModal()">✕</button>
    </div>

    <div class="modal-alert" id="fixed-modal-alert"></div>

    <div class="field-group">
      <label class="field-label">Nombre del Servicio/Gasto <span>*</span></label>
      <input id="fixed-name" type="text" class="ff-input" placeholder="Ej: Internet, Alquiler, Luz..." required />
    </div>

    <div class="field-group">
      <label class="field-label">Monto Mensual (Bs.) <span>*</span></label>
      <input id="fixed-monto" type="number" min="0.01" step="0.01" class="ff-input" placeholder="0.00" required />
    </div>

    <div class="field-group">
      <label class="field-label">Día de Pago (1-31) <span>*</span></label>
      <input id="fixed-dia" type="number" min="1" max="31" class="ff-input" placeholder="Ej: 15" required />
    </div>

    <button type="button" class="btn-submit" id="btn-submit-fixed">
      Programar Gasto Fijo
    </button>
  </div>
</div>

<script>
function openFixedExpenseModal() {
  document.getElementById('modal-fixed-expense').classList.add('open');
}

function closeFixedModal() {
  document.getElementById('modal-fixed-expense').classList.remove('open');
}

document.getElementById('btn-submit-fixed').addEventListener('click', async () => {
  const nombre = document.getElementById('fixed-name').value.trim();
  const monto = document.getElementById('fixed-monto').value;
  const dia = document.getElementById('fixed-dia').value;

  if (!nombre || !monto || !dia) {
    showFixedAlert('Todos los campos son obligatorios.');
    return;
  }

  const btn = document.getElementById('btn-submit-fixed');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span>Guardando...';

  try {
    const formData = new FormData();
    formData.append('nombre', nombre);
    formData.append('monto', monto);
    formData.append('dia', dia);

    const resp = await fetch('index.php?action=add_fixed_expense', {
      method: 'POST',
      body: formData
    });
    const res = await resp.json();

    if (res.success) {
      location.reload();
    } else {
      showFixedAlert(res.message);
    }
  } catch (e) {
    showFixedAlert('Error de conexión.');
  }
  btn.disabled = false;
  btn.textContent = 'Programar Gasto Fijo';
});

function showFixedAlert(msg) {
  const el = document.getElementById('fixed-modal-alert');
  el.textContent = msg;
  el.className = 'modal-alert visible error';
}

async function payExpense(id, btn) {
  if (!confirm('¿Confirmar el pago de este gasto fijo? Se registrará un nuevo movimiento financiero.')) return;
  
  btn.disabled = true;
  btn.textContent = 'Procesando...';
  
  try {
    const formData = new FormData();
    formData.append('id', id);
    
    const response = await fetch('index.php?action=pay_fixed_expense', {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    if (result.success) {
      const item = btn.closest('.checklist-item');
      item.classList.add('paid');
      btn.classList.add('disabled');
      btn.disabled = true;
      btn.textContent = '✓ Pagado';
    } else {
      alert(result.message || 'Error al procesar el pago.');
      btn.disabled = false;
      btn.textContent = 'Confirmar Pago';
    }
  } catch (err) {
    alert('Error de conexión.');
    btn.disabled = false;
    btn.textContent = 'Confirmar Pago';
  }
}

document.querySelectorAll('.tab').forEach(tab => {
  tab.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    
    const filter = tab.dataset.filter;
    const items = document.querySelectorAll('.checklist-item');
    
    items.forEach(item => {
      let show = false;
      if (filter === 'all') show = true;
      else if (filter === 'today') show = item.dataset.today === '1';
      else if (filter === 'week') show = item.dataset.week === '1';
      else if (filter === 'pending') show = item.dataset.status === 'pendiente';
      
      item.style.display = show ? 'flex' : 'none';
    });
  });
});
</script>
</body>
</html>
