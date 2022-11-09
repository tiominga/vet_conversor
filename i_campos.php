<?php

    $tabela = $_GET["tabela"]; 

    include_once("conn.class.php");
    include_once("conversor.js");
    include_once("./config_db/config.php");

    $obj_icampos = new Conexao($wdb,$whost,$wport,$wuser,$wpassword);

    $conn = $obj_icampos -> getConnection();
    
    $res = $conn -> query("describe $tabela");

    $miolo = "";

    while ($linha = $res -> fetch(PDO::FETCH_BOTH))
    {

        
        $campo = $linha[0];

        $miolo .="<div onClick=\"guarda(\'ed_i_campos\',this.innerText);\"><label  id=\'lb_i_campo_$campo\' class=\'lb_i_campos\'><font class=\'menu\'>$campo</font></label></div>";

    } 

   
    echo("<script>preenche('dv_icampos',`$miolo`);</script>");




?>