<?php
session_start();

require '../comunes/auxiliar.php';

comprobar_logueado();

if (!isset($_POST['csrf_token'])) {
    volver();
    return;
} elseif ($_POST['csrf_token'] != $_SESSION['csrf_token']) {
    volver();
    return;
}

if (isset($_POST['id'])) {

    $id = recoger_post('id');
    $pdo = conectar();
    
    if (comprobar_cita($id, $pdo)){
        $sent = $pdo->prepare('DELETE FROM citas WHERE id = :id');
        
        $sent->execute(['id' => $id]);

        $_SESSION['flash'] = 'Se ha anulado la cita correctamente';
        volver();
    } else {
        $_SESSION['flash'] = 'Error: la cita no existe';
        volver();
    }
}