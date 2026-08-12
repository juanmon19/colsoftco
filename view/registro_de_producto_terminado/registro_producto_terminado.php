<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

$db = new Conexion();
$conn = $db->getConnection();

$mensaje = '';
$mensajeTipo = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre_producto = trim($_POST['producto'] ?? '');
    $cantidad = $_POST['cantidad'] ?? '';

    if ($nombre_producto === '' || $cantidad === '' || !is_numeric($cantidad) || $cantidad < 0) {
        $mensaje = 'Por favor ingrese un producto y una cantidad válida.';
        $mensajeTipo = 'error';
    } else {
        try {
            $conn->beginTransaction();

            // Mismo patrón "upsert" que usa app/logica_colchones.php al fabricar:
            // si el producto ya existe se suma al stock, si no, se crea.
            $stmt = $conn->prepare(
                "SELECT id_producto, stock_actual FROM productos_terminados WHERE nombre_producto = :nombre LIMIT 1"
            );
            $stmt->execute([':nombre' => $nombre_producto]);
            $existente = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existente) {
                $stmt = $conn->prepare(
                    "UPDATE productos_terminados SET stock_actual = stock_actual + :cantidad WHERE id_producto = :id"
                );
                $stmt->execute([
                    ':cantidad' => $cantidad,
                    ':id' => $existente['id_producto'],
                ]);
                $idProducto = $existente['id_producto'];
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO productos_terminados (nombre_producto, stock_actual) VALUES (:nombre, :cantidad)"
                );
                $stmt->execute([
                    ':nombre' => $nombre_producto,
                    ':cantidad' => $cantidad,
                ]);
                $idProducto = $conn->lastInsertId();
            }

            $conn->commit();

            (new HistorialMovimientos())->registrar([
                'modulo'       => 'producto_terminado',
                'accion'       => 'crear',
                'id_registro'  => $idProducto,
                'descripcion'  => "Se registró producción de {$cantidad} unidades de '{$nombre_producto}'",
                'datos_nuevos' => [
                    'nombre_producto' => $nombre_producto,
                    'cantidad'        => $cantidad,
                ],
                'usuario_nombre' => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
            ]);

            $mensaje = "Producto '{$nombre_producto}' registrado correctamente.";
            $mensajeTipo = 'ok';
        } catch (Exception $e) {
            $conn->rollBack();
            $mensaje = 'Error al registrar el producto: ' . $e->getMessage();
            $mensajeTipo = 'error';
        }
    }
}
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
          <?php if ($mensaje): ?>
              <div class="mensaje mensaje-<?= $mensajeTipo ?>" style="margin-bottom:16px;padding:10px 14px;border-radius:6px;font-weight:600;
                  <?= $mensajeTipo === 'ok' ? 'background:#e5f7ec;color:#1e7e42;' : 'background:#fdecea;color:#c0392b;' ?>">
                  <?= htmlspecialchars($mensaje) ?>
              </div>
          <?php endif; ?>

          <form id="regForm" method="POST">
            <div class="form-grid">

              <div class="form-group full">
                <label for="producto">Producto</label>
                <input type="text" id="producto" name="producto" placeholder="Nombre del producto" required />
              </div>

              <div class="form-group full">
                <label for="cantidad">Cantidad</label>
                <input type="number" id="cantidad" name="cantidad" placeholder="Ingrese la cantidad" min="0" step="0.01" required />
              </div>

              <hr class="form-divider" />

              <div class="form-actions">
                <button type="reset" class="btn btn-outline">Limpiar</button>
                <button type="submit" class="btn btn-primary">Registrar</button>
              </div>

            </div>
          </form>
        </div>
      </div>

    </main>
  </div>

<script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
  
<script src="../../public/js/app.js"></script>
</body>

</html>