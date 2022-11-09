<?php

    $tabela = $_GET["tabela"];

    include_once("conn.class.php");
    include_once("conversor.js");
    include_once("./config_db/config.php");

    $obj_icampos = new Conexao($db,$host,$port,$user,$password);

    $conn = $obj_icampos -> getConnection();
    
    $res = $conn -> query("describe $tabela");

    $miolo = "";

    while ($linha = $res -> fetch(PDO::FETCH_BOTH))
    {

        $campo = $linha[0];

        $miolo .="<div onClick=guarda(\'ed_v_campos\',this.innerText);><label id=\'lb_v_campo_$campo\' class=\'lb_v_campos\'><font class=\'menu\'>$campo</font></label></div>";

    } 

    echo("<script>preenche('dv_vcampos',\"$miolo\");</script>");




?>