<?php
date_default_timezone_set('America/Bogota'); 
require_once __DIR__ . '/../../app/verificar_sesion.php';
require_once __DIR__ . '/../../app/HistorialMovimientos.php';
require_once __DIR__ . '/../../config/conexion.php';

// Conexión directa (para leer historial_produccion, que NO pasa por historial_movimientos)
$conexionColchones = new Conexion();
$dbConn = $conexionColchones->getConnection();

// -------------------------------------------------------------------------
// FPDF: como tu proyecto tiene carpeta vendor/ (Composer), asumo que FPDF
// se carga por autoload. Si en tu app/ReciboPDF.php lo cargas distinto
// (por ejemplo con una ruta manual a una carpeta libs/), dime cuál es y
// cambio esta línea por esa misma ruta.
// -------------------------------------------------------------------------
require_once __DIR__ . '/../../vendor/autoload.php';

// =========================================================================
// CONFIGURACIÓN — AJUSTA ESTOS VALORES SI TUS "modulo" EN
// historial_movimientos SE LLAMAN DIFERENTE. Las "accion" ya están
// confirmadas según tu historial.php: crear / editar / eliminar / entrada / salida
// =========================================================================
$CONFIG = [
    'materia_prima' => [
        'modulo'          => 'materia_prima',
        'accion_entrada'  => 'entrada',
        'accion_salida'   => 'salida',
    ],
    'proveedores' => [
        'modulo' => 'proveedores',
        'accion' => 'crear',
    ],
    // 'colchones' ya no usa esta configuración: se consulta directo de
    // historial_produccion (ver más abajo), porque ese módulo no escribe
    // en historial_movimientos.
    'usuarios' => [
        'modulo' => 'usuarios',
        'accion' => 'crear',
    ],
];

// =========================================================================
// 1. CALCULAR RANGO DE FECHAS SEGÚN tipo=dia|semana|mes
// =========================================================================
$tipo = $_GET['tipo'] ?? 'dia';

switch ($tipo) {

    case 'semana':
        $valor = $_GET['fecha_semana'] ?? date('o-\WW');
        [$anio, $semana] = explode('-W', $valor);
        $dt = new DateTime();
        $dt->setISODate((int) $anio, (int) $semana);
        $fechaInicio = clone $dt;
        $fechaFin = (clone $dt)->modify('+6 days');
        $etiquetaRango = 'Semana ' . $semana . ' de ' . $anio;
        break;

    case 'mes':
        $valor = $_GET['fecha_mes'] ?? date('Y-m');
        [$anio, $mes] = explode('-', $valor);
        $fechaInicio = new DateTime("$anio-$mes-01");
        $fechaFin = (clone $fechaInicio)->modify('last day of this month');
        $meses = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
                   7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
        $etiquetaRango = $meses[(int) $mes] . ' de ' . $anio;
        break;

    case 'dia':
    default:
        $valor = $_GET['fecha_dia'] ?? date('Y-m-d');
        $fechaInicio = new DateTime($valor);
        $fechaFin = new DateTime($valor);
        $etiquetaRango = $fechaInicio->format('d/m/Y');
        break;
}

$fechaInicioStr = $fechaInicio->format('Y-m-d');
$fechaFinStr = $fechaFin->format('Y-m-d');

// =========================================================================
// 2. CONSULTAR LOS 4 BLOQUES USANDO TU CLASE HistorialMovimientos
//    (necesita el método obtenerTodos() — ver PEGAR_EN_app_HistorialMovimientos.php)
// =========================================================================
$historial = new HistorialMovimientos();

$filtroFechas = [
    'fecha_inicio' => $fechaInicioStr,
    'fecha_fin'    => $fechaFinStr,
];

$movimientosMateriaPrima = $historial->obtenerTodos(array_merge($filtroFechas, [
    'modulo' => $CONFIG['materia_prima']['modulo'],
]));

$registrosProveedores = $historial->obtenerTodos(array_merge($filtroFechas, [
    'modulo' => $CONFIG['proveedores']['modulo'],
    'accion' => $CONFIG['proveedores']['accion'],
]));

