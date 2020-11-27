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

function encabezado()
{
    if ($logueado = logueado()): ?>
        <form class="row justify-content-end mt-2 mr-5" action="/comunes/logout.php" method="post">
            <a class="col-sm-1" href="/usuarios/index.php"><h3><strong>Mi lista</strong></h3></a>
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

    $pdo = new PDO('pgsql:host=localhost;dbname=bd', 'joseka', 'joseka');
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