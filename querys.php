<?php


    $query = $_GET["query"];

    include_once("conn.class.php");
    include_once("conversor.js");
    include_once("./config_db/config.php");

    $obj_icampos = new Conexao($wdb,$whost,$wport,$wuser,$wpassword);

    $conn = $obj_icampos -> getConnection();
    
    $res = $conn -> query($query); 
    
   
    echo("<script>window.parent.document.getElementById('if_relacionamentos').src = 'relacionamentos.php';</script>");



?>