<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

$accion = $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
} else {
    $datos = $_GET;
}

function responder($estado, $mensaje, $extra = []) {
    echo json_encode(array_merge(["estado" => $estado, "mensaje" => $mensaje], $extra));
    exit;
}

function verificarAuth() {
    if (!isset($_SESSION['id_usuario'])) {
        responder("error", "No estai logeado. Inicia sesión primero.");
    }
}

function verificarAdmin() {
    verificarAuth();
    if (!isset($_SESSION['es_admin']) || $_SESSION['es_admin'] != 1) {
        responder("error", "No tenís permisos de administrador weón.");
    }
}

switch ($accion) {
    case 'registro':
        $nombre_usuario = $datos['nombre_usuario'] ?? '';
        $clave = $datos['clave_secreta'] ?? '';
        if (empty($nombre_usuario) || empty($clave)) responder("error", "Faltan datos, llena todo porfa.");
        
        $hash = password_hash($clave, PASSWORD_DEFAULT);
        $es_admin = ($nombre_usuario === 'admin') ? 1 : 0;
        
        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_usuario, clave_secreta, es_admin) VALUES (?, ?, ?)");
            $stmt->execute([$nombre_usuario, $hash, $es_admin]);
            responder("exito", "Usuario creado bacán, ahora inicia sesión.");
        } catch (PDOException $e) {
            responder("error", "Pucha, hubo un error (quizás el nombre ya existe).");
        }
        break;

    case 'login':
        $nombre_usuario = $datos['nombre_usuario'] ?? '';
        $clave = $datos['clave_secreta'] ?? '';
        
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre_usuario = ?");
        $stmt->execute([$nombre_usuario]);
        $usuario = $stmt->fetch();
        
        if ($usuario && password_verify($clave, $usuario['clave_secreta'])) {
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];
            $_SESSION['es_admin'] = $usuario['es_admin'];
            responder("exito", "¡Entraste al tiro!");
        } else {
            responder("error", "Datos incorrectos, revisa bien.");
        }
        break;

    case 'logout':
        session_destroy();
        responder("exito", "Cerraste sesión.");
        break;

    case 'actualizar_sueldo':
        verificarAuth();
        $sueldo = $datos['sueldo'] ?? 0;
        $stmt = $pdo->prepare("UPDATE usuarios SET sueldo = ? WHERE id_usuario = ?");
        $stmt->execute([$sueldo, $_SESSION['id_usuario']]);
        responder("exito", "Sueldo actualizado al tiro.");
        break;

    case 'crear_tipo':
        verificarAuth();
        $nombre_tipo = $datos['nombre_tipo'] ?? '';
        $color = $datos['color_tipo'] ?? '#000000';
        $stmt = $pdo->prepare("INSERT INTO tipos_gasto (id_usuario, nombre_tipo, color_tipo) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['id_usuario'], $nombre_tipo, $color]);
        responder("exito", "Tipo de gasto guardado.");
        break;

    case 'obtener_tipos':
        verificarAuth();
        $stmt = $pdo->prepare("SELECT * FROM tipos_gasto WHERE id_usuario = ?");
        $stmt->execute([$_SESSION['id_usuario']]);
        responder("exito", "", ["data" => $stmt->fetchAll()]);
        break;

    case 'editar_tipo':
        verificarAuth();
        $id_tipo = $datos['id_tipo'] ?? '';
        $nombre_tipo = $datos['nombre_tipo'] ?? '';
        $color_tipo = $datos['color_tipo'] ?? '#000000';
        $stmt = $pdo->prepare("UPDATE tipos_gasto SET nombre_tipo = ?, color_tipo = ? WHERE id_tipo = ? AND id_usuario = ?");
        $stmt->execute([$nombre_tipo, $color_tipo, $id_tipo, $_SESSION['id_usuario']]);
        responder("exito", "Tipo de gasto modificado al tiro.");
        break;

    case 'eliminar_tipo':
        verificarAuth();
        $id_tipo = $datos['id_tipo'] ?? '';
        $stmt = $pdo->prepare("DELETE FROM tipos_gasto WHERE id_tipo = ? AND id_usuario = ?");
        $stmt->execute([$id_tipo, $_SESSION['id_usuario']]);
        responder("exito", "Tipo de gasto eliminado, chao nomás.");
        break;

    case 'crear_gasto':
        verificarAuth();
        $id_tipo = $datos['id_tipo'] ?? '';
        $plata = $datos['plata_gastada'] ?? '';
        $detalle = $datos['detalle'] ?? '';
        $fecha = $datos['fecha_gasto'] ?? date('Y-m-d');
        
        $stmt = $pdo->prepare("INSERT INTO gastos (id_usuario, id_tipo, plata_gastada, detalle, fecha_gasto) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['id_usuario'], $id_tipo, $plata, $detalle, $fecha]);
        responder("exito", "Gasto registrado, menos lucas en el bolsillo.");
        break;

    case 'obtener_gastos':
        verificarAuth();
        $stmt = $pdo->prepare("SELECT g.*, t.nombre_tipo, t.color_tipo FROM gastos g JOIN tipos_gasto t ON g.id_tipo = t.id_tipo WHERE g.id_usuario = ? ORDER BY g.fecha_gasto DESC");
        $stmt->execute([$_SESSION['id_usuario']]);
        responder("exito", "", ["data" => $stmt->fetchAll()]);
        break;

    case 'crear_meta':
        verificarAuth();
        $nombre = $datos['nombre_meta'] ?? '';
        $plata_objetivo = $datos['plata_objetivo'] ?? 0;
        $fecha_limite = $datos['fecha_limite'] ?? null;
        
        $stmt = $pdo->prepare("INSERT INTO metas_ahorro (id_usuario, nombre_meta, plata_objetivo, fecha_limite) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['id_usuario'], $nombre, $plata_objetivo, $fecha_limite]);
        responder("exito", "Meta lista. ¡A juntar plata!");
        break;

    case 'poner_plata_meta':
        verificarAuth();
        $id_meta = $datos['id_meta'] ?? '';
        $plata = $datos['plata'] ?? 0;
        $stmt = $pdo->prepare("UPDATE metas_ahorro SET plata_juntada = plata_juntada + ? WHERE id_meta = ? AND id_usuario = ?");
        $stmt->execute([$plata, $id_meta, $_SESSION['id_usuario']]);
        responder("exito", "Le pusiste lucas al chanchito.");
        break;

    case 'obtener_metas':
        verificarAuth();
        $stmt = $pdo->prepare("SELECT * FROM metas_ahorro WHERE id_usuario = ?");
        $stmt->execute([$_SESSION['id_usuario']]);
        responder("exito", "", ["data" => $stmt->fetchAll()]);
        break;

    case 'obtener_dashboard':
        verificarAuth();
        $uid = $_SESSION['id_usuario'];
        
        $stmt = $pdo->prepare("SELECT sueldo, es_admin FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$uid]);
        $info = $stmt->fetch();
        
        $sueldo = $info['sueldo'];
        
        $stmt = $pdo->prepare("SELECT SUM(plata_gastada) FROM gastos WHERE id_usuario = ? AND MONTH(fecha_gasto) = MONTH(CURRENT_DATE()) AND YEAR(fecha_gasto) = YEAR(CURRENT_DATE())");
        $stmt->execute([$uid]);
        $gastos_mes = $stmt->fetchColumn() ?: 0;
        
        $stmt = $pdo->prepare("SELECT SUM(plata_juntada) FROM metas_ahorro WHERE id_usuario = ?");
        $stmt->execute([$uid]);
        $plata_ahorrada = $stmt->fetchColumn() ?: 0;

        responder("exito", "", [
            "sueldo" => $sueldo,
            "gastos_mes" => $gastos_mes,
            "plata_ahorrada" => $plata_ahorrada,
            "saldo" => $sueldo - $gastos_mes,
            "es_admin" => $info['es_admin']
        ]);
        break;
        
    // --- RUTAS DE ADMINISTRADOR ---
    case 'obtener_usuarios':
        verificarAdmin();
        $stmt = $pdo->prepare("SELECT id_usuario, nombre_usuario, creado_el, es_admin FROM usuarios ORDER BY id_usuario DESC");
        $stmt->execute();
        responder("exito", "", ["data" => $stmt->fetchAll()]);
        break;

    case 'eliminar_usuario':
        verificarAdmin();
        $id_borrar = $datos['id_usuario'] ?? '';
        if ($id_borrar == $_SESSION['id_usuario']) {
            responder("error", "No podís borrarte a ti mismo po.");
        }
        
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id_borrar]);
        responder("exito", "Usuario eliminado, para la casa.");
        break;

    default:
        responder("error", "Acción no válida weón.");
}
?>
