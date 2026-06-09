<?php include 'views/header.php'; ?>

<div class="form-auth">
    <h2>Iniciar Sesión</h2>
    <p style="color: #64748b; margin-bottom: 20px;">Por favor, ingresa tus credenciales.</p>
    
    <form action="index.php?action=login" method="POST">
        <label>Usuario / NIE:</label>
        <input type="text" name="username" required>
        
        <label>Contraseña:</label>
        <input type="password" name="password" required>
        
        <button type="submit" style="width: 100%; margin-top: 10px;">Ingresar al Sistema</button>
    </form>
    
    <p style="text-align: center; font-size: 14px; margin-top: 20px;">
        ¿No tienes cuenta? <a href="index.php?action=registro" style="color: #2563eb; font-weight: 600;">Regístrate aquí</a>
    </p>
</div>

<?php include 'views/footer.php'; ?>