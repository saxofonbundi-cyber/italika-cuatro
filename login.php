<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Credenciales de control académica exigidas
    if ($usuario === '24160684' && $password === 'admin123') {
        $_SESSION['autenticado'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = "Credenciales incorrectas de acceso.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Italika Cuatro</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); width: 300px; }
        h2 { text-align: center; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; box-sizing: border-box; }
        input[type="submit"] { width: 100%; background: #e51a22; color: white; padding: 10px; border: none; cursor: pointer; font-size: 16px; }
        .error { color: red; font-size: 14px; text-align: center; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Iniciar Sesión</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Matrícula / Usuario:</label>
            <input type="text" name="usuario" required placeholder="Ej. 24160684">
            <label>Contraseña:</label>
            <input type="password" name="password" required>
            <input type="submit" value="Entrar al Dashboard">
        </form>
    </div>
</body>
</html>
