<?php

class product{

  private $sql_fields = array(
      'COMPANY' => '01',
      'STKCODE' => '',
      'PRICE_LIST' => '',
      'STKDESCP' => '',
      'BARCODE' => '',
      'MANUFACTURER' => '',
      'CATEGORY' => '',
      'COLLECTION' => '',
      'SIZE_OF_ROLL' => '',
      'COUNTRY_OF_ORGIN' => '',
      'STOCK_TYPE' => '',
      'BRAND' => '',
      'DATE_LAUNCHED' => '',
      'GENRE' => '',
      'STATUS' => '',
      'UNIT1' => '',
      'SALES_UNIT' => '',
      'PRICE' => '',
      'NON_STOCK_YN' => '',
      'SYNCH_STATUS' => '',
      'UNIQUE_ID' => '',
      'SF_ID' => ''
  );
  private $force_fields = array(
      'Id' => '',
      'Name' => '',
      'ProductCode' => '',
      'Description' => '',
      'Barcode__c' => '',
      'Brand__c' => '',
      'Category__c' => '',
      'Collection__c' => '',
      'Country_Of_Origin__c' => '',
      'Date_Launched__c' => '',
      'Genre__c' => '',
      'Manufacturer__c' => '',
      'Non_Stock__c' => '',
      'Size_Of_Roll__c' => '',
      'Status__c' => '',
      'Stock_Type__c' => '',
      'Main_Type__c' => '',
      'Main_Unit__c' => ''
  );
  private $mapping = array(
      'STKDESCP' => 'Name',
      'STKCODE' => 'ProductCode',
      'BARCODE' => 'Barcode__c',
      'MANUFACTURER' => 'Manufacturer__c',
      'CATEGORY' => 'Category__c',
      'COLLECTION' => 'Collection__c',
      'SIZE_OF_ROLL' => 'Size_Of_Roll__c',
      'COUNTRY_OF_ORGIN' => 'Country_Of_Origin__c',
      'STOCK_TYPE' => 'Stock_Type__c',
      'BRAND' => 'Brand__c',
      'DATE_LAUNCHED' => 'Date_Launched__c',
      'GENRE' => 'Genre__c',
      'STATUS' => 'Status__c',
      'NON_STOCK_YN' => 'Non_Stock__c',
      'SF_ID' => 'Id'
  );

  public function __construct($primary_keys = array()){
    if(count($primary_keys) > 0){
      $this->sql_fields['STKCODE'] = $primary_keys;
    }
  }

  public static function get_sf_fields(){
    $obj = new self();
    $keys = array_keys($obj->force_fields);
    return 'Product2.' . implode(',Product2.', $keys);
  }

  public function get_status(){
    return intval($this->sql_fields['SYNCH_STATUS']);
  }

  public function get_sf_id(){
    return $this->sql_fields['SF_ID'];
  }

  public function get_pricelist(){
    return $this->sql_fields['PRICE_LIST'];
  }

  public function get_price(){
    return floatval($this->sql_fields['PRICE']);
  }

  public function set_price($price){
    return $this->sql_fields['PRICE'] = floatval($price);
  }

  public function set_sql_fields($row){
    foreach($this->sql_fields as $key => $field){
      if(!empty($row[$key])){
        $this->sql_fields[$key] = $row[$key];
      }
    }
  }

  public function set_sf_fields($row){
    foreach($this->force_fields as $key => $field){
      if(!empty($row->$key)){
        $this->force_fields[$key] = $row->$key;
      }
    }
  }

  public function get_primary_keys(){
    return array('COMPANY' => $this->sql_fields['COMPANY'], 'STKCODE' => $this->sql_fields['STKCODE'], 'PRICE_LIST' => $this->sql_fields['PRICE_LIST']);
  }

  public function convert_to_force(){
    foreach($this->mapping as $sql_field => $force_field){
      if($this->sql_fields[$sql_field] && $force_field){
        switch($sql_field){
          case 'STKDESCP':
            $this->force_fields[$force_field] = $this->sql_fields[$sql_field];
            $this->force_fields['Description'] = $this->sql_fields[$sql_field];
            break;
          default :
            $this->force_fields[$force_field] = $this->sql_fields[$sql_field];
            break;
        }
      }
    }
  }

  public function convert_to_sql(){
    foreach($this->mapping as $sql_field => $force_field){
      if($this->force_fields[$force_field] && $sql_field){
        switch($sql_field){
          default :
            $this->sql_fields[$sql_field] = $this->force_fields[$force_field];
            break;
        }
      }
    }
  }

  public function get_force_object(){
    $this->convert_to_force();
    $rec = new stdclass();
    foreach($this->force_fields as $key => $field){
      if(!empty($field)){
        $rec->$key = $field;
      }
    }
    return $rec;
  }

  public function get_sql_record(){
    $this->convert_to_sql();
    $this->sql_fields['STKCODE'] = preg_replace_callback("|(\d+)|", function($matches){
      return ++$matches[1];
    }, $this->sql_fields['STKCODE']);
    $this->sql_fields['SYNCH_STATUS'] = '2';
    return $this->sql_fields;
  }

  public function compare_for_sql($sql_record){
    $this->set_sql_fields($sql_record);
    $record = array();
    foreach($this->mapping as $sql_field => $force_field){
      switch($sql_field){
        default :
          if($this->sql_fields[$sql_field] != $this->force_fields[$force_field]){
            $record[$sql_field] = $this->force_fields[$force_field];
          }
          break;
      }
    }
    $record['SYNCH_STATUS'] = '3';
    return $record;
  }

  public function compare_for_force($frecord){
    $this->set_sf_fields($frecord);
    $record = new stdClass();
    foreach($this->mapping as $sql_field => $force_field){
      switch($sql_field){
        default :
          if($this->sql_fields[$sql_field] != $this->force_fields[$force_field]){
            $record->$force_field = $this->sql_fields[$sql_field];
          }
          break;
      }
    }
    return $record;
  }

}
