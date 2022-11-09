<?php

class Conexao
{

    private $db; 
    private $host;
    private $port;
    private $user;
    private $password;
    


    function __construct($db,$host,$port,$user,$password)
    {

        $this -> db = $db;
        $this -> host = $host;
        $this -> port = $port;
        $this -> user = $user;
        $this -> password = $password;
        

    }



    private function connect()
    {

        $db = $this -> db ;
        $host = $this -> host;
        $port = $this -> port;
        $user = $this -> user;
        $password = $this -> password;


        $db = new PDO("mysql:host=$host:3306;port=$port;dbname=$db","$user","$password");        
        $this -> db = $db;
            
        $this->db->exec("set character set utf8");

        return $this->db;

    }

    private function oneField($table,$field,$id)
    {

        $conn = $this -> getConnection();        

        $res = $conn -> query("select $field as value from $table where cod_id=$id");
        
        while ($line = $res -> fetch(PDO::FETCH_ASSOC))
        {

           return $line["value"]; 

        }

    }

    public function getConnection()
    {
        return $this->connect();
    }   

    public function getOneField($table,$field,$id) //return one fiels value of a table
    {

        return $this -> oneField($table,$field,$id);
        
    }

}

?>



