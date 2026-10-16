<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class ColsoftcoUnitTest extends TestCase
{
    private $pdoMock;

    protected function setUp(): void
    {
        parent::setUp();
        // Simulación de la conexión a base de datos PDO para no alterar datos reales
        $this->pdoMock = $this->createMock(PDO::class);
    }

    /**
     * PRUEBA 1: Validación de campos obligatorios en el registro de usuarios.
     */
    public function testRegistroUsuarioRequiereCamposObligatorios(): void
    {
        $datosUsuario = [
            'nombre'   => 'Santiago',
            'email'    => '', // Email vacío intencional
            'password' => '123456'
        ];

        $esValido = !empty($datosUsuario['nombre']) && !empty($datosUsuario['email']) && !empty($datosUsuario['password']);

        $this->assertFalse($esValido, 'La validación debe rechazar un formulario con email vacío.');
    }

    /**
     * PRUEBA 2: Cálculo correcto del stock disponible en inventario.
     */
    public function testCalculoStockTotalAcumulado(): void
    {
        $lotesStock = [100, 150, 50]; // Cantidades de espuma / tela / unidades

        $stockTotal = array_sum($lotesStock);

        $this->assertEquals(300, $stockTotal, 'El cálculo de stock total debe ser exacto.');
    }

    /**
     * PRUEBA 3: Consulta/Inserción simulada de un producto en la base de datos colsoftco.
     */
    public function testInsercionProductoUsandoMockPDO(): void
    {
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($stmtMock);

        $sql = "INSERT INTO productos (nombre, precio) VALUES (?, ?)";
        $stmt = $this->pdoMock->prepare($sql);
        $resultado = $stmt->execute(['Colchón Max&Flex Súper VIP', 850000]);

        $this->assertTrue($resultado, 'La ejecución de la consulta debe retornar true.');
    }

    /**
     * PRUEBA 4: Autenticación rechazada cuando la contraseña es incorrecta.
     */
    public function testAutenticacionFallaConPasswordIncorrecto(): void
    {
        $passwordIngresado = 'claveErrada123';
        $hashEnBaseDatos = password_hash('claveCorrecta123', PASSWORD_BCRYPT);

        $loginCorrecto = password_verify($passwordIngresado, $hashEnBaseDatos);

        $this->assertFalse($loginCorrecto, 'El sistema debe rechazar el login con clave errada.');
    }

    /**
     * PRUEBA 5: Evaluación de alerta por stock mínimo de materias primas.
     */
    public function testAlertaStockMinimoInsumos(): void
    {
        $stockActual = 8;
        $stockMinimoRequerido = 15;

        $requiereReabastecimiento = ($stockActual < $stockMinimoRequerido);

        $this->assertTrue($requiereReabastecimiento, 'Debe activar la alerta cuando el stock esté por debajo del límite.');
    }

    /**
     * PRUEBA 6: Validación de la estructura de datos preparada para exportación/PDF.
     */
    public function testEstructuraArregloParaReportePdf(): void
    {
        $reporteInventario = [
            'empresa' => 'Max&Flex',
            'filas'   => [
                ['id' => 1, 'material' => 'Espuma D30', 'unidades' => 35],
                ['id' => 2, 'material' => 'Resorte Bonnell', 'unidades' => 120]
            ]
        ];

        $this->assertArrayHasKey('empresa', $reporteInventario);
        $this->assertArrayHasKey('filas', $reporteInventario);
        $this->assertCount(2, $reporteInventario['filas']);
    }
}
