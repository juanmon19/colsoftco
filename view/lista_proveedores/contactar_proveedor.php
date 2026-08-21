<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contactar Proveedor - COLSOFTCO</title>
    <link rel="stylesheet" href="contactar.css">
</head>
<body>

    <!-- Tarjeta Principal -->
    <div class="landing-container">
        <div class="provider-card">
            <div class="icon-proveedor">🏢</div>
            <h2>Espumas Colombia SAS</h2>
            <p>Fabricación y comercialización de espumas para colchones e insumos industriales.</p>
            
            <button id="btnContactar" class="btn-contacto">
                Realizar Pedido
            </button>
            
            <div style="margin-top: 15px;">
                <a href="lista_proveedores.php" style="color: #0A1F44; text-decoration: none; font-size: 14px;">
                    ← Volver a proveedores
                </a>
            </div>
        </div>
    </div>

    <!-- === NUEVO: MODAL DE PEDIDO === -->
    <div class="modal-overlay" id="modalPedidoOverlay">
        <div class="modal-pedido">
            <h3>Detalles del Pedido</h3>
            
            <label for="materiaPrima">Materia prima a pedir:</label>
            <select id="materiaPrima">
                <!-- Puedes cambiar estas opciones por las reales de tu inventario -->
                <option value="Espuma de poliuretano">Espuma de poliuretano</option>
                <option value="Tela Jacquard">Tela Jacquard</option>
                <option value="Resortes Bonnell">Resortes Bonnell</option>
                <option value="Fieltro aislante">Fieltro aislante</option>
            </select>

            <label for="cantidadPedido">Cantidad:</label>
            <input type="number" id="cantidadPedido" placeholder="Ej: 50" min="1">

            <div class="modal-botones">
                <button type="button" id="btnCerrarModal" class="btn-secundario">Cancelar</button>
                <button type="button" id="btnEnviarPedido" class="btn-primario">Confirmar Pedido</button>
            </div>
        </div>
    </div>

    <!-- Alerta Flotante (Toast) -->
    <div id="alertaPedido" class="toast-alerta">
        <span class="icono-check">✔️</span>
        <div class="texto-alerta">
            <strong>¡Pedido Enviado!</strong>
            <span id="mensajeDinamicoAlerta">El pedido ha sido enviado al proveedor.</span>
        </div>
    </div>

    <script src="../../public/js/contactar_proveedor.js"></script>
</body>
</html>