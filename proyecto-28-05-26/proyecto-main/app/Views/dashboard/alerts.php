<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinSight — Centro de Alertas</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
  .alerts-container { max-width: 800px; margin: 0 auto; }
  .alert-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 12px;
    display: flex;
    gap: 15px;
    align-items: flex-start;
    transition: var(--transition);
    position: relative;
    animation: fadeUp 0.3s ease both;
  }
  .alert-card.unread { border-left: 4px solid var(--accent); background: rgba(200,240,100,0.03); }
  .alert-card:hover { transform: translateX(5px); border-color: rgba(255,255,255,0.15); }
  
  .alert-icon-lg {
    width: 45px; height: 45px; border-radius: 12px;
    display: grid; place-items: center; font-size: 20px; flex-shrink: 0;
  }
  .alert-icon-lg.alta { background: rgba(244,96,96,0.1); color: var(--danger); }
  .alert-icon-lg.media { background: rgba(244,162,74,0.1); color: var(--accent3); }
  .alert-icon-lg.baja { background: rgba(74,244,194,0.1); color: var(--accent2); }

  .alert-content { flex: 1; }
  .alert-header { display: flex; justify-content: space-between; margin-bottom: 5px; }
  .alert-priority { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
  .alert-time { font-size: 11px; color: var(--muted); }
  .alert-text { font-size: 14px; color: var(--text); line-height: 1.5; }
  
  .alert-actions { display: flex; gap: 10px; margin-top: 15px; }
  .btn-alert-action {
    background: transparent; border: 1px solid var(--border);
    color: var(--muted); padding: 5px 12px; border-radius: 8px;
    font-size: 12px; cursor: pointer; transition: all 0.2s;
  }
  .btn-alert-action:hover { background: var(--bg3); color: var(--text); border-color: var(--muted); }
  .btn-alert-action.delete:hover { color: var(--danger); border-color: var(--danger); background: rgba(244,96,96,0.05); }

  .empty-alerts {
    text-align: center; padding: 100px 20px;
    background: var(--card); border: 1px dashed var(--border); border-radius: 20px;
  }
</style>
</head>
<body>
<div class="shell">
  <?php include 'app/Views/partials/sidebar.php'; ?>

  <header class="topbar">
    <div class="topbar-left">
      <h1>Centro de Alertas</h1>
      <p>Gestión de notificaciones del sistema</p>
    </div>
    <div class="topbar-right">
      <button class="pill" onclick="markAllRead()" style="height: 36px; padding: 0 20px;">Marcar todo como leído</button>
    </div>
  </header>

  <main class="main">
    <div class="alerts-container">
      <?php if (empty($todas_las_alertas)): ?>
        <div class="empty-alerts">
            <div style="font-size: 48px; margin-bottom: 20px;">🔔</div>
            <h3 style="color:var(--text)">Buzón vacío</h3>
            <p style="color:var(--muted)">No tienes alertas o notificaciones por ahora.</p>
        </div>
      <?php else: ?>
        <?php foreach ($todas_las_alertas as $a): ?>
          <div class="alert-card <?php echo ($a['leido'] == 0) ? 'unread' : ''; ?>" id="alert-<?php echo $a['id_alerta']; ?>">
            <div class="alert-icon-lg <?php echo strtolower($a['prioridad']); ?>">
                <?php 
                    if($a['prioridad'] == 'Alta') echo '⚠️';
                    else if($a['prioridad'] == 'Media') echo '🕒';
                    else echo '🎯';
                ?>
            </div>
            <div class="alert-content">
              <div class="alert-header">
                <span class="alert-priority <?php echo strtolower($a['prioridad']); ?>"><?php echo $a['prioridad']; ?></span>
                <span class="alert-time"><?php echo date('d M, H:i', strtotime($a['fecha_alerta'])); ?></span>
              </div>
              <div class="alert-text"><?php echo htmlspecialchars($a['mensaje']); ?></div>
              
              <div class="alert-actions">
                <?php if($a['leido'] == 0): ?>
                    <button class="btn-alert-action" onclick="markRead(<?php echo $a['id_alerta']; ?>)">Marcar como leído</button>
                <?php endif; ?>
                <button class="btn-alert-action delete" onclick="deleteAlert(<?php echo $a['id_alerta']; ?>)">Eliminar</button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>

<script>
async function markRead(id) {
    try {
        const resp = await fetch('index.php?action=mark_alert_read&id=' + id);
        const res = await resp.json();
        if (res.success) {
            document.getElementById('alert-' + id).classList.remove('unread');
            // Recargar para actualizar badge si es necesario o manejar vía JS
            location.reload();
        }
    } catch (e) {}
}

async function markAllRead() {
    try {
        const resp = await fetch('index.php?action=mark_alerts_read');
        const res = await resp.json();
        if (res.success) location.reload();
    } catch (e) {}
}

async function deleteAlert(id) {
    if (!confirm('¿Eliminar esta notificación?')) return;
    try {
        const resp = await fetch('index.php?action=delete_alert&id=' + id);
        const res = await resp.json();
        if (res.success) {
            document.getElementById('alert-' + id).style.opacity = '0';
            setTimeout(() => location.reload(), 300);
        }
    } catch (e) {}
}
</script>
</body>
</html>
