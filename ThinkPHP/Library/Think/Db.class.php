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
namespace Think;
/**
 * ThinkPHP ���ݿ��м��ʵ����
 */
class Db {
    // ���ݿ�����
    protected $dbType     = null;
    // �Ƿ��Զ��ͷŲ�ѯ���
    protected $autoFree   = false;
    // ��ǰ����������ģ����
    protected $model      = '_think_';
    // �Ƿ�ʹ����������
    protected $pconnect   = false;
    // ��ǰSQLָ��
    protected $queryStr   = '';
    protected $modelSql   = array();
    // ������ID
    protected $lastInsID  = null;
    // ���ػ���Ӱ���¼��
    protected $numRows    = 0;
    // �����ֶ���
    protected $numCols    = 0;
    // ����ָ����
    protected $transTimes = 0;
    // ������Ϣ
    protected $error      = '';
    // ���ݿ�����ID ֧�ֶ������
    protected $linkID     = array();
    // ��ǰ����ID
    protected $_linkID    = null;
    // ��ǰ��ѯID
    protected $queryID    = null;
    // �Ƿ��Ѿ��������ݿ�
    protected $connected  = false;
    // ���ݿ����Ӳ�������
    protected $config     = '';
    // ���ݿ���ʽ
    protected $comparison = array('eq'=>'=','neq'=>'<>','gt'=>'>','egt'=>'>=','lt'=>'<','elt'=>'<=','notlike'=>'NOT LIKE','like'=>'LIKE','in'=>'IN','notin'=>'NOT IN');
    // ��ѯ���ʽ
    protected $selectSql  = 'SELECT%DISTINCT% %FIELD% FROM %TABLE%%JOIN%%WHERE%%GROUP%%HAVING%%ORDER%%LIMIT% %UNION%%COMMENT%';
    // ������
    protected $bind       = array();

    /**
     * ȡ�����ݿ���ʵ��
     * @static
     * @access public
     * @return mixed �������ݿ�������
     */
    public static function getInstance($db_config='') {
		static $_instance	=	array();
		$guid	=	to_guid_string($db_config);
		if(!isset($_instance[$guid])){
			$obj	=	new Db();
			$_instance[$guid]	=	$obj->factory($db_config);
		}
		return $_instance[$guid];
    }

    /**
     * �������ݿ� ֧�������ļ����� DSN
     * @access public
     * @param mixed $db_config ���ݿ�������Ϣ
     * @return string
     */
    public function factory($db_config='') {
        // ��ȡ���ݿ�����
        $db_config = $this->parseConfig($db_config);
        if(empty($db_config['dbms']))
            E(L('_NO_DB_CONFIG_'));
        // ���ݿ�����
        if(strpos($db_config['dbms'],'\\')){
            $class  =   $db_config['dbms'];
        }else{
            $dbType =   ucwords(strtolower($db_config['dbms']));
            $class  =   'Think\\Db\\Driver\\'. $dbType;            
        }
        // ���������
        if(class_exists($class)) {
            $db = new $class($db_config);
        }else {
            // ��û�ж���
            E(L('_NO_DB_DRIVER_').': ' . $class);
        }
        return $db;
    }

    /**
     * ����DSN��ȡ���ݿ����� ���ش�д
     * @access protected
     * @param string $dsn  dsn�ַ���
     * @return string
     */
    protected function _getDsnType($dsn) {
        $match  =  explode(':',$dsn);
        $dbType = strtoupper(trim($match[0]));
        return $dbType;
    }

