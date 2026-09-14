<?php

require_once __DIR__ . '/logica_proveedores.php';

$logica = new ProveedorLogica();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    switch ($accion) {

        case 'registrar':

            $imagen = $_POST['imagen'] ?? '';

            $logica->registrarProveedor(
                $_POST['nombre_empresa'],
                $_POST['contacto_nombre'],
                $_POST['contacto_apellido'],
                $_POST['telefono'],
                $_POST['email'],
                $_POST['nit'],
                $_POST['direccion'],
                $_POST['descripcion_empresa'],
                $imagen
            );

            header("Location: ../view/lista_proveedores/lista_proveedores.php");
            exit;

        case 'editar':

            $imagen = $_POST['imagen'] ?? null;

            $logica->actualizarProveedor(
                (int) $_POST['id_proveedor'],
                $_POST['nombre_empresa'],
                $_POST['contacto_nombre'],
                $_POST['contacto_apellido'],
                $_POST['telefono'],
                $_POST['email'],
                $_POST['nit'],
                $_POST['direccion'],
                $_POST['descripcion_empresa'],
                $imagen
            );

            header("Location: ../view/lista_proveedores/lista_proveedores.php");
            exit;

        case 'eliminar':

            $logica->eliminarProveedor(
                (int) $_POST['id_proveedor']
            );

            header("Location: ../view/lista_proveedores/lista_proveedores.php");
            exit;
    }
}