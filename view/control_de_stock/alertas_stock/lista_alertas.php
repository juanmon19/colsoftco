<?php

require_once "../../../app/verificar_sesion.php";
require_once __DIR__ . '/../../../app/alerta_stock.php';

$logicaAlerta = new AlertaStockLogica();

// Marcar una notificación como leída
if (isset($_GET['marcar_leida'])) {
    $logicaAlerta->marcarNotificacionLeida((int)$_GET['marcar_leida']);
    header("Location: lista_alertas.php");
    exit();
}

$materialesEnAlerta = $logicaAlerta->listarMaterialesEnAlerta();
$notificaciones = $logicaAlerta->listarNotificaciones();

?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../controlstock.css">
  <title>Alertas de Stock</title>
</head>

<body>

  <header>
    <div class="logo">
      <a href="../../panel_admin/panel_admin.php">
        <img src="../../../public/imagenes/logo.png" alt="logo">
      </a>
    </div>

    <div class="header-title">
        <h1>Alertas de Stock</h1>
    </div>

    <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
      Cerrar sesión
    </button>
  </header>

  <div class="page-body">

    <div class="content" style="margin: 0 auto; max-width: 1000px; width: 100%;">

      <div style="text-align: left; margin-bottom: 5px;">
        <a href="../control_de_stock.php" class="btn-volver">
          ← Volver
        </a>
      </div>

      <!-- =========================
           MATERIALES ACTUALMENTE EN ALERTA
      ========================== -->
      <div class="form-card">
        <div class="form-header">
          <span class="form-header-bar"></span>
          Materiales por debajo del stock mínimo
        </div>

        <div class="form-body" style="padding: 0;">

          <?php if (count($materialesEnAlerta) > 0): ?>

            <table class="tabla-alertas">
              <thead>
                <tr>
                  <th>Material</th>
                  <th>Stock Actual</th>
                  <th>Stock Mínimo</th>
                  <th>Unidad</th>
                  <th>Correo Notificado</th>
                  <th>Estado</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($materialesEnAlerta as $material): ?>
                  <tr>
                    <td><?= htmlspecialchars($material['nombre_material']) ?></td>
                    <td><?= htmlspecialchars($material['stock_actual']) ?></td>
                    <td><?= htmlspecialchars($material['stock_minimo']) ?></td>
                    <td><?= htmlspecialchars($material['nombre_unidad'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($material['correo_notificacion'] ?? '— sin configurar —') ?></td>
                    <td><span class="badge badge-stock-bajo">STOCK BAJO</span></td>
                    <td>
                        <a href="../inventario/editar_inventario.php?id=<?= (int)$material['id_material'] ?>"
                           class="btn-marcar-leida">
                           Editar
                        </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

          <?php else: ?>

            <p class="sin-alertas">
              No hay materiales por debajo del stock mínimo en este momento.
            </p>

          <?php endif; ?>

        </div>
      </div>

      <!-- =========================
           HISTORIAL DE NOTIFICACIONES
      ========================== -->
      <div class="form-card">
        <div class="form-header">
          <span class="form-header-bar"></span>
          Historial de notificaciones
        </div>

        <div class="form-body" style="padding: 0;">

          <?php if (count($notificaciones) > 0): ?>

            <table class="tabla-alertas">
              <thead>
                <tr>
                  <th>Material</th>
                  <th>Mensaje</th>
                  <th>Fecha</th>
                  <th>Estado</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($notificaciones as $notif): ?>
                  <tr>
                    <td><?= htmlspecialchars($notif['nombre_material'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($notif['mensaje']) ?></td>
                    <td><?= htmlspecialchars($notif['fecha_generada']) ?></td>
                    <td>
                        <?php if ($notif['leida']): ?>
                            <span class="badge badge-leida">Leída</span>
                        <?php else: ?>
                            <span class="badge badge-no-leida">No leída</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!$notif['leida']): ?>
                            <a href="lista_alertas.php?marcar_leida=<?= (int)$notif['id_notificacion'] ?>"
                               class="btn-marcar-leida">
                                Marcar como leída
                            </a>
                        <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

          <?php else: ?>

            <p class="sin-alertas">
              Todavía no se ha generado ninguna notificación de stock.
            </p>

          <?php endif; ?>

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

  <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
  <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

  <script src="../../../public/js/app.js"></script>
</body>

</html>