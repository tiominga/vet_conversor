<?php

set_time_limit(0);
ini_set('display_errors',1);
ini_set('display_startup_erros',1);
error_reporting(E_ALL);

include_once("conn.class.php");
   
class Converte{
    
    private $tabela_origem; 
    private $campos_origem;
    private $prefixo;
    private $linha_a_linha;
    private $ano;
    
    //destino (web)
    private $db;
    private $host;
    private $port;
    private $user;
    private $password;

    //origem
    private $odb;
    private $ohost;
    private $oport;
    private $ouser;
    private $opassword;

    private $conexao_origem;
    private $conexao_destino;

    private $campo_origem; 
    private $tabela_destino; 
    private $campo_destino; 
    private $converter_string; 


    
    function __construct($db,$host,$port,$user,$password,$odb,$ohost,$oport,$ouser,$opassword){


            include_once("./config_db/config2.php");

            $this -> db = $db;
            $this -> host = $host;
            $this -> port = $port;
            $this -> user = $user;
            $this -> password = $password;

            $this -> odb = $odb;
            $this -> ohost = $ohost;
            $this -> oport = $oport;
            $this -> ouser = $ouser;
            $this -> opassword = $opassword;

            $this -> linha_a_linha = $compacto; //marcar como 1 em caso de erro que tenha que ver exatamente qual a linha (muito mais lento)
            $this -> ano = $ano;
                        
            //configura a conexão de destino

            $obj_conexao_destino = new Conexao($db,$host,$port,$user,$password);

            $conn_destino = $obj_conexao_destino -> getConnection();

            $this -> conexao_destino = $conn_destino;

            //configura a conexao de origem

            $obj_conexao_origem = new Conexao($odb,$ohost,$oport,$ouser,$opassword);

            $conn_origem = $obj_conexao_origem -> getConnection();

            $this -> conexao_origem = $conn_origem;

    }

   

    public function __set($propriedade,$valor){

        $this -> $propriedade = $valor;

    }

    public function __get($propriedade){

        return $this -> $propriedade;

    }

    private function total_registros($tabela){

        $db = $this -> odb;
        $host = $this -> ohost;
        $port = $this -> oport;
        $user = $this -> ouser;
        $password = $this -> opassword;

        $campo_id =  $this -> id_origem($tabela);       
 
        $conn = $this -> conexao_origem;
 
        $query = "select count($campo_id) as tot from $tabela";

        $res = $conn -> query($query);
        
        while ($linha = $res -> fetch(PDO::FETCH_BOTH)){

          $total = $linha["tot"];

        }    
 
        return $total;

    }

    private function prefixo($tabela){ 

        echo("<br><font>Preparando inserção da tabela $tabela</font>");

        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        $this -> tabela_destino = $tabela;

         //prepara a primeira parte da inserção dos dados
         $prefixo = "insert into $tabela ";

         //faz a consulta de todos os campos daquela tabela
 
        $conn = $this -> conexao_destino;
 
        $query = "select campo_destino,tabela_origem,campo_origem from conversao where tabela_destino =\"$tabela\"";

        $res = $conn -> query($query);

        $prefixob = "(";

        $campos_origem = "";
 
        while ($linha = $res -> fetch(PDO::FETCH_BOTH)){
 
            $campo = $linha[0];

            $this -> tabela_origem = $linha[1];

            if ($campos_origem != ""){
                $campos_origem .=",";
            }

            $campos_origem .= $linha[2];
 
            $prefixob .= $campo.",";
 
        }

         $this -> campos_origem = $campos_origem;

         $prefixob .=")";
 
         $prefixob = str_replace(",)",")",$prefixob);
 
         $prefixob .= " values ";
 
         $this -> prefixo = "$prefixo $prefixob";

    }


    private function sufixo(){ //precisa ser chamado sempre logo após os prefixo, no mesmo objeto que chamou o prefixo

        include("./config_db/config2.php");

        $tabela_origem =  $this -> tabela_origem;

        $tot = $this -> total_registros($tabela_origem);

        echo("<br>Consultando todos os $tot registros da tabela $tabela_origem. Seja paciente...");

        ob_flush();
        sleep(1);

        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        $campos_origem =  $this -> campos_origem;

        $conn_destino = $this -> conexao_destino;

        $query = "select $campos_origem from $tabela_origem "; 

        if ($limitar_cem == 1){ $query .= " limit 100"; } ///////<----------------para testar apenas 100 registros de cada tabela, mudar em config2.php a variável

        return $this -> dados($query);       

    }

    private function setPreparaOrigem(){
       

        $ano = $this -> ano;

        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;

        $db_destino = $this -> db;
        $host_destino = $this -> host;

        echo("<br>Base de origem: $odb em $ohost  Destino: $db_destino  em $host_destino ....");

        echo("<br>Preparando as tabelas de origem, apagando dados antigos e inativos....");

        //apaga na agenda vários campos, e nos animais e proprietários também

        $conn_origem = $this -> conexao_origem;

        $conn_origem -> query("delete from agenda where year(data) < $ano");

        $conn_origem -> query("delete from agenda where cod_animal=0");

        $conn_origem -> query("delete from agenda where tipo='Abertura/Caixa'");

        //pega o menor id da agenda, para poder apagar nas outras tabelas que não tem data

        $res = $conn_origem -> query("select min(codid_agenda) as minimo from agenda");

        while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $minimo = $linha["minimo"];

        }

        echo("<br>Menor id da agenda: $minimo.... Apagando e ajustando registros:");

        $conn_origem -> query("SET SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES'"); //cuida das datas por causa de incompatibilidade com o mysql antigo
        $conn_origem -> query("SET GLOBAL sql_mode = ''");

        $conn_origem -> query("delete from agenda where tipo like '%Dívida%'");
        $conn_origem -> query("delete from agenda where tipo='venda'");
        $conn_origem -> query("delete from agenda where status <= 0");
        $conn_origem -> query("delete from agenda where cod_animal = 0");
      
        $conn_origem -> query("delete from agenda where tipo='Dívida fatura'");
        $conn_origem -> query("delete from agenda where tipo='Crédito'");
        $conn_origem -> query("delete from agenda where tipo='Insere/estoque'");
        $conn_origem -> query("delete from agenda where tipo='Retira/estoque'");

        //$conn_origem -> query("delete from cadcli where status <= 0");
        //$conn_origem -> query("delete from animal where status <= 0");

