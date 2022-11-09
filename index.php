<?php

include_once("conversor.js");

$top=0;

$miolo ="<div style='position:absolute; width:20%; height:90%; left:0%;  top:$top%; border:0px blue solid'><text><font>Tb.origem</font></text></div>";
$miolo .="<div style='position:absolute; width:20%; height:90%; left:20%;  top:$top%; border:0px gray solid'><text><font>Cp.origem</font></text></div>";
$miolo .="<div style='position:absolute; width:20%; height:90%; left:40%;  top:$top%; border:0px gray solid'><text><font>Tb.destino</font></text></div>";
$miolo .="<div style='position:absolute; width:15%; height:90%; left:60%;  top:$top%; border:0px gray solid'><text><font>Cp.destino</font></text></div>";
$miolo .="<div style='position:absolute; width:15%; height:90%; left:76%;  top:$top%; border:0px gray solid'><text><font>Sql</font></text></div>";

?>


<html>

    <head>

        <link rel="stylesheet" href="./style.css">

    </head>

    <body>


   

        <main>

        <div id="dv_sql" style="position:absolute; background-color:#7FFFD4; display:none; width:25%; height:40%;left:38%;top:30%;z-index:100"> 

                <div style="position:absolute; background-color:white; width:99.5%; left:0.25%; height:7%; top:0%; border:0px solid;cursor:pointer">    
                            <font>Mysql query (use sempre as valor)</font>
                </div> 
    
                <div id="dv_sql_query" contentEditable="true" style="position:absolute; width:90%; left:5%; height:70%;top:10%;border: 0px solid">    
                   

                </div>    

                <div style="position:absolute; width:20%; left:40%; height:10%;top:85%;border: 0px solid;z-index:1">    
                            
                    <lebel onClick="sql_confirma()"><font class="menu">Confirma</font></label>

                </div>  

                <div  style="position:absolute; width:20%; left:40%; height:10%;top:85%;opacity:0.0;z-index:0">    
                            
                    <form id="f_query" name="query" action = "sql_salva.php" target="if_sql" method="post">

                            <input type="text" id="ed_query_id" name="id">

                            <textarea id="ta_query" rows=10 cols=10 name="query"> 
                                    
                            </textarea>

                    </form>

                </div>  
    
    
        </div>
        
        
          
            <div class="tools" id="dv_tools">
                <div style='position:absolute;left:0%;top:2%;width:99%;height:90%;border:0px solid'>
                    <div id="dv_progresso" style="position:relative; background-color:red; width:99%; height:90%; top:0%; left:0%; z-index:1">

                        <div id="dv_status" style="position:absolute; width:100%; height:90%; top:0%; left:45%; z-index:2">
                                <font><label id="status">Aguardando (versão 5.5 KdMinhaPicanha)</label></font>
                        </div>      

                    </div> 
                         
                </div>    
            </div>

            <div class="i_tabelas" id="dv_itabelas">
            
            </div>
        
            </div>

            <div class="i_campos"  id="dv_icampos">
                <font><--Selecione</font>
            </div>

            <div class="v_tabelas"  id="dv_vtabelas">
                <h1>v_tabelas</h1>
            </div>

            <div class="v_campos" id="dv_vcampos">
            <font><--Selecione</font>
            </div>

            <div class="relacionamentos" id="dv_relacionamentos">
                <h1>relacionamentos</h1>
            </div>

            <div class="query" id="dv_query">
                <h1>query</h1>
            </div>

            <div class="cab" id="dv_cab" style="left:0%; width:100%; height:99%; top:0%">
                <font>Tabelas Origem</font>
            </div>

            <div class="cab2" id="dv_cab2" style="left:0%; width:100%; height:99%; top:0%">
                <font>Campos Origem</font>
            </div>

            <div class="cab3" id="dv_cab3" style="left:0%; width:100%; height:99%; top:0%">
                <font>Tab. Web</font>
            </div>

            <div class="cab4" id="dv_cab4" style="left:0%; width:100%; height:99%; top:0%">
                <font>Campos Web</font>
            </div>

            <div class="cab4" id="dv_cab4" style="position:relative; border:0px solid red;left:0%; width:100%; height:99%; top:0%">

            
                <font><?=$miolo?></font>
            </div>

        </main>


        <div id="dv_result" style="position:absolute; width:80%; height:50%; top:25%; left:10%;display:none;background-color:white; z-index:100">

        <iframe id="if_run" name="if_run" marginHeight="0" marginWidth="0" scrolling="auto" width="100%" height="100%">
                                    
        </iframe>
           

        </div>

        <div style="position:absolute; width:90%; height:90%; top:50%; left:80%;display:none;background-color:white; z-index:100">

            <iframe id="if_relacionamentos" name="if_relacionamentos" marginHeight="0" marginWidth="0" scrolling="auto" src="relacionamentos.php" width="100%" height="100%">
                                    
            </iframe>

            <iframe id="if_sql" name="if_sql" marginHeight="0" marginWidth="0" scrolling="auto" src="descreve_relacionamento.php" width="100%" height="100%">
                                    
            </iframe>

            <iframe id="if_itabelas" name="if_itabelas" marginHeight="0" marginWidth="0" scrolling="auto" src="i_tabelas.php" width="100%" height="100%">
                                    
            </iframe>

            <iframe id="if_icampos" name="if_icampos" marginHeight="0" marginWidth="0" scrolling="auto"  width="100%" height="100%">
                                    
            </iframe>

            <iframe id="if_vtabelas" name="if_vtabelas" marginHeight="0" marginWidth="0" scrolling="auto" src="v_tabelas.php" width="100%" height="100%">
                                    
            </iframe>

            <iframe id="if_vcampos" name="if_vcampos" marginHeight="0" marginWidth="0" scrolling="auto" width="100%" height="100%">
                                    
            </iframe>

            <iframe id="if_query" name="if_query" marginHeight="0" marginWidth="0" scrolling="auto" src="descreve_relacionamento.php" width="100%" height="100%">
                                    
        </iframe>

        </div>      

    </body>

</html>

