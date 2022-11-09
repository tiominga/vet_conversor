<?php

    include_once("conn.class.php");
    include_once("conversor.js");
    include_once("./config_db/config.php");

    $obj_juncao = new Conexao($wdb,$whost,$wport,$wuser,$wpassword);

    $conn = $obj_juncao -> getConnection();

    $res = $conn -> query("select * from conversao order by tabela_origem,campo_origem");

    $top = 0;

    $miolo="";
    
    $top = 0;

    while ($linha = $res -> fetch(PDO::FETCH_BOTH))
    {

        $id = $linha[0];
        $tabela_origem = substr($linha[1],0,19);
        $campo_origem = substr($linha[2],0,19);
        $tabela_destino = substr($linha[3],0,19);
        $campo_destino = substr($linha[4],0,19);
        $sql = substr($linha[5],0,19);


        $tem_query = "Não";

        if (strlen($sql) > 10){

            $tem_query = "Sim";

        }



        



        $query = "delete from conversao where cod_id=$id";
        $query2 = "update conversao set converter_string=if(converter_string = 1,0,1) where cod_id=$id";
        //echo("<script>alert(\"$merda\");</script>");
        $miolo .="<div style='position:absolute; width:20%; height:5%; left:0%;  top:$top%'><text><font>$tabela_origem</font></text></div>";
        $miolo .="<div style='position:absolute; width:20%; height:5%; left:20%; top:$top%'><text><font>$campo_origem</font></text></div>";
        $miolo .="<div style='position:absolute; width:20%; height:5%; left:40%; top:$top%'><text><font>$tabela_destino</font></text></div>";
        $miolo .="<div style='position:absolute; width:20%; height:5%; left:60%; top:$top%'><text><font>$campo_destino</font></text></div>";
        $miolo .="<div onclick='sql_abre($id);' style='position:absolute; width:10%; height:5%; left:80%; top:$top%'><text><font class=\'menu\'>$tem_query</font></text></div>";
        $miolo .="<div onclick='sql_executa(\"$query\",\"Apagar?\");' style='position:absolute; width:20%; height:5%; left:90%;  top:$top%'><font class='menu'>Apaga</font></div>";

        $top = $top+5;


    } 


    echo("<script>preenche(`dv_relacionamentos`,`$miolo`);</script>");

?>