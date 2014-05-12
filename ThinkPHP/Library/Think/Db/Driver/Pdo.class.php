<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Db\Driver;
use Think\Db;
defined('THINK_PATH') or exit();
/**
 * PDO���ݿ����� 
 */
class Pdo extends Db{

    protected $PDOStatement = null;
    private   $table        = '';

    /**
     * �ܹ����� ��ȡ���ݿ�������Ϣ
     * @access public
     * @param array $config ���ݿ���������
     */
    public function __construct($config=''){
        if(!empty($config)) {
            $this->config   =   $config;
            if(empty($this->config['params'])) {
                $this->config['params'] =   array();
            }
        }

    }

    /**
     * �������ݿⷽ��
     * @access public
     */
    public function connect($config='',$linkNum=0) {
        if ( !isset($this->linkID[$linkNum]) ) {
            if(empty($config))  $config =   $this->config;
            if($this->pconnect) {
                $config['params'][\PDO::ATTR_PERSISTENT] = true;
            }
            if(version_compare(PHP_VERSION,'5.3.6','<=')){ //����ģ��Ԥ�������
                $config['params'][\PDO::ATTR_EMULATE_PREPARES]  =   false;
            }
            //$config['params'][PDO::ATTR_CASE] = C("DB_CASE_LOWER")?PDO::CASE_LOWER:PDO::CASE_UPPER;
            try{
                $this->linkID[$linkNum] = new \PDO( $config['dsn'], $config['username'], $config['password'],$config['params']);
            }catch (\PDOException $e) {
                E($e->getMessage());
            }
            // ��ΪPDO�������л����ܵ������ݿ����Ͳ�ͬ��������»�ȡ�µ�ǰ�����ݿ�����
            $this->dbType = $this->_getDsnType($config['dsn']);
            if(in_array($this->dbType,array('MSSQL','ORACLE','IBASE','OCI'))) {
                // ����PDO�������ϵ����ݿ�֧�ֲ������������������� �����Ȼϣ��ʹ��PDO ����ע������һ�д���
                E('����ĿǰPDO��ʱ��������֧��'.$this->dbType.' ��ʹ�ùٷ���'.$this->dbType.'����');
            }
            $this->linkID[$linkNum]->exec('SET NAMES '.$config['charset']);
            // ������ӳɹ�
            $this->connected    =   true;
            // ע�����ݿ�����������Ϣ
            if(1 != C('DB_DEPLOY_TYPE')) unset($this->config);
        }
        return $this->linkID[$linkNum];
    }

    /**
     * �ͷŲ�ѯ���
     * @access public
     */
    public function free() {
        $this->PDOStatement = null;
    }

