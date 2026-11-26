<?php

require_once "../../app/verificar_sesion.php";
require_once __DIR__ . '/../../app/logica_proveedores.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';
require_once __DIR__ . '/../../config/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/setting.php';
require_once __DIR__ . '/../../app/correo_plantilla.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$db = new Conexion();
$conn = $db->getConnection();

$logicaProveedor = new ProveedorLogica();

$idProveedor = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$proveedor = $logicaProveedor->getProveedorById($idProveedor);

if (!$proveedor) {
    die("Proveedor no encontrado");
}

// Materiales que este proveedor suministra
$stmtMateriales = $conn->prepare("
    SELECT mp.id_material, mp.nombre_material, um.nombre_unidad
    FROM materias_primas mp
    LEFT JOIN unidades_medida um ON mp.id_unidad = um.id_unidad
    WHERE mp.estado = 'activo' AND (mp.id_proveedor = :principal OR EXISTS (
        SELECT 1 FROM proveedores_materiales pm
        WHERE pm.id_material = mp.id_material AND pm.id_proveedor = :adicional
    ))
    ORDER BY mp.nombre_material ASC
");
$stmtMateriales->execute([':principal' => $idProveedor, ':adicional' => $idProveedor]);
$materiales = $stmtMateriales->fetchAll(PDO::FETCH_ASSOC);

$mensaje = $_SESSION['catalogo_proveedor_ok'] ?? '';
unset($_SESSION['catalogo_proveedor_ok']);
$mensajeTipo = 'exito'; // 'exito' o 'error'
// Bodeguero/Operario no contactan al proveedor: solicitan al admin.
$esSolicitudInterna = !es_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !validarTokenCSRF(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '')) {
    http_response_code(403);
    $mensaje = 'La sesión cambió. Vuelve a enviar el formulario.';
    $mensajeTipo = 'error';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idMaterial = $_POST['materiaPrima'] ?? '';
    $cantidad = $_POST['cantidadPedido'] ?? '';

    $catalogo = array_column($materiales, null, 'id_material');
    $infoMaterial = is_string($idMaterial) ? ($catalogo[$idMaterial] ?? null) : null;
    if (!$infoMaterial || $proveedor['estado'] !== 'activo' || !stockValido($cantidad) || (float)$cantidad <= 0) {

        $mensaje = "Selecciona la materia prima e ingresa una cantidad válida.";
        $mensajeTipo = 'error';

    } else {

        $nombreMaterial = $infoMaterial['nombre_material'] ?? 'Material';
        $unidadMaterial = $infoMaterial['nombre_unidad'] ?? '';

        $nombreSolicitante = trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema';
        $rolSolicitante = rol_legible();

        if ($esSolicitudInterna) {
            // =====================================
            // SOLICITUD INTERNA AL ADMINISTRADOR
            // No se crea pedido ni se contacta al proveedor.
            // Se avisa a todos los admins por mensajería + correo.
            // =====================================
            $textoSolicitud = "El {$rolSolicitante} {$nombreSolicitante} solicita pedir '{$nombreMaterial}' (cantidad: {$cantidad} {$unidadMaterial}) al proveedor '{$proveedor['nombre_empresa']}'. Por favor gestiónalo desde el módulo de proveedores.";
            try {
                $admins = $conn->query("SELECT id_usuario, email FROM usuarios WHERE rol = 'administrador' AND activo = 1")->fetchAll(PDO::FETCH_ASSOC);
                $miId = (int) ($_SESSION['user_id'] ?? 0);
                foreach ($admins as $adm) {
                    if ((int) $adm['id_usuario'] === $miId) {
                        continue;
                    }
                    $stmtMsg = $conn->prepare("INSERT INTO mensajes (id_remitente, id_destinatario, asunto, contenido) VALUES (:rem, :dest, :asunto, :contenido)");
                    $stmtMsg->execute([
                        ':rem' => $miId,
                        ':dest' => (int) $adm['id_usuario'],
                        ':asunto' => "Solicitud de pedido: {$proveedor['nombre_empresa']}",
                        ':contenido' => $textoSolicitud,
                    ]);
                }
                // Correo al buzón admin configurado
                if (defined('USERNAME') && filter_var(USERNAME, FILTER_VALIDATE_EMAIL)) {
                    enviarCorreoSolicitudAdmin(USERNAME, $proveedor['nombre_empresa'], $nombreMaterial, (string) $cantidad, (string) $unidadMaterial, $nombreSolicitante, $rolSolicitante);
                }
            } catch (Throwable $e) {
                error_log('Error en solicitud interna de pedido: ' . $e->getMessage());
            }

            (new HistorialMovimientos())->registrar([
                'modulo'       => 'pedidos_proveedor',
                'accion'       => 'solicitud',
                'id_registro'  => $idProveedor,
                'descripcion'  => "Solicitud interna: {$nombreSolicitante} ({$rolSolicitante}) pidió {$cantidad} {$unidadMaterial} de '{$nombreMaterial}' al proveedor '{$proveedor['nombre_empresa']}'",
                'datos_nuevos' => [
                    'id_proveedor'    => $idProveedor,
                    'id_material'     => $idMaterial,
                    'cantidad_pedida' => $cantidad,
                ],
                'usuario_nombre' => $nombreSolicitante,
            ]);

            $mensajeTipo = 'exito';
            $mensaje = "Solicitud enviada al administrador. Él gestionará el pedido con el proveedor.";
        } else {
        // =====================================
        // FLUJO ADMIN: GUARDAR PEDIDO EN LA BASE DE DATOS
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

        $mail->setFrom(USERNAME, 'Max & Flex - Pedidos');
        $mail->addAddress($correoDestino, $nombreEmpresaProveedor);

        $cantidadTexto = $unidadMaterial
            ? "{$cantidad} {$unidadMaterial}"
            : $cantidad;

        prepararCorreo($mail, 'Pedido de materia prima de Max & Flex', "Hola, equipo de {$nombreEmpresaProveedor}.",
            'Max & Flex solicita el siguiente material para producción.',
            ['Materia prima' => $nombreMaterial, 'Cantidad solicitada' => $cantidadTexto],
            'Por favor, responde a este correo para confirmar la disponibilidad y la fecha estimada de entrega.');


        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("Error enviando correo de pedido: {$mail->ErrorInfo}");
        return false;
    }
}

function enviarCorreoSolicitudAdmin($correoAdmin, $nombreEmpresaProveedor, $nombreMaterial, $cantidad, $unidadMaterial, $solicitante, $rolSolicitante)
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

        $mail->setFrom(USERNAME, 'COLSOFTCO - Solicitudes');
        $mail->addAddress($correoAdmin, 'Administrador COLSOFTCO');

        prepararCorreo($mail, "Solicitud de pedido para {$nombreEmpresaProveedor}", 'Hola, equipo de administración.',
            'Se recibió una solicitud interna de abastecimiento. Revisa los datos antes de contactar al proveedor.',
            ['Solicitante' => $solicitante, 'Rol' => $rolSolicitante, 'Proveedor' => $nombreEmpresaProveedor,
             'Materia prima' => $nombreMaterial, 'Cantidad' => trim($cantidad . ' ' . $unidadMaterial)],
            boton: 'Consultar proveedores', url: BASE_URL . '/view/lista_proveedores/lista_proveedores.php');


        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error enviando solicitud al admin: {$mail->ErrorInfo}");
        return false;
    }
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php include __DIR__ . '/../partials/seo.php'; ?>
    <title>Contactar Proveedor - COLSOFTCO</title>
    <!-- Orden estándar: global -> layout (shell) -> módulo -->
    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="contactar.css">
    <?php include __DIR__ . '/../partials/scripts_layout.php'; ?>
</head>

<body>

    <div class="app">

        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main">

            <?php
            $rolActual = 'Administrador';
            include __DIR__ . '/../partials/topbar.php';
            ?>

            <main class="content">

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
            <?php if ($esSolicitudInterna): ?>
            <p class="aviso-solicitud">Estás en modo solicitud: tu pedido llegará al administrador, no al proveedor.</p>
            <?php endif; ?>
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
                    <?= $esSolicitudInterna ? 'Solicitar Pedido al Administrador' : 'Realizar Pedido' ?>
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
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generarTokenCSRF()) ?>">
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
            <input type="number" id="cantidadPedido" name="cantidadPedido" placeholder="Ej: 50" min="0.01" step="0.01" required>

            <div class="modal-botones">
                <button type="button" id="btnCerrarModal" class="btn-secundario">Cancelar</button>
                <button type="submit" class="btn-primario">Confirmar Pedido</button>
            </div>
        </form>
    </div>

            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
    <script src="../../public/js/contactar_proveedor.js"></script>
</body>
</html>