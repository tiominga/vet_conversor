<?php

        include_once("conn.class.php");
        include_once("./config_db/config.php");
      

        $id = $_GET["id"];
       
        $obj_conexao = new Conexao($wdb,$whost,$wport,$wuser,$wpassword);

        $conn = $obj_conexao -> getConnection();

        $query = "select cod_id,query from conversao where cod_id = $id";

        echo($query);

        $res = $conn -> query($query);
               
        while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){

            $sql = $linha["query"];
            $id = $linha["cod_id"];           

        }


        echo("
        
        
            <script>
                
                window.parent.document.getElementById('dv_sql_query').innerText = \"$sql\";
                window.parent.document.getElementById('ed_query_id').innerText = \"$id\";

            </script>
        
        
        
        
        ")
        
        





?>