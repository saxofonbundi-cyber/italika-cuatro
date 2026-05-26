<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// 1. CONTROL DE ACCESO VISUAL: Filtro estricto con tus credenciales institucionales
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_form'])) {
    $correo_ingresado = trim($_POST['usuario']);
    $clave_ingresada  = trim($_POST['password']);

    // Validación local con tus datos de la escuela (separa el acceso web de la BD)
    if ($correo_ingresado === "24160684@itoaxaca.edu.mx" && $clave_ingresada === "24160684") {
        $_SESSION['usuario'] = $correo_ingresado;
    } else {
        header("Location: login.php?error=credenciales");
        exit();
    }
}

// Bloqueo de seguridad: Si no hay sesión activa, nadie pasa al panel
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// =========================================================================
// 2. CONFIGURACIÓN DE TU BASE DE DATOS (CON CONTRASEÑA PARA DEV_USER)
// =========================================================================
$host = "localhost";
$user = "dev_user"; 
$pass = "Devuser*2026"; // <-- CAMBIA ESTO por la contraseña real de tu dev_user
$db   = "italikacuatro";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("<div style='background:#1a1a1a; color:#ff5252; padding:20px; font-family:sans-serif; border-radius:8px; margin:20px;'>
            <strong>Error de conexión a MySQL:</strong> " . $conn->connect_error . " <br><br>
            <small>👉 Edita la línea de conexión en <code>admin.php</code> e introduce la contraseña correcta de tu usuario <strong>dev_user</strong>.</small>
         </div>");
}

$mensaje = "";
$tipo_alerta = "";

// =========================================================================
// 3. PROCESAMIENTO CRUD EN LA TABLA `refacciones` (USANDO ID)
// =========================================================================

// ACCIÓN: AGREGAR NUEVA REFACCIÓN
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $stock  = intval($_POST['stock']);

    // Usando la estructura correcta de tu tabla sin la columna codigo
    $stmt = $conn->prepare("INSERT INTO refacciones (nombre, precio, stock) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $nombre, $precio, $stock);
    
    if ($stmt->execute()) {
        $mensaje = "Refacción añadida al inventario Italika con éxito.";
        $tipo_alerta = "success";
    } else {
        $mensaje = "Error al insertar en la tabla `refacciones`: " . $stmt->error;
        $tipo_alerta = "danger";
    }
    $stmt->close();
}

// ACCIÓN: ELIMINAR REFACCIÓN
if (isset($_GET['eliminar'])) {
    $id_eliminar = intval($_GET['eliminar']);
    
    $stmt = $conn->prepare("DELETE FROM refacciones WHERE id = ?");
    $stmt->bind_param("i", $id_eliminar);
    
    if ($stmt->execute()) {
        $mensaje = "Componente removido del sistema correctamente.";
        $tipo_alerta = "success";
    } else {
        $mensaje = "Error al eliminar: " . $stmt->error;
        $tipo_alerta = "danger";
    }
    $stmt->close();
}

// ACCIÓN: EDITAR REFACCIÓN (GUARDAR CAMBIOS)
if (isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id     = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $precio = floatval($_POST['precio']);
    $stock  = intval($_POST['stock']);

    // Removido 'codigo' del UPDATE para usar el id
    $stmt = $conn->prepare("UPDATE refacciones SET nombre=?, precio=?, stock=? WHERE id=?");
    $stmt->bind_param("sdii", $nombre, $precio, $stock, $id);
    
    if ($stmt->execute()) {
        $mensaje = "Datos de la refacción actualizados en el servidor.";
        $tipo_alerta = "success";
    } else {
        $mensaje = "Error al actualizar: " . $stmt->error;
        $tipo_alerta = "danger";
    }
    $stmt->close();
}

// Consulta de lectura directa a la tabla `refacciones`
$resultado = $conn->query("SELECT * FROM refacciones ORDER BY id DESC");

