<?php
  define('ORA_CHARSET_DEFAULT', 'AMERICAN_AMERICA.CL8MSWIN1251');
  define('ORA_CONNECTION_TYPE_DEFAULT', 1);
  define('ORA_CONNECTION_TYPE_PERSISTENT', 2);
  define('ORA_CONNECTION_TYPE_NEW', 3);
  define('ORA_MESSAGES_NOT_CONNECTED', 'Not connected to Oracle instance');
  
  class ORACLE {
    private static $_instance;
    private $conn_handle;
    private $conn_data;
    private $errors_pool;
    private $statements = array();
    private $autocommit = false;
    private $fetch_mode = OCI_BOTH;
    private $last_query;
    private $var_max_size = 1000;
    private $execute_status = false;
    private $charset;
    private $session_mode = OCI_DEFAULT;

    /**
    * Set array fetching mode for Fetch methods
    * 
    * @param mixed $mode
    */
    public function SetFetchMode($mode = OCI_BOTH){
        $this->fetch_mode = $mode;
    }
    
    /**
    * Set on|off auto commit option
    * 
    * @param bool $mode
    */
    public function SetAutoCommit($mode = true){
        $this->autocommit = $mode;
    }
    
    /**
    * Set variable max size for binding
    * 
    * @param int $size
    */
    public function SetVarMaxSize($size){
        $this->var_max_size = $size;
    }
    
    /**
    * Returns the last error found. 
    * 
    */
    public function GetError(){
        return @oci_error($this->conn_handle);
    }    
    
    /**
    * Set nls_lang
    * 
    * @param string $charset
    */
    public function SetNlsLang($charset = ORA_CHARSET_DEFAULT){
        $this->charset = $charset;
    }
    
    /**
    * Constructor
    * 
    */
    public function __construct(){
        $this->SetNlsLang('CL8MSWIN1251');
        $this->SetFetchMode(OCI_ASSOC);
        $this->SetAutoCommit(false);
    }
    
    /**
    * Connect to Oracle DB
    * 
    * @param string $host
    * @param string $user
    * @param string $pass
    * @param int $mode (OCI_DEFAULT, OCI_SYSDBA, OCI_SYSOPER)
    * @param int $type (ORA_CONNECTION_TYPE_DEFAULT, ORA_CONNECTION_TYPE_NEW, ORA_CONNECTION_TYPE_PERSISTENT)
    * @return bool
    */
    public function Connect($host = 'localhost', $user='', $pass='', $mode = OCI_DEFAULT, $type = ORA_CONNECTION_TYPE_DEFAULT){
      switch ($type) {
          case ORA_CONNECTION_TYPE_PERSISTENT: {
              $this->conn_handle = oci_pconnect($user, $pass, $host, $this->charset, $mode);
          }; break;
          case ORA_CONNECTION_TYPE_NEW: {
              $this->conn_handle = oci_new_connect($user, $pass, $host, $this->charset, $mode);
          }; break;
          default: 
              $this->conn_handle = oci_connect($user, $pass, $host, $this->charset, $mode);
      }        
      return is_resource($this->conn_handle) ? true : false;
    }
    
    /**
    * Destructor
    * 
    */
    public function __destruct(){
      if (is_resource($this->conn_handle)) {
        @oci_close($this->conn_handle);
      }
    }
    
    /**
    * This function return last command exec status
    * 
    * @return bool
    */
    public function GetExecuteStatus(){
        return $this->execute_status;
    }
    
    private function GetBindingType($var){
        if (is_a($var, "OCI-Collection")) {
          $bind_type = SQLT_NTY;
          $this->SetVarMaxSize(-1);
        } elseif (is_a($var, "OCI-Lob")) {
          $bind_type = SQLT_CLOB;
          $this->SetVarMaxSize(-1);
        } else {
          $bind_type = SQLT_CHR;
        }
        return $bind_type;
    }
    
    /**
    * Private method for execute any sql query or pl/sql
    * 
    * @param string $sql_text
    * @param array | false $bind
    * @return resource | false
    */
    private function Execute($sql_text, &$bind = false){
        if (!is_resource($this->conn_handle)) return false;
        $this->last_query = $sql_text;
        
        $stid = @oci_parse($this->conn_handle, $sql_text);
        
        $this->statements[$stid]['text'] = $sql_text;
        $this->statements[$stid]['bind'] = $bind;
        
        if ($bind && is_array($bind)) {
            foreach($bind as $k=>$v){
                oci_bind_by_name($stid, $k, $bind[$k], $this->var_max_size, $this->GetBindingType($bind[$k]));
            }
        }
        $com_mode = $this->autocommit ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
        $this->execute_status = oci_execute($stid, $com_mode);
        return $this->execute_status ? $stid : false;
    }
    
    /**
    * select command wraooer
    * 
    * @param string $sql the query text
    * @param array | false $bind array of pairs binding variables
    * @return resource | false
    */
    public function Select($sql, $bind = false){
        return $this->Execute($sql, $bind);
    }
    
    /**
    * Fetch array of select statement
    * 
    * @param resource $statement valid OCI statement id
    * @return array
    */
    public function FetchArray($statement){
        return oci_fetch_array($statement, $this->fetch_mode);
    }
    
    /**
    * Function Returns a numerically indexed array containing the next result-set row of a query. 
    * Each array entry corresponds to a column of the row. This function is typically called in a loop 
    * until it returns FALSE, indicating no more rows exist. 
    * 
    * @param resource $statement valid OCI statement id
    * @return array Returns a numerically indexed array. If there are no more rows in the statement then FALSE is returned. 
    */
    public function FetchRow($statement){
        return oci_fetch_row($statement);
    }
    
    /**
    * Fetch rows from select operation
    * 
    * @param resourse $statement valid OCI statement identifier
    * @param int $skip number of initial rows to ignore when fetching the result (default value of 0, to start at the first line). 
    * @param int $maxrows number of rows to read, starting at the skip th row (default to -1, meaning all the rows). 
    * $return array
    */
    public function FetchAll($statement, $skip = 0, $maxrows = -1){
        $rows = array();
        oci_fetch_all($statement, $rows, $skip, $maxrows, OCI_FETCHSTATEMENT_BY_ROW);
        return $rows;
    }
    
    /**
    * Fetch row as object
    * 
    * @param resource $statement valid OCI statement identifier
    * @return object
    * @author Sergey Pimenov
    */
    public function FetchObject($statement){
        return oci_fetch_object($statement);
    }
    
    /**
    * Fetches the next row (for SELECT statements) into the internal result-buffer.
    * 
    * @param resource $statement valid OCI statement id
    * @return bool
    */
    public function Fetch($statement){
        return oci_fetch($statement);
    }
    
    /**
    * Returns the data from field in the current row, fetched by Fetch()
    * 
    * @param resource $statement valid OCI statement id
    * @param mixed $field Can be either use the column number (1-based) or the column name (in uppercase).
    * @return mixed
    */
    public function Result($statement, $field){
        return oci_result($statement, $field);
    }
    
    /**
    * Associates a PHP variable with a column for query fetches using Fetch(). 
    * 
    * @param resource $statement A valid OCI statement identifier 
    * @param string $column_name The column name used in the query. 
    * @param mixed $variable The PHP variable that will contain the returned column value. 
    * @param int $type The data type to be returned.
    * @return bool 
    */
    public function DefineByName($statement , $column_name , &$variable, $type = SQLT_CHR){
        return oci_define_by_name($statement, $column_name, $variable, $type);
    }
    
    public function FieldIsNull($statement, $field){
        return oci_field_is_null($statement, $field);
    }
    
    public function FieldName($statement, int $field){
        return oci_field_name($statement, $field);
    }
    
    public function FieldPrecition($statement, int $field){
        return oci_field_precision($statement, $field);
    }
    
    public function FieldScale($statement, int $field){
        return oci_field_scale($statement, $field);
    }
    
    public function FieldSize($statement, $field){
        return oci_field_size($statement, $field);
    }
    
    public function FieldTypeRaw($statement, int $field){
        return oci_field_type_raw($statement, $field);
    }
    
    public function FieldType($statement, int $field){
        return oci_field_type($statement, $field);
    }       
                
    /**
    * Insert row into table
    * 
    * @param string $table name of table
    * @param array $arrayFieldsValues define pair field => value
    * @param array $bind define pairs holder => value for binding
    * @param array $returning define fields for returning clause in insert statement
    * @return mixed if $returnig is defined function return array of fields defined in $returning
    * @author Sergey Pimenov
    */
    public function Insert($table, $arrayFieldsValues, &$bind = false, $returning = false){
        if (empty($arrayFieldsValues)) return false;
        $fields = array();
        $values = array();
        foreach($arrayFieldsValues as $f=>$v){
            $fields[] = $f;
            $values[] = $v;
        }
        $fields = implode(",", $fields);
        $values = implode(",", $values);
        $ret = "";
        if ($returning) {
            foreach($returning as $f=>$h){
                $ret_fields[] = $f;
                $ret_binds[] = ":$h";
                $bind[":$h"] = "";
            }
            $ret = " returning ".(implode(",", $ret_fields))." into ".(implode(",",$ret_binds));
        }
        $sql = "insert into $table ($fields) values($values) $ret";
        $result = $this->Execute($sql, $bind);
        if ($result === false) return false;
        if ($returning === false) {
            return $result;
        } else {
            $result = array();
            foreach($returning as $f=>$h){
                $result[$f] = $bind[":$h"];
            }
            return $result;
        }
    }
    
    /**
    * Method for update data in table
    * 
    * @param string $table
    * @param array $arrayFieldsValues
    * @param string | false $condition
    * @param array | false $bind
    * @return resource
    */
    public function Update($table, $arrayFieldsValues, $condition = false, &$bind = false, $returning = false){
        if (empty($arrayFieldsValues)) return false;
        $fields = array();
        $values = array();
        foreach($arrayFieldsValues as $f=>$v){
            $fields[] = "$f = $v";
        }
        $fields = implode(",", $fields);
        if ($condition === false) { $condition = "true";}
        $ret = "";
        if ($returning) {
            foreach($returning as $f=>$h){
                $ret_fields[] = $f;
                $ret_binds[] = ":$h";
                $bind[":$h"] = "";
            }
            $ret = " returning ".(implode(",", $ret_fields))." into ".(implode(",",$ret_binds));
        }
        $sql = "update $table set $fields where $condition $ret";
        $result = $this->Execute($sql, $bind);        
        if ($result === false) return false;
        if ($returning === false) {
            return $result;
        } else {
            $result = array();
            foreach($returning as $f=>$h){
                $result[$f] = $bind[":$h"];
            }
            return $result;
        }
    }
    
    public function Delete($table, $condition, &$bind = false, $returning = false){
        if ($condition === false) { $condition = "true";}
        $ret = "";
        if ($returning) {
            foreach($returning as $f=>$h){
                $ret_fields[] = $f;
                $ret_binds[] = ":$h";
                $bind[":$h"] = "";
            }
            $ret = " returning ".(implode(",", $ret_fields))." into ".(implode(",",$ret_binds));
        }
        $sql = "delete from $table where $condition $ret";
        $result = $this->Execute($sql, $bind);
        if ($result === false) return false;
        if ($returning === false) {
            return $result;
        } else {
            $result = array();
            foreach($returning as $f=>$h){
                $result[$f] = $bind[":$h"];
            }
            return $result;
        }
    }
    
    /**
    * Gets the number of rows affected during statement execution.
    * 
    * @param resource $statement
    * @return int
    */
    public function NumRows($statement){
        return oci_num_rows($statement);
    }
    
    /**
    * Synonym for NumRows()
    * 
    * @param resource $statement
    * @return int
    */
    public function RowsAffected($statement){
        return $this->NumRows($statement);
    }
    
    /**
    * Gets the number of columns in the given statement. 
    * 
    * @param resource $statement
    * @return int
    */
    public function NumFields($statement){
        return oci_num_fields($statement);
    }
    
    /**
    * Synonym for NumFields()
    * 
    * @param resource $statement
    * @return int
    */
    public function FieldsCount($statement){
        return $this->NumFields($statement);
    }
    
    // Support Lob
    
    /**
    * Allocates resources to hold descriptor or LOB locator. 
    * 
    * @param resource $connection
    * @param int $type Valid values for type are: OCI_DTYPE_FILE, OCI_DTYPE_LOB and OCI_DTYPE_ROWID. 
    * @return OCI-Lob
    */
    public function NewDescriptor($type = OCI_DTYPE_LOB){
        return oci_new_descriptor($this->conn_handle, $type);
    }
    
    /**
    * Allocates a new collection object 
    * 
    * @param string $typename Should be a valid named type (uppercase). 
    * @param string $schema Should point to the scheme, where the named type was created. The name of the current user is the default value. 
    * @return OCI-Collection
    */
    public function NewCollection($typename, $schema = null){
        return oci_new_collection($this->conn_handle, $typename, $schema);
    }   
    
    // Support stored procedures and functions
    
    /**
    * Method for execute stored procedure
    * 
    * @param mixed $name
    * @param string $params
    * @param mixed $bind
    * @return resource
    */
    public function StoredProc($name, $params = false, &$bind = false){
        if ($params) {
          if (is_array($params)) $params = implode(",", $params);
          $sql = "begin $name($params); end;";  
        } else {
          $sql = "begin $name; end;";
        }
        return $this->Execute($sql, $bind);
    }
    
    /**
    * Methos for execute stored function
    * 
    * @param mixed $name
    * @param string $params
    * @param mixed $bind
    * @return mixed
    */
    public function Func($name, $params = false, $bind = false){
        if ($params) {
          if (is_array($params)) $params = implode(",", $params);
          $sql = "select $name($params) as RESULT from dual";  
        } else {
          $sql = "select $name from dual";
        }
        $h = $this->Execute($sql, $bind);
        $r = $this->FetchArray($h);
        return $r['RESULT'];
    }
    
    /**
    * Method execute cursor defined in stored proc
    * 
    * @param string $stored_proc stored proc where cursor is defined
    * @param string $bind binding for out parameter in stored proc
    * @return resource
    * @example Cursor("utils.get_cursor", "dataset"); //begin utils.get_cursor(:dataset); end;
    */
    public function Cursor($stored_proc, $bind){
        if (!is_resource($this->conn_handle)) return false;
        $sql = "begin $stored_proc(:$bind); end;";
        $curs = oci_new_cursor($this->conn_handle);
        $stmt = oci_parse($this->conn_handle, $sql);
        oci_bind_by_name($stmt, $bind, $curs, -1, OCI_B_CURSOR);
        oci_execute($stmt);
        oci_execute($curs);
        $this->FreeStatement($stmt);
        return $curs;
    }
    
    /**
    * Invalidates a cursor, freeing all associated resources and cancels the ability to read from it. 
    * 
    * @param resource $statement valid OCI statement id
    * @return bool
    */
    public function Cancel($statement){
        return oci_cancel($statement);
    }
    
    /**
    * Free resource of OCI statement identifier
    * 
    * @param resource $stid
    * @return bool
    * @author Sergey Pimenov
    */
    public function FreeStatement($stid){
        unset($this->statements[$stid]);
        return oci_free_statement($stid);
    }
    
    /**
    * Free array of resources of OCI statement identifier
    * 
    * @param array $array_stid
    * @return bool
    * @author Sergey Pimenov
    */
    public function FreeStatements($array_stid){
        if (is_array($array_stid)) foreach($array_stid as $stid) {
          unset($this->statements[$stid]);
          oci_free_statement($stid);
        }
        return true;
    }
    
    /**
    * Commit transaction
    * 
    * @return bool
    * @author Sergey Pimenov 
    */
    public function Commit(){
        if (is_resource($this->conn_handle))
          return @oci_commit($this->conn_handle);
        else 
          return false;
    }  

    /**
    * Rollback transaction
    * 
    * @return bool
    * @author Sergey Pimenov 
    */
    public function Rollback(){
        if (is_resource($this->conn_handle))
          return @oci_rollback($this->conn_handle);
        else 
          return false;
    }  
    
    /**
    * Enables or disables internal debug output. 
    * 
    * @param bool $mode
    */
    public function InternalDebug($mode){
        oci_internal_debug($mode);
    }
    
    
    public function GetStatement($stid){
        return $this->statements[$stid] ? $this->statements[$stid] : false;
    }
    
    /**
    * Get sql text operation
    * 
    * @param resource $stid valid OCI statement id
    * @return string
    */
    public function QuerySnapshot($stid = false){
        if ($stid) return $this->statements[$stid]['text']; else return $this->last_query;
    }
    
    /**
    * Get Oracle Server version
    * 
    * @return string | false
    */
    public function ServerVer(){
        if (is_resource($this->conn_handle))
          return @oci_server_version($this->conn_handle);
        else 
          return false;
    }
    
    public function SetAction(string $action_name){
        return @oci_set_action($this->conn_handle, $action_name);
    }
    
    public function SetClientID(string $client_id){
        return @oci_set_client_identifier($this->conn_handle, $client_id);
    }
    
    public function SetClientInfo(string $client_info){
        return @oci_set_client_info($this->conn_handle, $client_info);
    }
    
    public function SepPrefetch(int $rows){
        return oci_set_prefetch($this->conn_handle, $rows);
    }
    
    /**
    * Returns a keyword identifying the type of the OCI statement. 
    * 
    * @param resource $statement
    * @return string (ALTER, BEGIN, CALL, CREATE, DECLARE, DELETE, DROP, INSERT, SELECT, UPDATE, UNKNOWN) return false on error
    */
    public function StatementType($statement){
        return oci_statement_type($statement);
    }
    
    public function DumpQueriesStack(){
        var_dump($this->statements);
    }
    
    public function Bye(){
        $this->__destruct();
    } 
    
    public function get_handle(){
        return $this->conn_handle;
    }   
  }