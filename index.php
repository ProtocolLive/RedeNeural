<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('error_reporting', -1);

require('rn.php');
$teste = new RedeNeural(2,1,2,1);

$teste->Entrada(1,1);
$teste->Calcula();
var_dump($teste->RedeGet());
$teste->Treina([[0,0]]);