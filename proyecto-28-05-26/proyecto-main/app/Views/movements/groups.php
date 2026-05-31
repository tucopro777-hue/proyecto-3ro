<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>FinSight — Mis Grupos</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Outfit:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
  .groups-container { display: flex; flex-direction: column; gap: 30px; }
  .group-section { background: var(--bg2); border: 1px solid var(--border); border-radius: 20px; padding: 30px; }
  .group-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid var(--border); padding-bottom: 20px; }
  .group-title h2 { font-family: var(--font-head); font-size: 24px; color: var(--accent); }
  
  .members-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }
  .member-card {
    background: var(--card); border: 1px solid var(--border); border-radius: 16px; padding: 20px;
    transition: var(--transition);
  }
  .member-card:hover { border-color: var(--accent2); transform: translateY(-3px); }
  
  .member-top { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
  .member-avatar { width: 40px; height: 40px; border-radius: 10px; background: var(--bg3); display: grid; place-items: center; font-weight: 600; color: var(--accent2); }
  .member-info { flex: 1; }
  .member-name { font-size: 14px; font-weight: 600; color: var(--text); }
  .member-role { font-size: 11px; color: var(--muted); }

  .member-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 15px; padding-top: 15px; border-top: 1px dashed var(--border); }
  .stat-item { font-size: 12px; }
  .stat-label { color: var(--muted); display: block; margin-bottom: 2px; }
  .stat-val { font-family: var(--font-mono); font-weight: 500; }
  .stat-val.ingreso { color: var(--accent2); }
  .stat-val.gasto { color: var(--danger); }

  .btn-invite { background: rgba(200,240,100,0.1); color: var(--accent); border: 1px solid rgba(200,240,100,0.2); padding: 8px 16px; border-radius: 10px; cursor: pointer; font-size: 13px; font-weight: 600; transition: all 0.2s; }
  .btn-invite:hover { background: var(--accent); color: #000; }
</style>
</head>
<body>
<div class="shell">
  <?php include 'app/Views/partials/sidebar.php'; ?>

  <header class="topbar">
    <div class="topbar-left">
      <h1>Gestión de Grupos</h1>
      <p>Supervisa y regula las finanzas de tus miembros</p>
    </div>
    <div class="topbar-right">
      <button class="btn-add" onclick="openInviteModal()">+ Añadir Miembro</button>
    </div>
  </header>

  <main class="main">
    <div class="groups-container">
      <?php if (empty($grupos_con_miembros)): ?>
        <div class="card" style="text-align: center; padding: 80px 20px; border: 2px dashed var(--border);">
            <div style="font-size: 50px; margin-bottom: 20px;">👨‍👩‍👧‍👦</div>
            <h2 style="color:var(--text)">No tienes grupos activos</h2>
            <p style="color:var(--muted); margin-bottom: 25px; max-width: 400px; margin-left: auto; margin-right: auto;">
                Como <b>Supervisor</b>, puedes crear un grupo para monitorear y regular las finanzas de tus miembros (familia, equipo o dependientes).
            </p>
            <button class="btn-add" style="margin: 0 auto; height: 45px; padding: 0 30px;" onclick="openCreateGroupModal()">
                Crear mi primer grupo
            </button>
        </div>
      <?php else: ?>
        <?php foreach ($grupos_con_miembros as $g): ?>
          <section class="group-section">
            <div class="group-header">
              <div class="group-title">
                <h2><?php echo htmlspecialchars($g['nombre_grupo']); ?></h2>
                <small style="color:var(--muted)">Creado el <?php echo date('d/m/Y', strtotime($g['fecha_creacion'])); ?></small>
              </div>
              <button class="btn-invite" onclick="openInviteModal(<?php echo $g['id_grupo']; ?>, '<?php echo addslashes($g['nombre_grupo']); ?>')">
                Invitar Miembro Existente
              </button>
            </div>

            <div class="members-grid">
              <?php foreach ($g['miembros'] as $m): ?>
                <div class="member-card">
                  <div class="member-top">
                    <div class="member-avatar"><?php echo strtoupper(substr($m['nombre'], 0, 2)); ?></div>
                    <div class="member-info">
                      <div class="member-name"><?php echo htmlspecialchars($m['nombre']); ?></div>
                      <div class="member-role"><?php echo $m['rango']; ?></div>
                    </div>
                  </div>
                  <div style="font-size: 11px; color: var(--muted);"><?php echo htmlspecialchars($m['email']); ?></div>
                  
                  <div class="member-stats">
                    <div class="stat-item">
                        <span class="stat-label">Ingresos Mes</span>
                        <span class="stat-val ingreso">Bs. <?php echo number_format($m['ingresos_mes'], 2); ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Gastos Mes</span>
                        <span class="stat-val gasto">Bs. <?php echo number_format($m['gastos_mes'], 2); ?></span>
                    </div>
                  </div>
                  
                  <div style="margin-top:15px; border-top: 1px dashed var(--border); padding-top:15px;">
                      <button class="pill" style="width:100%; border-style:dashed;" onclick="setMemberLimit(<?php echo $m['id_usuario']; ?>, '<?php echo addslashes($m['nombre']); ?>')">
                        Regular Límite de Gasto
                      </button>
                  </div>
                </div>
              <?php endforeach; ?>
              
              <?php if (empty($g['miembros'])): ?>
                <p style="grid-column: 1/-1; color:var(--muted); font-size:13px; text-align:center; padding: 20px;">Este grupo aún no tiene miembros.</p>
              <?php endif; ?>
            </div>
          </section>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- MODAL: INVITAR MIEMBRO -->
<div class="modal-overlay" id="modal-invite">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <div>
        <div class="modal-title">Añadir Miembro</div>
        <div class="modal-subtitle" id="invite-group-name"></div>
      </div>
      <button class="modal-close" onclick="closeModals()">✕</button>
    </div>
    <input type="hidden" id="invite-group-id" />
    <div class="field-group">
      <label class="field-label">Nombre o Gmail del Usuario</label>
      <input id="invite-identifier" type="text" class="ff-input" placeholder="correo@gmail.com o Nombre" />
    </div>
    <div class="field-group">
      <label class="field-label">Rango en el Grupo</label>
      <select id="invite-rango" class="ff-select">
        <option value="Miembro">Miembro Estándar</option>
        <option value="Hijo/Dependiente">Hijo / Dependiente</option>
        <option value="Socio">Socio</option>
      </select>
    </div>
    <button type="button" class="btn-submit" id="btn-do-invite">Vincular al Grupo</button>
  </div>
</div>

<script>
function openInviteModal(id, name) { 
    document.getElementById('invite-group-id').value = id;
    document.getElementById('invite-group-name').textContent = 'Grupo: ' + name;
    document.getElementById('modal-invite').classList.add('open'); 
}
function closeModals() { document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open')); }

document.getElementById('btn-do-invite').addEventListener('click', async () => {
    const gid = document.getElementById('invite-group-id').value;
    const iden = document.getElementById('invite-identifier').value.trim();
    const rango = document.getElementById('invite-rango').value;
    if (!iden) return;
    const formData = new FormData();
    formData.append('id_grupo', gid);
    formData.append('identificador', iden);
    formData.append('rango', rango);
    const resp = await fetch('index.php?action=add_group_member', { method: 'POST', body: formData });
    const res = await resp.json();
    if (res.success) location.reload();
    else alert(res.message);
});

function setMemberLimit(uid, name) {
    const limit = prompt('Establecer límite de gasto mensual para ' + name + ' (Bs.):');
    if (limit !== null) {
        const formData = new FormData();
        formData.append('limit', limit);
        formData.append('month', new Date().getMonth() + 1);
        formData.append('year', new Date().getFullYear());
        formData.append('target_user_id', uid);
        
        fetch('index.php?action=set_budget', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(res => { if(res.success) alert('Límite establecido correctamente.'); });
    }
}
</script>
</body>
</html>
