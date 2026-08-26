<?php

require_once "../../app/verificar_sesion.php";
require_once '../../config/conexion.php';

$conexion = new Conexion();
$dbConn = $conexion->getConnection();

$modelos = $dbConn->query(
    "SELECT id_modelo, nombre_modelo FROM modelos_colchon ORDER BY nombre_modelo ASC"
)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receta Colchones</title>
    <link href="receta_colchones.css" rel="stylesheet">
</head>

<body>

    <header>
        <a class="logo" href="../../app/ir_panel.php">
            <img src="../../public/imagenes/logo.png" alt="logo">
        </a>
        <span class="header-title">Receta Colchones</span>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
    </header>


    <div class="paneles">

        <!-- ══ PANEL PRODUCTO ══ -->
        <div class="panel panel-producto">
            <div class="panel-header">Producto</div>

            <div class="panel-body">

                <label for="producto">Producto</label>
                <select id="producto">
                    <?php foreach ($modelos as $m): ?>
                        <option value="<?= $m['id_modelo'] ?>">
                            <?= htmlspecialchars($m['nombre_modelo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="cantidad">Cantidad</label>
                <input type="number" id="cantidad" min="1" value="20">

                <button id="btnGenerar" class="btn btn-generar">
                    ⚙ Generar
                </button>

            </div>
        </div>

        <!-- ══ PANEL RESULTADO ══ -->
        <div class="panel panel-resultado">
            <div class="panel-header">Resultado</div>

            <div class="panel-body" id="resultadoBody">
                <p class="placeholder">
                    Selecciona un producto y una cantidad, luego presiona
                    <strong>Generar</strong> para ver si la producción es posible.
                </p>
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
        const selectProducto = document.getElementById('producto');
        const inputCantidad = document.getElementById('cantidad');
        const btnGenerar = document.getElementById('btnGenerar');
        const resultadoBody = document.getElementById('resultadoBody');

        let ultimoResultado = null;

        btnGenerar.addEventListener('click', generar);

        async function generar() {

            const idModelo = selectProducto.value;
            const cantidad = inputCantidad.value;

            resultadoBody.innerHTML = '<p class="placeholder">Calculando...</p>';

            try {
                const resp = await fetch(
                    `../../app/logica_colchones.php?accion=calcular&id_modelo=${idModelo}&cantidad=${cantidad}`
                );
                const data = await resp.json();

                if (!data.ok) {
                    resultadoBody.innerHTML = `<div class="banner banner-error">${data.error}</div>`;
                    return;
                }

                ultimoResultado = data;
                renderResultado(data);

            } catch (e) {
                resultadoBody.innerHTML = '<div class="banner banner-error">Error de conexión. Revisa la consola.</div>';
                console.error(e);
            }
        }

        function renderResultado(data) {

            const filas = data.materiales.map(m => `
                <tr>
                    <td class="celda-material">${m.icono} ${m.nombre_material}</td>
                    <td>${formatear(m.cantidad_requerida)} ${m.unidad}</td>
                    <td>${formatear(m.cantidad_disponible)} ${m.unidad}</td>
                    <td>
                        <span class="estado ${m.suficiente ? 'estado-ok' : 'estado-fail'}">
                            ${m.suficiente ? '✔ Disponible' : '✖ Insuficiente'}
                        </span>
                    </td>
                </tr>
            `).join('');

            const bannerTop = data.produccion_posible ?
                `<div class="banner banner-ok">
                        <span class="banner-icono">✔</span>
                        <div>
                            <strong>Producción posible</strong>
                            <p>Todos los materiales están disponibles para fabricar ${data.cantidad} ${data.nombre_producto.toLowerCase()}s.</p>
                        </div>
                   </div>` :
                `<div class="banner banner-error">
                        <span class="banner-icono">✖</span>
                        <div>
                            <strong>Producción no posible</strong>
                            <p>Falta materia prima para fabricar ${data.cantidad} ${data.nombre_producto.toLowerCase()}s.</p>
                        </div>
                   </div>`;

            const bannerBottom = data.produccion_posible ?
                `<div class="banner banner-ok">
                        <span class="banner-icono">✔</span>
                        <div>
                            <strong>La producción de ${data.cantidad} ${data.nombre_producto.toLowerCase()}s es un éxito.</strong>
                            <p>Al confirmar, se descontarán automáticamente las materias primas del inventario
                               y se aumentará el stock de productos terminados.</p>
                        </div>
                   </div>` :
                '';

            const botonFabricar = data.produccion_posible ?
                `<button id="btnFabricar" class="btn btn-fabricar">🏭 Fabricar</button>` :
                '';

            resultadoBody.innerHTML = `
                ${bannerTop}

                <table class="tabla-materiales">
                    <thead>
                        <tr>
                            <th>Materia prima</th>
                            <th>Cantidad requerida</th>
                            <th>Cantidad disponible</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>${filas}</tbody>
                </table>

                ${bannerBottom}

                <div class="acciones-resultado">${botonFabricar}</div>
            `;

            const btnFabricar = document.getElementById('btnFabricar');
            if (btnFabricar) {
                btnFabricar.addEventListener('click', fabricar);
            }
        }

        async function fabricar() {

            if (!ultimoResultado) return;

            try {
                const resp = await fetch(
                    `../../app/logica_colchones.php?accion=fabricar&id_modelo=${ultimoResultado.id_modelo}&cantidad=${ultimoResultado.cantidad}`
                );
                const data = await resp.json();

                if (data.ok) {
                    alert(data.mensaje);

                    // Descarga automática del recibo en PDF
                    if (data.recibo_pdf) {
                        descargarRecibo(data.recibo_pdf, data.numero_recibo);
                    }

                    generar(); // refresca el stock disponible
                } else {
                    alert(data.error || 'No se pudo fabricar.');
                }

            } catch (e) {
                alert('Error de conexión al fabricar.');
                console.error(e);
            }
        }

        /**
         * Fuerza la descarga del PDF generado por el backend,
         * sin necesidad de abrir una pestaña nueva.
         */
        function descargarRecibo(rutaPdf, numeroRecibo) {
            const enlace = document.createElement('a');
            enlace.href = rutaPdf;
            enlace.download = `recibo_${String(numeroRecibo).padStart(6, '0')}.pdf`;
            document.body.appendChild(enlace);
            enlace.click();
            enlace.remove();
        }

        function formatear(n) {
            return Number(n).toLocaleString('es-CO', {
                maximumFractionDigits: 2
            });
        }
    </script>

    <script src="../../public/js/app.js"></script>

    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>