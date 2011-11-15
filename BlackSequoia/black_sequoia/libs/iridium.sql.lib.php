<?php
class DB {

    var $host='localhost';
    var $user='avto';
    var $pass='FbUVdDA4ZBzadPUc';
    var $db='avto';
    var $db_link;
    var $conn = false;
    var $persistant = false;
    var $result;

    public $error = false;

    public function config(){ // class config
        $this->error = true;
        $this->persistant = false;
    }

    function connect(){ // connection function
        $this->config();

        // Establish the connection.
        if ($this->persistant)
           @$this->db_link = mysql_pconnect($this->host, $this->user, $this->pass, true);
        else
           @$this->db_link = mysql_connect($this->host, $this->user, $this->pass, true);

        if (!$this->db_link) {
            if ($this->error) {
                $this->error($type=1);
            }
            return false;
        }
        else {
        if (empty($this->db)) {
            if ($this->error) {
                $this->error($type=4);
            }
        }
        else {
            $db = mysql_select_db($this->db, $this->db_link); // select db
            if (!$db) {
                if ($this->error) {
                    $this->error($type=4);
                }
            return false;
            }
            @mysql_query("SET NAMES 'utf8'",$this->db_link) or $this->error($type=5);
            $this -> conn = true;
        }
            return $this->db_link;
        }
    }

    function close() { // close connection
        if ($this ->conn){ // check connection
            if ($this->persistant) {
                $this ->conn = false;
            }
            else {
                mysql_close($this->db_link);
                $this ->conn = false;
            }
        }
        else {
            if ($this->error) {
                return $this->error($type=4);
            }
        }
    }
    public function query($query){
        if ($this->conn) {
            $this->result=mysql_query($query,  $this->db_link);
            return $this->result;
        } else {
           if ($this->connect()){
               $this->result=mysql_query($query,  $this->db_link);
               return $this->result;
           }
        }
    }

    public function fquery($query){
        $qret=$this->query($query);
        if ($qret==false){
            return false;
        } else {
            return $this->fetch($qret);
        }
    }

    public function faquery($query){
        $qret=$this->query($query);
        if ($qret==false){
            return false;
        } else {
            return $this->fetch_all($qret);
        }
    }
    
    public function fetch($query_result){
        return mysql_fetch_array($query_result, MYSQL_ASSOC);
    }
    public function fetch_all($query_result){
        while ($row = $this->fetch($query_result)) {
            $result[]=$row;
        }
        return $result;
    }


    public function fetch_alias($result,$symb='.'){
        if (!($row = mysql_fetch_array($result))){
            return false;
        }
        $assoc = Array();
        $rowCount = mysql_num_fields($result);

        for ($idx = 0; $idx < $rowCount; $idx++){
            $table = mysql_field_table($result, $idx);
            $field = mysql_field_name($result, $idx);
            $assoc["$table$symb$field"] = $row[$idx];
        }
        return $assoc;
    }
    public function es($string){
        if (!$this->conn) {
            $this->connect();
        }
        return mysql_real_escape_string($string,  $this->db_link);
    }
    
    /** @todo: have to be reworked */
    public function error($type=''){
        if (empty($type)) {
            return false;
        }
        else {
            if ($type==1)
                die("<strong>Database could not connect</strong> ");
            else if ($type==2)
                die("<strong>mysql error</strong> " . mysql_error());
            else if ($type==3)
                die("<strong>error </strong>, Proses has been stopped");
            else if ($type==4)
                die("<strong>Fail select DB </strong>");
            else if ($type==5)
                die("<strong>MySQL fail to switch in utf8 codepage</strong>");
            else
                die("<strong>error </strong>, no connection !!!");
        }
    }
}

class ACTIVE_TABLE {
    //Active Record
    public $db_link;
    public $table_name;
    
