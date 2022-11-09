<?php

    include_once("converte.class.php");
    include_once("conn.class.php");
    include_once("./config_db/config.php");

    date_default_timezone_set('America/Sao_Paulo');

    $obj_connect = new Conexao($wdb,$whost,$wport,$wuser,$wpassword);

    $obj_converte = new Converte($wdb,$whost,$wport,$wuser,$wpassword,$db,$host,$port,$user,$password);

    $obj_converte -> getPrepara(); //apaga dados tanto da tabela de origem quanto da de destino

    $conn = $obj_connect -> getConnection();
    
    $res = $conn -> query("select tabela_origem,tabela_destino as tabela from conversao group by tabela_destino,tabela_origem order by tabela_origem");
    
    while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){ 
        
        $tabela = $linha["tabela"];
        $tabela_origem = $linha["tabela_origem"];

        echo("<br><b>Trabalhando com a tabela $tabela_origem</b>");

        
        echo date('d/m/Y \à\s H:i:s');

        flush();
        ob_flush();
        sleep(0.5);

        

        $prefixo = $obj_converte -> getPrefixo("$tabela");

        $sufixo = $obj_converte -> getSufixo(); //make all data and save in result.sql in tha same path of this. 
    

    }    


?>