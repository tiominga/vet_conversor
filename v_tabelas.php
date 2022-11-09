<?php

    include_once("conn.class.php");
    include_once("conversor.js");
    include_once("./config_db/config.php");

    $obj_itabelas = new Conexao($db,$host,$port,$user,$password);

    $conn = $obj_itabelas -> getConnection();
    
    $res = $conn -> query("show tables from $db");

    $miolo = "";
   

    while ($linha = $res -> fetch(PDO::FETCH_BOTH))
    {

        $tabela = $linha[0];

        $miolo .="<label onclick=\"carrega(\'if_vcampos\',\'v_campos.php?tabela=$tabela\'); guarda(\'ed_v_tabelas\',this.innerText);\"><font class=\'menu\'>$tabela</font></label><br>";

        
    } 

    echo("<script>preenche('dv_vtabelas',`$miolo`);</script>");



?>