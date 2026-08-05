<?php

require_once "../../app/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registro de productos terminados</title>
  <link href="registro_producto_terminado.css" rel="stylesheet">
</head>


<body>

  <header>
    <div class="logo">
      <a href="../panel_admin/panel_admin.php">
        <img src="../../public/imagenes/logo.png" alt="logo">
      </a>
    </div>


    <div class="header-title">
      <h1>Registro de Productos terminados</h1>
      <div class="title-underline"></div>
    </div>

      <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
          Cerrar sesión
      </button>
  </header>

  <!-- ── BODY ── -->
  <div class="page-body">

    <!-- CONTENT -->
    <main class="content" style="margin: 0 auto; max-width: 1000px; width: 100%;">

      <!-- FORMULARIO REGISTRAR PRODUCTO -->
      <div class="form-card">
        <div class="form-header">
          <span class="form-header-bar"></span>
          Registra Producto
        </div>

        <div class="form-body">
          <div class="form-grid">

            <div class="form-group full">
              <label for="producto">Producto</label>
              <input type="text" id="producto" placeholder="Nombre del producto" />
            </div>

            <div class="form-group full">
              <label for="cantidad">Cantidad</label>
              <input type="number" id="cantidad" placeholder="Ingrese la cantidad" min="0" />
            </div>

            <hr class="form-divider" />

            <div class="form-actions">
              <button class="btn btn-outline" onclick="limpiar()">Limpiar</button>
              <button class="btn btn-primary" onclick="registrar()">Registrar</button>
            </div>

          </div>
        </div>
      </div>

    </main>
  </div>

  <!-- TOAST -->
  <div class="toast" id="toast">✔ Producto registrado exitosamente.</div>

  <script>
    // ══════════════════════════════════════
    //  PANEL BODEGUERO – panel_bodeguero.js
    // ══════════════════════════════════════
    /**
     * Limpia los campos del formulario.
     */
    function limpiar() {
      document.getElementById('producto').value = '';
      document.getElementById('cantidad').value = '';
      quitarErrores();
    }

    /**
     * Valida y registra el producto.
     */
    function registrar() {
      const producto = document.getElementById('producto').value.trim();
      const cantidad = document.getElementById('cantidad').value.trim();

      quitarErrores();

      if (!producto) {
        mostrarError('producto', 'Por favor ingrese el nombre del producto.');
        return;
      }

      if (!cantidad || isNaN(cantidad) || Number(cantidad) < 0) {
        mostrarError('cantidad', 'Por favor ingrese una cantidad válida.');
        return;
      }

      // Aquí iría la llamada al backend
      console.log('Producto registrado:', { producto, cantidad: Number(cantidad) });

      limpiar();
      mostrarToast('✔ Producto registrado exitosamente.');
    }

    /**
     * Resalta el campo con error y muestra mensaje debajo.
     */
    function mostrarError(inputId, mensaje) {
      const input = document.getElementById(inputId);

      input.style.borderColor = '#c0392b';
      input.style.boxShadow = '0 0 0 3px rgba(192,57,43,.15)';
      input.focus();

      const msg = document.createElement('span');
      msg.className = 'field-error';
      msg.textContent = mensaje;
      msg.style.cssText =
        'color:#c0392b;font-size:12px;font-weight:600;margin-top:3px;display:block;';

      input.parentNode.appendChild(msg);
      input.addEventListener('input', quitarErrores, { once: true });
    }

    /**
     * Elimina estilos de error del formulario.
     */
    function quitarErrores() {
      document.querySelectorAll('.field-error').forEach(el => el.remove());
      ['producto', 'cantidad'].forEach(id => {
        const inp = document.getElementById(id);
        if (inp) {
          inp.style.borderColor = '';
          inp.style.boxShadow = '';
        }
      });
    }

    /**
     * Muestra el toast de notificación por 3 segundos.
     */
    function mostrarToast(texto) {
      const toast = document.getElementById('toast');
      toast.textContent = texto;
      toast.style.display = 'block';

      toast.style.animation = 'none';
      void toast.offsetWidth;
      toast.style.animation = '';

      clearTimeout(toast._timer);
      toast._timer = setTimeout(() => {
        toast.style.display = 'none';
      }, 3000);
    }

  </script>
<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
  <script src="../../public/js/Auth.js"></script>
  <script src="../../public/js/app.js"></script>
</body>

</html>