    /**  Constructor for class
     *      set:
     *          $table_name - string, table name
     *          $db_link - link to exemplar of DB class
     */
    function __construct($table_name,$db_link) {
        $this->db_link=$db_link;
        $this->table_name=$table_name;
    }

    /**  Add row to table - 2-ways:
     *      1 - set: 
     *            $data - array having the structure "['field'] = 'value'"
     *            $values - not used for this variation
     *            $conditions - string, addtional conditions (if need)
     *      2 - set:
     *            $data - string of fields, separated by commas "field1, field2, fieldN"
     *            $values - string of values​​, separated by commas in quotes "'value1','value2','valueN'"
     *            $conditions - string, addtional conditions (if need)
     */
    function Add($data,$values='',$conditions='') {
        $insert_start="INSERT INTO $this->table_name (";
        $insert_middle=') VALUES (';
        $insert_end=') ';
        $sql_end=';';

        if (is_array($data)) {
            $keys='';
            $values='';
            foreach ($data as $key => $value) {
                $keys .= $key.',';
                if ($value==NULL) {$value='NULL';} else {$value="'$value'";}
                $values .= "$value,";        
            }
            $keys=substr($keys, 0, strlen($keys)-1);
            $values=substr($values, 0, strlen($values)-1);
            $sql=$insert_start.$keys.$insert_middle.$values.$insert_end.$conditions.$sql_end;
        }

        if (is_string($data)) {
            $sql=$insert_start.$data.$insert_middle.$values.$insert_end.$conditions.$sql_end;
        }
        //query
        $ret=$this->db_link->query($sql);
        return $ret;
    }
    
    /**  Update row on table - 2-way:
     *      1 - set: 
     *            $data - array having the structure "['field'] = 'value'"
     *            $conditions - 2-way:
     *                  1 - array key-value for pattern (field='value') AND
     *                  2 - string
     *            $addtional_conditions - string, addtional conditions - for example 'ORDER BY ASC' (if need)
     *      2 - set:
     *            $data - string that lists the fields and assign values - "field1='value1', field2='value2', fieldN='valueN'"
     *            $conditions - same as on way 1
     *            $addtional_conditions - string, addtional conditions - for example 'ORDER BY ASC' (if need)
     */
   
    public function Update($data,$conditions='',$addtional_conditions=''){
        $update_start="UPDATE $this->table_name SET ";
        $sql_end=';';

        if (is_array($conditions)){
            $condition='WHERE ';
            foreach ($conditions as $key => $value) {
                if ($value==NULL) {$value='NULL';} else {$value="'$value'";}
                $condition.="($key = $value) AND ";
            }
            $condition=substr($condition, 0, strlen($condition)-4);
            $conditions=$condition;
        }
        
        if (is_array($data)){
            $pairs='';
            foreach ($data as $key => $value) {
                if ($value==NULL) {$value='NULL';} else {$value="'$value'";}
                $pairs.= "$key = $value,";
            }
            $pairs=substr($pairs, 0, strlen($pairs)-1);
            $pairs.=' ';
            $data=$pairs;
        }
        $sql=$update_start.$data.$conditions.$addtional_conditions.$sql_end;
        $ret=$this->db_link->query($sql);
        return $ret;
    }

    public function Delete($conditions,$del_label='id') {
        if (is_array($conditions)){
            $conditions="WHERE $del_label='".$conditions[$del_label]."'";
        }
        $sql="DELETE FROM $this->table_name $conditions;";
        $ret=$this->db_link->query($sql);
        return $ret;
    }

    public function DeleteByID($id) {
        return $this->Delete("WHERE id = '$id'");
    }
    
    public function DisableByID($id) {
        return $this->Update("enabled = '0'", "WHERE id = '$id'");
    }    

    public function Get($conditions, $what='*',$addtional_conditions=''){
        $select_start
    }
    
}

/*

 
   UPDATE  base SET  `summ` =  '150001',
 
 `probeg` =  '118001',
`color` =  'Снежная королева !' WHERE  `base`.`id` =2;
    

