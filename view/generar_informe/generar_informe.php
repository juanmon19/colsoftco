<?php

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Generar Informe</title>
    <link href="generar_informe.css" rel="stylesheet">
    <!-- SheetJS para exportar Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" crossorigin="anonymous"></script>
    <!-- jsPDF para exportar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"></script>
    <!-- jsPDF-AutoTable para tablas bonitas en PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"
        crossorigin="anonymous"></script>

    <style>
        /* Estilos solo para los elementos nuevos; el CSS original no se toca */

        .contenido {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 30px;
            flex-wrap: wrap;
        }

        #statusMsg {
            margin-top: 14px;
            padding: 9px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            display: none;
            color: white;
        }

        #statusMsg.ok {
            background: rgba(40, 167, 69, .25);
            border: 1px solid rgba(40, 167, 69, .5);
        }

        #statusMsg.err {
            background: rgba(220, 53, 69, .25);
            border: 1px solid rgba(220, 53, 69, .5);
        }

        /* Tarjeta materias primas con el mismo fondo y estilo que .informes */
        .materias {
            background-color: #0A1F44;
            padding: 30px 28px;
            width: 420px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
            margin-top: 60px;
        }

        .materias h2 {
            text-align: center;
            color: white;
            margin-bottom: 16px;
        }

        .mp-buscar {
            width: 100%;
            padding: 8px 10px;
            border-radius: 8px;
            border: none;
            background-color: white;
            font-size: 13px;
            margin-bottom: 14px;
            box-sizing: border-box;
        }

        .mp-wrap {
            max-height: 300px;
            overflow-y: auto;
            border-radius: 6px;
        }

        .mp-wrap::-webkit-scrollbar {
            width: 5px;
        }

        .mp-wrap::-webkit-scrollbar-thumb {
            background: rgba(212, 175, 55, .4);
            border-radius: 4px;
        }

        .mp-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .mp-tabla thead th {
            background: rgba(212, 175, 55, .15);
            color: #d4af37;
            font-weight: 700;
            padding: 8px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .8px;
            position: sticky;
            top: 0;
        }

        .mp-tabla tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, .06);
        }

        .mp-tabla tbody tr:hover {
            background: rgba(255, 255, 255, .04);
        }

        .mp-tabla tbody td {
            color: #c8d2e6;
            padding: 8px 10px;
            vertical-align: middle;
        }

        .mp-tabla tbody td:first-child {
            color: white;
            font-weight: 700;
        }

        .mp-badge {
            display: inline-block;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .mp-disp {
            background: rgba(40, 167, 69, .2);
            color: #6dffaa;
        }

        .mp-lim {
            background: rgba(255, 193, 7, .15);
            color: #ffd55a;
        }

        .mp-ago {
            background: rgba(220, 53, 69, .15);
            color: #ff8a8a;
        }

        .mp-count {
            color: rgba(200, 210, 230, .45);
            font-size: 11px;
            text-align: right;
            margin-top: 8px;
        }

        .radio-group {
            display: flex;
            gap: 16px;
            justify-content: center;
            margin: 10px 0;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: white;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <a class="logo" href="../../app/ir_panel.php">
                <img src="../../public/imagenes/logo.png" alt="logo">
            </a>
        </div>

        <div class="header-title">
            <h1>Informes</h1>
            <div class="title-underline"></div>
        </div>

        <button id="btnLogout" class="btn-logout" onclick="cerrarSesion()">
            Cerrar sesión
        </button>
        
    </header>

    <div class="contenido" style="margin: 0 auto; max-width: 1100px; width: 100%; padding: 0 16px;">

        <!-- ══ TARJETA ORIGINAL — diseño idéntico ══ -->
        <div class="informes">
            <h2>GENERAR INFORME</h2>
            <p>Marque la herramienta en la que desea generar el informe:</p>

            <div class="radio-group">
                <label><input type="radio" name="tipo" id="tipoExcel" value="excel" checked> Excel</label>
                <label><input type="radio" name="tipo" id="tipoPDF" value="pdf"> PDF</label>
            </div>

            <br>

            <p>Digite los meses a comparar:</p>

            <select id="mes_inicio" name="mes_inicio">
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>

            -

            <select id="mes_fin" name="mes_fin">
                <option value="1">Enero</option>
                <option value="2">Febrero</option>
                <option value="3">Marzo</option>
                <option value="4">Abril</option>
                <option value="5">Mayo</option>
                <option value="6">Junio</option>
                <option value="7">Julio</option>
                <option value="8">Agosto</option>
                <option value="9">Septiembre</option>
                <option value="10">Octubre</option>
                <option value="11">Noviembre</option>
                <option value="12">Diciembre</option>
            </select>

            <br><br>

            <button id="btnGenerar">Generar</button>

            <div id="statusMsg"></div>
        </div>

        <!-- ══ TARJETA MATERIAS PRIMAS ══ -->
        <div class="materias">
            <h2>MATERIAS PRIMAS</h2>

            <input type="text" class="mp-buscar" id="mpSearch" placeholder="Buscar por nombre o categoría...">

            <div class="mp-wrap">
                <table class="mp-tabla">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Stock</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody id="mpBody"></tbody>
                </table>
            </div>

            <p class="mp-count" id="mpCount"></p>
        </div>

    </div><!-- /contenido -->

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
        /* ═══════════════════════════════════════
           MATERIAS PRIMAS
        ═══════════════════════════════════════ */
        /* ═══════════════════════════════════════
        MATERIAS PRIMAS
     ═══════════════════════════════════════ */

        let MATERIAS = [];

        async function cargarMaterias() {

            console.log("Entró a cargarMaterias");

            try {

                const respuesta = await fetch('obtener_materias.php');
                const datos = await respuesta.json();

                console.log(datos);

                MATERIAS = datos;

                renderMP(MATERIAS);

            } catch (error) {

                console.error("ERROR:", error);

            }
        }

        function renderMP(data) {

            const tbody = document.getElementById("mpBody");
            const count = document.getElementById("mpCount");

            if (!data.length) {
                tbody.innerHTML =
                    `<tr>
            <td colspan="4"
                style="text-align:center;padding:20px;color:rgba(200,210,230,.4);">
                Sin resultados
            </td>
        </tr>`;
                count.textContent = "";
                return;
            }

            tbody.innerHTML = data.map(mp => {

                const cls =
                    mp.estado === "Disponible"
                        ? "mp-disp"
                        : mp.estado === "Limitado"
                            ? "mp-lim"
                            : "mp-ago";

                return `
        <tr>
            <td>${mp.nombre}</td>
            <td>${mp.categoria}</td>
            <td>${mp.stock}</td>
            <td>
                <span class="mp-badge ${cls}">
                    ${mp.estado}
                </span>
            </td>
        </tr>
        `;
            }).join("");

            count.textContent =
                `${data.length} de ${MATERIAS.length} registros`;
        }

        document.getElementById("mpSearch")
            .addEventListener("input", function () {

                const q = this.value.toLowerCase();

                const filtradas = MATERIAS.filter(mp =>
                    mp.nombre.toLowerCase().includes(q) ||
                    mp.categoria.toLowerCase().includes(q)
                );

                renderMP(filtradas);

            });

        cargarMaterias();
        /* ═══════════════════════════════════════
           EXPORTACIÓN EXCEL / PDF
        ═══════════════════════════════════════ */
        const MESES = ["", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
            "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

        // Genera filas de ejemplo. En producción: reemplaza con fetch() a tu PHP.
        function generarDatos(ini, fin) {
            const filas = [];
            for (let m = ini; m <= fin; m++) {
                MATERIAS.forEach(mp => {
                    const consumo = Math.floor(Math.random() * 300) + 10;
                    filas.push({
                        "Mes": MESES[m],
                        "Materia Prima": mp.nombre,
                        "Categoría": mp.categoria,
                        "Stock Actual": mp.stock,
                        "Consumo (kg)": consumo,
                        "Estado": mp.estado
                    });
                });
            }
            return filas;
        }

        function mostrarMsg(texto, tipo) {
            const el = document.getElementById("statusMsg");
            el.textContent = texto;
            el.className = tipo;
            el.style.display = "block";
            setTimeout(() => { el.style.display = "none"; }, 4000);
        }

        function exportarExcel(datos, nombre) {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.json_to_sheet(datos);
            ws['!cols'] = [{ wch: 12 }, { wch: 26 }, { wch: 16 }, { wch: 13 }, { wch: 14 }, { wch: 13 }];
            XLSX.utils.book_append_sheet(wb, ws, "Informe");
            XLSX.writeFile(wb, nombre + ".xlsx");
        }

        function exportarPDF(datos, titulo, nombre) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF({ orientation: "landscape", unit: "mm", format: "a4" });

            doc.setFillColor(10, 31, 68);
            doc.rect(0, 0, 297, 20, "F");
            doc.setDrawColor(212, 175, 55);
            doc.setLineWidth(0.8);
            doc.line(0, 20, 297, 20);

            doc.setTextColor(212, 175, 55);
            doc.setFontSize(13);
            doc.setFont("helvetica", "bold");
            doc.text(titulo, 148.5, 13, { align: "center" });

            doc.setTextColor(120, 120, 120);
            doc.setFontSize(8);
            doc.setFont("helvetica", "normal");
            doc.text("Generado: " + new Date().toLocaleString("es-CO"), 10, 26);

            const cols = Object.keys(datos[0]);
            const rows = datos.map(f => cols.map(c => f[c]));

            doc.autoTable({
                head: [cols], body: rows, startY: 30,
                styles: { fontSize: 8, cellPadding: 2.5 },
                headStyles: { fillColor: [10, 31, 68], textColor: [212, 175, 55], fontStyle: "bold" },
                alternateRowStyles: { fillColor: [240, 243, 248] },
                didDrawPage: (d) => {
                    doc.setFontSize(7);
                    doc.setTextColor(150, 150, 150);
                    doc.text(`Página ${d.pageNumber}`, 148.5, doc.internal.pageSize.height - 4, { align: "center" });
                }
            });

            doc.save(nombre + ".pdf");
        }

        document.getElementById("btnGenerar").addEventListener("click", () => {
            const tipo = document.querySelector('input[name="tipo"]:checked')?.value;
            const ini = parseInt(document.getElementById("mes_inicio").value);
            const fin = parseInt(document.getElementById("mes_fin").value);

            if (!tipo) { mostrarMsg("Seleccione un formato (Excel o PDF).", "err"); return; }
            if (ini > fin) { mostrarMsg("El mes de inicio no puede ser mayor al mes final.", "err"); return; }

            const rango = `${MESES[ini]} - ${MESES[fin]}`;
            const titulo = `Informe de Materias Primas | ${rango}`;
            const archivo = `Informe_${MESES[ini]}_${MESES[fin]}`;
            const datos = generarDatos(ini, fin);

            try {
                if (tipo === "excel") {
                    exportarExcel(datos, archivo);
                    mostrarMsg(`Informe Excel generado (${datos.length} registros).`, "ok");
                } else {
                    exportarPDF(datos, titulo, archivo);
                    mostrarMsg(`Informe PDF generado (${datos.length} registros).`, "ok");
                }
            } catch (e) {
                console.error(e);
                mostrarMsg("Error al generar el informe. Revise la consola.", "err");
            }
        });
    </script>
    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>