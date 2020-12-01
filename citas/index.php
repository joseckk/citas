<?php session_start() ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    <?php
    require '../comunes/auxiliar.php';

    comprobar_logueado();

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }

    $cita_id = recoger_get('cita_id');

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
    $pdo = conectar();
    $usu_id = logueado()['id'];
    $sent = null;

    if (comprobar_estado($usu_id, $pdo)) {
        $sent = $pdo->prepare("SELECT * 
                                 FROM citas
                                WHERE usuario_id = :usu_id");
        $sent->execute(['usu_id' => $usu_id]);
    } else {?>
        <div class="row ml-5">
        <div class="alert alert-danger mt-2" role="alert">
            <?= 'No tiene cita asignada' ?>
        </div>
        </div><?php
        
        $fecha = date("Y-m-d H:i:s");
        $fecha_fmt = new DateTime($fecha);
        $fecha_fmt->setTimezone(new DateTimeZone('Europe/Madrid'));
        $dia = $fecha_fmt->format('d-m-Y');
        $hora = $fecha_fmt->format('H:i:s');
            
        $proximo_dia = strtotime('+1 day', strtotime($dia)) ;
        $proximo_dia = date('d-m-Y', $proximo_dia);
    
        $ultimo_dia = strtotime('+1 month', strtotime($proximo_dia)) ;
        $ultimo_dia = date('d-m-Y', $ultimo_dia);
    
    
        $horas_disponibles = [];
        $dia_fmt = $proximo_dia;
        $hora_fmt = '16:00:00';
        $contador = 0;
        $indice = 0;
        $no_existe = false;
        for ($i=0; $dia_fmt != $ultimo_dia; $i++) {
            $hora_fmt = strtotime('+15 minute', strtotime($hora_fmt)) ;
            $hora_fmt = date('H:i:s', $hora_fmt);
    
            $hora_limit = date('H', strtotime($hora_fmt));
            if ($hora_limit == '00') {
                if ($contador % 4 == 0) {
                    $dia_fmt = strtotime('+1 day', strtotime($dia_fmt));
                    $dia_fmt = date('d-m-Y', $dia_fmt);
                }
                $contador++;
            }
            if (validar_fecha_hora($dia_fmt, $hora_fmt)) {
                $f = $dia_fmt . ' ' . $hora_fmt;
                if (!comprobar_fecha_hora($f, $pdo)) {
                    $horas_disponibles[$indice] = $f;
                    $indice++;
                } else {                        
                    $horas_disponibles[$i] ?? $no_existe = true;
                    if (!$no_existe) {
                        if ($horas_disponibles[$i] == $f) {
                            unset($horas_disponibles[$i]);
                        }
                    }
                }
            }
        }
        ?>
        <div class="row-md-12">
            <form action="" method="get">
                <div class="form-group mt-1">
                    <label class="col-lg-4 control-label" for="cita_id"><strong>Fecha y hora de la cita:</strong></label>
                    <div class="col-lg-4">
                        <select class="form-control" name="cita_id" id="cita_id">
                            <option value="<?= '' ?>
                            <?php for ($i=0; $i <= sizeof($horas_disponibles)-1; $i++) :?>
                                <option value="<?= $i ?>" <?= selected($cita_id, $i) ?>>
                                    <?= hh($horas_disponibles[$i]) ?>
                                </option>
                            <?php endfor ?>
                        </select>
                        <button type="submit" class="btn btn-primary mt-3">coger cita</button>
                    </div>
                </div>
            </form>
        </div><?php
        if ($cita_id != null) {
                $cita_id = intval($cita_id);
                $cita = $horas_disponibles[$cita_id];
                coger_cita($cita, $usu_id, $pdo);
        } else {
            return;
        }
        $sent = $pdo->prepare("SELECT * 
                                 FROM citas
                                WHERE usuario_id = :usuario_id");
        $sent->execute(['usuario_id' => $usu_id]); 
    }
    if ($sent != null) {
        pintar_tabla($sent);
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>