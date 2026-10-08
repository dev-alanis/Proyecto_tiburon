<?php
declare(strict_types=1);

/**
 * ============================================================
 *  INIT — Primer usuario administrador
 * ============================================================
 *  URL:  /public/init.php
 *  Solo funciona con APP_ENV=development.
 *  Se bloquea automáticamente si ya existe al menos un usuario.
 *
 *  Uso:
 *    1. Abrir en el navegador.
 *    2. Rellenar el formulario.
 *    3. Se crea el empleado + usuario ADMIN en una transacción.
 *    4. Cambiar APP_ENV=production y eliminar este archivo antes de desplegar.
 * ============================================================
 */

use App\Config\Config;
use App\Database\Database;
use App\Security\Roles;
use App\Security\Security;

require __DIR__ . '/../vendor/autoload.php';

Config::load(__DIR__ . '/..');

// --- Guardia de entorno ---
if (Config::get('app_env') !== 'development') {
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Not Found';
    exit;
}

$db = Database::connection();

$mensaje = null;
$error   = null;

$stmt = $db->query('SELECT COUNT(*) FROM usuario');
$existeUsuario = (int) $stmt->fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existeUsuario) {
    $nombre        = trim((string) ($_POST['nombre'] ?? ''));
    $ap            = trim((string) ($_POST['apellido_paterno'] ?? ''));
    $am            = trim((string) ($_POST['apellido_materno'] ?? ''));
    $correo        = trim((string) ($_POST['correo'] ?? ''));
    $puesto        = trim((string) ($_POST['puesto'] ?? 'Administrador'));
    $nombreUsuario = trim((string) ($_POST['nombre_usuario'] ?? ''));
    $password      = (string) ($_POST['password'] ?? '');
    $password2     = (string) ($_POST['password_confirm'] ?? '');

    if ($nombre === '' || $ap === '' || $nombreUsuario === '' || $password === '') {
        $error = 'Faltan campos obligatorios.';
    } elseif (mb_strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        try {
            $db->beginTransaction();

            $stmt = $db->prepare('
                INSERT INTO empleado
                    (nombre, apellido_paterno, apellido_materno, correo, puesto, fecha_ingreso, activo)
                VALUES
                    (:n, :ap, :am, :correo, :puesto, CURDATE(), 1)
            ');
            $stmt->execute([
                ':n'      => $nombre,
                ':ap'     => $ap,
                ':am'     => $am !== '' ? $am : null,
                ':correo' => $correo !== '' ? $correo : null,
                ':puesto' => $puesto,
            ]);
            $idEmpleado = (int) $db->lastInsertId();

            $stmt = $db->prepare('
                INSERT INTO usuario
                    (id_empleado, nombre_usuario, password_hash, rol, activo)
                VALUES
                    (:id_empleado, :nu, :ph, :rol, 1)
            ');
            $stmt->execute([
                ':id_empleado' => $idEmpleado,
                ':nu'          => $nombreUsuario,
                ':ph'          => Security::hashPassword($password),
                ':rol'         => Roles::ADMIN,
            ]);
            $idUsuario = (int) $db->lastInsertId();

            $db->commit();

            $mensaje = "Usuario administrador creado (id_usuario={$idUsuario}, id_empleado={$idEmpleado})."
                     . " Cambia APP_ENV a production y elimina este archivo.";
            $existeUsuario = true;
        } catch (\Throwable $e) {
            if ($db->inTransaction()) $db->rollBack();
            $error = 'Error al crear: ' . $e->getMessage();
        }
    }
}

$esc = static fn (?string $v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Init · Acuario Tiburón Feliz</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    :root { color-scheme: dark; }
    body {
        font-family: system-ui, -apple-system, Segoe UI, sans-serif;
        background: #0f172a; color: #e2e8f0;
        max-width: 560px; margin: 2rem auto; padding: 1.5rem;
    }
    h1 { color: #38bdf8; margin-top: 0; }
    .badge { background:#0284c7; color:#fff; padding:.15rem .5rem; border-radius:.5rem; font-size:.75rem; }
    .box { background:#1e293b; padding:1.25rem; border-radius:.75rem; margin-bottom:1rem; }
    label { display:block; margin:.6rem 0 .2rem; font-size:.85rem; color:#94a3b8; }
    input {
        width:100%; padding:.6rem; background:#0f172a; color:#e2e8f0;
        border:1px solid #334155; border-radius:.5rem; font-size:1rem;
    }
    input:focus { outline:2px solid #0284c7; border-color:transparent; }
    button {
        margin-top:1rem; padding:.7rem 1rem; background:#0284c7; color:#fff;
        border:none; border-radius:.5rem; font-weight:600; cursor:pointer; width:100%;
    }
    button:hover { background:#0369a1; }
    .ok  { background:#064e3b; color:#6ee7b7; padding:.75rem; border-radius:.5rem; margin-bottom:1rem; }
    .err { background:#7f1d1d; color:#fca5a5; padding:.75rem; border-radius:.5rem; margin-bottom:1rem; }
    code { background:#0f172a; padding:.1rem .3rem; border-radius:.25rem; font-size:.85em; }
</style>
</head>
<body>
    <h1>🐟 Init · Acuario Tiburón Feliz</h1>
    <p><span class="badge">development</span> Esta página solo funciona con <code>APP_ENV=development</code>.</p>

    <?php if ($mensaje): ?><div class="ok"><?= $esc($mensaje) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="err"><?= $esc($error) ?></div><?php endif; ?>

    <?php if ($existeUsuario): ?>
        <div class="box">
            <h2>✅ Sistema inicializado</h2>
            <p>Ya existe al menos un usuario. Esta página queda bloqueada.</p>
            <p>Login en <code>POST /api/v1/auth/login</code>.</p>
        </div>
    <?php else: ?>
        <form method="post" class="box" autocomplete="off">
            <h2>Crear primer administrador</h2>

            <label>Nombre *</label>
            <input name="nombre" required maxlength="50">

            <label>Apellido paterno *</label>
            <input name="apellido_paterno" required maxlength="50">

            <label>Apellido materno</label>
            <input name="apellido_materno" maxlength="50">

            <label>Correo</label>
            <input type="email" name="correo" maxlength="100">

            <label>Puesto</label>
            <input name="puesto" value="Administrador" maxlength="80">

            <label>Nombre de usuario *</label>
            <input name="nombre_usuario" required maxlength="50">

            <label>Contraseña * (mín. 6)</label>
            <input type="password" name="password" required minlength="6">

            <label>Confirmar contraseña *</label>
            <input type="password" name="password_confirm" required minlength="6">

            <button type="submit">Crear administrador</button>
        </form>
    <?php endif; ?>

    <p style="color:#64748b; font-size:.85rem;">
        Recuerda: cambiar <code>APP_ENV</code> a <code>production</code> y eliminar este archivo antes de desplegar.
    </p>
</body>
</html>