        $conn_origem -> query("delete from acconsultas where cod_agenda < $minimo");  
        $conn_origem -> query("delete from acinternacao where cod_agenda < $minimo"); 
        $conn_origem -> query("delete from rescirurgia where cod_agenda < $minimo"); 
        $conn_origem -> query("delete from resdiversos where cod_agenda < $minimo"); 
        $conn_origem -> query("delete from resestetica where cod_agenda < $minimo"); 
        $conn_origem -> query("delete from resexames where cod_agenda < $minimo"); 
        $conn_origem -> query("delete from resinternacao where cod_agenda < $minimo"); 
        $conn_origem -> query("delete from resvacina where cod_agenda < $minimo"); 

        $conn_origem -> query("update agenda set data_pgto='1900-01-01' where (data_pgto='0000-00-00' OR data_pgto='' OR data_pgto is null)");
		$conn_origem -> query("update agenda set vencimento='1900-01-01' where (vencimento='0000-00-00' OR vencimento='' OR vencimento is null)");

        $conn_origem -> query("alter table agenda change column descricao descricao text"); 
        $conn_origem -> query("update agenda set descricao=concat(descricao,' Conversão:',desc_interna2) where descricao not like '%Conversão:%' and descricao <> desc_interna2"); 

        $conn_origem -> query("alter table agenda change column telebusca telebusca varchar(50)");
        $conn_origem -> query("update agenda set telebusca='Buscar/Levar' where telebusca='Buscar e Levar'");

        $conn_origem -> query("update agenda set situacao='Atendido' where situacao='Retornou'");

        $conn_origem -> query("delete from fornecedor where status <=0"); 

        $conn_origem -> query("alter table fornecedor change column laboratorio laboratorio varchar(10)"); 
        $conn_origem -> query("update fornecedor set laboratorio='1' where laboratorio ='Sim'");
        $conn_origem -> query("update fornecedor set laboratorio='2' where laboratorio !='1'");

        $conn_origem -> query("update fornecedor set celular='' where celular like '%a%'");  
        $conn_origem -> query("update fornecedor set celular='' where celular like '%e%'");  
        $conn_origem -> query("update fornecedor set celular='' where celular like '%i%'");  
        $conn_origem -> query("update fornecedor set celular='' where celular like '%o%'");  
        $conn_origem -> query("update fornecedor set celular='' where celular like '%u%'");  
        