// Colchones fabricados: viene de historial_produccion (NO de historial_movimientos),
// igual que hace tu view/historial_fabricacion/historial_fabricacion.php
$stmtColchones = $dbConn->prepare(
    "SELECT h.id, m.nombre_modelo, h.cantidad, h.fecha_fabricacion, h.usuario
     FROM historial_produccion h
     INNER JOIN modelos_colchon m ON m.id_modelo = h.id_modelo
     WHERE h.fecha_fabricacion BETWEEN :inicio AND :fin
     ORDER BY h.fecha_fabricacion DESC"
);
$stmtColchones->execute([
    ':inicio' => $fechaInicioStr . ' 00:00:00',
    ':fin'    => $fechaFinStr . ' 23:59:59',
]);
$registrosColchones = $stmtColchones->fetchAll(PDO::FETCH_ASSOC);

$registrosUsuarios = $historial->obtenerTodos(array_merge($filtroFechas, [
    'modulo' => $CONFIG['usuarios']['modulo'],
    'accion' => $CONFIG['usuarios']['accion'],
]));

// Separar materia prima en entradas / salidas según "accion"
$entradas = [];
$salidas  = [];
foreach ($movimientosMateriaPrima as $mov) {
    $accionLower = mb_strtolower($mov['accion']);
    if (str_contains($accionLower, $CONFIG['materia_prima']['accion_salida'])) {
        $salidas[] = $mov;
    } else {
        $entradas[] = $mov;
    }
}

// =========================================================================
// 3. HELPERS
// =========================================================================
function u($texto) {
    // FPDF clásico no maneja UTF-8 directamente: se convierte a Latin-1
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string) $texto);
}

function extraerCampo(array $fila, array $claves, string $fallback = '—'): string {
    $datos = json_decode($fila['datos_nuevos'] ?? '', true);
    if (is_array($datos)) {
        foreach ($claves as $clave) {
            if (!empty($datos[$clave])) {
                return (string) $datos[$clave];
            }
        }
    }
    return $fila['descripcion'] ?: $fallback;
}

/**
 * Para un movimiento de materia prima, calcula la cantidad exacta que
 * entró o salió a partir del stock_actual guardado antes/después
 * (logica_inventario.php lo guarda en cada cambio).
 */
function calcularMovimientoMateriaPrima(array $mov): array {
    $antes = json_decode($mov['datos_anteriores'] ?? '', true);
    $despues = json_decode($mov['datos_nuevos'] ?? '', true);

    $stockAntes = is_array($antes) ? ($antes['stock_actual'] ?? null) : null;
    $stockDespues = is_array($despues) ? ($despues['stock_actual'] ?? null) : null;

    $nombreMaterial = is_array($despues) ? ($despues['nombre_material'] ?? null) : null;

    return [
        'material'  => $nombreMaterial ?: $mov['descripcion'],
        'cantidad'  => ($stockAntes !== null && $stockDespues !== null)
            ? abs((float) $stockDespues - (float) $stockAntes)
            : null,
    ];
}

// =========================================================================
// 4. CLASE PDF (estilo navy / dorado del sistema)
// =========================================================================
class InformePDF extends FPDF
{
    public string $etiquetaRango = '';

    function Header()
    {
        $logo = __DIR__ . '/../../public/imagenes/logo.png';
        if (file_exists($logo)) {
            $this->Image($logo, 95, 10, 20);
        }

        $this->SetY(34);
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(10, 31, 68);
        $this->Cell(0, 10, u('INFORME GENERAL DE ACTIVIDAD'), 0, 1, 'C');

        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 6, u('COLSOFTCO - Sistema de Gestión Max&Flex'), 0, 1, 'C');

        $this->Ln(2);
        $this->SetDrawColor(212, 175, 55);
        $this->SetLineWidth(0.8);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(6);

