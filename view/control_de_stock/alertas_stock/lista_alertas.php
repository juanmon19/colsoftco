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
  <title>Alertas de Stock</title>
  <!-- Orden estándar: global -> layout (shell) -> módulo -->
  <link rel="stylesheet" href="../../../public/css/global.css">
  <link rel="stylesheet" href="../../../public/css/layout.css">
  <link rel="stylesheet" href="../controlstock.css">
  <link rel="stylesheet" href="alertas.css">
  <?php include __DIR__ . '/../../partials/scripts_layout.php'; ?>
</head>

<body>

  <div class="app">

    <?php include __DIR__ . '/../../partials/sidebar.php'; ?>

    <div class="main">

      <?php
      $rolActual = 'Administrador';
      include __DIR__ . '/../../partials/topbar.php';
      ?>

      <main class="content">

  <!-- Contenido contenido como Registro stock (tarjeta centrada, sin estirar) -->
  <div class="page-body alerta-page-body">

    <div class="content alerta-contenido" style="margin: 0 auto; max-width: 1100px; width: 100%;">

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

        <!-- FIX acomodo: cuerpo con aire para que la tabla quede separada de los bordes -->
        <div class="form-body alerta-form-cuerpo">

          <?php if (count($materialesEnAlerta) > 0): ?>

            <!-- FIX responsive: scroll horizontal en móvil -->
            <div class="table-responsive alerta-tabla-envolvedora">
            <table class="tabla-alertas alerta-tabla-stock">
              <thead>
                <tr>
                  <th class="col-material">Material</th>
                  <th class="col-num">Stock Actual</th>
                  <th class="col-num">Stock Mínimo</th>
                  <th class="col-centro">Unidad</th>
                  <th class="col-correo">Correo Notificado</th>
                  <th class="col-estado">Estado</th>
                  <th class="col-accion">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($materialesEnAlerta as $material): ?>
                  <tr>
                    <td class="col-material"><?= htmlspecialchars($material['nombre_material']) ?></td>
                    <td class="col-num"><?= htmlspecialchars($material['stock_actual']) ?></td>
                    <td class="col-num"><?= htmlspecialchars($material['stock_minimo']) ?></td>
                    <td class="col-centro"><?= htmlspecialchars($material['nombre_unidad'] ?? '—') ?></td>
                    <td class="col-correo"><?= htmlspecialchars($material['correo_notificacion'] ?? '— sin configurar —') ?></td>
                    <td class="col-estado"><span class="badge badge-stock-bajo">STOCK BAJO</span></td>
                    <td class="col-accion">
                        <a href="../inventario/editar_inventario.php?id=<?= (int)$material['id_material'] ?>"
                           class="btn-marcar-leida">
                            Editar
                        </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>

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

        <!-- FIX acomodo: cuerpo con aire para que la tabla quede separada de los bordes -->
        <div class="form-body alerta-form-cuerpo">

          <?php if (count($notificaciones) > 0): ?>

            <div class="table-responsive alerta-tabla-envolvedora">
            <table class="tabla-alertas alerta-tabla-notificaciones">
              <colgroup>
                <col class="col-w-material">
                <col class="col-w-mensaje">
                <col class="col-w-fecha">
                <col class="col-w-estado">
                <col class="col-w-accion">
              </colgroup>
              <thead>
                <tr>
                  <th class="col-material">Material</th>
                  <th class="col-mensaje">Mensaje</th>
                  <th class="col-fecha">Fecha</th>
                  <th class="col-estado">Estado</th>
                  <th class="col-accion">Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($notificaciones as $notif): ?>
                  <tr>
                    <td class="col-material"><?= htmlspecialchars($notif['nombre_material'] ?? '—') ?></td>
                    <td class="col-mensaje"><?= htmlspecialchars($notif['mensaje']) ?></td>
                    <td class="col-fecha"><?= htmlspecialchars($notif['fecha_generada']) ?></td>
                    <td class="col-estado">
                        <?php if ($notif['leida']): ?>
                            <span class="badge badge-leida">Leída</span>
                        <?php else: ?>
                            <span class="badge badge-no-leida">No leída</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-accion">
                        <?php if (!$notif['leida']): ?>
                            <a href="lista_alertas.php?marcar_leida=<?= (int)$notif['id_notificacion'] ?>"
                               class="btn-marcar-leida">
                                Marcar como leída
                            </a>
                        <?php else: ?>
                            <span class="accion-hecha">—</span>
                        <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
            </div>

          <?php else: ?>

            <p class="sin-alertas">
              Todavía no se ha generado ninguna notificación de stock.
            </p>

          <?php endif; ?>

        </div>
      </div>

    </div>
  </div>
      </main>

      <?php include __DIR__ . '/../../partials/footer.php'; ?>

    </div>
  </div>

  <?php include __DIR__ . '/../../partials/scripts_layout_footer.php'; ?>
  <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
  <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>