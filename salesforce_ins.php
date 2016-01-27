<?php

require_once ('soapclient/SforceEnterpriseClient.php');

class salesforce_ins{

  var $user_name = 'devngc@tenacre.ae';
  var $password = 'Tenacre@2016a';
  var $token = 'TKAeW6wrX3lqtNv7N19flnSu';
  var $conn = NULL;

  public function __construct(){
    $this->conn = new SforceEnterpriseClient();
    $this->conn->createConnection('ngc.jsp.xml');
    $this->conn->login($this->user_name, $this->password . $this->token);
  }

  public function get_connection(){
    return $this->conn;
  }

  public function get_records(){
    $result = array();
    $query = 'SELECT ' . account::get_sf_fields() . ' FROM Account WHERE LastModifiedDate > ' . utcDateFormat(time() - 3600);
    $response = $this->conn->query($query);
    foreach($response->records as $record){
      $record->Contacts = array();
      $result[$record->Id] = $record;
    }
    $query = 'SELECT ' . contact::get_sf_fields() . ' FROM Contact WHERE LastModifiedDate > ' . utcDateFormat(time() - 3600);
    $response = $this->conn->query($query);
    foreach($response->records as $record){
      if(!isset($result[$record->AccountId]->Contacts)){
        $result[$record->AccountId]->Contacts = array();
      }
      $result[$record->AccountId]->Contacts[] = $record;
    }
    return $result;
  }

  public function get_products(){
    $result = array();
    $query = 'SELECT Id, Unitprice, Pricebook2.Id, Pricebook2.Name, ' . product::get_sf_fields() . ', LastModifiedDate FROM PricebookEntry WHERE LastModifiedDate > ' . utcDateFormat(time() + 7200);
    $response = $this->conn->query($query);
    foreach($response->records as $record){
      if(!isset($result[$record->Product2->Id])){
        $result[$record->Product2->Id] = $record->Product2;
      }
      $key = 'Standard';
      switch($record->Pricebook2->Name){
        case 'Price Book 1':
          $key = 'PRICE1';
          break;
        case 'Price Book 2':
          $key = 'PRICE2';
          break;
        case 'Price Book 3':
          $key = 'PRICE3';
          break;
      }
      $result[$record->Product2->Id]->$key = $record->UnitPrice;
      $result[$record->Product2->Id]->LastModifiedDate = $record->LastModifiedDate;
    }
    return $result;
  }

  public function get_pricebooks(){
    $result = array();
    $query = 'SELECT Id, Name FROM Pricebook2';
    $response = $this->conn->query($query);
    foreach($response->records as $record){
      $key = 'Standard';
      switch($record->Name){
        case 'Price Book 1':
          $key = 'PRICE1';
          break;
        case 'Price Book 2':
          $key = 'PRICE2';
          break;
        case 'Price Book 3':
          $key = 'PRICE3';
          break;
      }
      $result[$key] = $record->Id;
    }
    return $result;
  }

  public function insert($object, $records){
    $ids = FALSE;
    try{
      if(count($records) > 0){
        $response = $this->conn->create($records, $object);
        foreach($response as $result){
          $ids = $result->id ? : 0;
        }
      }
    }
    catch(Exception $e){
      log_force_data($records, 'RECORD:', TRUE);
      log_force_data('Soap Error ' . $e->getMessage(), 'ERROR:');
    }
    return $ids;
  }

  public function update($object, $records){
    $ids = FALSE;
    try{
      if(count($records) > 0){
        $response = $this->conn->update($records, $object);
        foreach($response as $result){
          $ids = $result->id ? : 0;
        }
      }
    }
    catch(Exception $e){
      log_force_data($records, 'RECORD:', TRUE);
      log_force_data('Soap Error ' . $e->getMessage(), 'ERROR:');
    }
    return $ids;
  }

}
