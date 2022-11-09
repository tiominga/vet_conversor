<?php

        include_once("conn.class.php");
        include_once("./config_db/config.php");
        include_once("conversor.js");


        $query = $_POST["query"];
        $id = $_POST["id"];

        $obj_conexao = new Conexao($wdb,$whost,$wport,$wuser,$wpassword);

        $conn = $obj_conexao -> getConnection();

        $query = "update conversao set query = \"$query\" where cod_id = $id";

        echo($query);
        
        $conn -> query("$query");

        echo("<script>sql_executa(\"select 1+1\",0);</script>");

?>
