<?php

class SQL_C{

  var $server_name = "ZULFIQARPC\SQLEXPRESS"; //serverName\instanceName
  var $database = 'BEAMS_NGC_SALESFORCE1';
  var $user_name = 'sa';
  var $password = 'cl0udstrife';
  var $conn = NULL;

  function get_connection(){
    $this->conn = sqlsrv_connect($this->server_name, array("Database" => $this->database, "UID" => $this->user_name, "PWD" => $this->password));
    return $this->conn;
  }

  function close_connection(){
    sqlsrv_close($this->conn);
  }

  function get_primary_key($table, $field){
    $result = 0;
    if(!$table || !$field){
      return $result;
    }
    $res = sqlsrv_query($this->conn, 'SELECT MAX(' . $field . ') as pk FROM ' . $table);
    while($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)){
      $result = ($row && $row['pk']) ? $row['pk'] : 0;
    }
    return $result;
  }

  function get_record_sf_id($table, $sf_id, $extra = array()){
    $query = 'SELECT * from ' . $table . ' WHERE SF_ID = \'' . $sf_id . '\'';
    if(count($extra) > 0){
      $q = array();
      foreach($extra as $f => $v){
        $q[] = "$f = '$v'";
      }
      $query .= " AND " . implode(' AND ', $q);
    }
    $res = sqlsrv_query($this->conn, $query);
    if($res === FALSE){
      return FALSE;
    }
    $record = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
    return $record;
  }

  function get_records(){
    $records = array();
    $query = 'SELECT * from account WHERE SYNCH_STATUS IN (0,1)';
    log_sql_data($query, 'QUERY:', TRUE);
    $res = sqlsrv_query($this->conn, $query);
    $count = 0;
    while($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)){
      $records[$row['COMPANY']][$row['SLCODE']]['Account'] = $row;
      $records[$row['COMPANY']][$row['SLCODE']]['Contact'] = FALSE;
      $count++;
    }
    log_sql_data($count . ' account rows returned', 'RESPONSE:');
    $query1 = 'SELECT * from contacts WHERE SYNCH_STATUS IN (0,1)';
    log_sql_data($query, 'QUERY:');
    $res1 = sqlsrv_query($this->conn, $query1);
    $count = 0;
    while($row = sqlsrv_fetch_array($res1, SQLSRV_FETCH_ASSOC)){
      $records[$row['COMPANY']][$row['SLCODE']]['Contact'][$row['SRNO']] = $row;
      if(!isset($records[$row['COMPANY']][$row['SLCODE']]['Account'])){
        $records[$row['COMPANY']][$row['SLCODE']]['Account'] = FALSE;
      }
      $count++;
    }
    log_sql_data($count . ' account rows returned', 'RESPONSE:');
    return $records;
  }

  function get_products(){
    $records = array();
    $query = 'SELECT TOP 5000 * from productmaster WHERE SYNCH_STATUS IN (0,1)';
    log_sql_data($query, 'QUERY:', TRUE);
    $res = sqlsrv_query($this->conn, $query);
    if(!$res){
      log_sql_data(sqlsrv_errors());
    }
    while($row = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC)){
      $records[$row['UNIQUE_ID']] = $row;
    }
    log_sql_data(count($records).' rows returned', 'RESPONSE:');
    return $records;
  }

  function insert($table, $record){
    if(count($record) < 1 || empty($table)){
      return FALSE;
    }
    $keys = array_keys($record);
    $values = array_values($record);
    foreach($values as &$value){
      $value = str_replace("'", "''", $value);
    }
    $query = 'INSERT INTO ' . $table . ' (' . implode(',', $keys) . ') values(\'' . implode('\',\'', $values) . '\')';
    log_sql_data($record, 'RECORD:', TRUE);
    log_sql_data($query, 'QUERY:');
    $res = sqlsrv_query($this->conn, $query);
    if(!$res){
      log_sql_data(sqlsrv_errors(), 'ERROR:');
    }
    log_sql_data($res, 'RESPONSE:');
    return $res;
  }

  function update($table, $record, $sf_id, $extra = array()){
    if(count($record) < 1 || empty($sf_id) || empty($table)){
      return FALSE;
    }
    $set = array();
    foreach($record as $key => $value){
      $value = str_replace("'", "''", $value);
      $set[] = "$key = '$value'";
    }
    $query = 'UPDATE ' . $table . ' SET ' . implode(',', $set) . ' WHERE SF_ID = \'' . $sf_id . '\'';
    if(count($extra) > 0){
      $q = array();
      foreach($extra as $f => $v){
        $q[] = "$f = '$v'";
      }
      $query .= " AND " . implode(' AND ', $q);
    }
    log_sql_data($record, 'RECORD:', TRUE);
    log_sql_data($query, 'QUERY:');
    $res = sqlsrv_query($this->conn, $query);
    if(!$res){
      log_sql_data(sqlsrv_errors(), 'ERROR:');
    }
    log_sql_data($res, 'RESPONSE:');
    return $res;
  }

  function update_account_status($keys, $status, $sf_id){
    $query = 'UPDATE account SET SYNCH_STATUS = \'' . $status . '\', SF_ID = \'' . $sf_id . '\' WHERE COMPANY = \'' . $keys['COMPANY'] . '\' AND SLCODE = \'' . $keys['SLCODE'] . '\'';
    log_sql_data($query, 'QUERY:', TRUE);
    $res = sqlsrv_query($this->conn, $query);
    if(!$res){
      log_sql_data(sqlsrv_errors(), 'ERROR:');
    }
    log_sql_data($res, 'RESPONSE:');
    return $res;
  }

  function update_contact_status($keys, $status, $sf_id){
    $query = 'UPDATE contacts SET SYNCH_STATUS = \'' . $status . '\', SF_ID = \'' . $sf_id . '\' WHERE COMPANY = \'' . $keys['COMPANY'] . '\' AND SLCODE = \'' . $keys['SLCODE'] . '\' AND SRNO = \'' . $keys['SRNO'] . '\'';
    log_sql_data($query, 'QUERY:', TRUE);
    $res = sqlsrv_query($this->conn, $query);
    if(!$res){
      log_sql_data(sqlsrv_errors(), 'ERROR:');
    }
    log_sql_data($res, 'RESPONSE:');
    return $res;
  }

  function update_product_status($keys, $status, $sf_id){
    $query = 'UPDATE productmaster SET SYNCH_STATUS = \'' . $status . '\', SF_ID = \'' . $sf_id . '\' WHERE COMPANY = \'' . $keys['COMPANY'] . '\' AND STKCODE = \'' . $keys['STKCODE'] . '\' AND PRICE_LIST = \'' . $keys['PRICE_LIST'] . '\'';
    log_sql_data($query, 'QUERY:', TRUE);
    $res = sqlsrv_query($this->conn, $query);
    if(!$res){
      log_sql_data(sqlsrv_errors(), 'ERROR:');
    }
    log_sql_data($res, 'RESPONSE:');
    return $res;
  }

}
