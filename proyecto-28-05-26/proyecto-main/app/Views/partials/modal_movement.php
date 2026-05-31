<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>

<!-- ══════════════════════════════════════════
     MODAL: AÑADIR MOVIMIENTO (GLOBAL)
══════════════════════════════════════════ -->
<div class="modal-overlay" id="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title" id="modal-title">Nuevo movimiento</div>
        <div class="modal-subtitle">Registra un ingreso o gasto en tu historial</div>
      </div>
      <button class="modal-close" id="btn-close-modal" aria-label="Cerrar">✕</button>
    </div>

    <div class="modal-alert" id="modal-alert"></div>

    <style>
      /* Hide arrows/spinners in number inputs */
      input[type=number]::-webkit-inner-spin-button, 
      input[type=number]::-webkit-outer-spin-button { 
        -webkit-appearance: none; margin: 0; 
      }
      input[type=number] { -moz-appearance: textfield; }
      
      /* Estilo para los inputs de fecha Flatpickr */
      .flatpickr-input { background: var(--bg3) !important; color: var(--text) !important; cursor: pointer; }
      .flatpickr-mobile { display: none; }

      /* REDISEÑO 2 COLUMNAS PROFESIONAL CON SCROLL */
      .modal { 
        width: 820px; 
        max-width: 95vw; 
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        padding: 0;
        background: var(--card);
        border: 1px solid var(--border);
        box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        overflow: hidden;
      }
      
      .modal-header { padding: 25px 30px; border-bottom: 1px solid var(--border); flex-shrink: 0; }
      
      .modal-scroll-area {
        flex: 1;
        overflow-y: auto;
        padding: 0 30px 30px;
      }

      .modal-body-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 30px;
        margin-top: 20px;
      }

      .modal-section-title {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--muted);
        margin-bottom: 15px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 8px;
      }
      .modal-section-title::after {
        content: ''; flex: 1; height: 1px; background: var(--border);
      }

      .config-panel {
        background: rgba(255,255,255,0.02);
        padding: 20px;
        border-radius: var(--r);
        border: 1px solid var(--border);
        height: fit-content;
      }

      .btn-submit-container {
        padding: 20px 30px;
        border-top: 1px solid var(--border);
        background: var(--bg2);
        flex-shrink: 0;
      }

      @media (max-width: 768px) {
        .modal-body-grid { grid-template-columns: 1fr; gap: 20px; }
        .modal { max-height: 95vh; }
      }
    </style>

    <div class="modal-scroll-area">
        <div class="type-toggle" style="margin-top: 25px; margin-bottom: 25px;">
          <button type="button" class="type-btn active gasto" data-type="gasto" id="type-gasto">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2v12M14 8l-6 6-6-6"/></svg>
            Gasto
          </button>
          <button type="button" class="type-btn ingreso" data-type="ingreso" id="type-ingreso">
            <svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 14V2M2 8l6-6 6 6"/></svg>
            Ingreso
          </button>
        </div>

        <div class="modal-body-grid">
          <!-- COLUMNA IZQUIERDA: DATOS PRINCIPALES -->
          <div class="main-fields">
            <div class="modal-section-title">Datos Principales</div>
            
            <div class="field-group">
              <label class="field-label" for="mov-desc">Descripción <span>*</span></label>
              <input id="mov-desc" type="text" class="ff-input" placeholder="Ej: Almuerzo, Pasaje, Sueldo…" required />
            </div>

            <div class="field-group">
              <label class="field-label" for="mov-monto">Monto (Bs.) <span>*</span></label>
              <input id="mov-monto" type="number" min="0.10" step="0.10" class="ff-input" placeholder="0.00" required 
                     onkeydown="if(['e','E','+','-'].includes(event.key)) event.preventDefault();" />
            </div>

            <div class="field-group">
              <label class="field-label" for="mov-categoria">Categoría <span>*</span></label>
              <select id="mov-categoria" class="ff-select">
                <option value="" disabled selected>Selecciona una categoría</option>
                <!-- Categorías dinámicas vía JS -->
                <option value="__nueva__">＋ Crear nueva categoría…</option>
              </select>
              <div class="new-cat-row" id="new-cat-panel" style="display:none; margin-top:10px;">
                <input id="new-cat-input" type="text" class="ff-input" placeholder="Nombre de categoría" />
                <button class="btn-cat-save" id="btn-save-cat" type="button">OK</button>
              </div>
            </div>

            <div class="field-group">
              <label class="field-label" for="mov-cuenta">Cuenta / Destino <span>*</span></label>
              <select id="mov-cuenta" class="ff-select">
                <?php if(empty($cuentas)): ?>
                    <option value="" disabled selected>No tienes cuentas registradas</option>
                <?php else: ?>
                    <?php foreach($cuentas as $acc): ?>
                        <option value="<?php echo $acc['id_cuenta']; ?>">
                            <?php echo htmlspecialchars($acc['nombre_cuenta']); ?> 
                            (<?php echo $acc['codigo']; ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <!-- COLUMNA DERECHA: CONFIGURACIÓN ADICIONAL -->
          <div class="config-panel">
            <div class="modal-section-title">Opciones Avanzadas</div>
            
            <div id="extra-options">
              <label class="check-row" for="mov-fijo">
                <input type="checkbox" id="mov-fijo" />
                <div class="check-text">
                  ¿Gasto Fijo Recurrente?
                  <small>Se programará para todos los meses.</small>
                </div>
              </label>
              
              <div id="fijo-date-panel" style="display:none; margin-bottom:15px; padding-left:28px;">
                <label class="field-label" for="mov-fijo-fecha" style="font-size:12px;">Fecha del primer pago</label>
                <input id="mov-fijo-fecha" type="text" class="ff-input datepicker" placeholder="Seleccionar fecha" />
              </div>

              <label class="check-row" for="mov-hormiga">
                <input type="checkbox" id="mov-hormiga" />
                <div class="check-text">
                  ¿Gasto Hormiga / Ocasional?
                  <small>Para pequeños gastos no planificados.</small>
                </div>
              </label>
            </div>

            <!-- Sección de Programación de Ingresos -->
            <div id="income-scheduling" style="display:none;">
              <label class="check-row" for="mov-programar-ingreso">
                <input type="checkbox" id="mov-programar-ingreso" />
                <div class="check-text">
                  ¿Programar Ingreso Recurrente?
                  <small>Automatiza la entrada de dinero.</small>
                </div>
              </label>

              <div id="scheduling-details" style="display:none; margin-top:15px; padding-left:28px;">
                <div class="field-group">
                  <label class="field-label" for="mov-intervalo" style="font-size:12px;">Intervalo</label>
                  <select id="mov-intervalo" class="ff-select">
                    <option value="Semanal">Semanal</option>
                    <option value="Quincenal">Quincenal</option>
                    <option value="Mensual" selected>Mensual</option>
                    <option value="Personalizado">Personalizado...</option>
                  </select>
                </div>

                <div class="field-group" id="custom-interval-panel" style="display:none">
                  <label class="field-label" for="mov-intervalo-dias" style="font-size:12px;">Días</label>
                  <input id="mov-intervalo-dias" type="number" min="1" class="ff-input" placeholder="Ej: 45" />
                </div>

                <div class="field-group">
                  <label class="field-label" for="mov-fecha-limite" style="font-size:12px;">Fecha Límite</label>
                  <input id="mov-fecha-limite" type="text" class="ff-input datepicker" placeholder="Opcional: Indefinido" />
                </div>
              </div>
            </div>
          </div>
        </div>
    </div>

    <div class="btn-submit-container">
        <button type="button" class="btn-submit" id="btn-submit-mov" style="height: 50px; font-size: 16px;">
          Confirmar Registro
        </button>
    </div>

  </div>
</div>

<script>
(function() {
  const ALL_CATEGORIES = <?php echo json_encode($all_categories); ?>;

  function updateCategorySelect() {
      const selCat = document.getElementById('mov-categoria');
      const currentType = selectedType === 'gasto' ? 'Gasto' : 'Ingreso';
      
      // Guardar la opción "Nueva categoría"
      const newCatOpt = selCat.options[selCat.options.length - 1];
      
      // Limpiar opciones
      selCat.innerHTML = '<option value="" disabled selected>Selecciona una categoría</option>';

      // Filtrar, Ordenar y añadir
      const filtered = ALL_CATEGORIES
        .filter(c => c.tipo_categoria === currentType && 
                     c.nombre_categoria.toLowerCase() !== 'ahorro' && 
                     c.nombre_categoria.toLowerCase() !== 'ahorro automático' &&
                     c.nombre_categoria.toLowerCase() !== 'ahorro automatico')
        .sort((a, b) => a.nombre_categoria.localeCompare(b.nombre_categoria));

      filtered.forEach(c => {
          const opt = document.createElement('option');
          opt.value = c.id_categoria;
          opt.textContent = c.nombre_categoria;
          selCat.appendChild(opt);
      });
      
      selCat.appendChild(newCatOpt);
      selCat.value = "";
  }

  // Inicializar Flatpickr en español con tema oscuro
  flatpickr(".datepicker", {
    locale: "es",
    dateFormat: "Y-m-d",
    altInput: true,
    altFormat: "d/m/Y",
    minDate: "today",
    animate: true
  });

  const modalOverlay = document.getElementById('modal-overlay');
  const btnClose = document.getElementById('btn-close-modal');
  const btnSubmit = document.getElementById('btn-submit-mov');
  const typeBtns = document.querySelectorAll('.type-btn');
  const selCat = document.getElementById('mov-categoria');
  const newCatPanel = document.getElementById('new-cat-panel');
  const btnSaveCat = document.getElementById('btn-save-cat');
  const extraOptions = document.getElementById('extra-options');
  const checkFijo = document.getElementById('mov-fijo');
  const checkHormiga = document.getElementById('mov-hormiga');
  const fijoDatePanel = document.getElementById('fijo-date-panel');
  const incomeScheduling = document.getElementById('income-scheduling');
  const checkProgramarIngreso = document.getElementById('mov-programar-ingreso');
  const schedulingDetails = document.getElementById('scheduling-details');
  const selIntervalo = document.getElementById('mov-intervalo');
  const customIntervalPanel = document.getElementById('custom-interval-panel');

  let selectedType = 'gasto';
  let isTypeLocked = false;

  function updateUIForType() {
    if (selectedType === 'ingreso') {
      extraOptions.style.display = 'none';
      incomeScheduling.style.display = 'block';
    } else {
      extraOptions.style.display = 'block';
      incomeScheduling.style.display = 'none';
    }
    updateCategorySelect();
  }

  typeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      if (isTypeLocked) return;
      typeBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      selectedType = btn.dataset.type;
      updateUIForType();
    });
  });

  checkFijo.addEventListener('change', () => {
    if (checkFijo.checked) {
      checkHormiga.checked = false;
      fijoDatePanel.style.display = 'block';
    } else {
      fijoDatePanel.style.display = 'none';
    }
  });

  checkHormiga.addEventListener('change', () => {
    if (checkHormiga.checked) {
      checkFijo.checked = false;
      fijoDatePanel.style.display = 'none';
    }
  });

  checkProgramarIngreso.addEventListener('change', () => {
    schedulingDetails.style.display = checkProgramarIngreso.checked ? 'block' : 'none';
  });

  selIntervalo.addEventListener('change', () => {
    customIntervalPanel.style.display = selIntervalo.value === 'Personalizado' ? 'block' : 'none';
  });

  selCat.addEventListener('change', function() {
    newCatPanel.style.display = this.value === '__nueva__' ? 'flex' : 'none';
  });

  btnSaveCat.addEventListener('click', async () => {
    const nombre = document.getElementById('new-cat-input').value.trim();
    if (!nombre) return;
    btnSaveCat.disabled = true;
    try {
      const formData = new FormData();
      formData.append('nombre', nombre);
      formData.append('tipo', selectedType === 'gasto' ? 'Gasto' : 'Ingreso');
      const resp = await fetch('index.php?action=add_category', { method: 'POST', body: formData });
      const res = await resp.json();
      if (res.success) {
        ALL_CATEGORIES.push({
            id_categoria: res.id,
            nombre_categoria: res.nombre,
            tipo_categoria: selectedType === 'gasto' ? 'Gasto' : 'Ingreso'
        });
        updateCategorySelect();
        selCat.value = res.id;
        newCatPanel.style.display = 'none';
        document.getElementById('new-cat-input').value = '';
      } else {
          showModalAlert(res.message);
      }
    } catch (e) {
        showModalAlert('Error al crear categoría.');
    }
    btnSaveCat.disabled = false;
  });

  btnSubmit.addEventListener('click', async () => {
    const desc = document.getElementById('mov-desc').value.trim();
    const monto = document.getElementById('mov-monto').value;
    const catId = document.getElementById('mov-categoria').value;
    const accountId = document.getElementById('mov-cuenta').value;
    const isHormiga = document.getElementById('mov-hormiga').checked ? 1 : 0;
    const isFijo = document.getElementById('mov-fijo').checked ? 1 : 0;
    const fechaFijo = document.getElementById('mov-fijo-fecha').value;
    
    const programarIngreso = document.getElementById('mov-programar-ingreso').checked ? 1 : 0;
    const intervalo = document.getElementById('mov-intervalo').value;
    const intervaloDias = document.getElementById('mov-intervalo-dias').value;
    const fechaLimite = document.getElementById('mov-fecha-limite').value;

    if (!desc || !monto || !catId || catId === '__nueva__' || !accountId) {
      showModalAlert('Completa todos los campos obligatorios, incluyendo la cuenta.');
      return;
    }

    if (isFijo && !fechaFijo) {
      showModalAlert('Indica la fecha de pago para el gasto fijo.');
      return;
    }

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="spinner"></span>Guardando…';

    try {
      const formData = new FormData();
      formData.append('tipo', selectedType === 'gasto' ? 'Gasto' : 'Ingreso');
      formData.append('descripcion', desc);
      formData.append('monto', monto);
      formData.append('categoria_id', catId);
      formData.append('id_cuenta', accountId);
      formData.append('es_ocasional', isHormiga);
      formData.append('es_fijo', isFijo);
      if (isFijo) {
          const day = new Date(fechaFijo + 'T00:00:00').getDate();
          formData.append('dia_pago', day);
      }
      if (selectedType === 'ingreso' && programarIngreso) {
        formData.append('programar_ingreso', 1);
        formData.append('intervalo', intervalo);
        if (intervalo === 'Personalizado') formData.append('intervalo_personalizado', intervaloDias);
        formData.append('fecha_limite', fechaLimite);
      }

      const resp = await fetch('index.php?action=add_movement', { method: 'POST', body: formData });
      const res = await resp.json();
      if (res.success) {
        location.reload();
      } else {
        showModalAlert(res.message);
      }
    } catch (e) {
      console.error(e);
      showModalAlert('Error de conexión o de servidor.');
    }
    btnSubmit.disabled = false;
    btnSubmit.textContent = 'Confirmar Registro';
  });

  btnClose.addEventListener('click', () => modalOverlay.classList.remove('open'));
  
  function showModalAlert(msg) {
    const el = document.getElementById('modal-alert');
    el.textContent = msg;
    el.className = 'modal-alert visible error';
  }

  window.openMovementModal = function(forceType = '') {
    modalOverlay.classList.add('open');
    if (forceType === 'ingreso') {
      selectedType = 'ingreso';
      isTypeLocked = true;
      document.getElementById('type-gasto').style.display = 'none';
      document.getElementById('type-ingreso').classList.add('active');
      document.getElementById('type-gasto').classList.remove('active');
      document.getElementById('modal-title').textContent = 'Nuevo ingreso';
    } else if (forceType === 'gasto') {
      selectedType = 'gasto';
      isTypeLocked = true;
      document.getElementById('type-ingreso').style.display = 'none';
      document.getElementById('type-gasto').classList.add('active');
      document.getElementById('type-ingreso').classList.remove('active');
      document.getElementById('modal-title').textContent = 'Nuevo gasto';
    } else {
      isTypeLocked = false;
      document.getElementById('type-gasto').style.display = 'flex';
      document.getElementById('type-ingreso').style.display = 'flex';
      document.getElementById('modal-title').textContent = 'Nuevo movimiento';
    }
    updateUIForType();
  };
})();
</script>
