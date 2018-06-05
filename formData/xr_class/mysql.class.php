<?php
class mysql{
    var $link = null;
    
    function __construct() {
        $this->connect();
    }
    /**
     * 连接
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $charset
     * @param string $database
     * @return object 连接标识符
     */
    function connect() {
        global $config;
        $this->$link = mysqli_connect ($config['db_server'],$config['db_username'],$config['db_password']) or die ('数据库连接失败');
        mysqli_set_charset ($this->$link,$config['dbcharset']);
        mysqli_select_db ($this->$link,$config['db_name']) or die ( '指定数据库打开失败<br/>ERROR ' . mysqli_errno ($this->$link ) . ':' . mysqli_error ($this->$link) );
        return$this->$link;
    }

    /*
     array(
     'username' =>'king',
     'password' =>'king',
     'age'      =>'12',
     'regTime'  =>'123123123'
     );
     INSERT user(username,password,age,regTime) VALUES('king','king','12','123123123');

     * 插入操作
     * @param array $data
     * @param string $table
     * @return boolean
     */
    function insert($data,$table){
        $keys = join ( ',', array_keys ( $data ) );
        $vals = "'" . join ( "','", array_values ( $data ) ) . "'";
        $query = "INSERT {$table}({$keys}) VALUES({$vals})";

        global $config;
        if ($config['show_sql_error']) {
            $errorstr = "error";
        }else $errorstr = "error:".$query;

        $res = mysqli_query ($this->$link,$query) or die($errorstr);

        return mysqli_insert_id ($this->$link );
        mysqli_close($this->$link);
    }

    /**
     array(
        'username' =>'king123',
        'password' =>'king123',
        'age'      =>'32',
        'regTime'  =>'123123123'
     );
     UPDATE user SET username='king123',password='king123',age='32',regTime='123123123' WHERE id=1

     * 更新操作
     * @param array $data
     * @param string $table
     * @param string $where
     * @return boolean
     */
    function update($data, $table, $where = null) {
        foreach ( $data as $key => $val ) {
            $set .= "{$key}='{$val}',";
        }
        $set = trim ( $set, ',' );
        $where = $where == null ? '' : ' WHERE ' . $where;
        $query = "UPDATE {$table} SET {$set} {$where}";
        global $config;
        if ($config['show_sql_error']) {
            $errorstr = "error";
        }else $errorstr = "error:".$query;

        $res = mysqli_query ($this->$link,$query) or die($errorstr);
        
        return $res;
        mysqli_close($this->$link);
    }

    //DELETE FROM user WHERE id=
    /**
     * 删除操作
     * @param string $table
     * @param string $where
     * @return boolean
     */
    function delete($table, $where = null) {
        $where = $where ? ' WHERE ' . $where : '';
        $query = "DELETE FROM {$table} {$where}";

        global $config;
        if ($config['show_sql_error']) {
            $errorstr = "error";
        }else $errorstr = "error:".$query;

        $res = mysqli_query ($this->$link,$query) or die($errorstr);

        return mysqli_affected_rows ($this->$link);
        mysqli_close($this->$link);
    }

    /**
     * 执行一条sql语句
     *
     * @param  [type] $query [description]
     *
     * @return [type] [description]
     */
    function query($query){
        global $config;
        if ($config['show_sql_error']) {
            $errorstr = "error";
        }else $errorstr = "error:".$query;
        
        $res = mysqli_query ($this->$link,$query) or die($errorstr);

        return mysqli_affected_rows ($this->$link );

        mysqli_close($this->$link);
    }

    /**
     * 查询指定记录
     * @param string $query
     * @param string $result_type
     * @return array|boolean
     */
    function getone($query, $result_type = MYSQLI_ASSOC) {
        global $config;
        if ($config['show_sql_error']) {
            $errorstr = "error";
        }else $errorstr = "error:".$query;

        $result = mysqli_query ($this->$link,$query) or die($errorstr);

        $row = mysqli_fetch_array ( $result, $result_type );
        return $row;

        mysqli_close($this->$link);
    }

    /**
     * 查询所有记录
     * @param string $query
     * @param string $result_type
     * @return array|boolean
     */
    function getall($query, $result_type = MYSQLI_ASSOC) {
        global $config;
        if ($config['show_sql_error']) {
            $errorstr = "error";
        }else $errorstr = "error:".$query;

        $result = mysqli_query ($this->$link,$query) or die($errorstr);

        while ( $row = mysqli_fetch_array ( $result, $result_type ) ) {
            $rows [] = $row;
        }
        return $rows;
        mysqli_close($this->$link);
    }

    /**
     * 得到表中的记录数
     * @param string $table
     * @return number|boolean
     */
    function getTotalRows($table) {
        global $config;
        if ($config['show_sql_error']) {
            $errorstr = "error";
        }else $errorstr = "error:".$query;

        $query = "SELECT COUNT(*) AS totalRows FROM {$table}";
        $result = mysqli_query ($this->$link,$query) or die($errorstr);
        if ($result && mysqli_num_rows ( $result ) == 1) {
            $row = mysqli_fetch_assoc ( $result );
        }else{
        	$row = 0;
        }
        return $row ['totalRows'];
        mysqli_close($this->$link);
    }

    /**
     * 得到结果集的记录条数
     * @param string $query
     * @return boolean
     */
    function getResultRows($query) {
        global $config;
        if ($config['show_sql_error']) {
            $errorstr = "error";
        }else $errorstr = "error:".$query;

        $result = mysqli_query ($this->$link,$query) or die($errorstr);
        return mysqli_num_rows ( $result );

        mysqli_close($this->$link);
    }

    /**
     * 事务操作数据
     *
     * @param  [type] $sqlstr_arr [description]
     *
     * @return [type] [description]
     */
    function transaction($sqlstr_arr){
        mysqli_query ($this->$link,"START TRANSACTION");
        for ($i=0; $i < count($sqlstr_arr); $i++) { 
            $sql = $sqlstr_arr[$i];
            $res = mysqli_query($this->$link,$sql);
            if (!$res) {
                $errorstr = "执行失败";
            }
        }
        if(!$errorstr){
            mysqli_query ($this->$link,"COMMIT");
            $result = "success";
        }else{
            mysqli_query ($this->$link,"ROLLBACK");
            $result = "error";
        }  
        return $result;      
    }

    function getServerInfo() {
        return mysqli_get_server_info ($this->$link);
    }
    function getClientInfo() {
        return mysqli_get_client_info ($this->$link);
    }
    function getHostInfo(){
        return mysqli_get_host_info($this->$link);
    }
    function getProtoInfo() {
        return mysqli_get_proto_info ($this->$link);
    }
}
