<?php

 include_once("converte.class.php");     
 include_once("conn.class.php");
 include_once("./config_db/config.php");

 date_default_timezone_set('America/Sao_Paulo');

 $obj_connect = new Conexao($wdb,$whost,$wport,$wuser,$wpassword);

 $obj_converte = new Converte($wdb,$whost,$wport,$wuser,$wpassword,$db,$host,$port,$user,$password);

   
    $obj_converte -> getConverteExame();

    echo("iniciando...");


?>
