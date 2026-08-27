<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('America/Bogota');

ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; line-height: 1.5; font-size: 14px; margin: 30px; }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 10px; border-bottom: 2px solid #0A1F44; }
        h1 { color: #0A1F44; font-size: 22px; text-transform: uppercase; margin-bottom: 5px; }
        h2 { color: #D4AF37; font-size: 16px; margin-top: 25px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .prioridad { display: inline-block; padding: 3px 8px; border-radius: 4px; font-weight: bold; font-size: 11px; margin-bottom: 10px; color: white; }
        .p-urgente { background-color: #dc3545; }
        .p-alta { background-color: #fd7e14; }
        .p-media { background-color: #ffc107; color: #000; }
        .p-baja { background-color: #0dcaf0; color: #000; }
        p { margin-bottom: 10px; }
        .footer { position: fixed; bottom: -20px; left: 0; right: 0; text-align: center; font-size: 10px; color: #777; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Auditoría de Seguridad y Código</h1>
        <p>Reporte Detallado de Hallazgos Técnicos</p>
        <p style="font-size: 12px; color: #666;">Generado: <?= date('d/m/Y H:i') ?></p>
    </div>

    <h2>1. Falta de Autenticación (BAC)</h2>
    <span class="prioridad p-urgente">Prioridad: 1 (URGENTE)</span>
    <p><strong>Descripción:</strong> Un archivo expuesto (como <i>logica_colchones.php</i>) permite que un usuario acceda mediante la URL directa sin haber iniciado sesión en el sistema. Esto representa una falla crítica de Control de Acceso.</p>
    <p><strong>Solución:</strong> Implementar la directiva <code>require_once</code> apuntando al validador de sesión en la línea número 1 del archivo para bloquear peticiones anónimas.</p>

    <h2>2. Fallo Transaccional & Commit Prematuro</h2>
    <span class="prioridad p-urgente">Prioridad: 2 (URGENTE)</span>
    <p><strong>Descripción:</strong> Cerrar una transacción (commit) antes de completar todas las consultas SQL relacionadas pone en riesgo la integridad de la base de datos. Si una consulta falla después del commit, el sistema quedará con inventarios inconsistentes.</p>
    <p><strong>Solución:</strong> Trasladar <code>$db->commit()</code> al final del bloque de ejecución lógica, garantizando que todas las acciones (descuento, registro y logs) se cumplan exitosamente en conjunto.</p>

    <h2>3. Condición de Carrera en Descuentos</h2>
    <span class="prioridad p-alta">Prioridad: 3 (ALTA)</span>
    <p><strong>Descripción:</strong> Riesgo lógico que ocurre cuando múltiples solicitudes simultáneas intentan restar inventario al mismo tiempo, pudiendo eludir las validaciones previas en PHP y generando un stock negativo.</p>
    <p><strong>Solución:</strong> Delegar la validación condicional directamente al motor de la base de datos agregando <code>WHERE stock_actual >= requerida</code> en la cláusula del UPDATE.</p>

    <h2>4. Falta de Token CSRF</h2>
    <span class="prioridad p-alta">Prioridad: 4 (ALTA)</span>
    <p><strong>Descripción:</strong> Cross-Site Request Forgery (Falsificación de Petición en Sitios Cruzados). Permite que un sitio web malicioso de terceros induzca al navegador del usuario a realizar acciones no deseadas dentro de nuestra plataforma aprovechando su sesión activa.</p>
    <p><strong>Solución:</strong> Generar e incrustar un token secreto único en los formularios HTML y validar que el servidor reciba exactamente el mismo token antes de procesar órdenes de fabricación.</p>

    <h2>5. Inyección XSS en Alertas</h2>
    <span class="prioridad p-media">Prioridad: 5 (MEDIA)</span>
    <p><strong>Descripción:</strong> Vulnerabilidad que permite la ejecución de secuencias de comandos JavaScript ocultas en los campos de texto, las cuales pueden activarse al visualizar alertas o correos del sistema.</p>
    <p><strong>Solución:</strong> Sanear todas las variables de salida usando la función <code>htmlspecialchars()</code> para neutralizar etiquetas de programación.</p>

    <h2>6. Optimización SQL y Refactorización MVC</h2>
    <span class="prioridad p-baja">Prioridad: 6 (BAJA)</span>
    <p><strong>Descripción:</strong> Deuda técnica en el diseño del código. Declarar consultas SQL preparadas dentro de bucles reduce el rendimiento. Mezclar HTML con PHP puro dificulta la mantenibilidad visual del proyecto.</p>
    <p><strong>Solución:</strong> Instanciar el método <code>prepare()</code> antes del bucle y separar el código de interfaz a una subcarpeta de Vistas (Arquitectura MVC).</p>

    <div class="footer">
        Documento de Auditoría de Código Generado Automáticamente.
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$dompdf->stream('Auditoria_de_Codigo.pdf', ['Attachment' => true]);
exit;
?>