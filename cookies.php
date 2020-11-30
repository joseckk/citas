<?php
require 'comunes/auxiliar.php';

setcookie('acepta_cookies', '1', time() + 3600 * 24 * 365);
volver();