<?php

require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/setting.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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

        if ($bajoMinimo && $material['alerta_enviada'] == 0) {

            $mensaje = "El material '{$material['nombre_material']}' alcanzó su stock mínimo "
                . "(actual: {$material['stock_actual']}, mínimo: {$material['stock_minimo']}).";

            // Registro en la bandeja de notificaciones interna (siempre)
            $this->registrarNotificacion($idMaterial, $mensaje);

            // Envío de correo, solo si el material lo tiene configurado
            if ($material['notificar_email'] == 1 && !empty($material['correo_notificacion'])) {
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
        if (!$bajoMinimo && $material['alerta_enviada'] == 1) {
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
        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = USERNAME;
            $mail->Password   = PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('colsoftco4@gmail.com', 'Colsoftco - Alertas de Stock');
            $mail->addAddress($correoDestino);

            $mail->isHTML(true);
            $mail->Subject = "⚠ Stock mínimo alcanzado: {$nombreMaterial}";
            $mail->Body    = "
                <p>El material <b>{$nombreMaterial}</b> alcanzó su nivel mínimo de stock.</p>
                <p><b>Stock actual:</b> {$stockActual}<br>
                <b>Stock mínimo configurado:</b> {$stockMinimo}</p>
                <p>Por favor gestiona un nuevo pedido con el proveedor correspondiente.</p>
            ";

            $mail->send();
            return true;

        } catch (Exception $e) {
            error_log("Error enviando alerta de stock: {$mail->ErrorInfo}");
            return false;
        }
    }
}
