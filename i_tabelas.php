<?php

    include_once("conn.class.php");
    include_once("conversor.js");
    include_once("./config_db/config.php");
    

    $obj_itabelas = new Conexao($wdb,$whost,$wport,$wuser,$wpassword);

    $conn = $obj_itabelas -> getConnection();
    
    $res = $conn -> query("show tables from $wdb");

    $miolo = "";

    while ($linha = $res -> fetch(PDO::FETCH_BOTH))
    {

        $tabela = $linha[0];

        $miolo .="<label onclick=\"carrega(\'if_itabelas\',\'i_campos.php?tabela=$tabela\'); guarda(\'ed_i_tabelas\',this.innerText);\"><font class=\'menu\'>$tabela</font></label><br>";

        
    } 

   
    echo("<script>preenche('dv_itabelas',`$miolo`);</script>");



?>