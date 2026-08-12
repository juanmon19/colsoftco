<?php

require_once "../../app/verificar_sesion.php";
require_once '../../app/logica_proveedores.php';

$logica = new ProveedorLogica();
$proveedores = $logica->getProveedores();

?>

<link rel="stylesheet" href="lista_proveedores.css">

<div class="lista-proveedores">

    <div class="controls-container">

        <div class="tabs">
            <button class="tab active" type="button">
                Todos
            </button>
        </div>

        <div class="actions">

            <button
                class="btn-action"
                type="button"
                onclick="cargarModulo('../lista_proveedores/registro_proveedores.php')">
                Registrar Proveedor
            </button>

            <button
                class="btn-action"
                type="button"
                onclick="cargarModulo('../historial_movimientos/historial.php')">
                Historial de movimientos
            </button>

        </div>

    </div>


    <div class="provider-list">

        <?php if (count($proveedores) > 0): ?>

            <?php foreach ($proveedores as $proveedor): ?>

                <div class="provider-card">

                    <div class="provider-logo">

                        <div style="
                            font-weight: bold;
                            font-size: 24px;
                            color: #2E8B57;
                            text-align: center;
                        ">
                            <?php
                            echo strtoupper(
                                substr(
                                    $proveedor['nombre_empresa'],
                                    0,
                                    3
                                )
                            );
                            ?>
                        </div>

                    </div>


                    <div class="provider-info">

                        <p>
                            <strong>Proveedor:</strong>
                            <?php
                            echo htmlspecialchars(
                                $proveedor['nombre_empresa']
                            );
                            ?>
                        </p>


                        <?php if (isset($proveedor['nit'])): ?>

                            <p>
                                <strong>NIT:</strong>
                                <?php
                                echo htmlspecialchars(
                                    $proveedor['nit']
                                );
                                ?>
                            </p>

                        <?php endif; ?>


                        <?php if (isset($proveedor['direccion'])): ?>

                            <p>
                                <strong>Dirección:</strong>
                                <?php
                                echo htmlspecialchars(
                                    $proveedor['direccion']
                                );
                                ?>
                            </p>

                        <?php endif; ?>


                        <?php if (isset($proveedor['descripcion_empresa'])): ?>

                            <p>
                                <strong>Descripción:</strong>
                                <?php
                                echo htmlspecialchars(
                                    $proveedor['descripcion_empresa']
                                );
                                ?>
                            </p>

                        <?php endif; ?>


                        <p>
                            <strong>Contacto:</strong>

                            <?php
                            echo htmlspecialchars(
                                $proveedor['contacto_nombre'] . ' ' .
                                $proveedor['contacto_apellido']
                            );
                            ?>
                        </p>


                        <p>
                            <strong>Correo:</strong>

                            <?php
                            echo htmlspecialchars(
                                $proveedor['email']
                            );
                            ?>
                        </p>


                        <p>
                            <strong>Teléfono:</strong>

                            <?php
                            echo htmlspecialchars(
                                $proveedor['telefono']
                            );
                            ?>
                        </p>

                    </div>


                    <div class="provider-buttons">

                        <div class="btn-group-top">

                            <button
                                class="btn-card"
                                type="button"
                                onclick="editarProveedor(<?php echo $proveedor['id_proveedor']; ?>)">
                                Editar
                            </button>


                            <button
                                class="btn-card"
                                type="button"
                                onclick="eliminarProveedor(<?php echo $proveedor['id_proveedor']; ?>)">
                                Eliminar
                            </button>

                        </div>


                        <button
                            class="btn-card btn-card-large"
                            type="button">
                            Hacer Pedido
                        </button>


                        <button
                            class="btn-card btn-card-large"
                            type="button">
                            Contactar
                        </button>

                    </div>

                </div>

            <?php endforeach; ?>


        <?php else: ?>

            <div class="provider-card">

                <div class="provider-info">

                    <p>
                        No existen proveedores registrados.
                    </p>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>


<script>

function editarProveedor(id) {

    window.location.href =
        "../lista_proveedores/editar_proveedor.php?id=" + id;
}


function eliminarProveedor(id) {

    window.location.href =
        "../lista_proveedores/eliminar_proveedor.php?id=" + id;
}


document.querySelectorAll('.tab').forEach(tab => {

    tab.addEventListener('click', function() {

        document.querySelectorAll('.tab')
            .forEach(t => t.classList.remove('active'));

        this.classList.add('active');

    });

});

</script>