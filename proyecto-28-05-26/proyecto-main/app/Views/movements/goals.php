<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinSight — Metas de Ahorro</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
<style>
  .goals-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 24px;
  }
  .goal-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    transition: var(--transition);
    position: relative;
  }
  .goal-card:hover { transform: translateY(-4px); border-color: var(--accent2); box-shadow: var(--shadow); }
  
  .goal-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
  .goal-title h3 { font-size: 18px; font-weight: 600; color: var(--text); }
  .goal-status { font-size: 10px; font-weight: 700; text-transform: uppercase; padding: 4px 8px; border-radius: 10px; background: rgba(74,244,194,0.1); color: var(--accent2); }
  
  .goal-progress { margin-bottom: 20px; }
  .progress-info { display: flex; justify-content: space-between; font-size: 13px; color: var(--muted); margin-bottom: 8px; }
  .progress-track { height: 8px; background: var(--bg3); border-radius: 4px; overflow: hidden; }
  .progress-fill { height: 100%; background: var(--accent2); border-radius: 4px; transition: width 1s ease-out; }
  
  .goal-amounts { font-family: var(--font-mono); font-size: 14px; font-weight: 500; }
  .goal-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 15px; border-top: 1px solid var(--border); padding-top: 15px; }
  .goal-date { font-size: 12px; color: var(--muted); }
  
  .btn-delete-goal {
    background: transparent; border: none; color: var(--muted); cursor: pointer; padding: 5px; transition: color 0.2s;
  }
  .btn-delete-goal:hover { color: var(--danger); }

  /* Modal Specifics */
  .modal-goal { max-width: 500px; }
