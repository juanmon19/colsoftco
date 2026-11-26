<?php
require_once __DIR__ . '/validar_stock.php';

require_once __DIR__ . '/../config/conexion.php';

foreach ([
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../config/setting.php',
    __DIR__ . '/correo_plantilla.php',
] as $__depAlerta) {
    if (file_exists($__depAlerta)) {
        require_once $__depAlerta;
    }
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class AlertaStockLogica
{
    private $conn;

    public function __construct()
    {
        $db = new Conexion();
        $this->conn = $db->getConnection();
    }

    /**
     * Guarda el stock mínimo y la configuración de notificación de un material.
     */
    public function guardarConfiguracion($idMaterial, $stockMinimo, $notificarEmail, $correoNotificacion)
    {
        if (!stockValido($stockMinimo)) {
            return false;
        }
        $sql = "
            UPDATE materias_primas
            SET
                stock_minimo = :stock_minimo,
                notificar_email = :notificar_email,
                correo_notificacion = :correo_notificacion
            WHERE id_material = :id_material
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':stock_minimo'        => $stockMinimo,
            ':notificar_email'     => $notificarEmail ? 1 : 0,
            ':correo_notificacion' => $correoNotificacion ?: null,
            ':id_material'         => $idMaterial,
        ]);
    }

    /**
     * Revisa UN material: si su stock_actual <= stock_minimo y tiene notificación
     * activada, envía el correo (una sola vez, hasta que el stock vuelva a subir).
     * Debe llamarse justo después de cualquier operación que modifique stock_actual.
     */
    public function verificarStock($idMaterial)
    {
        $sql = "SELECT * FROM materias_primas WHERE id_material = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $idMaterial]);
        $material = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$material) {
            return;
        }

        $bajoMinimo = $material['stock_actual'] <= $material['stock_minimo'];

        if ($bajoMinimo && (int) $material['alerta_enviada'] === 0) {

            $mensaje = "El material '{$material['nombre_material']}' alcanzó su stock mínimo "
                . "(actual: {$material['stock_actual']}, mínimo: {$material['stock_minimo']}).";

            // Registro en la bandeja de notificaciones interna (siempre)
            $this->registrarNotificacion($idMaterial, $mensaje);

            // Envío de correo, solo si el material lo tiene configurado
            if ((int) $material['notificar_email'] === 1 && !empty($material['correo_notificacion'])) {
                $this->enviarCorreoAlerta(
                    $material['correo_notificacion'],
                    $material['nombre_material'],
                    $material['stock_actual'],
                    $material['stock_minimo']
                );
            }

            $this->marcarAlertaEnviada($idMaterial, true);
        }

        // Si el stock volvió a subir por encima del mínimo, se habilita
        // para poder enviar una nueva alerta la próxima vez que baje.
        if (!$bajoMinimo && (int) $material['alerta_enviada'] === 1) {
            $this->marcarAlertaEnviada($idMaterial, false);
        }
    }

    /**
     * Inserta un registro en la tabla notificaciones para que aparezca
     * en la bandeja de notificaciones dentro de la aplicación.
     */
    private function registrarNotificacion($idMaterial, $mensaje)
    {
        $sql = "
            INSERT INTO notificaciones (id_material, mensaje)
            VALUES (:id_material, :mensaje)
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id_material' => $idMaterial,
            ':mensaje'     => $mensaje,
        ]);
    }

    /**
     * Revisa TODOS los materiales. Útil para correr manualmente o vía cron
     * (por ejemplo una vez al día) además de las verificaciones puntuales.
     */
    public function verificarTodoElInventario()
    {
        $sql = "SELECT id_material FROM materias_primas";
        $stmt = $this->conn->query($sql);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            $this->verificarStock($fila['id_material']);
        }
    }

    public function listarMaterialesEnAlerta()
    {
        $sql = "
            SELECT mp.*, um.nombre_unidad
            FROM materias_primas mp
            LEFT JOIN unidades_medida um ON mp.id_unidad = um.id_unidad
            WHERE mp.stock_actual <= mp.stock_minimo
            ORDER BY mp.nombre_material ASC
        ";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarNotificaciones($soloNoLeidas = false)
    {
        $sql = "
            SELECT n.*, mp.nombre_material
            FROM notificaciones n
            LEFT JOIN materias_primas mp ON n.id_material = mp.id_material
        ";

        if ($soloNoLeidas) {
            $sql .= " WHERE n.leida = 0";
        }

        $sql .= " ORDER BY n.fecha_generada DESC";

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarNotificacionLeida($idNotificacion)
    {
        $sql = "UPDATE notificaciones SET leida = 1 WHERE id_notificacion = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':id' => $idNotificacion]);
    }

    private function marcarAlertaEnviada($idMaterial, $enviada)
    {
        $sql = "UPDATE materias_primas SET alerta_enviada = :valor WHERE id_material = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':valor' => $enviada ? 1 : 0,
            ':id'    => $idMaterial,
        ]);
    }

    private function enviarCorreoAlerta($correoDestino, $nombreMaterial, $stockActual, $stockMinimo)
    {
        if (
            !class_exists(PHPMailer::class) ||
            !function_exists('prepararCorreo') ||
            !defined('HOST') ||
            !defined('USERNAME') ||
            !defined('PASSWORD') ||
            USERNAME === '' ||
            PASSWORD === ''
        ) {
            error_log('Alerta de stock: falta PHPMailer, plantilla o credenciales SMTP.');
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->SMTPDebug  = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = USERNAME;
            $mail->Password   = PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;
            $mail->Timeout    = 8;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(USERNAME, 'Colsoftco - Alertas de Stock');
            $mail->addAddress($correoDestino);

            prepararCorreo($mail, "Stock mínimo alcanzado: {$nombreMaterial}", '',
                'Las existencias de esta materia prima llegaron al mínimo configurado o están por debajo de él.',
                ['Materia prima' => $nombreMaterial, 'Stock actual' => $stockActual, 'Stock mínimo' => $stockMinimo],
                'Revisa las existencias y coordina la reposición con el proveedor correspondiente.');

            $mail->send();
            return true;
        } catch (\Throwable $e) {
            error_log('Error enviando alerta de stock: ' . $e->getMessage());
            return false;
        }
    }
}