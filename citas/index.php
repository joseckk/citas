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

    $pdo = conectar();

    $usu_id = logueado()['id'];

    $dia = recoger_get('dia');

    $dia_post = recoger_post('dia');
    $hora_post = recoger_post('hora');
    $hora = $hora_post;

    if (isset($dia_post, $hora_post)) {
        if (validar_fecha_hora($dia_post, $hora_post)) {
            $fecha_cita = "$dia_post $hora_post";
            coger_cita($fecha_cita, $usu_id, $pdo);
        }
    }
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
    if (!comprobar_estado($usu_id, $pdo)) {
        // Ese usuario no tiene citas vigentes
        // Darle la opción de reservar una
        $sent = $pdo->query('SELECT fecha_hora::date AS fecha
                               FROM citas
                              WHERE fecha_hora::date != CURRENT_DATE
                           GROUP BY 1
                             HAVING COUNT(*) >= 16
                           ORDER BY 1');
        $fechasOcupadas = $sent->fetchAll(PDO::FETCH_COLUMN, 0);
        $fechas = [];
        $intervalo = new DateInterval('P1D');
        $fechaActual = (new DateTime())->add($intervalo);
        $i = 0;
        while ($i < 30) {
            $dow = $fechaActual->format('w');
            $fecha = $fechaActual->format('Y-m-d');
            if (in_array($dow, ['1', '3', '5'])
                && !in_array($fecha, $fechasOcupadas)) {
                $fechas[] = $fecha;
                // var_dump($fechasOcupadas);
            }
            $i++;
            $fechaActual->add($intervalo);
        } ?>
    <form action="" method="get">
        <label for="dia">Seleccione el día de la cita:</label>
        <select name="dia" id="dia">
            <?php foreach ($fechas as $f): ?>
                <option value="<?= $f ?>" <?= selected($dia, $f) ?> >
                    <?= $f ?>
                </option>
            <?php endforeach ?>
        </select>
        <button type="submit">Seleccionar</button>
    </form>
    <?php
    if ($dia !== null) {
        $match = [];
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dia, $match) !== 1) {
            volver();
            return;
        }
        if (!checkdate($match[2], $match[3], $match[1])) {
            volver();
            return;
        }
        $sent = $pdo->prepare('SELECT fecha_hora::time(0) AS hora
                                 FROM citas
                                WHERE fecha_hora::date = :dia
                             ORDER BY 1');
        $sent->execute(['dia' => $dia]);
        $horasOcupadas = $sent->fetchAll(PDO::FETCH_COLUMN, 0);
        $madrid = new DateTimeZone('Europe/Madrid');
        foreach ($horasOcupadas as $k => $h) {
            $hh = DateTime::createFromFormat('H:i:s', $h);
            //$hh->setTimeZone($madrid);
            $horasOcupadas[$k] = $hh->format('H:i:s');
        }
        $horas = [];
        $intervalo = new DateInterval('PT15M');
        $utc = new DateTimeZone('UTC');
        $horaActual = (new DateTime())->setTimezone($madrid)->setTime(16, 0, 0);
        $horaFin = clone $horaActual;
        $horaFin->setTime(20, 0, 0);
        while ($horaActual < $horaFin) {
            if (!in_array($horaActual->format('H:i:s'), $horasOcupadas)) {
                $horas[] = $horaActual->format('H:i:s');
            }
            $horaActual->add($intervalo);
        } ?>
        <form action="" method="post">
            <input type="hidden" name="dia" value="<?= $dia ?>">
            <label for="hora">Seleccione la hora:</label>
            <select name="hora" id ="hora">
                <?php foreach ($horas as $h): ?>
                    <option value="<?= $h ?>" <?= selected($h, $hora) ?> >
                        <?= $h ?>
                    </option>
                <?php endforeach ?>
            </select>
            <button type="submit">Reservar</button>
        </form>
        <?php
        }
    } else {
        // El usuario tiene citas vigentes
        $sent = $pdo->prepare("SELECT *
                                 FROM citas
                                WHERE fecha_hora > CURRENT_TIMESTAMP
                                  AND usuario_id = :usu_id");
        $sent->execute(['usu_id' => $usu_id]);
        pintar_tabla($sent);
    }
    ?>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>