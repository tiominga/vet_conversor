<script>
       

        function preenche(div,html){

           
            window.parent.document.getElementById(div).innerHTML = html;

        }

        function carrega(div,arquivo){
           
                   
                    window.parent.document.getElementById(div).src = arquivo;  


        }

        function sql_confirma(){


          window.parent.document.getElementById('ta_query').value = window.parent.document.getElementById('dv_sql_query').innerText;

          let form = window.parent.document.getElementById('f_query'); //no index
          form.submit();

          window.parent.document.getElementById('dv_sql').style.display ="none";  



        }

        function sql_abre(id){

            window.parent.document.getElementById('dv_sql').style.display ="block"; 

            window.parent.document.getElementById('ed_query_id').value = id;

            i_frame = window.parent.document.getElementById('if_sql');
            i_frame.src = 'sql_mostra.php?id='+id;


        }

        

        function sql_executa(query,confirma){

                        
            if (confirma != 0){

                if(confirm(confirma)){
                window.parent.document.getElementById('if_query').src = 'querys.php?query='+query;  

                }    
            }
            else{

                window.parent.document.getElementById('if_query').src = 'querys.php?query='+query; 
            }

            


        }
       

        function guarda(componente,valor){

           
            window.parent.document.getElementById(componente).value = valor;

        }

        function run(){

            if (confirm("Apague todos os arquivos .sql antes de continuar...")){

                window.parent.document.getElementById('dv_result').style.display = "block";
                window.parent.document.getElementById('if_run').src = 'run.php';  

            }


        }

        function exame_integridade(){

            if ( confirm("(Exames) Você já rodou o passo 6 e usou o mysql. A base convertida já está rodando no banco de dados antes de continuar.") ){

                window.parent.document.getElementById('dv_result').style.display = "block";
                window.parent.document.getElementById('if_run').src = 'exame_integridade.php';  

            }



        }


        function exame_cadastro_integridade(){

            if ( confirm("(Cadastro de Exames) Você já rodou o passo 6 e usou o mysql. A base convertida já está rodando no banco de dados antes de continuar.") ){

                window.parent.document.getElementById('dv_result').style.display = "block";
                window.parent.document.getElementById('if_run').src = 'exame_cadastro_integridade.php';  

            }

        }


        function peso_integridade(){

            if ( confirm("(peso) Você já rodou o passo 6 e usou o mysql. A base convertida já está rodando no banco de dados antes de continuar.") ){

                window.parent.document.getElementById('dv_result').style.display = "block";
                window.parent.document.getElementById('if_run').src = 'peso_integridade.php';  

            }



        }


        function run_quantidade(){

            if (confirm("Você já rodou o passo 6 e usou o mysql. A base convertida já está rodando no banco de daodos.Deve também ter atualizado as quantidades no infovet antigo antes de continuar.")){
                i_frame = window.parent.document.getElementById('if_run');
                window.parent.document.getElementById('dv_result').style.display = "block";
                i_frame.src = 'estoque_ajusta.php';                
            }


        }


        function insere_relacionamento(){

                let i_tabela = window.parent.document.getElementById('ed_i_tabelas').value;
                let i_campo = window.parent.document.getElementById('ed_i_campos').value;
                let v_tabela = window.parent.document.getElementById('ed_v_tabelas').value;
                let v_campo = window.parent.document.getElementById('ed_v_campos').value;

               

                if (i_tabela.length > 2 && i_campo.length > 2 && v_tabela.length > 2 && v_campo.length > 2){
                


                    let inserir = 'insert into conversao values (null,"'+v_tabela+'","'+v_campo+'","'+i_tabela+'","'+i_campo+'",0)';

                    sql_executa(inserir,0);
                }
                else{


                    alert('Todos os campos precisam estar preenchidos.');


                }

        }

        function agendamentos(){

            if ( confirm("(Agendamentos) Você já rodou o passo 6 e usou o mysql. A base convertida já está rodando no banco de dados antes de continuar.") ){

                window.parent.document.getElementById('dv_result').style.display = "block";
                window.parent.document.getElementById('if_run').src = 'agendamentos.php';  

            }




        }


        function compara(){


           

            let i_tabela = window.parent.document.getElementById('ed_i_tabelas').value;
            let v_tabela = window.parent.document.getElementById('ed_v_tabelas').value;

            if(confirm('todos os relacionamentos da tabela '+i_tabela+' serão apagados antes do preenchimento. Continuar?')){

                if (i_tabela.length > 0 && v_tabela.length > 0){

                    
                    let arr_i_campos = window.parent.document.getElementsByClassName('lb_i_campos');
                    let arr_v_campos = window.parent.document.getElementsByClassName('lb_v_campos');

                    var sql = 'delete from conversao where tabela_destino="'+i_tabela+'";';
                    var achei_total = 0;

                    //enquanto tiver campos na tabela nova, procura na velha um igual
                    for (let i=0;i < arr_i_campos.length;i++){

                        let campo_nova = arr_i_campos[i].id;
                        campo_nova = campo_nova.replace('lb_i_campo_','');

                        var achei = 0;                    
                        //vai fazer o laço e procurar na antiga
                        for (let i=0;i < arr_v_campos.length;i++){
                        
                            var campo_antiga = arr_v_campos[i].id;
                            campo_antiga = campo_antiga.replace('lb_v_campo_','');

                            if (campo_nova == campo_antiga){

                                achei = 1;
                                achei_total = achei_total + 1;
                                sql = sql +'insert into conversao values(null,"'+v_tabela+'","'+campo_antiga+'","'+i_tabela+'","'+campo_nova+'",0);';

                            }
                        
                        }    

                        if (achei == 0){

                            let id = 'lb_i_campo_'+campo_nova;
                            
                            window.parent.document.getElementById(id).style.font = 'italic bold 20px arial,serif';

                        }

                    }

                }
                else{


                    alert('Selecione a tabela origem e destino para fazer a comparação.');

                }

                if (achei_total > 0){
                sql_executa(sql);
                }    
            }    


        }




</script>
