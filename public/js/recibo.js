// ============================================================
// Reemplaza tu función fabricar() actual en receta_colchones.php
// por esta versión, y agrega la función descargarRecibo().
// ============================================================

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