    /**
     * �������ݿ�������Ϣ��֧�������DSN
     * @access private
     * @param mixed $db_config ���ݿ�������Ϣ
     * @return string
     */
    private function parseConfig($db_config='') {
        if ( !empty($db_config) && is_string($db_config)) {
            // ���DSN�ַ�������н���
            $db_config = $this->parseDSN($db_config);
        }elseif(is_array($db_config)) { // ��������
             $db_config =   array_change_key_case($db_config);
             $db_config = array(
                  'dbms'      =>  $db_config['db_type'],
                  'username'  =>  $db_config['db_user'],
                  'password'  =>  $db_config['db_pwd'],
                  'hostname'  =>  $db_config['db_host'],
                  'hostport'  =>  $db_config['db_port'],
                  'database'  =>  $db_config['db_name'],
                  'dsn'       =>  $db_config['db_dsn'],
                  'params'    =>  $db_config['db_params'],
                  'charset'   =>  isset($db_config['db_charset'])?$db_config['db_charset']:'utf8',
             );
        }elseif(empty($db_config)) {
            // �������Ϊ�գ���ȡ�����ļ�����
            if( C('DB_DSN') && 'pdo' != strtolower(C('DB_TYPE')) ) { // ���������DB_DSN ������
                $db_config =  $this->parseDSN(C('DB_DSN'));
            }else{
                $db_config = array (
                    'dbms'      =>  C('DB_TYPE'),
                    'username'  =>  C('DB_USER'),
                    'password'  =>  C('DB_PWD'),
                    'hostname'  =>  C('DB_HOST'),
                    'hostport'  =>  C('DB_PORT'),
                    'database'  =>  C('DB_NAME'),
                    'dsn'       =>  C('DB_DSN'),
                    'params'    =>  C('DB_PARAMS'),
                    'charset'   =>  C('DB_CHARSET'),
                );
            }
        }
        return $db_config;
    }

    /**
     * ��ʼ�����ݿ�����
     * @access protected
     * @param boolean $master ��������
     * @return void
     */
    protected function initConnect($master=true) {
        if(1 == C('DB_DEPLOY_TYPE'))
            // ���÷ֲ�ʽ���ݿ�
            $this->_linkID = $this->multiConnect($master);
        else
            // Ĭ�ϵ����ݿ�
            if ( !$this->connected ) $this->_linkID = $this->connect();
    }

    /**
     * ���ӷֲ�ʽ������
     * @access protected
     * @param boolean $master ��������
     * @return void
     */
    protected function multiConnect($master=false) {
        foreach ($this->config as $key=>$val){
            $_config[$key]      =   explode(',',$val);
        }        
        // ���ݿ��д�Ƿ����
        if(C('DB_RW_SEPARATE')){
            // ����ʽ���ö�д����
            if($master)
                // ��������д��
                $r  =   floor(mt_rand(0,C('DB_MASTER_NUM')-1));
            else{
                if(is_numeric(C('DB_SLAVE_NO'))) {// ָ����������
                    $r = C('DB_SLAVE_NO');
                }else{
                    // ���������Ӵӷ�����
                    $r = floor(mt_rand(C('DB_MASTER_NUM'),count($_config['hostname'])-1));   // ÿ��������ӵ����ݿ�
                }
            }
        }else{
            // ��д���������ַ�����
            $r = floor(mt_rand(0,count($_config['hostname'])-1));   // ÿ��������ӵ����ݿ�
        }
        $db_config = array(
            'username'  =>  isset($_config['username'][$r])?$_config['username'][$r]:$_config['username'][0],
            'password'  =>  isset($_config['password'][$r])?$_config['password'][$r]:$_config['password'][0],
            'hostname'  =>  isset($_config['hostname'][$r])?$_config['hostname'][$r]:$_config['hostname'][0],
            'hostport'  =>  isset($_config['hostport'][$r])?$_config['hostport'][$r]:$_config['hostport'][0],
            'database'  =>  isset($_config['database'][$r])?$_config['database'][$r]:$_config['database'][0],
            'dsn'       =>  isset($_config['dsn'][$r])?$_config['dsn'][$r]:$_config['dsn'][0],
            'params'    =>  isset($_config['params'][$r])?$_config['params'][$r]:$_config['params'][0],
            'charset'   =>  isset($_config['charset'][$r])?$_config['charset'][$r]:$_config['charset'][0],            
        );
        return $this->connect($db_config,$r);
    }

