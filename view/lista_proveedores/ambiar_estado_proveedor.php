<?php

require_once __DIR__ . '/../../app/logica_proveedores.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';

session_start();

$logica = new ProveedorLogica();

$id = $_GET['id'] ?? 0;
$proveedor = $logica->getProveedorById($id);

if (!$proveedor) {
    die("Proveedor no encontrado");
}

/* El estado hacia el que va a cambiar (lo contrario del actual) */
$nuevoEstado = $proveedor['estado'] === 'activo' ? 'inactivo' : 'activo';
$esDeshabilitar = $nuevoEstado === 'inactivo';

/* A qué pestaña de la lista regresar después de guardar */
$volverA = $_GET['volver'] ?? ($proveedor['estado'] ?? 'activo');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $logica->cambiarEstadoProveedor($id, $nuevoEstado);

    (new HistorialMovimientos())->registrar([
        'modulo'           => 'proveedores',
        'accion'           => $esDeshabilitar ? 'deshabilitar' : 'habilitar',
        'id_registro'      => $id,
        'descripcion'      => $esDeshabilitar
            ? "Se deshabilitó el proveedor '{$proveedor['nombre_empresa']}'"
            : "Se habilitó el proveedor '{$proveedor['nombre_empresa']}'",
        'datos_anteriores' => ['estado' => $proveedor['estado']],
        'datos_nuevos'     => ['estado' => $nuevoEstado],
        'usuario_nombre'   => trim(($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')) ?: 'Sistema',
    ]);

    header("Location: ../lista_proveedores/lista_proveedores.php?estado=" . urlencode($volverA));
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $esDeshabilitar ? 'Deshabilitar' : 'Habilitar' ?> Proveedor</title>

<link rel="stylesheet" href="crud_proveedor.css">

</head>
<body>

    <header class="header">
        <div class="logo">
            <a href="../lista_proveedores/lista_proveedores.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1><?= $esDeshabilitar ? 'Deshabilitar' : 'Habilitar' ?> Proveedor</h1>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>

    <div class="contenedor">

        <div class="acciones-superior">
            <a href="../lista_proveedores/lista_proveedores.php?estado=<?= urlencode($volverA) ?>" class="btn-volver">
                ← Volver a Proveedores
            </a>
        </div>

        <div class="card">

            <div class="card-header">
                <?= $esDeshabilitar ? 'Deshabilitar Proveedor' : 'Habilitar Proveedor' ?>
            </div>

            <div class="card-body">

                <h2>
                    <?= $esDeshabilitar
                        ? '¿Deseas deshabilitar este proveedor?'
                        : '¿Deseas habilitar nuevamente este proveedor?' ?>
                </h2>

                <?php if ($esDeshabilitar): ?>
                    <p style="color:#64748b; margin-top:8px;">
                        No se eliminará ningún dato. El proveedor pasará a la pestaña de
                        "Deshabilitados" y podrás habilitarlo de nuevo cuando quieras.
                    </p>
                <?php endif; ?>

                <br>

                <p>
                    <strong>Empresa:</strong>
                    <?= htmlspecialchars($proveedor['nombre_empresa']) ?>
                </p>

                <p>
                    <strong>NIT:</strong>
                    <?= htmlspecialchars($proveedor['nit']) ?>
                </p>

                <p>
                    <strong>Contacto:</strong>
                    <?= htmlspecialchars($proveedor['contacto_nombre']) ?>
                    <?= htmlspecialchars($proveedor['contacto_apellido']) ?>
                </p>

                <p>
                    <strong>Correo:</strong>
                    <?= htmlspecialchars($proveedor['email']) ?>
                </p>

                <p>
                    <strong>Teléfono:</strong>
                    <?= htmlspecialchars($proveedor['telefono']) ?>
                </p>

                <div class="botones">

                    <form method="POST">
                        <button type="submit" class="btn btn-guardar">
                            <?= $esDeshabilitar ? 'Sí, deshabilitar' : 'Sí, habilitar' ?>
                        </button>
                    </form>

                    <a href="../lista_proveedores/lista_proveedores.php?estado=<?= urlencode($volverA) ?>"
                       class="btn btn-volver-secundario">
                        Cancelar
                    </a>

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
</body>
</html>