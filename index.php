<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cencogastos - Ingresar</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="auth-container">
        <div class="panel auth-box">
            <h1>Cencogastos</h1>
            
            <div id="login-form-container">
                <form id="login-form">
                    <div class="form-group">
                        <label>Nombre de Usuario</label>
                        <input type="text" id="login-usuario" class="form-control" placeholder="Ej: compadre_juan" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña secreta</label>
                        <input type="password" id="login-clave" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Entrar al tiro</button>
                </form>
                <div class="toggle-auth">
                    ¿No tienes cuenta? <a onclick="toggleAuth()">Regístrate acá</a>
                </div>
            </div>

            <div id="register-form-container" style="display: none;">
                <form id="register-form">
                    <div class="form-group">
                        <label>Elige tu Usuario</label>
                        <input type="text" id="reg-usuario" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Crea tu Contraseña</label>
                        <input type="password" id="reg-clave" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Crear Cuenta</button>
                </form>
                <div class="toggle-auth">
                    ¿Ya estai registrado? <a onclick="toggleAuth()">Inicia Sesión</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="app.js"></script>
</body>
</html>
