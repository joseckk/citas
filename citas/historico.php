<?php session_start() ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <?php
    require '../comunes/auxiliar.php';

    comprobar_logueado();

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }

    $pdo = conectar();
    $usu_id = logueado()['id'];
    ?>

    <div class="container-fluid">
        <div class="row-md-12">
            <?php head() ?>
            <div class="col-md-12">
                <nav class="navbar navbar-expand-lg navbar-light">
                            
                    <img src="/imagenes/icon.png" width="5%" height="2%">

                    <a class="navbar-brand ml-5" href="../index.php">Inicio</a>
                </nav>
            </div>
        </div>

        <?php 
        $fecha = date("Y-m-d H:i:s");
        $fecha_fmt = new DateTime($fecha);
        $fecha_fmt->setTimezone(new DateTimeZone('Europe/Madrid'));
        $fecha_hoy = $fecha_fmt->format("Y-m-d H:i:s");
        if (comprobar_estado($usu_id, $fecha_hoy, $pdo)) {
            $sent = $pdo->prepare("SELECT *
                                     FROM citas
                                    WHERE fecha_hora > :fecha_hoy
                                      AND usuario_id = :usu_id");
            $sent->execute(['fecha_hoy' => $fecha_hoy
                          , 'usu_id' => $usu_id]);
        } else {?>
            <div class="row ml-5">
            <div class="alert alert-danger mt-2" role="alert">
                <?= 'Usted no tiene citas pendientes' ?>
            </div>
            </div><?php
            return;
        }
        ?>
        <div class="row-md-12">
            <table class="table table-hover table-bordered text-center">
                <thead class="thead-dark">
                    <th scope="col">FECHA Y HORA</th>
                </thead>
                <tbody>
                    <?php foreach ($sent as $fila):
                        extract($fila);?>
                        <tr>
                            <td scope="row"><?= hh($fecha_hora) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table> 
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>