</style>
</head>
<body>
<div class="shell">
  <?php 
    include 'app/Views/partials/sidebar.php'; 
  ?>

  <header class="topbar">
    <div class="topbar-left">
      <h1>Metas de Ahorro</h1>
      <p>Tus objetivos financieros a largo plazo</p>
    </div>
    <div class="topbar-right">
      <button class="btn-add" onclick="openGoalModal()">+ Nueva Meta</button>
    </div>
  </header>

  <main class="main">
    <div class="goals-grid">
      <?php if (empty($metas)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 60px;">
          <div style="font-size: 40px; margin-bottom: 20px;">🎯</div>
          <p style="color:var(--muted); font-size: 16px;">No tienes metas de ahorro definidas.<br>¡Define tu primer gran objetivo hoy mismo!</p>
          <button class="btn-add" style="margin: 20px auto 0;" onclick="openGoalModal()">Crear mi primera meta</button>
        </div>
      <?php else: ?>
        <?php foreach ($metas as $m): ?>
          <?php $pct = min(100, round(($m['monto_actual'] / $m['monto_objetivo']) * 100)); ?>
          <div class="goal-card" id="goal-<?php echo $m['id_meta']; ?>">
            <div class="goal-header">
              <div class="goal-title">
                <h3><?php echo htmlspecialchars($m['nombre_meta']); ?></h3>
                <?php if($m['porcentaje_ingreso'] > 0): ?>
                    <div style="font-size: 11px; color: var(--accent2); margin-top: 4px;">
                        ⚡ Autoguardado: <?php echo number_format($m['porcentaje_ingreso'], 1); ?>% de ingresos
                    </div>
                <?php endif; ?>
              </div>
              <button class="btn-delete-goal" onclick="confirmDeleteGoal(<?php echo $m['id_meta']; ?>, '<?php echo addslashes($m['nombre_meta']); ?>', <?php echo $m['monto_actual']; ?>)" title="Eliminar meta">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
              </button>
            </div>
            
            <div class="goal-progress">
              <div class="progress-info">
                <span>Progreso</span>
                <span><?php echo $pct; ?>%</span>
              </div>
              <div class="progress-track">
                <div class="progress-fill" style="width: <?php echo $pct; ?>%"></div>
              </div>
            </div>
            
            <div class="goal-amounts">
              Bs. <?php echo number_format($m['monto_actual'], 2); ?> / <span style="color:var(--muted)">Bs. <?php echo number_format($m['monto_objetivo'], 2); ?></span>
            </div>
            
            <div class="goal-footer">
              <div class="goal-date">
                📅 Límite: <?php echo $m['fecha_limite'] ? date('d/m/Y', strtotime($m['fecha_limite'])) : 'Sin fecha'; ?>
              </div>
              <span class="goal-status" style="<?php echo ($pct >= 100) ? 'background:rgba(200,240,100,0.1); color:var(--accent);' : ''; ?>">
                <?php echo ($pct >= 100) ? '¡Lograda!' : 'En curso'; ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- MODAL: NUEVA META -->
<div class="modal-overlay" id="goal-modal-overlay">
  <div class="modal modal-goal">
    <div class="modal-header">
      <div>
        <div class="modal-title">Nueva Meta de Ahorro</div>
        <div class="modal-subtitle">Define tu próximo gran paso financiero</div>
      </div>
      <button class="modal-close" onclick="closeGoalModal()">✕</button>
    </div>

    <div class="modal-alert" id="goal-modal-alert"></div>

    <div class="field-group">
      <label class="field-label">Nombre de la Meta <span>*</span></label>
      <input id="goal-nombre" type="text" class="ff-input" placeholder="Ej: Viaje a Europa, Nuevo Auto..." required />
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px;">
        <div class="field-group">
          <label class="field-label">Monto Objetivo <span>*</span></label>
          <input id="goal-objetivo" type="number" step="0.10" class="ff-input" placeholder="0.00" required />
        </div>
        <div class="field-group">
          <label class="field-label">Monto Inicial</label>
          <input id="goal-actual" type="number" step="0.10" class="ff-input" value="0.00" />
        </div>
    </div>

    <div class="field-group">
      <label class="field-label">Fecha Límite <span>(Opcional)</span></label>
      <input id="goal-fecha" type="text" class="ff-input datepicker" placeholder="Selecciona una fecha..." />
    </div>

    <div style="margin-top: 20px; padding: 15px; background: rgba(74,244,194,0.05); border: 1px solid rgba(74,244,194,0.2); border-radius: 12px;">
        <label class="field-label" style="color: var(--accent2);">Ahorro Automático Inteligente</label>
        <div style="display:flex; align-items:center; gap:10px; margin-top: 10px;">
            <div style="flex:1; font-size: 12px; color: var(--muted);">
                Destinar un porcentaje de cada ingreso que recibas a esta meta.
            </div>
            <div style="width: 80px; position:relative;">
                <input id="goal-porcentaje" type="number" min="0" max="100" step="0.5" class="ff-input" placeholder="0" style="padding-right: 25px;" />
                <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size: 12px; color: var(--muted);">%</span>
            </div>
        </div>
    </div>

    <button type="button" class="btn-submit" id="btn-save-goal" style="margin-top:25px;" onclick="saveGoal()">
      Crear Meta
    </button>
  </div>
</div>

<!-- MODAL: ELIMINAR META -->
<div class="modal-overlay" id="delete-modal-overlay">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <div>
        <div class="modal-title">¿Eliminar Meta?</div>
        <div class="modal-subtitle" id="delete-goal-name"></div>
      </div>
    </div>
    
    <div style="margin: 20px 0;">
        <label class="check-row" for="devolver-presupuesto">
            <input type="checkbox" id="devolver-presupuesto" checked />
            <div class="check-text">
                Devolver lo ahorrado al presupuesto
                <small id="delete-goal-saved-text"></small>
            </div>
        </label>
        <p style="font-size: 12px; color: var(--muted); line-height: 1.4;">
            Esta acción no se puede deshacer. Se registrará un ingreso por el monto devuelto.
        </p>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
        <button class="ff-input" style="background:transparent; cursor:pointer;" onclick="closeDeleteModal()">Cancelar</button>
        <button class="btn-submit" style="background:var(--danger); box-shadow: 0 0 20px rgba(244,96,96,0.2);" id="btn-confirm-delete">Eliminar</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
<script>
  flatpickr(".datepicker", {
    locale: "es",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d/m/Y",
    minDate: "today"
  });

  const goalModal = document.getElementById('goal-modal-overlay');
  const deleteModal = document.getElementById('delete-modal-overlay');
  let currentGoalToDelete = null;

  function openGoalModal() { goalModal.classList.add('open'); }
  function closeGoalModal() { goalModal.classList.remove('open'); }

  async function saveGoal() {
    const btn = document.getElementById('btn-save-goal');
    const nombre = document.getElementById('goal-nombre').value.trim();
    const objetivo = document.getElementById('goal-objetivo').value;
    const actual = document.getElementById('goal-actual').value;
    const fecha = document.getElementById('goal-fecha').value;
    const porcentaje = document.getElementById('goal-porcentaje').value || 0;

    if (!nombre || !objetivo) {
        showModalAlert('Completa los campos obligatorios.', 'goal-modal-alert');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>Guardando...';

    try {
        const formData = new FormData();
        formData.append('nombre', nombre);
        formData.append('objetivo', objetivo);
        formData.append('actual', actual);
        formData.append('fecha_limite', fecha);
        formData.append('porcentaje', porcentaje);

        const resp = await fetch('index.php?action=add_goal', { method: 'POST', body: formData });
        const res = await resp.json();
        if (res.success) {
            location.reload();
        } else {
            showModalAlert(res.message, 'goal-modal-alert');
            btn.disabled = false;
            btn.textContent = 'Crear Meta';
        }
    } catch (e) {
        showModalAlert('Error de conexión.', 'goal-modal-alert');
        btn.disabled = false;
        btn.textContent = 'Crear Meta';
    }
  }

  function confirmDeleteGoal(id, name, saved) {
    currentGoalToDelete = id;
    document.getElementById('delete-goal-name').textContent = name;
    document.getElementById('delete-goal-saved-text').textContent = 'Bs. ' + parseFloat(saved).toLocaleString('es-BO', {minimumFractionDigits: 2});
    deleteModal.classList.add('open');
    
    document.getElementById('btn-confirm-delete').onclick = async () => {
        const devolver = document.getElementById('devolver-presupuesto').checked ? '1' : '0';
        const btn = document.getElementById('btn-confirm-delete');
        btn.disabled = true;
        
        try {
            const formData = new FormData();
            formData.append('id_meta', id);
            formData.append('devolver', devolver);

            const resp = await fetch('index.php?action=delete_goal', { method: 'POST', body: formData });
            const res = await resp.json();
            if (res.success) {
                location.reload();
            }
        } catch (e) {}
        btn.disabled = false;
    };
  }

  function closeDeleteModal() { deleteModal.classList.remove('open'); }

  function showModalAlert(msg, elementId) {
    const el = document.getElementById(elementId);
    el.textContent = msg;
    el.className = 'modal-alert visible error';
  }
</script>
</body>
</html>