    /**
     * ִ�в�ѯ �������ݼ�
     * @access public
     * @param string $str  sqlָ��
     * @param array $bind ������
     * @return mixed
     */
    public function query($str,$bind=array()) {
        $this->initConnect(false);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        if(!empty($bind)){
            $this->queryStr     .=   '[ '.print_r($bind,true).' ]';
        }        
        //�ͷ�ǰ�εĲ�ѯ���
        if ( !empty($this->PDOStatement) ) $this->free();
        N('db_query',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->PDOStatement = $this->_linkID->prepare($str);
        if(false === $this->PDOStatement)
            E($this->error());
        // ������
        $this->bindPdoParam($bind);
        $result =   $this->PDOStatement->execute();
        $this->debug();
        if ( false === $result ) {
            $this->error();
            return false;
        } else {
            return $this->getAll();
        }
    }

    /**
     * ִ�����
     * @access public
     * @param string $str  sqlָ��
     * @param array $bind ������
     * @return integer
     */
    public function execute($str,$bind=array()) {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        $this->queryStr = $str;
        if(!empty($bind)){
            $this->queryStr     .=   '[ '.print_r($bind,true).' ]';
        }        
        $flag = false;
        if($this->dbType == 'OCI') {
            if(preg_match("/^\s*(INSERT\s+INTO)\s+(\w+)\s+/i", $this->queryStr, $match)) {
                $this->table = C("DB_SEQUENCE_PREFIX").str_ireplace(C("DB_PREFIX"), "", $match[2]);
                $flag = (boolean)$this->query("SELECT * FROM user_sequences WHERE sequence_name='" . strtoupper($this->table) . "'");
            }
        }
        //�ͷ�ǰ�εĲ�ѯ���
        if ( !empty($this->PDOStatement) ) $this->free();
        N('db_write',1);
        // ��¼��ʼִ��ʱ��
        G('queryStartTime');
        $this->PDOStatement = $this->_linkID->prepare($str);
        if(false === $this->PDOStatement) {
            E($this->error());
        }
        // ������
        $this->bindPdoParam($bind);        
        $result = $this->PDOStatement->execute();
        $this->debug();
        if ( false === $result) {
            $this->error();
            return false;
        } else {
            $this->numRows = $this->PDOStatement->rowCount();
            if($flag || preg_match("/^\s*(INSERT\s+INTO|REPLACE\s+INTO)\s+/i", $str)) {
                $this->lastInsID = $this->getLastInsertId();
            }
            return $this->numRows;
        }
    }

    /**
     * ������
     * @access protected
     * @return void
     */
    protected function bindPdoParam($bind){
        // ������
        foreach($bind as $key=>$val){
            if(is_array($val)){
              array_unshift($val,$key);
            }else{
              $val  = array($key,$val);
            }
            call_user_func_array(array($this->PDOStatement,'bindParam'),$val);
        }      
    }

    /**
     * ��������
     * @access public
     * @return void
     */
    public function startTrans() {
        $this->initConnect(true);
        if ( !$this->_linkID ) return false;
        //����rollback ֧��
        if ($this->transTimes == 0) {
            $this->_linkID->beginTransaction();
        }
        $this->transTimes++;
        return ;
    }

    /**
     * ���ڷ��Զ��ύ״̬����Ĳ�ѯ�ύ
     * @access public
     * @return boolen
     */
    public function commit() {
        if ($this->transTimes > 0) {
            $result = $this->_linkID->commit();
            $this->transTimes = 0;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * ����ع�
     * @access public
     * @return boolen
     */
    public function rollback() {
        if ($this->transTimes > 0) {
            $result = $this->_linkID->rollback();
            $this->transTimes = 0;
            if(!$result){
                $this->error();
                return false;
            }
        }
        return true;
    }

    /**
     * ������еĲ�ѯ����
     * @access private
     * @return array
     */
    private function getAll() {
        //�������ݼ�
        $result =   $this->PDOStatement->fetchAll(\PDO::FETCH_ASSOC);
        $this->numRows = count( $result );
        return $result;
    }

    /**
     * ȡ�����ݱ���ֶ���Ϣ
     * @access public
     */
    public function getFields($tableName) {
        $this->initConnect(true);
        if(C('DB_DESCRIBE_TABLE_SQL')) {
            // ����������ֶβ�ѯSQL
            $sql   = str_replace('%table%',$tableName,C('DB_DESCRIBE_TABLE_SQL'));
        }else{
            switch($this->dbType) {
                case 'MSSQL':
                case 'SQLSRV':
                    $sql   = "SELECT   column_name as 'Name',   data_type as 'Type',   column_default as 'Default',   is_nullable as 'Null'
        FROM    information_schema.tables AS t
        JOIN    information_schema.columns AS c
        ON  t.table_catalog = c.table_catalog
        AND t.table_schema  = c.table_schema
        AND t.table_name    = c.table_name
        WHERE   t.table_name = '$tableName'";
                    break;
                case 'SQLITE':
                    $sql   = 'PRAGMA table_info ('.$tableName.') ';
                    break;
                case 'ORACLE':
                case 'OCI':
                    $sql   = "SELECT a.column_name \"Name\",data_type \"Type\",decode(nullable,'Y',0,1) notnull,data_default \"Default\",decode(a.column_name,b.column_name,1,0) \"pk\" "
                      ."FROM user_tab_columns a,(SELECT column_name FROM user_constraints c,user_cons_columns col "
                      ."WHERE c.constraint_name=col.constraint_name AND c.constraint_type='P' and c.table_name='".strtoupper($tableName)
                      ."') b where table_name='".strtoupper($tableName)."' and a.column_name=b.column_name(+)";
                    break;
                case 'PGSQL':
                    $sql   = 'select fields_name as "Name",fields_type as "Type",fields_not_null as "Null",fields_key_name as "Key",fields_default as "Default",fields_default as "Extra" from table_msg('.$tableName.');';
                    break;
                case 'IBASE':
                    break;
                case 'MYSQL':
                default:
                    $sql   = 'DESCRIBE '.$tableName;//��ע: �����಻ֻ���mysql�����ܼ�``
            }
        }
        $result = $this->query($sql);
        $info   =   array();
        if($result) {
            foreach ($result as $key => $val) {
                $val            =   array_change_key_case($val);
                $val['name']    =   isset($val['name'])?$val['name']:"";
                $val['type']    =   isset($val['type'])?$val['type']:"";
                $name           =   isset($val['field'])?$val['field']:$val['name'];
                $info[$name]    =   array(
                    'name'    => $name ,
                    'type'    => $val['type'],
                    'notnull' => (bool)(((isset($val['null'])) && ($val['null'] === '')) || ((isset($val['notnull'])) && ($val['notnull'] === ''))), // not null is empty, null is yes
                    'default' => isset($val['default'])? $val['default'] :(isset($val['dflt_value'])?$val['dflt_value']:""),
                    'primary' => isset($val['key'])?strtolower($val['key']) == 'pri':(isset($val['pk'])?$val['pk']:false),
                    'autoinc' => isset($val['extra'])?strtolower($val['extra']) == 'auto_increment':(isset($val['key'])?$val['key']:false),
                );
            }
        }
        return $info;
    }

    /**
     * ȡ�����ݿ�ı���Ϣ
     * @access public
     */
    public function getTables($dbName='') {
        if(C('DB_FETCH_TABLES_SQL')) {
            // ��������ı��ѯSQL
            $sql   = str_replace('%db%',$dbName,C('DB_FETCH_TABLES_SQL'));
        }else{
            switch($this->dbType) {
            case 'ORACLE':
            case 'OCI':
                $sql   = 'SELECT table_name FROM user_tables';
                break;
            case 'MSSQL':
            case 'SQLSRV':
                $sql   = "SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'";
                break;
            case 'PGSQL':
                $sql   = "select tablename as Tables_in_test from pg_tables where  schemaname ='public'";
                break;
            case 'IBASE':
                // ��ʱ��֧��
                E(L('_NOT_SUPPORT_DB_').':IBASE');
                break;
            case 'SQLITE':
                $sql   = "SELECT name FROM sqlite_master WHERE type='table' "
                         . "UNION ALL SELECT name FROM sqlite_temp_master "
                         . "WHERE type='table' ORDER BY name";
                 break;
            case 'MYSQL':
            default:
                if(!empty($dbName)) {
                   $sql    = 'SHOW TABLES FROM '.$dbName;
                }else{
                   $sql    = 'SHOW TABLES ';
                }
            }
        }
        $result = $this->query($sql);
        $info   =   array();
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }

    /**
     * limit����
     * @access protected
     * @param mixed $lmit
     * @return string
     */
    protected function parseLimit($limit) {
        $limitStr    = '';
        if(!empty($limit)) {
            switch($this->dbType){
                case 'PGSQL':
                case 'SQLITE':
                    $limit  =   explode(',',$limit);
                    if(count($limit)>1) {
                        $limitStr .= ' LIMIT '.$limit[1].' OFFSET '.$limit[0].' ';
                    }else{
                        $limitStr .= ' LIMIT '.$limit[0].' ';
                    }
                    break;
                case 'MSSQL':
                case 'SQLSRV':
                    break;
                case 'IBASE':
                    // ��ʱ��֧��
                    break;
                case 'ORACLE':
                case 'OCI':
                    break;
                case 'MYSQL':
                default:
                    $limitStr .= ' LIMIT '.$limit.' ';
            }
        }
        return $limitStr;
    }

    /**
     * �ֶκͱ�������
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        if($this->dbType=='MYSQL'){
            $key   =  trim($key);
            if(!preg_match('/[,\'\"\*\(\)`.\s]/',$key)) {
               $key = '`'.$key.'`';
            }
            return $key;            
        }else{
            return parent::parseKey($key);
        }

    }

    /**
     * �ر����ݿ�
     * @access public
     */
    public function close() {
        $this->_linkID = null;
    }

    /**
     * ���ݿ������Ϣ
     * ����ʾ��ǰ��SQL���
     * @access public
     * @return string
     */
    public function error() {
        if($this->PDOStatement) {
            $error = $this->PDOStatement->errorInfo();
            $this->error = $error[1].':'.$error[2];
        }else{
            $this->error = '';
        }
        if('' != $this->queryStr){
            $this->error .= "\n [ SQL��� ] : ".$this->queryStr;
        }
        trace($this->error,'','ERR');
        return $this->error;
    }

    /**
     * SQLָ�ȫ����
     * @access public
     * @param string $str  SQLָ��
     * @return string
     */
    public function escapeString($str) {
         switch($this->dbType) {
            case 'MSSQL':
            case 'SQLSRV':
            case 'MYSQL':
                return addslashes($str);
            case 'PGSQL':                
            case 'IBASE':                
            case 'SQLITE':
            case 'ORACLE':
            case 'OCI':
                return str_ireplace("'", "''", $str);
        }
    }

    /**
     * value����
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function parseValue($value) {
        if(is_string($value)) {
            $value =  strpos($value,':') === 0 ? $this->escapeString($value) : '\''.$this->escapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->escapeString($value[1]);
        }elseif(is_array($value)) {
            $value =  array_map(array($this, 'parseValue'),$value);
        }elseif(is_bool($value)){
            $value =  $value ? '1' : '0';
        }elseif(is_null($value)){
            $value =  'null';
        }
        return $value;
    }

    /**
     * ��ȡ������id
     * @access public
     * @return integer
     */
    public function getLastInsertId() {
         switch($this->dbType) {
            case 'PGSQL':
            case 'SQLITE':
            case 'MSSQL':
            case 'SQLSRV':
            case 'IBASE':
            case 'MYSQL':
                return $this->_linkID->lastInsertId();
            case 'ORACLE':
            case 'OCI':
                $sequenceName = $this->table;
                $vo = $this->query("SELECT {$sequenceName}.currval currval FROM dual");
                return $vo?$vo[0]["currval"]:0;
        }
    }
}