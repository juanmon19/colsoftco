<?php
// Plantilla única de errores. Las páginas error_*.php solo definen variables y la incluyen.
$codigo = $codigo ?? 404;
$titulo = $titulo ?? 'Página no encontrada (Not Found)';
$imagen = $imagen ?? '/colsoftco/public/imagenes/errores/404.jpeg';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= htmlspecialchars((string)$codigo) ?> - COLSOFTCO</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            width: 100%;
            height: auto;
            min-height: 100%;
            overflow-y: auto;
            background-color: #0b172d;
            font-family: Arial, sans-serif;
            color: white;
            text-align: center;
        }

        .error-contenedor {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 20px 10px 50px 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .error-imagen {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
        }

        .error-overlay {
            margin-top: 15px;
            width: 100%;
        }

        .error-overlay h2 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .error-overlay button {
            display: inline-block;
            padding: 12px 32px;
            background: #D4AF37;
            color: #0A1F44;
            border: none;
            font-weight: bold;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .error-overlay button:hover {
            background: #ffffff;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="error-contenedor">
        <img src="<?= htmlspecialchars($imagen) ?>" alt="Error <?= htmlspecialchars((string)$codigo) ?>" class="error-imagen">

        <div class="error-overlay">
            <h2><?= htmlspecialchars($titulo) ?></h2>
            <button onclick="volverAtras()">Volver</button>
        </div>
    </div>

    <script>
        function volverAtras() {
            if (document.referrer && window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/colsoftco/index.html';
            }
        }
    </script>
</body>
</html>
