<?php

require_once "../../app/verificar_sesion.php";

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
<<<<<<< HEAD

=======
>>>>>>> 20280bdb7681e20d508d82ed4fe0a4b948dbf84f
  </header>

  <div class="page-body">

    <div class="content" style="margin: 0 auto; max-width: 1000px; width: 100%;">

      <div class="form-card">
        <div class="form-header">
          <span class="form-header-bar"></span>
          Registra el nivel mínimo de cada materia prima
        </div>

        <div class="form-body">
          <form id="regForm">
            <div class="form-grid">

              <div class="form-group">
                <label for="producto">Producto</label>
                <div class="select-wrap">
                  <select id="producto" name="producto">
                    <option value="">-- Seleccione --</option>
                    <option>Espuma de poliuretano</option>
                    <option>Tela Jacquard</option>
                    <option>Resortes Bonell </option>
                    <option>Espuma </option>
                    <option>Fieltro aislante </option>
                    <option>Pegante industrial </option>
                    <option>Hilo de costura </option>
                    <option>Espuma viscoelástica </option>
                    <option>Tela antideslizante </option>
                    <option>Borde perimetral </option>
                    <option>Empaque plástico </option>
                    <option>Tela </option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label for="unidad">Unidad de medida</label>
                <div class="select-wrap">
                  <select id="unidad" name="unidad">
                    <option value="">-- Seleccione --</option>
                    <option value="metro">(metro) M</option>
                    <option value="kg">(kilogramo) Kg</option>
                    <option value="unidad">(unidad) Und</option>
                    <option value="litro">(litro) L</option>
                    <option value="cm">(centímetro) Cm</option>
                  </select>
                </div>
              </div>

              <div class="form-group full">
                <label for="cantidad">Cantidad mínima</label>
                <input type="number" id="cantidad" name="cantidad" min="0" placeholder="Ingrese la cantidad mínima" />
              </div>

              <hr class="form-divider" />

              <div class="check-section">
                <label>Seleccione dónde recibir la notificación</label>
                <div class="check-options">
                  <label class="check-opt">
                    <input type="checkbox" id="chkEmail" name="notif" value="email" />
                    <span>Correo Electrónico</span>
                  </label>
                  <label class="check-opt">
                    <input type="checkbox" id="chkSms" name="notif" value="sms" />
                    <span>Mensaje de Texto</span>
                  </label>
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

  <div class="toast" id="toast"></div>

  <script>
    const form = document.getElementById('regForm');
    const toast = document.getElementById('toast');

    form.addEventListener('submit', function(e) {
      e.preventDefault();

      const producto = document.getElementById('producto').value;
      const unidad = document.getElementById('unidad').value;
      const cantidad = document.getElementById('cantidad').value;

      if (!producto) {
        alert('Por favor seleccione un producto.');
        return;
      } 
      if (!unidad) {
        alert('Por favor seleccione la unidad de medida.');
        return;
      } 
      if (cantidad === '' || Number(cantidad) < 0) {
        alert('Ingrese una cantidad mínima válida (número mayor o igual a 0).');
        return; 
      }

      showToast('✔ Materia prima registrada correctamente.');
      form.reset();
    });

    function resetForm() {
      form.reset();
    }

    function showToast(mensaje) {
      toast.textContent = mensaje || '✔ Operación exitosa.';
      toast.style.display = 'block';
      setTimeout(function() {
        toast.style.display = 'none';
      }, 3500);
    }
  </script>
  <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
  <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
  
  <script src="../../public/js/app.js"></script>
</body>

</html>