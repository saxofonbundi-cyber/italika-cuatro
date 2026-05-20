<?php
session_start();
if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== true) {
    header('Location: login.php');
    exit;
}

$host = 'localhost';
$db = 'italikacuatro';
$user = 'dev_user';
$pass = 'DesarrolloItalika2026!';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (\PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = '';

// ALTA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre = trim($_POST['nombre']);
    $precio = trim($_POST['precio']);
    $stock  = trim($_POST['stock']);
    $stmt = $pdo->prepare("INSERT INTO refacciones (nombre, precio, stock) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $precio, $stock]);
    $mensaje = "✅ Refacción agregada correctamente.";
}

// BAJA
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM refacciones WHERE id = ?");
    $stmt->execute([$id]);
    $mensaje = "🗑️ Refacción eliminada.";
}

// MODIFICACIÓN - cargar datos
$editando = null;
if (isset($_GET['editar'])) {
    $id = (int)$_GET['editar'];
    $stmt = $pdo->prepare("SELECT * FROM refacciones WHERE id = ?");
    $stmt->execute([$id]);
    $editando = $stmt->fetch(PDO::FETCH_ASSOC);
}

// MODIFICACIÓN - guardar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id     = (int)$_POST['id'];
    $nombre = trim($_POST['nombre']);
    $precio = trim($_POST['precio']);
    $stock  = trim($_POST['stock']);
    $stmt = $pdo->prepare("UPDATE refacciones SET nombre=?, precio=?, stock=? WHERE id=?");
    $stmt->execute([$nombre, $precio, $stock, $id]);
    $mensaje = "✏️ Refacción actualizada correctamente.";
    $editando = null;
}

// CONSULTA
$stmt = $pdo->query("SELECT id, nombre, precio, stock FROM refacciones ORDER BY id");
$refacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Dashboard - Italika Cuatro</title>
<style>
* { box-sizing: border-box; }
body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
.header { background: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; border-radius: 5px; }
.header h1 { margin: 0; font-size: 18px; }
.btn-logout { color: #fff; background: #e51a22; padding: 8px 15px; text-decoration: none; border-radius: 3px; }
.container { background: white; padding: 20px; margin-top: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.05); }
h2 { color: #333; }
.mensaje { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
form.inline { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
form.inline input[type="text"],
form.inline input[type="number"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; width: 200px; }
form.inline button { background: #e51a22; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
form.inline button.verde { background: #28a745; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
th { background: #f2f2f2; }
tr:hover { background: #f9f9f9; }
.btn-edit { background: #ffc107; color: #333; padding: 4px 10px; border-radius: 3px; text-decoration: none; font-size: 13px; }
.btn-del  { background: #e51a22; color: white; padding: 4px 10px; border-radius: 3px; text-decoration: none; font-size: 13px; }
</style>
</head>
<body>

<div class="header">
    <h1>Panel de Administración - Italika Cuatro</h1>
    <a href="logout.php" class="btn-logout">Cerrar Sesión</a>
</div>

<div class="container">

    <?php if ($mensaje): ?>
        <div class="mensaje"><?= $mensaje ?></div>
    <?php endif; ?>

    <?php if ($editando): ?>
        <h2>✏️ Editar Refacción</h2>
        <form method="POST" class="inline">
            <input type="hidden" name="accion" value="editar">
            <input type="hidden" name="id" value="<?= $editando['id'] ?>">
            <input type="text"   name="nombre" value="<?= htmlspecialchars($editando['nombre']) ?>" placeholder="Nombre" required>
            <input type="number" name="precio" value="<?= $editando['precio'] ?>" placeholder="Precio" step="0.01" required>
            <input type="number" name="stock"  value="<?= $editando['stock'] ?>"  placeholder="Stock" required>
            <button type="submit" class="verde">Guardar Cambios</button>
            <a href="admin.php" style="padding:8px 16px;background:#999;color:white;border-radius:4px;text-decoration:none;">Cancelar</a>
        </form>
    <?php else: ?>
        <h2>➕ Agregar Nueva Refacción</h2>
        <form method="POST" class="inline">
            <input type="hidden" name="accion" value="agregar">
            <input type="text"   name="nombre" placeholder="Nombre de la refacción" required>
            <input type="number" name="precio" placeholder="Precio" step="0.01" required>
            <input type="number" name="stock"  placeholder="Stock" required>
            <button type="submit">Agregar</button>
        </form>
    <?php endif; ?>

    <h2>📋 Inventario de Refacciones (<?= count($refacciones) ?> registros)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($refacciones as $row): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['nombre']) ?></td>
                <td>$<?= number_format($row['precio'], 2) ?></td>
                <td><?= $row['stock'] ?> u.</td>
                <td>
                    <a href="admin.php?editar=<?= $row['id'] ?>" class="btn-edit">Editar</a>
                    <a href="admin.php?eliminar=<?= $row['id'] ?>" class="btn-del"
                       onclick="return confirm('¿Eliminar esta refacción?')">Eliminar</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
