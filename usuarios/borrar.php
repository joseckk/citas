<?php
session_start();

require '../comunes/auxiliar.php';

comprobar_admin();

if (!isset($_POST['csrf_token'])) {
    volver();
    return;
} elseif ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
    volver();
    return;
}

if (isset($_POST['id'])) {

    $id = trim($_POST['id']);
    $pdo = conectar();
    
    if (!comprobar_estado($id, $pdo)){
        $sent = $pdo->prepare('DELETE FROM usuarios WHERE id = :id');
        
        $sent->execute(['id' => $id]);
        
        $_SESSION['flash'] = 'Se ha borrado el usuario correctamente';
        volver();
    } else {
        $_SESSION['flash'] = 'El usuario tiene una cita pendiente';
        volver();
    }
}
volver();
