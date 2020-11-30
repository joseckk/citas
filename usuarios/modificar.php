<?php session_start() ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar un usuario</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
    
    <?php
    require '../comunes/auxiliar.php';

    comprobar_admin();

    $login = recoger_post('login');
    $password = recoger_post('password');
    $crypt_password = recoger_post('crypt_password');
    $id = recoger_get('id');

    $pdo = conectar();

    if (isset($login, $password, $crypt_password, $id, $pdo)) {
        // Validación y saneado de la entrada:
        $error_vacio = [
            'login' => [],
            'password' => [],
            'crypt_password' => [],
        ];
        $error = $error_vacio;

        if ($login == '') {
            $error['login'][] = 'El nombre es obligatoria.';
        } else {
            if (mb_strlen($login) > 255) {
                $error['login'][] = 'El nombre es demasiado larga.';
            } else {
                if (comprobar_usuario_otra_fila($login, $pdo, $id)) {
                    $error['login'][] = 'El usuario ya existe.';
                }
            }
        }


        $set = '';
        $execute = [
            'login' => $login,
            'id' => $id,
        ];

        if ($password != '' && $crypt_password != '') {
            if ($password != $crypt_password) {
                $error['password'][] = 'Las contraseñas no coinciden.';
            } else {
                $set = ', password = :password';
                $execute['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
        }


        // Modificar fila
        if ($error === $error_vacio) {
            try {
                $sent = $pdo->prepare("UPDATE usuario
                                          SET login = :login
                                              $set
                                        WHERE id = :id");
                                        
                $sent->execute($execute);
                              
                $_SESSION['flash'] = 'Se ha modificado la fila correctamente';
                volver();
            } catch (PDOException $e) {
                error('No se ha podido modificar la fila.');
            }
        } else {
                mostrar_errores($error);
            } 
    } else {
        if (isset($id)) {
            $sent = $pdo->prepare('SELECT *
                                     FROM usuario
                                    WHERE id = :id');
            $sent->execute(['id'=> $id]);
            
            foreach ($sent as $fila) {
                $login = $fila['login'];
            }
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
    </div>

    <div class="container">
        <div class="row-md-12">
            <form action="" method="post">
                <div class="form-group mt-5 mr-5">
                    <label class="col-lg-4 control-label" for="login">Nombre:</label>
                    <div class="col-lg-4">
                        <input type="text" class="form-control" name="login" id="login" 
                                value="<?= hh($login) ?>">
                    </div>
                </div>
                <div class="form-group mt-5">
                    <label class="col-lg-4 control-label" for="password">Contraseña:</label>
                    <div class="col-lg-4">
                        <input type="password" class="form-control" name="password" id="password">
                    </div>
                </div>
                <div class="form-group mt-5">
                    <label class="col-lg-4 control-label" for="crypt_password">Repite la contraseña:</label>
                    <div class="col-lg-4">
                        <input type="password" class="form-control" name="crypt_password" id="crypt_password">
                    </div>
                </div>
                <div class="col-lg-4">
                    <button type="submit" class="btn btn-primary mt-5">Modificar</button>
                </div>
            </form>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>