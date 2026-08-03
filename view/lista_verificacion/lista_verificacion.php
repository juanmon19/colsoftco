<?php
require_once 'conexion.php';

$mensaje = '';
$tipoMensaje = '';

/* =========================================================
   ACCIÓN: Registrar nuevo movimiento
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO movimientos_materia_prima
            (tipo_movimiento, codigo_material, nombre_material, cantidad, unidad_medida,
             lote, origen_destino, responsable, observaciones, estado_verificacion)
            VALUES
            (:tipo_movimiento, :codigo_material, :nombre_material, :cantidad, :unidad_medida,
             :lote, :origen_destino, :responsable, :observaciones, 'Pendiente')
        ");

        $stmt->execute([
            ':tipo_movimiento' => $_POST['tipo_movimiento'],
            ':codigo_material' => trim($_POST['codigo_material']),
            ':nombre_material' => trim($_POST['nombre_material']),
            ':cantidad'        => $_POST['cantidad'],
            ':unidad_medida'   => $_POST['unidad_medida'],
            ':lote'            => trim($_POST['lote']) ?: null,
            ':origen_destino'  => trim($_POST['origen_destino']) ?: null,
            ':responsable'     => trim($_POST['responsable']),
            ':observaciones'   => trim($_POST['observaciones']) ?: null,
        ]);

        $mensaje = 'Movimiento registrado correctamente. Queda pendiente de verificación.';
        $tipoMensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error al registrar el movimiento: ' . $e->getMessage();
        $tipoMensaje = 'error';
    }
}

/* =========================================================
   ACCIÓN: Verificar / Rechazar movimiento
   ========================================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'cambiar_estado') {
    try {
        $stmt = $pdo->prepare("
            UPDATE movimientos_materia_prima
            SET estado_verificacion = :estado,
                verificado_por = :verificado_por,
                fecha_verificacion = NOW()
            WHERE id = :id
        ");
        $stmt->execute([
            ':estado'         => $_POST['nuevo_estado'],
            ':verificado_por' => trim($_POST['verificado_por']) ?: 'Sin especificar',
            ':id'             => $_POST['id'],
        ]);

        $mensaje = 'Estado de verificación actualizado.';
        $tipoMensaje = 'exito';
    } catch (PDOException $e) {
        $mensaje = 'Error al actualizar el estado: ' . $e->getMessage();
        $tipoMensaje = 'error';
    }
}

/* =========================================================
   FILTROS DE BÚSQUEDA
   ========================================================= */
$filtroTipo   = $_GET['filtro_tipo']   ?? '';
$filtroEstado = $_GET['filtro_estado'] ?? '';
$buscar       = $_GET['buscar']        ?? '';

$condiciones = [];
$parametros  = [];

if ($filtroTipo !== '') {
    $condiciones[] = 'tipo_movimiento = :tipo';
    $parametros[':tipo'] = $filtroTipo;
}
if ($filtroEstado !== '') {
    $condiciones[] = 'estado_verificacion = :estado';
    $parametros[':estado'] = $filtroEstado;
}
if ($buscar !== '') {
    $condiciones[] = '(codigo_material LIKE :buscar OR nombre_material LIKE :buscar OR responsable LIKE :buscar)';
    $parametros[':buscar'] = '%' . $buscar . '%';
}

$sql = "SELECT * FROM movimientos_materia_prima";
if (!empty($condiciones)) {
    $sql .= ' WHERE ' . implode(' AND ', $condiciones);
}
$sql .= ' ORDER BY fecha_hora DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($parametros);
$movimientos = $stmt->fetchAll();