    /**
     * DSN����
     * ��ʽ�� mysql://username:passwd@localhost:3306/DbName#charset
     * @static
     * @access public
     * @param string $dsnStr
     * @return array
     */
    public function parseDSN($dsnStr) {
        if( empty($dsnStr) ){return false;}
        $info = parse_url($dsnStr);
        if($info['scheme']){
            $dsn = array(
            'dbms'      =>  $info['scheme'],
            'username'  =>  isset($info['user']) ? $info['user'] : '',
            'password'  =>  isset($info['pass']) ? $info['pass'] : '',
            'hostname'  =>  isset($info['host']) ? $info['host'] : '',
            'hostport'  =>  isset($info['port']) ? $info['port'] : '',
            'database'  =>  isset($info['path']) ? substr($info['path'],1) : '',
            'charset'   =>  isset($info['fragment'])?$info['fragment']:'utf8',
            );
        }else {
            preg_match('/^(.*?)\:\/\/(.*?)\:(.*?)\@(.*?)\:([0-9]{1, 6})\/(.*?)$/',trim($dsnStr),$matches);
            $dsn = array (
            'dbms'      =>  $matches[1],
            'username'  =>  $matches[2],
            'password'  =>  $matches[3],
            'hostname'  =>  $matches[4],
            'hostport'  =>  $matches[5],
            'database'  =>  $matches[6]
            );
        }
        $dsn['dsn'] =  ''; // ����������Ϣ����
        return $dsn;
     }

    /**
     * ���ݿ���� ��¼��ǰSQL
     * @access protected
     */
    protected function debug() {
        $this->modelSql[$this->model]   =  $this->queryStr;
        $this->model  =   '_think_';
        // ��¼��������ʱ��
        if (C('DB_SQL_LOG')) {
            G('queryEndTime');
            trace($this->queryStr.' [ RunTime:'.G('queryStartTime','queryEndTime',6).'s ]','','SQL');
        }
    }

    /**
     * ����������
     * @access protected
     * @return string
     */
    protected function parseLock($lock=false) {
        if(!$lock) return '';
        if('ORACLE' == $this->dbType) {
            return ' FOR UPDATE NOWAIT ';
        }
        return ' FOR UPDATE ';
    }

    /**
     * set����
     * @access protected
     * @param array $data
     * @return string
     */
    protected function parseSet($data) {
        foreach ($data as $key=>$val){
            if(is_array($val) && 'exp' == $val[0]){
                $set[]  =   $this->parseKey($key).'='.$val[1];
            }elseif(is_scalar($val) || is_null($val)) { // ���˷Ǳ�������
              if(C('DB_BIND_PARAM') && 0 !== strpos($val,':')){
                $name   =   md5($key);
                $set[]  =   $this->parseKey($key).'=:'.$name;
                $this->bindParam($name,$val);
              }else{
                $set[]  =   $this->parseKey($key).'='.$this->tpParseValue($val);
              }
            }
        }
        return ' SET '.implode(',',$set);
    }

     /**
     * ������
     * @access protected
     * @param string $name �󶨲�����
     * @param mixed $value ��ֵ
     * @return void
     */
    protected function bindParam($name,$value){
        $this->bind[':'.$name]  =   $value;
    }

     /**
     * �����󶨷���
     * @access protected
     * @param array $bind
     * @return array
     */
    protected function parseBind($bind){
        $bind           =   array_merge($this->bind,$bind);
        $this->bind     =   array();
        return $bind;
    }

    /**
     * �ֶ�������
     * @access protected
     * @param string $key
     * @return string
     */
    protected function parseKey(&$key) {
        return $key;
    }
    
