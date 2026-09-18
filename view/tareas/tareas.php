<?php

require_once "../../app/verificar_sesion.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colsoftco - Tareas</title>

    <link rel="stylesheet" href="../../public/css/global.css">
    <link rel="stylesheet" href="../../public/css/layout.css">
    <link rel="stylesheet" href="../../public/css/kanban.css">
    <link href="tareas.css" rel="stylesheet">

    <?php include __DIR__ . '/../partials/scripts_layout.php'; ?>
</head>

<body>

    <div class="app">

        <?php include __DIR__ . '/../partials/sidebar.php'; ?>

        <div class="main">

            <?php include __DIR__ . '/../partials/topbar.php'; ?>

            <main class="content">
                <div class="tareas-pagina">

                    <div class="modulo-head">
                        <h2>☑ Gestión de Tareas</h2>
                        <p>Tus tareas en Kanban o Calendario. Arrastra las tarjetas entre columnas para cambiar su estado.</p>
                    </div>

                    <article class="tasks card" id="tareas">
                        <div class="title-row">
                            <h3><span>▣</span> Mis Tareas</h3>
                            <div class="title-actions">
                                <div class="view-tabs">
                                    <button type="button" data-vista="kanban" class="active">Kanban</button>
                                    <button type="button" data-vista="calendario">Calendario</button>
                                </div>
                                <button id="btnNuevaTarea" class="btn-nueva-tarea" type="button">+ Nueva tarea</button>
                            </div>
                        </div>

                        <div class="kanban" id="taskTableBody">
                            <p class="placeholder">Cargando tareas...</p>
                        </div>

                        <div class="calendario-tareas" id="calTareas" hidden></div>

                        <div class="tasks-summary" id="tasksSummary">
                            <span class="sum-loading">Calculando resumen…</span>
                        </div>
                    </article>

                </div>
            </main>

            <?php include __DIR__ . '/../partials/footer.php'; ?>

        </div>
    </div>

    <!-- ══ MODAL NUEVA TAREA ══ -->
    <div class="modal-overlay" id="modalTareaOverlay">
        <div class="modal-tarea">
            <h3>Registrar nueva tarea</h3>
            <form id="formNuevaTarea">
                <label for="tareaTitulo">Título</label>
                <input type="text" id="tareaTitulo" list="sugerenciasTareas"
                    placeholder="Ej. Solicitar espuma" autocomplete="off" required>
                <datalist id="sugerenciasTareas"></datalist>

                <label for="tareaPrioridad">Prioridad</label>
                <select id="tareaPrioridad">
                    <option value="low">Baja</option>
                    <option value="medium" selected>Media</option>
                    <option value="high">Alta</option>
                </select>

                <label for="tareaVencimiento">Fecha de vencimiento</label>
                <input type="date" id="tareaVencimiento">

                <div id="wrapAsignado" style="display:none">
                    <label for="tareaAsignado">Asignar a (solo admin)</label>
                    <select id="tareaAsignado"></select>
                </div>

                <div class="modal-actions">
                    <button type="button" id="btnCancelarTarea" class="btn-outline">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar tarea</button>
                </div>
            </form>
        </div>
    </div>

    <?php include __DIR__ . '/../partials/scripts_layout_footer.php'; ?>
    <script src="../../public/js/tareas.js"></script>
    <script src="../../public/js/calendario_tareas.js"></script>
    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>

</body>

</html>
