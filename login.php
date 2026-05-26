<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$mensaje_error = "";

// Procesar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_ingresado = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $password_ingresada = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Credenciales fijas estrictas
    $usuario_correcto = "24160684@itoaxaca.edu.mx";
    $password_correcta = "24160684";

    if ($usuario_ingresado === $usuario_correcto && $password_ingresada === $password_correcta) {
        $_SESSION['usuario'] = $usuario_ingresado;
        header("Location: admin.php");
        exit();
    } else {
        $mensaje_error = "El usuario o la contraseña son incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Italika - Iniciar Sesión</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --italika-red: #e30613;
            --italika-red-glow: rgba(227, 6, 19, 0.3);
            --dark-bg: #0b0b0b;
            --panel-bg: rgba(20, 20, 20, 0.8);
            --text-light: #ffffff;
            --text-muted: #aaaaaa;
            --border-color: #262626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-image: linear-gradient(135deg, rgba(11,11,11,0.7) 0%, rgba(11,11,11,0.95) 100%), 
                              url('https://images.unsplash.com/photo-1609630875171-b1321377ee65?q=80&w=1920&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* --- KEYFRAMES PARA ANIMACIONES --- */
        @keyframes containerReveal {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes borderPulse {
            0% { border-color: var(--border-color); }
            50% { border-color: rgba(227, 6, 19, 0.5); box-shadow: 0 0 15px rgba(227, 6, 19, 0.2); }
            100% { border-color: var(--border-color); }
        }

        /* --- CONTENEDOR DE LOGIN GLASSMORPHISM --- */
        .login-container {
            background: var(--panel-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 50px 45px;
            border-radius: 24px;
            width: 100%;
            max-width: 460px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.7);
            animation: containerReveal 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
        }

        .login-brand {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-brand h1 {
            font-size: 36px;
            font-weight: 900;
            letter-spacing: 1.5px;
            font-style: italic;
        }

        .login-brand h1 span {
            color: var(--italika-red);
            text-shadow: 0 0 15px rgba(227, 6, 19, 0.6);
        }

        .login-brand p {
            color: var(--text-muted);
            font-size: 12px;
            margin-top: 6px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- INPUTS CORPORATIVOS ANIMADOS --- */
        .form-group {
            margin-bottom: 28px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: var(--text-muted);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 18px;
            color: var(--text-muted);
            font-size: 18px;
            transition: color 0.3s ease;
        }

        .form-group input {
            width: 100%;
            padding: 16px 16px 16px 52px;
            background: rgba(20, 20, 20, 0.6);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            outline: none;
            font-size: 15px;
            color: white;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* Efecto de foco dinámico con brillo rojo */
        .form-group input:focus {
            border-color: var(--italika-red);
            background: rgba(30, 30, 30, 0.8);
            box-shadow: 0 0 20px var(--italika-red-glow);
        }

        .form-group input:focus + i {
            color: var(--italika-red);
        }

        /* --- BOTÓN DE ENTRADA CON REVOLUCIONES --- */
        .btn-login {
            background-color: var(--italika-red);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 800;
            font-size: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 100%;
            box-shadow: 0 4px 20px var(--italika-red-glow);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 15px;
        }

        .btn-login:hover {
            background-color: #ff121f;
            transform: scale(1.02);
            box-shadow: 0 6px 25px rgba(227, 6, 19, 0.6);
        }

        .btn-login:active {
            transform: scale(0.99);
        }

        /* --- CUADRO DE ERROR DINÁMICO --- */
        .error-box {
            background: rgba(227, 6, 19, 0.1);
            border: 1px solid var(--italika-red);
            color: #ff737b;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 0 15px rgba(227, 6, 19, 0.15);
        }

        /* Enlace para regresar al inicio */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: color 0.2s ease;
        }

        .back-link:hover {
            color: var(--text-light);
        }
    </style>
</head>
<body>

    <div class="login-container">
        <div class="login-brand">
            <h1>ITALIKA<span>.</span></h1>
            <p>Portal Admin Daniel</p>
        </div>

        <?php if(!empty($mensaje_error)): ?>
            <div class="error-box">
                <i class="bi bi-exclamation-octagon-fill"></i>
                <span><?php echo $mensaje_error; ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Usuario Institucional</label>
                <div class="input-wrapper">
                    <i class="bi bi-person-fill"></i>
                    <input type="text" name="usuario" placeholder="ejemplo@itoaxaca.edu.mx" required autocomplete="off">
                </div>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <div class="input-wrapper">
                    <i class="bi bi-shield-lock-fill"></i>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right"></i> Encender Sistema
            </button>
        </form>

        <a href="index.html" class="back-link"><i class="bi bi-arrow-left"></i> Volver al Inicio</a>
    </div>

</body>
</html>