    /**
     * value����
     * @access protected
     * @param mixed $value
     * @return string
     */
    protected function tpParseValue($value) {
        if(is_string($value)) {
            $value =  '\''.$this->tpEscapeString($value).'\'';
        }elseif(isset($value[0]) && is_string($value[0]) && strtolower($value[0]) == 'exp'){
            $value =  $this->tpEscapeString($value[1]);
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
     * field����
     * @access protected
     * @param mixed $fields
     * @return string
     */
    protected function parseField($fields) {
        if(is_string($fields) && strpos($fields,',')) {
            $fields    = explode(',',$fields);
        }
        if(is_array($fields)) {
            // �������鷽ʽ���ֶ�����֧��
            // ֧�� 'field1'=>'field2' �������ֶα�������
            $array   =  array();
            foreach ($fields as $key=>$field){
                if(!is_numeric($key))
                    $array[] =  $this->parseKey($key).' AS '.$this->parseKey($field);
                else
                    $array[] =  $this->parseKey($field);
            }
            $fieldsStr = implode(',', $array);
        }elseif(is_string($fields) && !empty($fields)) {
            $fieldsStr = $this->parseKey($fields);
        }else{
            $fieldsStr = '*';
        }
        //TODO ����ǲ�ѯȫ���ֶΣ�������join�ķ�ʽ����ô�Ͱ�Ҫ��ı�Ӹ������������ֶα�����
        return $fieldsStr;
    }

    /**
     * table����
     * @access protected
     * @param mixed $table
     * @return string
     */
    protected function parseTable($tables) {
        if(is_array($tables)) {// ֧�ֱ�������
            $array   =  array();
            foreach ($tables as $table=>$alias){
                if(!is_numeric($table))
                    $array[] =  $this->parseKey($table).' '.$this->parseKey($alias);
                else
                    $array[] =  $this->parseKey($table);
            }
            $tables  =  $array;
        }elseif(is_string($tables)){
            $tables  =  explode(',',$tables);
            array_walk($tables, array(&$this, 'parseKey'));
        }
        $tables = implode(',',$tables);
        return $tables;
    }

    /**
     * where����
     * @access protected
     * @param mixed $where
     * @return string
     */
    protected function parseWhere($where) {
        $whereStr = '';
        if(is_string($where)) {
            // ֱ��ʹ���ַ�������
            $whereStr = $where;
        }else{ // ʹ��������ʽ
            $operate  = isset($where['_logic'])?strtoupper($where['_logic']):'';
            if(in_array($operate,array('AND','OR','XOR'))){
                // �����߼�������� ���� OR XOR AND NOT
                $operate    =   ' '.$operate.' ';
                unset($where['_logic']);
            }else{
                // Ĭ�Ͻ��� AND ����
                $operate    =   ' AND ';
            }
            foreach ($where as $key=>$val){
                $whereStr .= '( ';
                if(is_numeric($key)){
                    $key  = '_complex';
                }                    
                if(0===strpos($key,'_')) {
                    // ���������������ʽ
                    $whereStr   .= $this->parseThinkWhere($key,$val);
                }else{
                    // ��ѯ�ֶεİ�ȫ����
                    if(!preg_match('/^[A-Z_\|\&\-.a-z0-9\(\)\,]+$/',trim($key))){
                        E(L('_EXPRESS_ERROR_').':'.$key);
                    }
                    // ������֧��
                    $multi  = is_array($val) &&  isset($val['_multi']);
                    $key    = trim($key);
                    if(strpos($key,'|')) { // ֧�� name|title|nickname ��ʽ�����ѯ�ֶ�
                        $array =  explode('|',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' OR ',$str);
                    }elseif(strpos($key,'&')){
                        $array =  explode('&',$key);
                        $str   =  array();
                        foreach ($array as $m=>$k){
                            $v =  $multi?$val[$m]:$val;
                            $str[]   = '('.$this->parseWhereItem($this->parseKey($k),$v).')';
                        }
                        $whereStr .= implode(' AND ',$str);
                    }else{
                        $whereStr .= $this->parseWhereItem($this->parseKey($key),$val);
                    }
                }
                $whereStr .= ' )'.$operate;
            }
            $whereStr = substr($whereStr,0,-strlen($operate));
        }
        return empty($whereStr)?'':' WHERE '.$whereStr;
    }

    // where�ӵ�Ԫ����
    protected function parseWhereItem($key,$val) {
        $whereStr = '';
        if(is_array($val)) {
            if(is_string($val[0])) {
                if(preg_match('/^(EQ|NEQ|GT|EGT|LT|ELT)$/i',$val[0])) { // �Ƚ�����
                    $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->tpParseValue($val[1]);
                }elseif(preg_match('/^(NOTLIKE|LIKE)$/i',$val[0])){// ģ������
                    if(is_array($val[1])) {
                        $likeLogic  =   isset($val[2])?strtoupper($val[2]):'OR';
                        if(in_array($likeLogic,array('AND','OR','XOR'))){
                            $likeStr    =   $this->comparison[strtolower($val[0])];
                            $like       =   array();
                            foreach ($val[1] as $item){
                                $like[] = $key.' '.$likeStr.' '.$this->tpParseValue($item);
                            }
                            $whereStr .= '('.implode(' '.$likeLogic.' ',$like).')';                          
                        }
                    }else{
                        $whereStr .= $key.' '.$this->comparison[strtolower($val[0])].' '.$this->tpParseValue($val[1]);
                    }
                }elseif('exp'==strtolower($val[0])){ // ʹ�ñ��ʽ
                    $whereStr .= ' ('.$key.' '.$val[1].') ';
                }elseif(preg_match('/IN/i',$val[0])){ // IN ����
                    if(isset($val[2]) && 'exp'==$val[2]) {
                        $whereStr .= $key.' '.strtoupper($val[0]).' '.$val[1];
                    }else{
                        if(is_string($val[1])) {
                             $val[1] =  explode(',',$val[1]);
                        }
                        $zone      =   implode(',',$this->tpParseValue($val[1]));
                        $whereStr .= $key.' '.strtoupper($val[0]).' ('.$zone.')';
                    }
                }elseif(preg_match('/BETWEEN/i',$val[0])){ // BETWEEN����
                    $data = is_string($val[1])? explode(',',$val[1]):$val[1];
                    $whereStr .=  ' ('.$key.' '.strtoupper($val[0]).' '.$this->tpParseValue($data[0]).' AND '.$this->tpParseValue($data[1]).' )';
                }else{
                    E(L('_EXPRESS_ERROR_').':'.$val[0]);
                }
            }else {
                $count = count($val);
                $rule  = isset($val[$count-1]) ? (is_array($val[$count-1]) ? strtoupper($val[$count-1][0]) : strtoupper($val[$count-1]) ) : '' ; 
                if(in_array($rule,array('AND','OR','XOR'))) {
                    $count  = $count -1;
                }else{
                    $rule   = 'AND';
                }
                for($i=0;$i<$count;$i++) {
                    $data = is_array($val[$i])?$val[$i][1]:$val[$i];
                    if('exp'==strtolower($val[$i][0])) {
                        $whereStr .= '('.$key.' '.$data.') '.$rule.' ';
                    }else{
                        $whereStr .= '('.$this->parseWhereItem($key,$val[$i]).') '.$rule.' ';
                    }
                }
                $whereStr = substr($whereStr,0,-4);
            }
        }else {
            //���ַ��������ֶβ���ģ��ƥ��
            if(C('DB_LIKE_FIELDS') && preg_match('/('.C('DB_LIKE_FIELDS').')/i',$key)) {
                $val  =  '%'.$val.'%';
                $whereStr .= $key.' LIKE '.$this->tpParseValue($val);
            }else {
                $whereStr .= $key.' = '.$this->tpParseValue($val);
            }
        }
        return $whereStr;
    }

    /**
     * ������������
     * @access protected
     * @param string $key
     * @param mixed $val
     * @return string
     */
    protected function parseThinkWhere($key,$val) {
        $whereStr   = '';
        switch($key) {
            case '_string':
                // �ַ���ģʽ��ѯ����
                $whereStr = $val;
                break;
            case '_complex':
                // ���ϲ�ѯ����
                $whereStr   =   is_string($val)? $val : substr($this->parseWhere($val),6);
                break;
            case '_query':
                // �ַ���ģʽ��ѯ����
                parse_str($val,$where);
                if(isset($where['_logic'])) {
                    $op   =  ' '.strtoupper($where['_logic']).' ';
                    unset($where['_logic']);
                }else{
                    $op   =  ' AND ';
                }
                $array   =  array();
                foreach ($where as $field=>$data)
                    $array[] = $this->parseKey($field).' = '.$this->tpParseValue($data);
                $whereStr   = implode($op,$array);
                break;
        }
        return $whereStr;
    }

    /**
     * limit����
     * @access protected
     * @param mixed $lmit
     * @return string
     */
    protected function parseLimit($limit) {
        return !empty($limit)?   ' LIMIT '.$limit.' ':'';
    }

    /**
     * join����
     * @access protected
     * @param array $join
     * @return string
     */
    protected function parseJoin($join) {
        $joinStr = '';
        if(!empty($join)) {
            $joinStr    =   ' '.implode(' ',$join).' ';
        }
        return $joinStr;
    }

    /**
     * order����
     * @access protected
     * @param mixed $order
     * @return string
     */
    protected function parseOrder($order) {
        if(is_array($order)) {
            $array   =  array();
            foreach ($order as $key=>$val){
                if(is_numeric($key)) {
                    $array[] =  $this->parseKey($val);
                }else{
                    $array[] =  $this->parseKey($key).' '.$val;
                }
            }
            $order   =  implode(',',$array);
        }
        return !empty($order)?  ' ORDER BY '.$order:'';
    }

    /**
     * group����
     * @access protected
     * @param mixed $group
     * @return string
     */
    protected function parseGroup($group) {
        return !empty($group)? ' GROUP BY '.$group:'';
    }

    /**
     * having����
     * @access protected
     * @param string $having
     * @return string
     */
    protected function parseHaving($having) {
        return  !empty($having)?   ' HAVING '.$having:'';
    }

    /**
     * comment����
     * @access protected
     * @param string $comment
     * @return string
     */
    protected function parseComment($comment) {
        return  !empty($comment)?   ' /* '.$comment.' */':'';
    }

    /**
     * distinct����
     * @access protected
     * @param mixed $distinct
     * @return string
     */
    protected function parseDistinct($distinct) {
        return !empty($distinct)?   ' DISTINCT ' :'';
    }

    /**
     * union����
     * @access protected
     * @param mixed $union
     * @return string
     */
    protected function parseUnion($union) {
        if(empty($union)) return '';
        if(isset($union['_all'])) {
            $str  =   'UNION ALL ';
            unset($union['_all']);
        }else{
            $str  =   'UNION ';
        }
        foreach ($union as $u){
            $sql[] = $str.(is_array($u)?$this->buildSelectSql($u):$u);
        }
        return implode(' ',$sql);
    }

    /**
     * �����¼
     * @access public
     * @param mixed $data ����
     * @param array $options �������ʽ
     * @param boolean $replace �Ƿ�replace
     * @return false | integer
     */
    public function insert($data,$options=array(),$replace=false) {
		$values  =  $fields    = array();
        $this->model  =   $options['model'];
        foreach ($data as $key=>$val){
			if(is_array($val) && 'exp' == $val[0]){
                $fields[]   =  $this->parseKey($key);
                $values[]   =  $val[1];
            }elseif(is_scalar($val) || is_null($val)) { // ���˷Ǳ�������
              $fields[]   =  $this->parseKey($key);
              if(C('DB_BIND_PARAM') && 0 !== strpos($val,':')){
                $name       =   md5($key);
                $values[]   =   ':'.$name;
                $this->bindParam($name,$val);
              }else{
                $values[]   =  $this->tpParseValue($val);
              }                
            }
        }
        $sql   =  ($replace?'REPLACE':'INSERT').' INTO '.$this->parseTable($options['table']).' ('.implode(',', $fields).') VALUES ('.implode(',', $values).')';
        $sql   .= $this->parseLock(isset($options['lock'])?$options['lock']:false);
        $sql   .= $this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * ͨ��Select��ʽ�����¼
     * @access public
     * @param string $fields Ҫ��������ݱ��ֶ���
     * @param string $table Ҫ��������ݱ���
     * @param array $option  ��ѯ���ݲ���
     * @return false | integer
     */
    public function selectInsert($fields,$table,$options=array()) {
        $this->model  =   $options['model'];
        if(is_string($fields))   $fields    = explode(',',$fields);
        array_walk($fields, array($this, 'parseKey'));
        $sql   =    'INSERT INTO '.$this->parseTable($table).' ('.implode(',', $fields).') ';
        $sql   .= $this->buildSelectSql($options);
        return $this->execute($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * ���¼�¼
     * @access public
     * @param mixed $data ����
     * @param array $options ���ʽ
     * @return false | integer
     */
    public function update($data,$options) {
        $this->model  =   $options['model'];
        $sql   = 'UPDATE '
            .$this->parseTable($options['table'])
            .$this->parseSet($data)
            .$this->parseWhere(!empty($options['where'])?$options['where']:'')
            .$this->parseOrder(!empty($options['order'])?$options['order']:'')
            .$this->parseLimit(!empty($options['limit'])?$options['limit']:'')
            .$this->parseLock(isset($options['lock'])?$options['lock']:false)
            .$this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * ɾ����¼
     * @access public
     * @param array $options ���ʽ
     * @return false | integer
     */
    public function delete($options=array()) {
        $this->model  =   $options['model'];
        $sql   = 'DELETE FROM '
            .$this->parseTable($options['table'])
            .$this->parseWhere(!empty($options['where'])?$options['where']:'')
            .$this->parseOrder(!empty($options['order'])?$options['order']:'')
            .$this->parseLimit(!empty($options['limit'])?$options['limit']:'')
            .$this->parseLock(isset($options['lock'])?$options['lock']:false)
            .$this->parseComment(!empty($options['comment'])?$options['comment']:'');
        return $this->execute($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
    }

    /**
     * ���Ҽ�¼
     * @access public
     * @param array $options ���ʽ
     * @return mixed
     */
    public function select($options=array()) {
        $this->model  =   $options['model'];
        $sql        =   $this->buildSelectSql($options);
        $result     =   $this->query($sql,$this->parseBind(!empty($options['bind'])?$options['bind']:array()));
        return $result;
    }

    /**
     * ���ɲ�ѯSQL
     * @access public
     * @param array $options ���ʽ
     * @return string
     */
    public function buildSelectSql($options=array()) {
        if(isset($options['page'])) {
            // ����ҳ������limit
            if(strpos($options['page'],',')) {
                list($page,$listRows) =  explode(',',$options['page']);
            }else{
                $page = $options['page'];
            }
            $page    =  $page?:1;
            $listRows=  isset($listRows)?$listRows:(is_numeric($options['limit'])?$options['limit']:20);
            $offset  =  $listRows*((int)$page-1);
            $options['limit'] =  $offset.','.$listRows;
        }
        if(C('DB_SQL_BUILD_CACHE')) { // SQL��������
            $key    =  md5(serialize($options));
            $value  =  S($key);
            if(false !== $value) {
                return $value;
            }
        }
        $sql  =     $this->parseSql($this->selectSql,$options);
        $sql .=     $this->parseLock(isset($options['lock'])?$options['lock']:false);
        if(isset($key)) { // д��SQL��������
            S($key,$sql,array('expire'=>0,'length'=>C('DB_SQL_BUILD_LENGTH'),'queue'=>C('DB_SQL_BUILD_QUEUE')));
        }
        return $sql;
    }

    /**
     * �滻SQL����б��ʽ
     * @access public
     * @param array $options ���ʽ
     * @return string
     */
    public function parseSql($sql,$options=array()){
        $sql   = str_replace(
            array('%TABLE%','%DISTINCT%','%FIELD%','%JOIN%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%','%UNION%','%COMMENT%'),
            array(
                $this->parseTable($options['table']),
                $this->parseDistinct(isset($options['distinct'])?$options['distinct']:false),
                $this->parseField(!empty($options['field'])?$options['field']:'*'),
                $this->parseJoin(!empty($options['join'])?$options['join']:''),
                $this->parseWhere(!empty($options['where'])?$options['where']:''),
                $this->parseGroup(!empty($options['group'])?$options['group']:''),
                $this->parseHaving(!empty($options['having'])?$options['having']:''),
                $this->parseOrder(!empty($options['order'])?$options['order']:''),
                $this->parseLimit(!empty($options['limit'])?$options['limit']:''),
                $this->parseUnion(!empty($options['union'])?$options['union']:''),
                $this->parseComment(!empty($options['comment'])?$options['comment']:'')
            ),$sql);
        return $sql;
    }

    /**
     * ��ȡ���һ�β�ѯ��sql��� 
     * @param string $model  ģ����
     * @access public
     * @return string
     */
    public function getLastSql($model='') {
        return $model?$this->modelSql[$model]:$this->queryStr;
    }

    /**
     * ��ȡ��������ID
     * @access public
     * @return string
     */
    public function getLastInsID() {
        return $this->lastInsID;
    }

    /**
     * ��ȡ����Ĵ�����Ϣ
     * @access public
     * @return string
     */
    public function getError() {
        return $this->error;
    }

    /**
     * SQLָ�ȫ����
     * @access public
     * @param string $str  SQL�ַ���
     * @return string
     */
    public function tpEscapeString($str) {
        return addslashes($str);
    }

    /**
     * ���õ�ǰ����ģ��
     * @access public
     * @param string $model  ģ����
     * @return void
     */
    public function setModel($model){
        $this->model =  $model;
    }

   /**
     * ��������
     * @access public
     */
    public function __destruct() {
        // �ͷŲ�ѯ
        if ($this->queryID){
            $this->free();
        }
        // �ر�����
        $this->close();
    }

    // �ر����ݿ� �������ඨ��
    public function close(){}
}