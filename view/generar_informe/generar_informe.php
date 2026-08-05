<?php

require_once "../../app/verificar_sesion.php";

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Informe</title>
    <link href="generar_informe.css" rel="stylesheet">
    <!-- SheetJS para exportar Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js" crossorigin="anonymous"></script>
    <!-- jsPDF para exportar PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" crossorigin="anonymous"></script>
    <!-- jsPDF-AutoTable para tablas bonitas en PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"
        crossorigin="anonymous"></script>
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

    <div class="contenido">

        <!-- ══ TARJETA INFORMES ══ -->
        <div class="informes">
            <h2>GENERAR INFORME</h2>
            <p>Marque la herramienta en la que desea generar el informe:</p>

            <div class="radio-group">
                <label><input type="radio" name="tipo" id="tipoExcel" value="excel" checked> Excel</label>
                <label><input type="radio" name="tipo" id="tipoPDF" value="pdf"> PDF</label>
            </div>

            <br>

            <p>Digite los meses a comparar:</p>

            <div style="display: flex; gap: 10px; justify-content: center;">
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
                <span style="color: white; display: flex; align-items: center;">-</span>
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
            </div>

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
        let MATERIAS = [];

        async function cargarMaterias() {
            try {
                const respuesta = await fetch('obtener_materias.php');
                const datos = await respuesta.json();
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
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" style="text-align:center;padding:20px;color:rgba(200,210,230,.4);">
                            Sin resultados
                        </td>
                    </tr>`;
                count.textContent = "";
                return;
            }

            tbody.innerHTML = data.map(mp => {
                const cls =
                    mp.estado === "Disponible" ? "mp-disp" :
                    mp.estado === "Limitado" ? "mp-lim" : "mp-ago";

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
                </tr>`;
            }).join("");

            count.textContent = `${data.length} de ${MATERIAS.length} registros`;
        }

        document.getElementById("mpSearch").addEventListener("input", function () {
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

    <script src="../../public/js/app.js"></script>
    
    <script src="https://cdn.botpress.cloud/webchat/v3.6/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/05/14/19/20260514194818-J71XBHCL.js" defer></script>
</body>

</html>