        $this->SetFillColor(244, 246, 251);
        $this->SetTextColor(10, 31, 68);
        $this->SetFont('Arial', 'B', 10);
        $this->Rect(15, $this->GetY(), 180, 14, 'F');
        $this->SetX(20);
        $this->Cell(0, 7, u('Rango evaluado: ' . $this->etiquetaRango), 0, 2);
        $this->SetX(20);
        $this->Cell(0, 7, u('Fecha de generación: ' . date('d/m/Y H:i')), 0, 1);
        $this->Ln(6);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(150, 150, 150);
        $this->Cell(0, 10, u('Generado automáticamente por el sistema COLSOFTCO • Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
    }

    function TituloSeccion(string $titulo)
    {
        $this->SetFont('Arial', 'B', 13);
        $this->SetTextColor(10, 31, 68);
        $this->Cell(0, 9, u($titulo), 0, 1, 'L');
        $this->Ln(1);
    }

    function EncabezadoTabla(array $columnas, array $anchos)
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(10, 31, 68);
        $this->SetTextColor(255, 255, 255);
        foreach ($columnas as $i => $col) {
            $this->Cell($anchos[$i], 8, u($col), 0, 0, 'L', true);
        }
        $this->Ln();
    }

    function FilaVacia(string $mensaje)
    {
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 10, u($mensaje), 0, 1, 'C');
        $this->Ln(2);
    }
}

$pdf = new InformePDF();
$pdf->etiquetaRango = $etiquetaRango;
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetAutoPageBreak(true, 20);

// -------------------------------------------------------------------------
// TABLA 1: ENTRADAS / SALIDAS DE MATERIA PRIMA
// -------------------------------------------------------------------------
$pdf->TituloSeccion('Movimientos de Materia Prima');

$anchosMp = [28, 55, 25, 22, 25, 25];
$pdf->EncabezadoTabla(['Fecha', 'Material', 'Tipo', 'Cantidad', 'Usuario', ''], $anchosMp);

$totalEntradas = 0;
$totalSalidas = 0;

if (empty($entradas) && empty($salidas)) {
    $pdf->FilaVacia('No se registraron movimientos de materia prima en este rango.');
} else {
    $pdf->SetFont('Arial', '', 9);
    foreach (array_merge($entradas, $salidas) as $mov) {
        $esEntrada = in_array($mov, $entradas, true);
        $detalle = calcularMovimientoMateriaPrima($mov);
        $cantidadTexto = $detalle['cantidad'] !== null ? number_format($detalle['cantidad'], 2) : '—';

        if ($esEntrada && $detalle['cantidad'] !== null) {
            $totalEntradas += $detalle['cantidad'];
        } elseif (!$esEntrada && $detalle['cantidad'] !== null) {
            $totalSalidas += $detalle['cantidad'];
        }

        $pdf->SetTextColor(30, 30, 30);
        $pdf->Cell($anchosMp[0], 7, date('d/m/Y H:i', strtotime($mov['fecha_hora'])), 0, 0);
        $pdf->Cell($anchosMp[1], 7, u(mb_substr($detalle['material'], 0, 32)), 0, 0);

        if ($esEntrada) {
            $pdf->SetTextColor(22, 130, 60);
            $pdf->Cell($anchosMp[2], 7, u('Entrada'), 0, 0);
        } else {
            $pdf->SetTextColor(190, 30, 30);
            $pdf->Cell($anchosMp[2], 7, u('Salida'), 0, 0);
        }

        $pdf->SetTextColor(30, 30, 30);
        $signo = $esEntrada ? '+' : '-';
        $pdf->Cell($anchosMp[3], 7, u($signo . $cantidadTexto), 0, 0);
        $pdf->Cell($anchosMp[4], 7, u($mov['usuario_nombre'] ?: 'Sistema'), 0, 0);
        $pdf->Ln();
    }

    // ── Resumen: cuánta materia prima entró y salió en total ──
    $pdf->Ln(2);
    $pdf->SetFillColor(240, 253, 244);
    $pdf->SetTextColor(22, 130, 60);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Rect(15, $pdf->GetY(), 87, 9, 'F');
    $pdf->SetX(18);
    $pdf->Cell(84, 9, u('Total que ENTRÓ: ' . number_format($totalEntradas, 2) . ' unidades'), 0, 0);

    $pdf->SetFillColor(254, 242, 242);
    $pdf->SetTextColor(190, 30, 30);
    $pdf->Rect(103, $pdf->GetY(), 87, 9, 'F');
    $pdf->SetX(106);
    $pdf->Cell(84, 9, u('Total que SALIÓ: ' . number_format($totalSalidas, 2) . ' unidades'), 0, 1);
}
$pdf->Ln(8);

