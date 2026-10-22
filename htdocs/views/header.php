<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INCA Notes - Sistema de Notas</title>
    <link rel="stylesheet" href="views/estilos.css">
    <style>
        .main-header {
            background-color: #ffffff;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            border-radius: 6px;
        }
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
        }
        .logo-img {
            max-height: 50px;
            width: auto;
        }
        .system-name {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    
    <header class="main-header no-print">
        <div class="logo-container">
            <img src="/registro_academico/img/logo_inca.png" alt="Logo INCA Notes" class="logo-img">
            <span class="system-name">INCA NOTES</span>
        </div>
        
        <?php if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['nombre_completo'])): ?>
            <div style="font-size: 14px; color: #64748b; text-align: right;">
                Usuario activo:<br>
                <strong style="color: #0f172a;"><?php echo $_SESSION['nombre_completo']; ?></strong>
            </div>
        <?php endif; ?>
    </header>

    <main class="container">