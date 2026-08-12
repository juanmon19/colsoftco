<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../app/logica_inventario.php';
require_once __DIR__ . '/../../app/alerta_stock.php';

$logicaInventario = new InventarioLogica();
$logicaAlerta = new AlertaStockLogica();

$materiales = $logicaInventario->listarMateriales();

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $idMaterial = $_POST['producto'] ?? '';
  $cantidadMinima = $_POST['cantidad'] ?? '';
  $notificarEmail = isset($_POST['notif']) && in_array('email', $_POST['notif']);
  $correoNotificacion = trim($_POST['correo_notificacion'] ?? '');

  if ($idMaterial === '' || $cantidadMinima === '') {
    $mensaje = "Selecciona un producto e ingresa la cantidad mínima.";
  } elseif ($notificarEmail && $correoNotificacion === '') {
    $mensaje = "Ingresa el correo donde deseas recibir la notificación.";
  } else {

    $logicaAlerta->guardarConfiguracion(
      $idMaterial,
      $cantidadMinima,
      $notificarEmail,
      $correoNotificacion
    );

    // Revisa de inmediato si con este mínimo el material ya está en alerta
    $logicaAlerta->verificarStock($idMaterial);

    $mensaje = "Configuración de stock mínimo guardada correctamente.";
    $materiales = $logicaInventario->listarMateriales();
  }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="controlstock.css">
  <title>Control de stock</title>
</head>

<body>

  <header>
    <div class="logo">
      <a href="../panel_admin/panel_admin.php">
        <img src="../../public/imagenes/logo.png" alt="logo">
      </a>
    </div>

    <div class="header-title">
      <h1>Control de Stock</h1>
    </div>

    <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
      Cerrar sesión
    </button>
  </header>

  <div class="page-body">

    <div class="content" style="margin: 0 auto; max-width: 1000px; width: 100%;">

      <?php if (!empty($mensaje)): ?>
        <div class="mensaje-stock">
          <?= htmlspecialchars($mensaje) ?>
        </div>
      <?php endif; ?>

      <div class="form-card">
        <div class="form-header">
          <span class="form-header-bar"></span>
          Registra el nivel mínimo de cada materia prima
        </div>

        <div class="form-body">
          <form id="regForm" method="POST">
            <div class="form-grid">

              <div class="form-group">
                <label for="producto">Producto</label>
                <div class="select-wrap">
                  <select id="producto" name="producto" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($materiales as $material): ?>
                      <option value="<?= (int)$material['id_material'] ?>">
                        <?= htmlspecialchars($material['nombre_material']) ?>
                        (stock actual: <?= htmlspecialchars($material['stock_actual']) ?>
                        <?= htmlspecialchars($material['nombre_unidad'] ?? '') ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <div class="form-group full">
                <label for="cantidad">Cantidad mínima</label>
                <input type="number" id="cantidad" name="cantidad" min="0" step="0.01" placeholder="Ingrese la cantidad mínima" required />
              </div>

              <hr class="form-divider" />

              <div class="check-section">
                <label>Seleccione dónde recibir la notificación</label>
                <div class="check-options">
                  <label class="check-opt">
                    <input type="checkbox" id="chkEmail" name="notif[]" value="email" />
                    <span>Correo Electrónico</span>
                  </label>
                </div>

                <div class="form-group full" id="campoCorreo" style="display:none; margin-top: 12px;">
                  <label for="correo_notificacion">Correo de notificación</label>
                  <input type="email" id="correo_notificacion" name="correo_notificacion" placeholder="correo@empresa.com">
                </div>
              </div>

              <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="resetForm()">Limpiar</button>
                <button type="submit" class="btn btn-primary">Registrar</button>
              </div>
            </div>
          </form>

          <hr class="form-divider" style="margin: 30px 0;" />

          <div class="acciones-stock">
            <button type="button" class="btn btn-primary"
              onclick="window.location.href='inventario/registrar_stock.php'">
              Registrar Stock
            </button>

            <button type="button" class="btn btn-primary"
              onclick="window.location.href='alertas_stock/lista_alertas.php'">
              Ver Alertas de Stock
            </button>

            <button type="button" class="btn btn-primary"
              onclick="window.location.href='inventario/lista_inventario.php'">
              Ver Inventario
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>

  <footer>
    <div class="footer-divider"></div>

    <div class="footer-top">
      <div>
        <p class="footer-brand-name">COLSOFTCO</p>
        <p class="footer-brand-sub">Sistema de Gestión</p>
        <p class="footer-brand-desc">
          Sistema de gestión y administración de materias primas para Max&Flex.
          Eficiencia en inventarios y movimientos empresariales.
        </p>
      </div>

      <div>
        <p class="footer-col-title">Contacto</p>
        <div class="footer-contact-item">📍 Bogotá, Colombia</div>
        <div class="footer-contact-item">✉ contacto@colsoftco.com</div>
        <div class="footer-contact-item">📞 +57 (1) 234-5678</div>
        <div class="footer-contact-item">🕐 Lun – Vie: 8:00 am – 6:00 pm</div>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© 2026 <strong>COLSOFTCO</strong> · Max&Flex. Todos los derechos reservados.</span>
      <span>Desarrollado por <strong>Equipo SENA</strong></span>
    </div>
  </footer>

  <script>
    const form = document.getElementById('regForm');
    const chkEmail = document.getElementById('chkEmail');
    const campoCorreo = document.getElementById('campoCorreo');
    const inputCorreo = document.getElementById('correo_notificacion');

    chkEmail.addEventListener('change', function() {
      campoCorreo.style.display = this.checked ? 'block' : 'none';
      inputCorreo.required = this.checked;
    });

    form.addEventListener('submit', function(e) {
      const producto = document.getElementById('producto').value;
      const cantidad = document.getElementById('cantidad').value;

      if (!producto) {
        alert('Por favor seleccione un producto.');
        e.preventDefault();
        return;
      }
      if (cantidad === '' || Number(cantidad) < 0) {
        alert('Ingrese una cantidad mínima válida (número mayor o igual a 0).');
        e.preventDefault();
        return;
      }
      if (chkEmail.checked && inputCorreo.value.trim() === '') {
        alert('Ingrese el correo donde desea recibir la notificación.');
        e.preventDefault();
        return;
      }
      // Si todo está bien, el formulario se envía normalmente al servidor.
    });

    function resetForm() {
      form.reset();
      campoCorreo.style.display = 'none';
    }
  </script>
  <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
  <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

  <script src="../../public/js/app.js"></script>
</body>

</html>