/* Totales rápidos para las tarjetas resumen */
$totalPendientes  = 0;
$totalVerificados = 0;
$totalRechazados  = 0;
foreach ($movimientos as $m) {
    if ($m['estado_verificacion'] === 'Pendiente')  $totalPendientes++;
    if ($m['estado_verificacion'] === 'Verificado') $totalVerificados++;
    if ($m['estado_verificacion'] === 'Rechazado')  $totalRechazados++;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Colsoftco - Lista de Verificación</title>
<link rel="stylesheet" href="../lista_verificacion/listaverificacion.css">
</head>
<body>

<header class="top-bar">
    <div class="logo-circulo">COL<br>SOFT</div>
    <h1>Lista de Verificación</h1>
</header>

<div class="contenedor">

    <div class="tarjeta">
        <h2>Lista de Verificación de Inventario y Procesos</h2>
        <p class="subtitulo">Registro y control de movimientos de materias primas (entradas, salidas, ajustes y devoluciones).</p>
    </div>

    <?php if ($mensaje): ?>
        <div class="alerta <?= $tipoMensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <!-- RESUMEN -->
    <div class="resumen">
        <div class="caja pendiente">
            <div class="numero"><?= $totalPendientes ?></div>
            <div class="etiqueta">Pendientes</div>
        </div>
        <div class="caja verificado">
            <div class="numero"><?= $totalVerificados ?></div>
            <div class="etiqueta">Verificados</div>
        </div>
        <div class="caja rechazado">
            <div class="numero"><?= $totalRechazados ?></div>
            <div class="etiqueta">Rechazados</div>
        </div>
        <div class="caja">
            <div class="numero"><?= count($movimientos) ?></div>
            <div class="etiqueta">Total movimientos</div>
        </div>
    </div>

    <!-- FORMULARIO DE REGISTRO -->
    <div class="tarjeta">
        <h2 style="text-align:left; margin-bottom:18px;">Registrar nuevo movimiento</h2>

        <form class="form-movimiento" method="POST" action="">
            <input type="hidden" name="accion" value="registrar">

            <div class="campo">
                <label>Tipo de movimiento</label>
                <select name="tipo_movimiento" required>
                    <option value="Entrada">Entrada</option>
                    <option value="Salida">Salida</option>
                    <option value="Ajuste">Ajuste</option>
                    <option value="Devolucion">Devolución</option>
                </select>
            </div>

            <div class="campo">
                <label>Código de material</label>
                <input type="text" name="codigo_material" placeholder="Ej. MP-001" required>
            </div>

            <div class="campo">
                <label>Nombre del material</label>
                <input type="text" name="nombre_material" placeholder="Ej. Resortes" required>
            </div>

            <div class="campo">
                <label>Cantidad</label>
                <input type="number" step="0.01" name="cantidad" placeholder="Ej. 100.00" required>
            </div>

            <div class="campo">
                <label>Unidad de medida</label>
                <select name="unidad_medida" required>
                    <option value="kg">Kilogramos (kg)</option>
                    <option value="g">Gramos (g)</option>
                    <option value="l">Litros (l)</option>
                    <option value="ml">Mililitros (ml)</option>
                    <option value="unidades">Unidades</option>
                </select>
            </div>

            <div class="campo">
                <label>Lote</label>
                <input type="text" name="lote" placeholder="Ej. L-2026-07-01">
            </div>

            <div class="campo">
                <label>Origen / Destino</label>
                <input type="text" name="origen_destino" placeholder="Proveedor, bodega, área de producción...">
            </div>

            <div class="campo">
                <label>Responsable</label>
                <input type="text" name="responsable" placeholder="Nombre de quien registra" required>
            </div>

            <div class="campo full">
                <label>Observaciones</label>
                <textarea name="observaciones" rows="3" placeholder="Detalles adicionales del movimiento..."></textarea>
            </div>

            <div class="campo full" style="align-items:flex-start;">
                <button type="submit" class="btn btn-primario">Registrar movimiento</button>
            </div>
        </form>
    </div>

    <!-- FILTROS Y TABLA -->
    <div class="tarjeta">
        <h2 style="text-align:left; margin-bottom:18px;">Movimientos registrados</h2>

        <form method="GET" action="" class="filtros">
            <div class="campo">
                <label>Tipo</label>
                <select name="filtro_tipo">
                    <option value="">Todos</option>
                    <option value="Entrada"    <?= $filtroTipo === 'Entrada' ? 'selected' : '' ?>>Entrada</option>
                    <option value="Salida"     <?= $filtroTipo === 'Salida' ? 'selected' : '' ?>>Salida</option>
                    <option value="Ajuste"     <?= $filtroTipo === 'Ajuste' ? 'selected' : '' ?>>Ajuste</option>
                    <option value="Devolucion" <?= $filtroTipo === 'Devolucion' ? 'selected' : '' ?>>Devolución</option>
                </select>
            </div>

            <div class="campo">
                <label>Estado</label>
                <select name="filtro_estado">
                    <option value="">Todos</option>
                    <option value="Pendiente"  <?= $filtroEstado === 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                    <option value="Verificado" <?= $filtroEstado === 'Verificado' ? 'selected' : '' ?>>Verificado</option>
                    <option value="Rechazado"  <?= $filtroEstado === 'Rechazado' ? 'selected' : '' ?>>Rechazado</option>
                </select>
            </div>

            <div class="campo buscar">
                <label>Buscar (código, material, responsable)</label>
                <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>" placeholder="Escribe para buscar...">
            </div>

            <div class="campo">
                <button type="submit" class="btn btn-dorado">Filtrar</button>
            </div>
        </form>

        <div class="tabla-wrapper">
            <table class="tabla-movimientos">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Código</th>
                        <th>Material</th>
                        <th>Cantidad</th>
                        <th>Lote</th>
                        <th>Origen/Destino</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="10" class="sin-datos">No hay movimientos registrados con los filtros seleccionados.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($movimientos as $m): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($m['fecha_hora'])) ?></td>
                            <td><span class="badge <?= $m['tipo_movimiento'] ?>"><?= $m['tipo_movimiento'] === 'Devolucion' ? 'Devolución' : $m['tipo_movimiento'] ?></span></td>
                            <td><?= htmlspecialchars($m['codigo_material']) ?></td>
                            <td><?= htmlspecialchars($m['nombre_material']) ?></td>
                            <td><?= number_format($m['cantidad'], 2) ?> <?= htmlspecialchars($m['unidad_medida']) ?></td>
                            <td><?= htmlspecialchars($m['lote'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($m['origen_destino'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($m['responsable']) ?></td>
                            <td><span class="estado-badge <?= $m['estado_verificacion'] ?>"><?= $m['estado_verificacion'] ?></span></td>
                            <td>
                                <?php if ($m['estado_verificacion'] === 'Pendiente'): ?>
                                    <div class="acciones-fila">
                                        <form method="POST" action="" onsubmit="return prepararVerificacion(this);">
                                            <input type="hidden" name="accion" value="cambiar_estado">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <input type="hidden" name="nuevo_estado" value="Verificado">
                                            <input type="hidden" name="verificado_por" class="campo-verificador">
                                            <button type="submit" class="btn btn-pequeno btn-verde">Verificar</button>
                                        </form>
                                        <form method="POST" action="" onsubmit="return prepararVerificacion(this);">
                                            <input type="hidden" name="accion" value="cambiar_estado">
                                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                                            <input type="hidden" name="nuevo_estado" value="Rechazado">
                                            <input type="hidden" name="verificado_por" class="campo-verificador">
                                            <button type="submit" class="btn btn-pequeno btn-rojo">Rechazar</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <small style="color:var(--gris-texto);">
                                        Por: <?= htmlspecialchars($m['verificado_por'] ?? '-') ?><br>
                                        <?= $m['fecha_verificacion'] ? date('d/m/Y H:i', strtotime($m['fecha_verificacion'])) : '' ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// Pide el nombre de quien verifica/rechaza antes de enviar el formulario
function prepararVerificacion(formulario) {
    const nombre = prompt('Nombre de quien realiza la verificación:');
    if (!nombre) {
        return false; // cancela el envío si no se ingresa nombre
    }
    formulario.querySelector('.campo-verificador').value = nombre;
    return true;
}
</script>

</body>
</html>