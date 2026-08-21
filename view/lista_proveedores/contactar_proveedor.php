<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../app/logica_proveedores.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/setting.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$db = new Conexion();
$conn = $db->getConnection();

$logicaProveedor = new ProveedorLogica();

$idProveedor = $_GET['id'] ?? 0;
$proveedor = $logicaProveedor->getProveedorById($idProveedor);

if (!$proveedor) {
    die("Proveedor no encontrado");
}

// Materiales que este proveedor suministra
$stmtMateriales = $conn->prepare("
    SELECT mp.id_material, mp.nombre_material, um.nombre_unidad
    FROM materias_primas mp
    LEFT JOIN unidades_medida um ON mp.id_unidad = um.id_unidad
    WHERE mp.id_proveedor = :id_proveedor
    ORDER BY mp.nombre_material ASC
");
$stmtMateriales->execute([':id_proveedor' => $idProveedor]);
$materiales = $stmtMateriales->fetchAll(PDO::FETCH_ASSOC);

$mensaje = '';
$mensajeTipo = ''; // 'exito' o 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idMaterial = $_POST['materiaPrima'] ?? '';
    $cantidad = $_POST['cantidadPedido'] ?? '';

    if ($idMaterial === '' || $cantidad === '' || (float)$cantidad <= 0) {

        $mensaje = "Selecciona la materia prima e ingresa una cantidad válida.";
        $mensajeTipo = 'error';

    } else {

        // Nombre y unidad del material, para el correo e historial
        $stmtNombre = $conn->prepare("
            SELECT mp.nombre_material, um.nombre_unidad
            FROM materias_primas mp
            LEFT JOIN unidades_medida um ON mp.id_unidad = um.id_unidad
            WHERE mp.id_material = :id
        ");
        $stmtNombre->execute([':id' => $idMaterial]);
        $infoMaterial = $stmtNombre->fetch(PDO::FETCH_ASSOC);
        $nombreMaterial = $infoMaterial['nombre_material'] ?? 'Material';
        $unidadMaterial = $infoMaterial['nombre_unidad'] ?? '';

        // =====================================
        // GUARDAR PEDIDO EN LA BASE DE DATOS
        // =====================================

        $stmtInsert = $conn->prepare("
            INSERT INTO pedidos_proveedor (id_proveedor, id_material, cantidad_pedida)
            VALUES (:id_proveedor, :id_material, :cantidad_pedida)
        ");

        $insertado = $stmtInsert->execute([
            ':id_proveedor'    => $idProveedor,
            ':id_material'     => $idMaterial,
            ':cantidad_pedida' => $cantidad,
        ]);

        if ($insertado) {

            $idPedido = $conn->lastInsertId();

            // =====================================
            // ENVIAR CORREO AL PROVEEDOR
            // =====================================

            $correoEnviado = false;

            if (!empty($proveedor['email'])) {
                $correoEnviado = enviarCorreoPedido(
                    $proveedor['email'],
                    $proveedor['nombre_empresa'],
                    $nombreMaterial,
                    $cantidad,
                    $unidadMaterial
                );
            }

            // =====================================
            // HISTORIAL
            // =====================================

            (new HistorialMovimientos())->registrar([
                'modulo'       => 'pedidos_proveedor',
                'accion'       => 'crear',
                'id_registro'  => $idPedido,
                'descripcion'  => "Se solicitó un pedido de '{$nombreMaterial}' (cantidad: {$cantidad}) al proveedor '{$proveedor['nombre_empresa']}'",
                'datos_nuevos' => [
                    'id_proveedor'    => $idProveedor,
                    'id_material'     => $idMaterial,
                    'cantidad_pedida' => $cantidad,
                ],
                'usuario_nombre' => trim(
                    ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')
                ) ?: 'Sistema',
            ]);

            $mensajeTipo = 'exito';
            $mensaje = $correoEnviado
                ? "Pedido registrado y enviado por correo al proveedor."
                : "Pedido registrado, pero no se pudo enviar el correo (revisa que el proveedor tenga un correo válido).";

        } else {
            $mensaje = "No se pudo registrar el pedido. Intenta de nuevo.";
            $mensajeTipo = 'error';
        }
    }
}

function enviarCorreoPedido($correoDestino, $nombreEmpresaProveedor, $nombreMaterial, $cantidad, $unidadMaterial = '')
{
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = USERNAME;
        $mail->Password   = PASSWORD;
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom('colsoftco4@gmail.com', 'Max & Flex - Pedidos');
        $mail->addAddress($correoDestino, $nombreEmpresaProveedor);

        $cantidadTexto = $unidadMaterial
            ? "{$cantidad} {$unidadMaterial}"
            : $cantidad;

        $mail->isHTML(true);
        $mail->Subject = "Nuevo pedido de materia prima - Max & Flex";
        $mail->Body    = "
            <p>Estimado proveedor <b>{$nombreEmpresaProveedor}</b>,</p>
            <p>Max & Flex desea realizar el siguiente pedido:</p>
            <p><b>Materia prima:</b> {$nombreMaterial}<br>
            <b>Cantidad solicitada:</b> {$cantidadTexto}</p>
            <p>Por favor confirma la disponibilidad y el tiempo estimado de entrega.</p>
            <p>Saludos,<br>Max & Flex</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error enviando correo de pedido: {$mail->ErrorInfo}");
        return false;
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactar Proveedor - COLSOFTCO</title>
    <link rel="stylesheet" href="contactar.css">
</head>

<body>

    <header class="header">
        <div class="logo">
            <a href="lista_proveedores.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Contactar Proveedor</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="landing-container">

        <div class="provider-card">

            <div class="imagen-preview" style="margin: 0 auto 15px;">
                <?php if (!empty($proveedor['imagen'])): ?>
                    <img
                        src="../../public/imagenes/proveedores/<?= htmlspecialchars($proveedor['imagen']) ?>"
                        alt="<?= htmlspecialchars($proveedor['nombre_empresa']) ?>">
                <?php else: ?>
                    <span class="imagen-placeholder">Sin imagen</span>
                <?php endif; ?>
            </div>

            <h2><?= htmlspecialchars($proveedor['nombre_empresa']) ?></h2>
            <p><?= htmlspecialchars($proveedor['descripcion_empresa']) ?></p>

            <?php if (!empty($mensaje)): ?>
                <div class="mensaje-pedido mensaje-<?= $mensajeTipo ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if (count($materiales) === 0): ?>

                <p class="sin-materiales">
                    Este proveedor no tiene materias primas registradas todavía.
                </p>

            <?php else: ?>

                <button id="btnContactar" class="btn-contacto">
                    Realizar Pedido
                </button>

            <?php endif; ?>

            <div style="margin-top: 15px;">
                <a href="lista_proveedores.php" class="link-volver">
                    ← Volver a proveedores
                </a>
            </div>
        </div>
    </div>

    <!-- MODAL DE PEDIDO -->
    <div class="modal-overlay" id="modalPedidoOverlay">
        <form method="POST" class="modal-pedido">
            <h3>Detalles del Pedido</h3>

            <label for="materiaPrima">Materia prima a pedir:</label>
            <select id="materiaPrima" name="materiaPrima" required>
                <option value="">-- Seleccione --</option>
                <?php foreach ($materiales as $material): ?>
                    <option
                        value="<?= (int)$material['id_material'] ?>"
                        data-unidad="<?= htmlspecialchars($material['nombre_unidad'] ?? '') ?>">
                        <?= htmlspecialchars($material['nombre_material']) ?>
                        <?= !empty($material['nombre_unidad']) ? '(' . htmlspecialchars($material['nombre_unidad']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="cantidadPedido">
                Cantidad
                <span id="unidadCantidad" class="unidad-badge"></span>:
            </label>
            <input type="number" id="cantidadPedido" name="cantidadPedido" placeholder="Ej: 50" min="1" step="0.01" required>

            <div class="modal-botones">
                <button type="button" id="btnCerrarModal" class="btn-secundario">Cancelar</button>
                <button type="submit" class="btn-primario">Confirmar Pedido</button>
            </div>
        </form>
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

    <script src="../../public/js/app.js"></script>
    <script src="../../public/js/contactar_proveedor.js"></script>
</body>
</html>