<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$user_role_id = isset($_SESSION['user_role_id']) ? (int)$_SESSION['user_role_id'] : 3;
$user_name = $_SESSION['user_name'] ?? 'Usuario';
?>
  <!-- ── SIDEBAR ── -->
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-icon">
        <svg viewBox="0 0 20 20" fill="none">
          <path d="M3 14l4-5 3 3 3-4 4 6" stroke="#0b0e14" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <circle cx="15" cy="5" r="2.5" fill="#0b0e14"/>
        </svg>
      </div>
      <span class="logo-name">Fin<span>Sight</span></span>
    </div>

    <nav class="nav-section">
      <div class="nav-label">Principal</div>
      <a class="nav-item <?php echo $current_action == 'dashboard' ? 'active' : ''; ?>" href="index.php?action=dashboard">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="1" y="1" width="6" height="6" rx="1.5"/><rect x="9" y="1" width="6" height="6" rx="1.5"/>
          <rect x="1" y="9" width="6" height="6" rx="1.5"/><rect x="9" y="9" width="6" height="6" rx="1.5"/>
        </svg>
        Dashboard
      </a>
      
      <a class="nav-item <?php echo $current_action == 'movimientos' ? 'active' : ''; ?>" href="index.php?action=movimientos">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 8h12M2 4h8M2 12h10"/></svg>
        <?php echo ($user_role_id === 3) ? 'Mis Movimientos' : 'Control Global'; ?>
      </a>

      <a class="nav-item <?php echo $current_action == 'gastos_fijos' ? 'active' : ''; ?>" href="index.php?action=gastos_fijos">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="6"/><path d="M8 5v3l2 2"/></svg>
        Gastos Fijos
      </a>

      <div class="nav-label">Finanzas</div>
      <a class="nav-item <?php echo $current_action == 'ingresos' ? 'active' : ''; ?>" href="index.php?action=ingresos">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2v12M4 6l4-4 4 4"/></svg>
        Ingresos
      </a>
      <a class="nav-item <?php echo $current_action == 'metas' ? 'active' : ''; ?>" href="index.php?action=metas">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="8" cy="8" r="5"/><path d="M8 5v3M8 11v.5"/></svg>
        Metas de Ahorro
      </a>
      <a class="nav-item <?php echo $current_action == 'alerts' ? 'active' : ''; ?>" href="index.php?action=alerts">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2a6 6 0 100 12A6 6 0 008 2zM8 6v2l1.5 1.5"/></svg>
        Alertas
      </a>

      <?php if ($user_role_id === 1 || $user_role_id === 2): ?>
      <div class="nav-label">Administración</div>
      <a class="nav-item <?php echo $current_action == 'grupos' ? 'active' : ''; ?>" href="index.php?action=grupos">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 8a3 3 0 100-6 3 3 0 000 6zM2 14.5c0-3.5 3-5 6-5s6 1.5 6 5"/></svg>
        Administrar Grupo
      </a>
      <?php endif; ?>

      <?php if ($user_role_id === 1): ?>
      <div class="nav-label">Sistema</div>
      <a class="nav-item <?php echo $current_action == 'audit' ? 'active' : ''; ?>" href="index.php?action=audit">
        <svg class="nav-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 13H4a2 2 0 01-2-2V5a2 2 0 012-2h11a2 2 0 012 2v2.5M13 13h4M15 11v4"/></svg>
        Auditoría Global
      </a>
      <?php endif; ?>

      <?php if (!empty($avisos_pago)): ?>
      <div class="nav-label" style="color:var(--accent3);">⚠️ Avisos de Pago</div>
      <div style="padding: 0 16px;">
        <?php foreach($avisos_pago as $aviso): ?>
            <div class="payment-alert-item">
                <span style="font-size:16px;">⏳</span>
                <div>
                    <div style="font-weight:600;"><?php echo htmlspecialchars($aviso['nombre']); ?></div>
                    <div style="opacity:0.8;">Vence pronto</div>
                </div>
            </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </nav>

    <div class="sidebar-bottom">
      <div class="user-chip">
        <div class="avatar" id="user-avatar"><?php 
          $parts = explode(' ', trim($user_name));
          if (count($parts) >= 2) {
              echo strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
          } else {
              echo strtoupper(substr($user_name, 0, 2));
          }
        ?></div>
        <div class="user-info">
          <div class="user-name" id="user-name"><?php echo htmlspecialchars($user_name); ?></div>
          <div class="user-role"><?php echo htmlspecialchars($_SESSION['user_role_name'] ?? ''); ?> · Bs. BOB</div>
        </div>
      </div>
      <button class="btn-logout" id="btn-logout-sidebar">
        <svg width="13" height="13" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M10 2H3a1 1 0 00-1 1v10a1 1 0 001 1h7M11 5l3 3-3 3M6 8h8"/></svg>
        Cerrar sesión
      </button>
    </div>
  </aside>

  <script>
    document.getElementById('btn-logout-sidebar').addEventListener('click', () => {
      window.location.href = 'index.php?action=logout';
    });
  </script>