// Precargar datos en el formulario si se va a editar una pieza
$refaccion_editar = null;
if (isset($_GET['editar'])) {
    $id_editar = intval($_GET['editar']);
    $res_edit = $conn->query("SELECT * FROM refacciones WHERE id = $id_editar");
    if ($res_edit->num_rows > 0) {
        $refaccion_editar = $res_edit->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Italika - Panel de Administración</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --italika-red: #e30613;
            --italika-red-hover: #ff121f;
            --dark-bg: #0b0b0b;
            --panel-bg: #121212;
            --card-bg: #1a1a1a;
            --border-color: #262626;
            --text-light: #ffffff;
            --text-muted: #aaaaaa;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--dark-bg); color: var(--text-light); display: flex; min-height: 100vh; overflow-x: hidden; }

        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* SIDEBAR ESTÉTICO */
        .sidebar { width: 280px; background-color: #060606; border-right: 1px solid var(--border-color); display: flex; flex-direction: column; padding: 30px 20px; position: fixed; height: 100vh; }
        .sidebar .brand { font-size: 26px; font-weight: 900; font-style: italic; letter-spacing: 1px; margin-bottom: 50px; padding-left: 10px; }
        .sidebar .brand span { color: var(--italika-red); }
        
        .sidebar-menu { list-style: none; display: flex; flex-direction: column; gap: 15px; }
        .sidebar-menu a { display: flex; align-items: center; gap: 15px; color: var(--text-muted); text-decoration: none; padding: 14px 18px; border-radius: 10px; font-weight: 700; font-size: 14px; transition: all 0.2s ease; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(227, 6, 19, 0.1); color: var(--text-light); border-left: 4px solid var(--italika-red); }
        .sidebar-menu a.logout { margin-top: auto; color: #ff5252; }
        .sidebar-menu a.logout:hover { background: rgba(255, 82, 82, 0.1); }

        /* CONTENIDO PRINCIPAL */
        .main-content { margin-left: 280px; flex-grow: 1; padding: 40px; min-width: 0; animation: fadeIn 0.5s ease; }
        
        .header-dashboard { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 1px solid var(--border-color); padding-bottom: 20px; }
        .header-dashboard h2 { font-size: 28px; font-weight: 800; text-transform: uppercase; }
        .header-dashboard h2 span { color: var(--italika-red); }
        .user-pill { background: var(--card-bg); padding: 10px 20px; border-radius: 30px; font-size: 13px; font-weight: 700; border: 1px solid var(--border-color); color: var(--text-muted); display: flex; align-items: center; gap: 10px; }
        .user-pill i { color: var(--italika-red); }

        .alert-toast { background: rgba(46, 204, 113, 0.1); border: 1px solid #2ecc71; color: #2ecc71; padding: 15px 20px; border-radius: 10px; margin-bottom: 30px; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 12px; animation: slideIn 0.3s ease; }
        .alert-toast.danger { background: rgba(231, 76, 60, 0.1); border: 1px solid #e74c3c; color: #e74c3c; }

        .dashboard-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 35px; align-items: start; }
        
        .panel-card { background-color: var(--panel-bg); border: 1px solid var(--border-color); border-radius: 18px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .panel-card h3 { font-size: 18px; font-weight: 800; text-transform: uppercase; margin-bottom: 25px; border-left: 4px solid var(--italika-red); padding-left: 12px; }

        /* FORMULARIOS */
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: var(--text-muted); }
        .form-group input { width: 100%; padding: 14px 16px; background: #1a1a1a; border: 1px solid var(--border-color); border-radius: 10px; color: white; font-size: 14px; font-weight: 600; outline: none; transition: all 0.2s ease; }
        .form-group input:focus { border-color: var(--italika-red); background: #222; box-shadow: 0 0 15px rgba(227, 6, 19, 0.2); }
        
        .btn-submit { width: 100%; background: var(--italika-red); color: white; border: none; padding: 14px; border-radius: 10px; font-weight: 800; text-transform: uppercase; font-size: 13px; cursor: pointer; transition: all 0.2s ease; display: flex; justify-content: center; align-items: center; gap: 10px; }
        .btn-submit:hover { background: var(--italika-red-hover); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(227, 6, 19, 0.4); }
        .btn-cancelar { display: block; text-align: center; text-decoration: none; color: var(--text-muted); font-size: 12px; font-weight: 700; margin-top: 15px; text-transform: uppercase; }

        /* TABLA */
        .table-responsive { width: 100%; overflow-x: auto; }
        .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; margin-top: -8px; }
        .custom-table th { background: #060606; color: var(--text-muted); font-weight: 700; text-transform: uppercase; font-size: 11px; padding: 16px 20px; text-align: left; }
        .custom-table td { background: var(--card-bg); padding: 16px 20px; font-size: 14px; font-weight: 600; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); transition: all 0.2s ease; }
        
        .custom-table tr td:first-child { border-left: 1px solid var(--border-color); border-top-left-radius: 10px; border-bottom-left-radius: 10px; }
        .custom-table tr td:last-child { border-right: 1px solid var(--border-color); border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
        .custom-table tr:hover td { background: #222222; border-color: #333; }

        .badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .badge.instock { background: rgba(46, 204, 113, 0.15); color: #2ecc71; }
        .badge.outstock { background: rgba(231, 76, 60, 0.15); color: #e74c3c; }

        .actions-cell { display: flex; gap: 10px; }
        .btn-action { width: 34px; height: 34px; display: flex; justify-content: center; align-items: center; border-radius: 8px; text-decoration: none; font-size: 15px; border: 1px solid var(--border-color); transition: all 0.2s ease; }
        .btn-action.edit { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .btn-action.edit:hover { background: #3498db; color: white; transform: scale(1.1); }
        .btn-action.delete { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        .btn-action.delete:hover { background: #e74c3c; color: white; transform: scale(1.1); }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand">ITALIKA<span>.</span></div>
        <ul class="sidebar-menu">
            <li><a href="admin.php" class="active"><i class="bi bi-box-seam-fill"></i> Inventario</a></li>
            <li style="margin-top: auto;"><a href="logout.php" class="logout"><i class="bi bi-box-arrow-left"></i> Cerrar Sesión</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header-dashboard">
            <h2>Control de <span>Refacciones</span></h2>
            <div class="user-pill">
                <i class="bi bi-person-circle"></i>
                <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            </div>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div class="alert-toast <?php echo $tipo_alerta == 'danger' ? 'danger' : ''; ?>">
                <i class="bi <?php echo $tipo_alerta == 'danger' ? 'bi-x-circle-fill' : 'bi-check-circle-fill'; ?>"></i>
                <span><?php echo $mensaje; ?></span>
            </div>
        <?php endif; ?>

        <div class="dashboard-grid">
            
            <div class="panel-card">
                <?php if ($refaccion_editar): ?>
                    <h3>Modificar Registro</h3>
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="accion" value="editar">
                        <input type="hidden" name="id" value="<?php echo $refaccion_editar['id']; ?>">
                        
                        <div class="form-group">
                            <label>Nombre del Componente</label>
                            <input type="text" name="nombre" value="<?php echo htmlspecialchars($refaccion_editar['nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Precio Unitario ($)</label>
                            <input type="number" step="0.01" name="precio" value="<?php echo $refaccion_editar['precio']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Existencia (Stock)</label>
                            <input type="number" name="stock" value="<?php echo $refaccion_editar['stock']; ?>" required>
                        </div>
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-pencil-square"></i> Guardar Cambios
                        </button>
                        <a href="admin.php" class="btn-cancelar">Cancelar Edición</a>
                    </form>
                <?php else: ?>
                    <h3>Agregar Refacción</h3>
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="accion" value="agregar">
                        
                        <div class="form-group">
                            <label>Nombre del Componente</label>
                            <input type="text" name="nombre" placeholder="Ej. Filtro de Gasolina" required autocomplete="off">
                        </div>
                        <div class="form-group">
                            <label>Precio Unitario ($)</label>
                            <input type="number" step="0.01" name="precio" placeholder="0.00" required>
                        </div>
                        <div class="form-group">
                            <label>Existencia (Stock)</label>
                            <input type="number" name="stock" placeholder="0" required>
                        </div>
                        <button type="submit" class="btn-submit">
                            <i class="bi bi-plus-circle-fill"></i> Guardar en Tabla
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="panel-card">
                <h3>Inventario de Refacciones</h3>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Código (ID)</th>
                                <th>Precio</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado && $resultado->num_rows > 0): ?>
                                <?php while($fila = $resultado->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                                        <td style="color: var(--text-muted); font-family: monospace; font-size: 13px;">#<?php echo htmlspecialchars($fila['id']); ?></td>
                                        <td style="color: white; font-weight: 700;">$<?php echo number_format($fila['precio'], 2); ?></td>
                                        <td>
                                            <?php if($fila['stock'] > 0): ?>
                                                <span class="badge instock"><?php echo $fila['stock']; ?> piezas</span>
                                            <?php else: ?>
                                                <span class="badge outstock">Agotado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="actions-cell">
                                            <a href="admin.php?editar=<?php echo $fila['id']; ?>" class="btn-action edit" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                            <a href="admin.php?eliminar=<?php echo $fila['id']; ?>" class="btn-action delete" title="Eliminar" onclick="return confirm('¿Eliminar pieza de la tabla refacciones?');"><i class="bi bi-trash3-fill"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                                        <i class="bi bi-layers-half" style="font-size: 24px; display:block; margin-bottom:10px;"></i>
                                        Ninguna refacción agregada en este momento.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>