<?php

function banner() 
{
    if (!isset($_COOKIE['acepta_cookies'])) {?>
        <h3>
            Este sitio usa cookies
            <a href="../cookies.php">Aceptar</a>
        </h3><?php
    } 
}
function error($mensaje)
{?>
    <div class="row ml-5">
        <div class="alert alert-danger mt-2" role="alert">
                <?= $mensaje ?>
        </div>
    </div><?php
    return true;
}
function recoger($tipo, $nombre)
{
    return filter_input($tipo, $nombre, FILTER_CALLBACK, [
        'options' => 'trim'
    ]);
}
function recoger_get($nombre)
{
    return recoger(INPUT_GET, $nombre);
}
function recoger_post($nombre)
{
    return recoger(INPUT_POST, $nombre);
}
function logueado()
{
    return $_SESSION['login'] ?? false;
}
function comprobar_usuario($login, $pdo)
{
    $sent = $pdo->prepare('SELECT *
                             FROM usuarios
                            WHERE login = :login');
    $sent->execute(['login' => $login]);

    return $sent->fetchColumn() != 0;
}

function comprobar_usuario_otra_fila($login, $pdo, $id)
{
    $sent = $pdo->prepare('SELECT *
                             FROM usuarios
                            WHERE login = :login
                              AND id != :id');
    $sent->execute(['login' => $login,
                    'id' => $id]);

    return $sent->fetchColumn() != 0;
}
function comprobar_estado($id, $pdo) 
{
    $sent = $pdo->prepare("SELECT *
                             FROM citas
                            WHERE usuario_id = :usu_id
                              AND fecha_hora > CURRENT_TIMESTAMP");
    $sent->execute(['usu_id' => $id]);

    return $sent->fetch();
}
function validar_fecha_hora($dia, $hora)
{   
    $fecha_valida = false;
    $dia_fmt = date('D', strtotime($dia));
    $hora_fmt = date('H', strtotime($hora));
    $minuto = date('i', strtotime($hora));

    if ($dia_fmt == 'Mon' || $dia_fmt == 'Wen'
        || $dia_fmt == 'Fri') {
            if ($hora_fmt >= '16'
                && $hora_fmt < '20') {
                if ($hora_fmt == '19') {
                    if ($minuto <= '45') {
                        $fecha_valida = true;
                    } else {
                        $fecha_valida = true;
                    }
                } else {
                    $fecha_valida = true;
                }
            }
    }
    return $fecha_valida;
}
function comprobar_fecha_hora($fecha_hora, $pdo)
{
    $sent = $pdo->prepare('SELECT *
                             FROM citas
                            WHERE fecha_hora = :fecha_hora');
    $sent->execute(['fecha_hora' => $fecha_hora]);

    return $sent->fetchColumn() != 0;
}
function coger_cita($cita, $id, $pdo)
{
    $sent = $pdo->prepare("INSERT INTO citas(fecha_hora, usuario_id)
                                VALUES (:fecha_hora, :usuario_id)");

    $sent->execute([ 'fecha_hora' => $cita
                    ,'usuario_id' => $id]);
}
function comprobar_cita($id, $pdo)
{
    $sent = $pdo->prepare('SELECT *
                             FROM citas
                            WHERE id = :id');
    $sent->execute(['id' => $id]);

    return $sent->fetchColumn() != 0;
}
function pintar_tabla($sent)
{?>
    <div class="row-md-12">
    <table class="table table-hover table-bordered text-center">
        <thead class="thead-dark">
            <th scope="col">FECHA Y HORA</th>
            <th scope="col">ACCIONES</th>
        </thead>
        <tbody>
            <?php foreach ($sent as $fila):
                extract($fila);?>
                <tr>
                    <td scope="row"><?= hh($fecha_hora) ?></td>
                    <td scope="row">
                        <form action="/citas/anular.php" method="post">
                            <input type="hidden" name="id" value="<?=hh($id)?>">
                            <input type="hidden" name="csrf_token"
                                value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="bg-danger">anular</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table> 
</div>
</div><?php
}
function selected($a, $b)
{
    return ($a == $b) ? 'selected' : '';
}
function encabezado()
{
    if ($logueado = logueado()): ?>
        <form class="row justify-content-end mt-2 mr-5" action="/comunes/logout.php" method="post">
            <a class="col-sm-1" href="/citas/historico.php"><h3><strong>Mis citas</strong></h3></a>
            <?= $logueado['nombre'] ?>
            <button type="submit" class="btn btn-outline-danger ml-2">Logout</button>
        </form><?php
    else: ?>
        <form class="row justify-content-end mt-2 mr-5" action="/comunes/login.php" >
            <button type="submit" class="btn btn-outline-success">Login</button>
        </form><?php
    endif;
}


function conectar() 
{

    $pdo = new PDO('pgsql:host=localhost;dbname=bdCitas', 'josekas', 'josekas');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}

function volver() 
{   
    header('Location: ../index.php');
}

function mostrar_errores($error) 
{
               
    foreach ($error as $key => $array) {
        foreach ($array as $value) {?>
            <div class="row ml-5">
                <div class="alert alert-danger mt-2" role="alert">
                        <?= $value ?>
                </div>
            </div><?php
        }
    }
}
function flash()
{
    if (isset($_SESSION['flash'])) {
        echo "<h3>{$_SESSION['flash']}</h3>";
        unset($_SESSION['flash']);
    }
}

function head()
{   
    encabezado();
    banner();
    flash();
}

function comprobar_logueado()
{
    if (!logueado()) {
        $_SESSION['flash'] = 'Debe estar logueado.';
        header('Location: /comunes/login.php');
    }
}

function comprobar_admin()
{
    comprobar_logueado();

    if (logueado()['nombre'] != 'admin') {
        $_SESSION['flash'] = 'Debe ser administrador.';
        volver();
    }
}

function hh($s)
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE);
}