// -------------------------------------------------------------------------
// TABLA 2: PROVEEDORES REGISTRADOS
// -------------------------------------------------------------------------
$pdf->TituloSeccion('Proveedores registrados');
$anchosProv = [35, 90, 30, 25];
$pdf->EncabezadoTabla(['Fecha', 'Proveedor', 'Usuario', ''], $anchosProv);

if (empty($registrosProveedores)) {
    $pdf->FilaVacia('No se registraron proveedores nuevos en este rango.');
} else {
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(30, 30, 30);
    foreach ($registrosProveedores as $prov) {
        $nombre = extraerCampo($prov, ['nombre', 'nombre_proveedor', 'razon_social']);
        $pdf->Cell($anchosProv[0], 7, date('d/m/Y H:i', strtotime($prov['fecha_hora'])), 0, 0);
        $pdf->Cell($anchosProv[1], 7, u(mb_substr($nombre, 0, 55)), 0, 0);
        $pdf->Cell($anchosProv[2], 7, u($prov['usuario_nombre'] ?: 'Sistema'), 0, 0);
        $pdf->Ln();
    }
}
$pdf->Ln(6);

// -------------------------------------------------------------------------
// TABLA 3: COLCHONES FABRICADOS
// -------------------------------------------------------------------------
$pdf->TituloSeccion('Colchones fabricados');
$anchosCol = [35, 70, 30, 25, 20];
$pdf->EncabezadoTabla(['Fecha', 'Modelo', 'Cantidad', 'Usuario', ''], $anchosCol);

if (empty($registrosColchones)) {
    $pdf->FilaVacia('No se fabricaron colchones en este rango.');
} else {
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(30, 30, 30);
    foreach ($registrosColchones as $col) {
        $pdf->Cell($anchosCol[0], 7, date('d/m/Y H:i', strtotime($col['fecha_fabricacion'])), 0, 0);
        $pdf->Cell($anchosCol[1], 7, u(mb_substr($col['nombre_modelo'], 0, 42)), 0, 0);
        $pdf->Cell($anchosCol[2], 7, u((int) $col['cantidad'] . ' uds.'), 0, 0);
        $pdf->Cell($anchosCol[3], 7, u($col['usuario'] ?: 'Sistema'), 0, 0);
        $pdf->Ln();
    }
}
$pdf->Ln(6);

// -------------------------------------------------------------------------
// TABLA 4: USUARIOS REGISTRADOS
// -------------------------------------------------------------------------
$pdf->TituloSeccion('Usuarios registrados');
$anchosUsr = [35, 70, 40, 35];
$pdf->EncabezadoTabla(['Fecha', 'Usuario nuevo', 'Rol', 'Registrado por'], $anchosUsr);

if (empty($registrosUsuarios)) {
    $pdf->FilaVacia('No se registraron usuarios nuevos en este rango.');
} else {
    $pdf->SetFont('Arial', '', 9);
    $pdf->SetTextColor(30, 30, 30);
    foreach ($registrosUsuarios as $usr) {
        $nombreNuevo = extraerCampo($usr, ['nombre', 'nombre_usuario']);
        $rol = extraerCampo($usr, ['rol'], '—');
        $pdf->Cell($anchosUsr[0], 7, date('d/m/Y H:i', strtotime($usr['fecha_hora'])), 0, 0);
        $pdf->Cell($anchosUsr[1], 7, u(mb_substr($nombreNuevo, 0, 42)), 0, 0);
        $pdf->Cell($anchosUsr[2], 7, u($rol), 0, 0);
        $pdf->Cell($anchosUsr[3], 7, u($usr['usuario_nombre'] ?: 'Sistema'), 0, 0);
        $pdf->Ln();
    }
}

// =========================================================================
// 5. SALIDA DEL PDF
// =========================================================================
$nombreArchivo = 'Informe_General_' . $fechaInicioStr . '_a_' . $fechaFinStr . '.pdf';
$pdf->Output('D', $nombreArchivo); // 'D' = fuerza la descarga del archivo