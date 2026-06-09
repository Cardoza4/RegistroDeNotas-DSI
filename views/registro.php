<?php include 'views/header.php'; ?>

<div class="form-auth">
    <h2>Registro de Nuevo Usuario</h2>
    <p style="color: #64748b; margin-bottom: 20px;">Crea una cuenta para acceder.</p>
    
    <form action="index.php?action=registro" method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" placeholder="Ej: Oswaldo" required>
        
        <label>Apellido:</label>
        <input type="text" name="apellido" placeholder="Ej: Cardoza" required>
        
        <label>Usuario (Estudiantes usar NIE):</label>
        <input type="text" name="username" placeholder="Ej: cd15015 o 12345" required>
        
        <label>Contraseña:</label>
        <input type="password" name="password" placeholder="Crea una contraseña segura" required>
        
        <label>Rol de Cuenta:</label>
        <select name="rol" required>
            <option value="estudiante">Estudiante</option>
            <option value="docente">Docente</option>
            <option value="administrador">Administrador</option>
        </select>
        
        <button type="submit" style="width: 100%; margin-top: 10px;">Registrar Cuenta</button>
    </form>
    
    <p style="text-align: center; font-size: 14px; margin-top: 20px;">
        <a href="index.php?action=login" style="color: #64748b; font-weight: 600;">← Volver al Login</a>
    </p>
</div>

<?php include 'views/footer.php'; ?>