        $conn_origem -> query("update fornecedor set celular='' where LENGTH(celular) > 11");   
        $conn_origem -> query("update fornecedor set cep='' where LENGTH(cep) > 11");  

    }


    private function setPreparaDestino(){

        echo("<br>Preparando as tabelas de destino, apagando dados antigos e inativos....");

        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;
        
        //apaga vários campos que possam estar na tabela de destino

        $conn_destino = $this -> conexao_destino;

        $conn_destino -> query("delete from agendas");        
        $conn_destino -> query("delete from proprietarios");
        $conn_destino -> query("delete from animais");
        $conn_destino -> query("delete from racas");
        $conn_destino -> query("delete from especies");
        $conn_destino -> query("delete from pelagens");
        $conn_destino -> query("delete from faturas");
        $conn_destino -> query("delete from pagamentos");
        $conn_destino -> query ("alter table servicos add column id_old int");    

        $conn_destino -> query("alter table agendas change column obs obs text");
        $conn_destino -> query("alter table fornecedores change column cep cep varchar(15)");

    }

    private function id_origem($tabela_origem){

        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;

        //pego o campo id da origem       

        $conn_origem = $this -> conexao_origem;

        $query_key = "show columns from $tabela_origem where `Key` = 'PRI'";

        $conn_origem -> query($query_key);
    
        $res_key = $conn_origem -> query($query_key);

        while ($linha_key = $res_key -> fetch(PDO::FETCH_BOTH)){
    
            $chave = $linha_key[0];   

        }

        return $chave;
    }

    private function tem_query($i,$valor_id){

            $db = $this -> db;
            $host = $this -> host;
            $port = $this -> port;
            $user = $this -> user;
            $password = $this -> password;

            //o de origem
            $odb = $this -> odb;
            $ohost = $this -> ohost;
            $oport = $this -> oport;
            $ouser = $this -> ouser;
            $opassword = $this -> opassword;

            $tabela_origem = $this -> tabela_origem;

            $valor = "";

             //verifica se é para mudar o valor por uma query
            

             $conn2 = $this -> conexao_destino;

             $query2 = "select query from conversao where tabela_origem = \"$tabela_origem\" limit $i,1";

             

             $res2 = $conn2 -> query($query2);

             while ($linha2 = $res2 -> fetch(PDO::FETCH_BOTH)){
             
                 $query_subs = $linha2["query"];   

             }     

             //se é para mudar o valor por uma query, coloca o campo valor (sempre vai ser valor) no lugar do original
             if ($query_subs != "" && strlen($query_subs) > 10 ){



                 $query_subs = str_replace("id_origem","$valor_id",$query_subs); //muda a string id_origem (caso exista) para o valor do id de origem 

                 

                 $conn_subs = $this -> conexao_origem;

                echo($query_subs);

                 $res_subs = $conn_subs -> query($query_subs);

                 while ($linha_subs = $res_subs -> fetch(PDO::FETCH_BOTH)){
             
                     $valor = $linha_subs["valor"];   
 
                 }   

             }

             return $valor;

    }


    private function saida_usuario($texto,$perc){


          flush();
          sleep(0.02);

          echo("<script>
          
          window.parent.document.getElementById('status').innerText = \"$texto\";

          window.parent.document.getElementById('dv_progresso').style.width = \"$perc%\";           

          </script>");

          //fim da saída para o usuáio

    }


    private function dados($query){ //chamado pelo sufixo
        //o de origem

       

        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;


        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        

        $conn_origem = $this -> conexao_origem;

        $dados = "";

        $res_origem = $conn_origem -> query($query); //geralmente select campo1,campo2 from tabela_origem, ou seja, vai pegar todos os dados que vieram lá do sufixo

        $ncolunas = $res_origem -> columnCount();

        $nregistros = $res_origem -> rowCount();

        $tabela_origem = $this -> tabela_origem;

        $tabela_destino = $this -> tabela_destino;

        $id_origem = $this -> id_origem($tabela_origem);

        $prefixo = $this -> prefixo ;

        $miolo = "";

        $cont = 0;

        while ($linha = $res_origem -> fetch(PDO::FETCH_BOTH)){

            $miolo_linha = "(";

            for ($i = 0; $i < $ncolunas;$i++){ 

                $valor = $linha[$i];               

                $valor_id = $linha["$id_origem"];
                 
                $valor_query = $this -> tem_query($i,$valor_id);

                if ($valor_query != ""){

                    $valor = $valor_query;

                }

                $valor = str_replace('"',"'",$valor); 
                $valor = str_replace('0000-00-00','null',$valor);                 
                $valor = preg_replace( "/\r|\n/", "", $valor);
                $valor = str_replace('\\','',$valor);                 


                if ($valor != "null"){

                    $miolo_linha.= "\"" . $valor ."\",";

                }else{

                    $miolo_linha.= $valor.",";    

                }

            }


           
          //saída para o usuário, esta parte faz com que demore mais de 4 vezes mais
          $cont ++;

          $perc = $cont * 100 / $nregistros;

          $perc = round($perc,1);

          $texto = "$perc"."%";          

          $this -> saida_usuario($texto,$perc);

            $miolo_linha = str_replace("\"\"","null",$miolo_linha);                       

            $miolo_linha.="),\n";
            $miolo_linha = str_replace(",),","),",$miolo_linha);

            $linha_a_linha = $this -> __get("linha_a_linha");

            if ($linha_a_linha ==1){

                $miolo_linha = $prefixo . $miolo_linha; 
                $miolo .= $miolo_linha .";";

            }else{

                if ($cont == 1){

                    $miolo_linha = $prefixo . $miolo_linha; 

                }

                $miolo .= $miolo_linha;

            }

        }

        $miolo = $miolo . ";";

        $miolo = str_replace(",;",";",$miolo);
       
        $miolo = str_replace("),\n;",");\n",$miolo);

        if ($tabela_destino != "servicos"){ //está no prepara_servicos e aqui apagaria os exames
            $miolo =" delete from $tabela_destino;".$miolo;  
        }     
       
        $this -> file_add($miolo);

    }

    private function converToPlain($texto){
        $texto = preg_replace('"{\*?\\\\.+(;})|\\s?\\\[A-Za-z0-9]+|\\s?{\\s?\\\[A-Za-z0-9‹]+\\s?|\\s?}\\s?"', '', $texto);
        return $texto;
    }


    private function tiraRtf($texto){


        $texto = $this -> converToPlain($texto);

        //$texto = mb_convert_encoding($texto,"utf-8","AUTO");       

        $texto = str_replace(" ","",$texto);

        return $texto;

    }


    private function file_add($texto){

        $texto = $this -> tiraRtf($texto);

        $odb = $this -> odb;

        $file_name = $odb.".sql";

        $arquivo = fopen($file_name,'a'); //a coloca o ponteiro no final, w no início, use a


        fwrite($arquivo,$texto);
        

        fclose($arquivo);

        $server = $_SERVER['SERVER_NAME'];
        
        $path = $_SERVER['SCRIPT_FILENAME'];
        $path_parts = pathinfo($path);

        $path_ex = str_replace("/",";",$path);

        $arr_path = explode(";",$path_ex);
    
        $folder = $arr_path[1];

        $server = $path_parts["dirname"];
        $path = "$server/$file_name";

        echo("Arquivo gerado com sucesso<br>");
        echo("Rode o seguinte comando mysql:<br>");
        echo("mysql -uNOMEDABASEDEDADOS -p -hmysql26-farm1.kinghost.net --binary-mode=1 --force --default-character-set=utf8 NOMEDABASEDEDADOS < $path");

    }

    private function retorna_cod_proprietario_origem($cod_animal){

         //o de origem
         $odb = $this -> odb;
         $ohost = $this -> ohost;
         $oport = $this -> oport;
         $ouser = $this -> ouser;
         $opassword = $this -> opassword;

         $conn_origem = $this -> conexao_origem;

         $query = "select cod_cli from animal where codid_animal = $cod_animal";

         $res = $conn_origem -> query($query);
       

         while ( $row = $res -> FETCH(PDO::FETCH_ASSOC) ){

            return $row["cod_cli"];

         } 
         
         return 0;




    }

    private function setConverteExame(){

        echo("Preparando a tabela exames... Aguarde");

        flush();
        sleep(0.01);

        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        //o de origem
        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;


         //cria a conexão de origem, pois o arquivo 0 tem um script com update resexames set resultado = concat('Resultado:',resultado,' Laudo: ',obs,' Interpretação: ',Interpret);         
         $conexao_origem = $this -> conexao_origem;

         $conexao_destino = $this -> conexao_destino;

          
         //seleciona todos os registros da tabela resexames (origem)
         $query = "select cod_agenda,resultado from resexames where resultado like '%laudo:%' and cod_agenda>0";

         //enquanto tiver aqui vai jogando na tabela agendas

         $res = $conexao_origem -> query($query);

         $tot = $res -> rowCount();
         $cont=0;

         while ($row = $res -> FETCH(PDO::FETCH_ASSOC)){

                $obs = $row["resultado"];
                $id_agenda = $row["cod_agenda"];

                //$query3 = "update agendas set obs = \"Obs: Não Informado\" where obs is null and cod_id=$id_agenda";
                //$conexao_destino -> query($query3);                

                //$query2 = "update agendas set obs = concat(obs,' ',\"$obs\") where cod_id=$id_agenda"; 
                //$conexao_destino -> query($query2); 

                $query4 = "update agendas_exame_outros set obs = \"$obs\" where cod_agendas=$id_agenda";                
                $conexao_destino -> query($query4);

                $cont++;           
                $perc = $cont * 100 / $tot;
    
                $this -> saida_usuario("Convertento exame $cont de $tot",$perc);

         }

         $this -> saida_usuario("Fim",100);

    }



    private function setConverteCadExame(){

        echo("Preparando a tabela de cad. de exames... Aguarde");

        flush();
        sleep(0.01);


          
           $conn_destino = $this -> conexao_destino;
           $conn_origem = $this -> conexao_origem;
       
           //seleciona todos os cadastros de exame na destino
            $query = "select cod_id,nome from servicos where tipo = 'Exame'";

            $res = $conn_destino -> query($query);

            $tot = $res -> rowCount(); 
            $cont_geral = 0;

            while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

                $id_servico = $linha["cod_id"];
                $nome = $linha["nome"];
               
                //procura para ver a integridade referencial dele na tabela servicos detalhes
                $query = "select cod_id from servicos_detalhes where cod_servicos = $id_servico";

                //echo("$query <br>");

               
                $res2 = $conn_destino -> query($query);

                $cont = $res2 -> rowCount();

                //echo("Achou? $cont  <br>");

                

                
                

                if ($cont == 0){ //se não tem insere

                      //procura o exame na tabela de origem
                      $query = "select codid_exame,nome,descricao,cod_laboratorio,espera,valor from exames where nome = \"$nome\"";

                    
                      $res3 = $conn_origem -> query($query);

                      while ($linha3 = $res3 -> FETCH(PDO::FETCH_ASSOC)){

                        $cod_servicos = $id_servico;
                        $cod_laboratorios = $linha3["cod_laboratorio"];
                        $dias = $linha3["espera"];

                          //insere na tabela servicos_detalhes no destino os dados
                          $query = "insert into servicos_detalhes(cod_id,cod_servicos,cod_fornecedores,cod_laboratorios,dias,obs) values (null,\"$cod_servicos\",0,\"$cod_laboratorios\",\"$dias\",\"conversão\")";

                          //echo("$query <br>");

                          $conn_destino -> query($query);

                      }  

                }

                $cont_geral++;           
                $perc = $cont_geral * 100 / $tot;
    
                $this -> saida_usuario("Convertento exames $cont_geral de $tot",$perc);

            }

            $this -> saida_usuario("Fim",100);

    }






    private function setConvertePeso(){

        echo("Preparando a tabela Peso... Aguarde");

        flush();
        sleep(0.01);

        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        //o de origem
        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;


         //cria a conexão de destino         
         $conn_destino = $this -> conexao_destino;

       
         //se preocupa com a tabela de origem         
         $conn_origem = $this -> conexao_origem;

         $prefixo_agendas = "insert into agendas(cod_id,cod_usuarios,cod_proprietarios,cod_animais,cod_fornecedores,status,data,hora,tipo,situacao,cod_servicos,externo,avisado,retorno,emergencia,grupo,telebusca,sessao,quantidade,valor,cod_faturas,fatura_comissao,obs,descricao) values ";
         
         $prefixo_agendas_consultas = "insert into agendas_consultas(cod_id,cod_agendas,cod_fotos,cod_patologias,status,temperatura,frequencia_cardiaca,frequencia_respiratoria,trc,hidratacao,peso,terapeutica,anamnese,obs) values ";

         $query_peso = "select * from peso where status = 1";
        
         $res = $conn_origem -> query($query_peso); 
         
         $cont = 0;

         $tot = $res -> rowCount();
                  

         while ( $row = $res -> FETCH(PDO::FETCH_ASSOC) ){


            $cod_id= $row["cod_id"];
            $cod_animal= $row["cod_animal"];
            $data= $row["data"];
            $peso= $row["peso"];
            $status= $row["status"];
            $cod_consulta= $row["cod_consulta"];
            $temperatura= $row["temperatura"];
            $f_cardiaca= $row["f_cardiaca"];
            $f_respiratoria= $row["f_respiratoria"];
            $trc= $row["trc"];
            $hidratacao= $row["hidratacao"];
            $obs= $row["obs"];
            $sincronizado= $row["sincronizado"];

            if ( strlen($temperatura) ==0 ) {$temperatura = "0";}
            if ( strlen($f_cardiaca) ==0 ) {$f_cardiaca = "0";}
            if ( strlen($f_respiratoria) ==0 ) {$f_respiratoria = "0";}
            if ( strlen($peso) ==0 ) {$peso = "0";}
            if ( strlen($trc) ==0 ) {$trc = "0";}
            if ( strlen($hidratacao) ==0 ) {$hidratacao = "0";}
            


            $cod_cli = $this -> retorna_cod_proprietario_origem($cod_animal);

            $sufixo_agendas = "(null,1000,\"$cod_cli\",\"$cod_animal\",\"0\",\"8\",\"$data\",\"00:00\",\"Exame/Físico\",\"Atendido\",\"0\",\"Não\",\"Sim\",\"Não\",\"Não\",\"0\",\"Não\",\"0\",\"1\",\"0\",\"0\",\"0\",\"Convertido\",\"\");";

            $query_agendas = $prefixo_agendas . $sufixo_agendas;
          

            $conn_destino -> query($query_agendas);  
            
          

            //$this -> file_add($query_agendas."\n");


            $novo_id_agendas = $conn_destino->lastInsertId();

           
            

            $sufixo_agendas_consultas = "(null,\"$novo_id_agendas\",\"1\",\"0\",\"8\",\"$temperatura\",\"$f_cardiaca\",\"$f_respiratoria\",\"$trc\",\"$hidratacao\",\"$peso\",\"\",\"\",\"conversor\");";
            
            $query_agendas_consultas = $prefixo_agendas_consultas . $sufixo_agendas_consultas;

            $conn_destino -> query($query_agendas_consultas);
            //$this -> file_add($query_agendas_consultas."\n");


            $cont++;           
            $perc = $cont * 100 / $tot;

            $this -> saida_usuario("Convertento peso $cont de $tot",$perc);

         }
         
         $this -> saida_usuario("Fim",100);

    }


    private function retornaIdServico($nome){

       $nome = trim($nome); 

       $conn_destino = $this -> conexao_destino;  
        
       $query = "select cod_id from servicos where nome=\"$nome\" limit 1";

       $res = $conn_destino -> query($query);
   
       while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){
            $id_servicos = $linha["cod_id"];
       } 

       return $id_servicos;

    }



    private function converteAgendamentos(){

      $conn_destino = $this -> conexao_destino; 

      $query="insert into servicos (nome) values ('consulta conversão')"; 

      $conn_destino -> query($query);

      $query="select cod_id,obs from agendas where tipo !='Exame/Físico' and tipo !='Compra' and cod_servicos=0 and situacao != 'Atendido' and data>=curdate() order by cod_id desc"; 

      $res = $conn_destino -> query($query);

      $tot = $res -> rowCount();

      $cont = 0;


      while ($row = $res -> FETCH(PDO::FETCH_ASSOC)){

            $obs = $row["obs"];
            $cod_id = $row["cod_id"];
            

            if ( substr_count($obs,"/") ){

               $so_servico = substr($obs,11);            
                                   
            }else{

                $so_servico = "consulta conversão";
            }

            $id_servico = $this -> retornaIdServico($so_servico);

            $query="update agendas set cod_servicos = $id_servico,situacao=\"Marcado\" where cod_id = $cod_id"; 

            $conn_destino -> query($query);

            echo("Executando: $query (Huggies)<br>");
           

            $cont++;           
            $perc = $cont * 100 / $tot;

            $this -> saida_usuario("Convertento agendamentos $cont de $tot",$perc);
        }

      $this -> saida_usuario("Fim",100);

    }



    private function converte_esteticas(){


        echo("Preparando a tabela ESTÉTICA... Aguarde");

        flush();
        sleep(0.01);


        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        //o de origem
        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;


         //cria a conexão de destino   
         
        
         $conn_destino = $this -> conexao_destino;

       
         //se preocupa com a tabela de origem
         
         $conn_origem = $this -> conexao_origem;

         $conn_origem -> query("alter table resestetica MODIFY pelo varchar(10)");
         $conn_origem -> query("update resestetica set pelo='2' where pelo='Pequeno'");
         $conn_origem -> query("update resestetica set pelo='2' where pelo='curto'");
         $conn_origem -> query("update resestetica set pelo='3' where pelo='Médio'");
         $conn_origem -> query("update resestetica set pelo='4' where pelo='Grande'"); 
         $conn_origem -> query("update resestetica set pelo='4' where pelo='longo'"); 
         $conn_origem -> query("update resestetica set pelo='5' where pelo='Gigante'"); 



         $conn_destino -> query ("delete from servicos"); //aqui apago todos, nos outros somente o específico

         $prefixo_servico = "insert into servicos (cod_id,nome,preco,tipo,id_old) values ";

         $cont = 0;

         $linha_a_linha = $this -> __get("linha_a_linha");

         //enaquanto tiver valores para passar da tabela estetica para a tabela servicos vai fazer

         $query = "select codid_estetica,nome,valpeqcurto from estetica where status>0";
  
         $res = $conn_origem -> query($query);  

          while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $cont++;

            $nome = $linha["nome"];

            $preco = $linha["valpeqcurto"];

            $id_antigo = $linha["codid_estetica"];

            //insere o valor na tabela servicos do infovet web

            $query = $prefixo_servico . " (null,\"$nome\",\"$preco\",\"Estética\",\"$id_antigo\")";

            $conn_destino -> query ($query);

            echo($query);

          }

          //agora que tem as esteticas inseridas na base web junto com os ids antigos ele pega na base de origem, o resesteticas e já muda os ids para quando for jogar no agendas_esteticas jogar correto

          $res = $conn_destino -> query ("select cod_id,id_old from servicos where tipo='Estética'");

          while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $id_novo = $linha["cod_id"];
            $id_antigo = $linha["id_old"];

            $conn_origem -> query("update resestetica set cod_estetica=$id_novo where cod_estetica=$id_antigo");

          }
          
          //agora a resesteticas tem o id correto, preciso jogar na agenda , tudo na origem - cuidado
       
         $query = "select cod_estetica,cod_agenda from resestetica where status>0";
  
         $res = $conn_origem -> query($query);

         while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $cod_estetica = $linha["cod_estetica"];
            $cod_agenda = $linha["cod_agenda"];

            $query ="update agenda set cod_servico=\"$cod_estetica\" where codid_agenda=\"$cod_agenda\"";            

            $conn_origem -> query($query);

          }

    }


    private function converte_exames(){

        echo("Preparando a tabela EXAMES... Aguarde");

        flush();
        sleep(0.01);

        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

         //o de origem
         $odb = $this -> odb;
         $ohost = $this -> ohost;
         $oport = $this -> oport;
         $ouser = $this -> ouser;
         $opassword = $this -> opassword;

         //cria a conexão de destino                    
         $conn_destino = $this -> conexao_destino;     
        
         //insere o exame conversao
         $conn_destino -> query ("insert into servicos (cod_id,nome,preco,tipo) values (null,\"conversao\",\"0\",\"Exame\")");
      

         $id_exame = $conn_destino -> lastInsertId();

         //na tabela origem já muda todos os exames para o exame conversão     
         
         $conn_origem = $this -> conexao_origem;
  
         $query = "update agenda set cod_servico=\"$id_exame\" where tipo = \"Exame\"";

         $conn_origem -> query ($query);         
         
    }     


    private function converte_diversos(){


        echo("Preparando a tabela DIVERSOS... Aguarde");

        flush();
        sleep(0.01);


        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        //o de origem
        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;


         //cria a conexão de destino   
        
        
         $conn_destino = $this -> conexao_destino;

       
         //se preocupa com a tabela de origem
        

         $conn_origem = $this -> conexao_origem;
  
         $query = "select codid_procedimento,nome,valor from procedimentos where status>0";
  
         $res = $conn_origem -> query($query);

         $conn_destino -> query ("delete from servicos where tipo='Diversos'");         

         //enaquanto tiver valores para passar da tabela procedimentos para a tabela servicos vai fazer (não pode ser o antigo pois agora os serviços estão todos na mesma tabela e pode dar conflito
         //vou então inserir todos os diversos na tabela serviços, mantendo o id original deles no campo id_old, depois mudo na resdiversos para o id novo
          while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $nome = $linha["nome"];

            $preco = $linha["valor"];

            $id_antigo = $linha["codid_procedimento"];

            //insere o valor na tabela servicos do infovet web

            $query = "insert into servicos (cod_id,nome,preco,tipo,id_old) values (null,\"$nome\",\"$preco\",\"Diversos\",\"$id_antigo\")";

            $conn_destino -> query ($query);

          }

          //agora que tem os diversos inseridos na base web  (tabela servicos) junto com os ids antigos ele pega na base de origem, o resdiversos e já muda os ids para quando for jogar no agendas_esteticas jogar correto

          $res = $conn_destino -> query ("select cod_id,id_old from servicos where tipo='Diversos'");

          while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $id_novo = $linha["cod_id"];
            $id_antigo = $linha["id_old"];

            $conn_origem -> query("update resdiversos set cod_proc=$id_novo where cod_proc=$id_antigo");

          }
          
          //agora a resdiversos tem o id correto, preciso jogar na agenda , tudo na origem - cuidado
       
         $query = "select cod_proc,cod_agenda,obs from resdiversos where status>0";
  
         $res = $conn_origem -> query($query);

         while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $cod_diverso = $linha["cod_proc"];
            $cod_agenda = $linha["cod_agenda"];
            $obs = $linha["obs"];

            $obs = str_replace('"','',$obs);

            $query ="update agenda set cod_servico=\"$cod_diverso\",descricao=concat(descricao,' -> ',\"$obs\") where codid_agenda=\"$cod_agenda\"";             
            
            $conn_origem -> query($query);

          }

    }

    private function converte_cirurgia(){

        echo("<br>Preparando a tabela cirurgia... Aguarde");

        flush();
        sleep(0.01);


        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        //o de origem
        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;


         //cria a conexão de destino          
        
         $conn_destino = $this -> conexao_destino;

         $conn_destino -> query ("delete from servicos where tipo='Cirurgia'");       

       
         //se preocupa com a tabela de origem        

         $conn_origem = $this -> conexao_origem;
  
         $query = "select codid_cirurgia,nome,valor from cirurgia where status>0";
  
         $res = $conn_origem -> query($query);
                 

         //enaquanto tiver valores para passar da tabela cirurgia para a tabela servicos vai fazer
          while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $nome = $linha["nome"];

            $preco = $linha["valor"];

            $id_antigo = $linha["codid_cirurgia"];

            //insere o valor na tabela servicos do infovet web

            $query = "insert into servicos (cod_id,nome,preco,tipo,id_old) values (null,\"$nome\",\"$preco\",\"Cirurgia\",\"$id_antigo\")";

            echo($query."<br>");

            $conn_destino -> query ($query);

          }

          //agora que tem os cirurgias inseridos na base web  (tabela servicos) junto com os ids antigos ele corrige no rescirurgia o id das cirurgias pelos novos ids

          $res = $conn_destino -> query ("select cod_id,id_old from servicos where tipo='Cirurgia'");

          while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $id_novo = $linha["cod_id"];
            $id_antigo = $linha["id_old"];

            $conn_origem -> query("update rescirurgia set CODCIRUR=$id_novo where CODCIRUR=$id_antigo");

          }
          
          //agora a resesteticas tem o id correto, preciso jogar na agenda , tudo na origem - cuidado
       
         $query = "select CODCIRUR,cod_agenda from rescirurgia where status>0";
  
         $res = $conn_origem -> query($query);

         while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $cod_estetica = $linha["CODCIRUR"];
            $cod_agenda = $linha["cod_agenda"];

            $query ="update agenda set cod_servico=\"$cod_estetica\" where codid_agenda=\"$cod_agenda\"";            


            $conn_origem -> query($query);

          }

    }
    //vacina ok depois de rodar o depois.bck
    
    private function SetTabelaServicos(){

        $db = $this -> db;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;

        //o de origem
        $odb = $this -> odb;
        $ohost = $this -> ohost;
        $oport = $this -> oport;
        $ouser = $this -> ouser;
        $opassword = $this -> opassword;
        
        
        $prefixo = "insert into servicos(cod_id,cod_fotos,cod_centros_custos,status,nome,preco,comissao,tipo,cod_valores) values ";

       

        $conn = $this -> conexao_destino;

        $res = $conn -> query("select * from servicos");

        $core = "";

        $all = "";

        $cont = 0;

        while ($linha = $res -> FETCH(PDO::FETCH_ASSOC)){

            $cont ++;

            $cod_id = $linha["cod_id"]; 
            $cod_fotos = $linha["cod_fotos"]; 
            $cod_centros_custos = $linha["cod_centros_custos"]; 
            $status = $linha["status"]; 
            $nome = $linha["nome"]; 
            $preco = $linha["preco"]; 
            $comissao = $linha["comissao"]; 
            $tipo = $linha["tipo"]; 
            $cod_valores = $linha["cod_valores"];

            $linha_a_linha = $this -> __get("linha_a_linha");

            if ($linha_a_linha == 1){

                $core = "(\"$cod_id\",\"$cod_fotos\",\"$cod_centros_custos\",\"$status\",\"$nome\",\"$preco\",\"$comissao\",\"$tipo\",\"$cod_valores\");";

                $core = str_replace('""',"null",$core);

                $all .= $prefixo . $core."\n";
                
            }else{

                $core = "(\"$cod_id\",\"$cod_fotos\",\"$cod_centros_custos\",\"$status\",\"$nome\",\"$preco\",\"$comissao\",\"$tipo\",\"$cod_valores\"),";

                $core = str_replace('""',"null",$core);

                if ($cont==1){

                    $all .= $prefixo . $core."\n";

                }else{

                    $all .= $core."\n";

                }

            }

        }

        $all .= ";";
        $all = str_replace(","."\n".";",";",$all);
        $all .="\n";

        $this -> file_add($all);

    }

    private function SetPreparaServicos(){

        $this -> file_add("delete from servicos;"); 
        $this -> converte_esteticas();
        $this -> converte_exames();
        $this -> converte_diversos();
        $this -> converte_cirurgia();
        $this -> setConvertePeso();

    }

    private function setPreparaUnidades(){

        //se preocupa com a tabela de origem       
 
        $conn_origem = $this -> conexao_origem;

        $db = $this -> odb;         

        $conn_origem -> query("alter table estoque change column data data varchar(15)");
        $conn_origem -> query("update estoque set data=null where data='0000-00-00'");

        $conn_origem -> query("alter table estoque change column unidade unidade varchar(30)");
        $conn_origem -> query("update estoque set unidade = '1' where unidade='unidade'");
        $conn_origem -> query("update estoque set unidade = '2' where unidade='GR'");
        $conn_origem -> query("update estoque set unidade = '3' where unidade='KG'");
        $conn_origem -> query("update estoque set unidade = '3' where unidade='Kilo'");
        $conn_origem -> query("update estoque set unidade = '4' where unidade='Litro'");
        $conn_origem -> query("update estoque set unidade = '5' where unidade='MT'");
        $conn_origem -> query("update estoque set unidade = '5' where unidade='Metro'");
        $conn_origem -> query("update estoque set unidade = '6' where unidade='CM'");
        $conn_origem -> query("update estoque set unidade = '7' where unidade='Comprimido'");
        $conn_origem -> query("update estoque set unidade = '8' where unidade='ML'");

        $conn_origem -> query("update estoque set embalagem = '1' where embalagem='Caixa'");
        $conn_origem -> query("update estoque set embalagem = '2' where embalagem='Lata'");
        $conn_origem -> query("update estoque set embalagem = '3' where embalagem='Blister'");
        $conn_origem -> query("update estoque set embalagem = '4' where embalagem='Fardo'");
        $conn_origem -> query("update estoque set embalagem = '5' where embalagem='Saco'");

        $conn_origem -> query("update estoque set embalagem = '7' where embalagem='Pote'");
        $conn_origem -> query("update estoque set embalagem = '8' where embalagem='Cartela'");
        $conn_origem -> query("update estoque set embalagem = '9' where embalagem='Ampola'");
        $conn_origem -> query("update estoque set embalagem = '10' where embalagem='Peça'");
        $conn_origem -> query("update estoque set embalagem = '11' where embalagem='Bisnaga'");
        $conn_origem -> query("update estoque set embalagem = '12' where embalagem='Envelope'");
        $conn_origem -> query("update estoque set embalagem = '13' where embalagem='Sachet'");
        $conn_origem -> query("update estoque set embalagem = '14' where embalagem='Frasco'");             
        
        $conn_origem -> query("alter table estoque change column etica etica varchar(30)");
        $conn_origem -> query("update estoque set etica = '0' where etica <> 'Sim'");
        $conn_origem -> query("update estoque set etica = '1' where etica='Sim'");

        $conn_origem -> query("update estoque set qtd_fardo =1 where qtd_fardo=0");
        $conn_origem -> query("update estoque set qtd_fardo =1 where qtd_fardo=null");
        

        $conn_origem -> query("update estoque set qtdembala =1 where qtdembala=0");
        $conn_origem -> query("update estoque set qtdembala =1 where qtdembala=null");






        $conn_origem -> query("alter table estoque change column data data date");


    }



    private function setIdVacinaMarcada(){

        $conn_destino = $this -> conexao_destino;

        $query = "insert into estoques (cod_id,nome,quantidade) values (null,'Conversão',1)";

        $res = $conn_destino -> query($query);   

        $id_estoque_conversao = $conn_destino -> lastInsertId();


        $conn_destino = $this -> conexao_destino;

        $conn_destino -> query("alter table agendas_exame_outros change column obs obs text");

      //vacinas marcadas o cod_servico no infovet antigo era zero, o nome da vacina estava na descrição e aqui foi pra obs da agenda. Tenho que corrigir isso
      $query = "select obs,cod_id from agendas where tipo='Vacina' and cod_servicos=0";

      $res = $conn_destino -> query($query);
  
      while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){
  
          $nome_vacina = $linha["obs"];
          $id_agenda = $linha["cod_id"];

          //insere na tabela agendas_vacinas pois marcados não tem pois não tinha como saber o id da vacina

          //procura no estoque pelo id da vacina através do nome (aff)
          $query2 = "select cod_id from estoques where nome=\"$nome_vacina\"";

            $res2 = $conn_destino -> query($query2);

            $achou = 0;
        
            while ($linha2 = $res2 -> fetch(PDO::FETCH_ASSOC)){

                $achou = 1;

                $id_vacina = $linha2["cod_id"];

                $query3="update agendas set cod_servicos=$id_vacina where cod_id=$id_agenda";

                $query4 = "insert into agendas_vacinas (cod_id,cod_agendas,cod_estoques,obs) values (null,$id_agenda,$id_vacina,\"$nome_vacina\")";

                $conn_destino -> query($query3);

                $conn_destino -> query($query4);



            }  
            
            if ($achou == 0){

                $query5 = "insert into agendas_vacinas (cod_id,cod_agendas,cod_estoques,obs) values (null,$id_agenda,$id_estoque_conversao,\"$nome_vacina\")";

                $conn_destino -> query($query5);

            }



       } 
       
       
       //se alterou o nome da vacina nao vai achar, ai coloca como conversao
       $query = "select cod_id from servicos where nome='conversao'";

       $res = $conn_destino -> query($query);
   
       while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){
            $id_servicos = $linha["cod_id"];
       } 


       $query = "update agendas set cod_servicos = $id_servicos where cod_servicos=0 and tipo='vacina'";

       $res = $conn_destino -> query($query);

    }

    private function setVacinaServico(){ //na conversão o cod_servicos das vacinas é o id delas no estoque, aqui transformo no id da tabela servicos

        $this -> setIdVacinaMarcada();

     //primeiro jogo todas as vacinas do estoque na tabela servicos
        $conn_destino = $this -> conexao_destino;

        $query = "select nome,cod_id,preco from estoques where tipo='Vacina'";

        $res = $conn_destino -> query($query);

        while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){

            $nome_vacina = $linha["nome"];
            $id_vacina = $linha["cod_id"];
            $preco = $linha["preco"];

            if ($preco == ''){

                $preco=0;

            }

            $query = "INSERT INTO servicos (cod_id, cod_fotos, cod_centros_custos, status, nome, preco, comissao, tipo, cod_valores) VALUES ";
            $query.="(null,1, 2, 1,\"$nome_vacina\", $preco , NULL,'Vacina', NULL);";

            $conn_destino -> query($query);

    //depois de jogar a vacina na tabela de servicos preciso agora mudar o id na agenda que era o cod_id no estoque para o cod_id na tabela servicos        
            $id_servico = $conn_destino -> lastInsertId();

            $query2="update agendas set cod_servicos = $id_servico where tipo='Vacina' and cod_servicos=$id_vacina";

            $conn_destino -> query($query2);

        }   
        
        
  

        $this -> saida_usuario("Fim",100);

    }


    private function setUsuariosPermissoes(){

        $conn_destino = $this -> conexao_destino;

        $conn_destino -> query("delete from usuarios_permissoes");

        $qr_adm = "INSERT INTO usuarios_permissoes (cod_id, cod_usuarios, status, geral_imprime, geral_exclui, geral_altera, geral_inclui, geral_cartas, geral_estorna, geral_filiais, cadastro_animal, cadastro_clinico, cadastro_medicamento, cadastro_estetica, cadastro_estoque, cadastro_proprietario, cadastro_fornecedor, cadastro_ccusto, cadastro_funcionario, cadastro_texto, acompanhamento_menu, acompanhamento_agenda_retorno, acompanhamento_medicamento, acompanhamento_internacao, acompanhamento_exame, acompanhamento_estetica, acompanhamento_agenda_clinica, hospedagem_menu, hospedagem_reserva, estetica_menu, petshop_menu, petshop_lista_preco, petshop_estoque, petshop_compra, petshop_venda, petshop_controle_venda, petshop_entrada_saida, financeiro_menu, financeiro_servico_ncobrado, financeiro_servico_cobrado, financeiro_movimento, financeiro_salario, financeiro_relatorio, financeiro_centro_custo, financeiro_atividade, financeiro_caixa, financeiro_fatura) VALUES(10000,10000, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1)";

        $conn_destino -> query($qr_adm);

        $conn_destino -> query("update usuarios set administrador=1 where cod_id=10000");

        $res = $conn_destino -> query("select cod_id from usuarios where cod_id != 10000");

        while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){

            $id_usuario = $linha["cod_id"];

            $query="INSERT INTO usuarios_permissoes (cod_id, cod_usuarios, status, geral_imprime, geral_exclui, geral_altera, geral_inclui, geral_cartas, geral_estorna, geral_filiais, cadastro_animal, cadastro_clinico, cadastro_medicamento, cadastro_estetica, cadastro_estoque, cadastro_proprietario, cadastro_fornecedor, cadastro_ccusto, cadastro_funcionario, cadastro_texto, acompanhamento_menu, acompanhamento_agenda_retorno, acompanhamento_medicamento, acompanhamento_internacao, acompanhamento_exame, acompanhamento_estetica, acompanhamento_agenda_clinica, hospedagem_menu, hospedagem_reserva, estetica_menu, petshop_menu, petshop_lista_preco, petshop_estoque, petshop_compra, petshop_venda, petshop_controle_venda, petshop_entrada_saida, financeiro_menu, financeiro_servico_ncobrado, financeiro_servico_cobrado, financeiro_movimento, financeiro_salario, financeiro_relatorio, financeiro_centro_custo, financeiro_atividade, financeiro_caixa, financeiro_fatura) VALUES";
            $query.=" ($id_usuario,$id_usuario, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1);";
          
            $conn_destino -> query($query);
        }    

            $this -> setVacinaServico();

    }


    private function setEstoqueQuantidade(){

        $conn_destino = $this -> conexao_destino;

        $res = $conn_destino -> query("select count(cod_id) as tot from estoques where quantidade>0");

        while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){
          
            $tot = $linha["tot"];
        }    


         
        //pega todos os produtos que tem no estoque com quantidade maior do que zero
        $res = $conn_destino -> query("select cod_id,quantidade from estoques where quantidade>0");

        $cont = 0;

        $texto="";

        //enquanto tiver produtos, insere na agenda
        while ($linha = $res -> fetch(PDO::FETCH_ASSOC)){

            $cod_estoques = $linha["cod_id"];
            $quantidade = $linha["quantidade"];
            
            //insere na tabela vendas
            $query="INSERT INTO vendas (cod_id, cod_estoques, cod_usuarios, cod_sessao, status, grupo, tipo, `data`, hora, quantidade, total) VALUES ";
            $query.="(null,$cod_estoques, 0, 20178, 2, 0,'Compra', curdate(),curtime(),$quantidade,0)";

            $conn_destino -> query($query);
            
            $id_vendas = $conn_destino -> lastInsertId();

            
            
            //insere na agenda
            $query = "INSERT INTO agendas (cod_id, cod_usuarios, cod_proprietarios, cod_animais, cod_fornecedores, status, data, hora, tipo, situacao, cod_servicos, externo, avisado, retorno, emergencia, grupo, telebusca, sessao, quantidade, valor, cod_faturas, fatura_comissao, obs) VALUES ";
            $query.="(null,10000, 1, 1, 1, 6,curdate(),curtime(),'Compra', 'Atendido', $id_vendas, 'Não', 'Sim', 'Não', 'Não',null, 'Não',0, $quantidade, 0.00, -6, 0, 'Ajuste Conversão');";

            $conn_destino -> query($query);
            
            $id_agendas = $conn_destino -> lastInsertId();

            $conn_destino -> query ("update agendas set cod_servicos = $id_agendas where cod_id = $id_agendas");
            $conn_destino -> query ("update agendas set grupo = $id_agendas where cod_id = $id_agendas");
            $conn_destino -> query ("update vendas set grupo = $id_agendas where cod_id = $id_vendas");

            

            $texto = "$cont/$tot";
            
            $this -> saida_usuario("Runing_$texto",0);

            $cont++;


        }

        $this -> setUsuariosPermissoes();

       

        //insere na tabela vendas

    }



    private function setPreparaPortes(){

        $conn_origem = $this -> conexao_origem;        

        $conn_origem -> query("alter table animal change column datacad datacad date"); //tira o default 0000-00-00 do campo

        $conn_origem -> query("update animal set datacad=null where datacad='0000-00-00'"); 
        $conn_origem -> query("update animal set obito=null where obito='0000-00-00'");
        $conn_origem -> query("update animal set datanasc=null where datanasc='0000-00-00'");

    
        $conn_origem -> query("alter table animal change column porte porte varchar(15)");
        $conn_origem -> query("update animal set porte='2' where porte='Pequeno'");
        $conn_origem -> query("update animal set porte='3' where porte='Médio'");
        $conn_origem -> query("update animal set porte='4' where porte='Grande'");
        $conn_origem -> query("update animal set porte='5' where porte='Gigante'");
      
    }    



    public function getPrefixo($tabela){


        return $this -> prefixo($tabela);
        

    }

    public function getSufixo(){


        return $this -> sufixo();
        

    }

    public function getDados(){


        return $this -> dados();


    }

    public function getPreparaOrigem(){

        $this -> setPreparaOrigem();

    }

    public function getPrepara(){

        $this -> setPreparaDestino();
        $this -> setPreparaOrigem();       
        $this -> setPreparaUnidades();
        $this -> setPreparaPortes();
        $this -> setPreparaServicos();    
        $this -> SetTabelaServicos();

    }

    public function getEstoqueQuantidade(){

        $this -> setEstoqueQuantidade();

    }   
    
    public function getConvertePeso(){

        $this -> setConvertePeso();

    }

    public function getConverteExame(){

        $this -> setConverteExame();

    }


    public function getConverteCadExame(){

        $this -> setConverteCadExame();

    }

    public function getConverteAgendamentos(){

        $this -> ConverteAgendamentos();

    }



}    

?>