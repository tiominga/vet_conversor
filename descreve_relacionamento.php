<?php
    include_once("conversor.js");

    $miolo="<div style='position:absolute;left:2%;top:2%;width:96%;height:90%;border:0px solid'>";

        $miolo.="<div style='position:absolute;left:5%;top:25%;width:20%'><font>A tabela origem<br></font><input type=\"text\" id=\"ed_v_tabelas\"></div>";
        $miolo.="<div style='position:absolute;left:26%;top:25%;width:20%'><font>o campo:<br></font><input type=\"text\" id=\"ed_v_campos\"></div>";

        $miolo.="<div style='position:absolute;left:50%;top:25%;width:20%'><font>é enviado para a destino (web)<br></font><input type=\"text\" id=\"ed_i_tabelas\"></div>";
        $miolo.="<div style='position:absolute;left:76%;top:25%;width:20%'><font>no campo<br></font><input type=\"text\" id=\"ed_i_campos\"></div>";


        $miolo.="<div style='position:absolute;left:90%;top:39%;width:20%'><button onClick=\"insere_relacionamento();\" type='button'>Confirmar campos</button></div>";

        $miolo.="<div style='position:absolute;left:5%;top:75%;width:20%'><button onClick=\"compara();\" type='button'>Comparar</button></div>";
       
        $miolo.="<div style='position:absolute;left:12.5%;top:75%;width:20%'><button onClick=\"run();\" type='button'>Rodar >>></button></div>";
        $miolo.="<div style='position:absolute;left:22%;top:75%;width:20%'><button onClick=\"run_quantidade();\" type='button'>Quantidade</button></div>";
        $miolo.="<div style='position:absolute;left:32%;top:75%;width:20%'><button onClick=\"peso_integridade();\" type='button'>Peso (depois sql)</button></div>";
        $miolo.="<div style='position:absolute;left:42%;top:75%;width:20%'><button onClick=\"exame_integridade();\" type='button'>Exames (depois tudo)</button></div>";
        $miolo.="<div style='position:absolute;left:52%;top:75%;width:20%'><button onClick=\"exame_cadastro_integridade();\" type='button'>Cad Exames (depois tudo)</button></div>";
        $miolo.="<div style='position:absolute;left:62%;top:75%;width:20%'><button onClick=\"agendamentos();\" type='button'>Agendamentos (depois tudo)</button></div>";

        
       

    $miolo.="</div>";

    echo("<script>preenche('dv_query',`$miolo`);</script>");


?>