<?php

class account{

  public $sql_fields = array(
      'COMPANY' => '01',
      'SLCODE' => '',
      'SLDESCP' => '',
      'PARENT_SLCODE' => '',
      'SMAN' => '',
      'SMAN_NAME' => '',
      'REGION' => '',
      'AREA' => '',
      'SUBAREA' => '',
      'DIVISION' => '',
      'TYPE' => '',
      'PRICE_LIST' => '',
      'AC_SOURCE' => '',
      'TEL1' => '',
      'WEBSITE' => '',
      'CITY' => '',
      'CITY_NAME' => '',
      'COUNTRY' => '',
      'COUNTRY_NAME' => '',
      'POBOX' => '',
      'ADDRESS1' => '',
      'STATE' => '',
      'STATE_NAME' => '',
      'SHIP_CITY' => '',
      'SHIP_CITY_NAME' => '',
      'SHIP_COUNTRY' => '',
      'SHIP_COUNTRY_NAME' => '',
      'SHIP_POBOX' => '',
      'SHIP_ADDRESS1' => '',
      'SHIP_STATE' => '',
      'SHIP_STATE_NAME' => '',
      'DESCP' => '',
      'CURR' => '',
      'MURASPEC_YN' => 'N',
      'BHS_YN' => 'N',
      'GOODRICH_YN' => 'N',
      'HITECH_YN' => 'N',
      'NEWMORE_YN' => 'N',
      'VESCOM_YN' => 'N',
      'EDGE_YN' => 'N',
      'DECORE_YN' => 'N',
      'CASANA_YN' => 'N',
      'SELTEX_YN' => 'N',
      'SYNCH_STATUS' => '',
      'SF_ID' => ''
  );
  public $force_fields = array(
      'Id' => '',
      'Name' => '',
      'OwnerId' => '',
      'Division__c' => '',
      'Type' => '',
      'Price_List__c' => '',
      'Region__c' => '',
      'Area__c' => '',
      'Sub_Area__c' => '',
      'CurrencyIsoCode' => '',
      'Customer_Code__c' => '',
      'AccountSource' => '',
      'Phone' => '',
      'Website' => '',
      'Muraspec__c' => FALSE,
      'BSH__c' => FALSE,
      'Goodrich__c' => FALSE,
      'Hi_Tech__c' => FALSE,
      'Newmore__c' => FALSE,
      'Vescom__c' => FALSE,
      'Edge__c' => FALSE,
      'Decor__c' => FALSE,
      'Casana__c' => FALSE,
      'Seltex__c' => FALSE,
      'Description' => '',
      'BillingStreet' => '',
      'BillingCity' => '',
      'BillingPostalCode' => '',
      'BillingState' => '',
      'BillingStateCode' => '',
      'BillingCountry' => '',
      'BillingCountryCode' => '',
      'ShippingStreet' => '',
      'ShippingCity' => '',
      'ShippingPostalCode' => '',
      'ShippingState' => '',
      'ShippingStateCode' => '',
      'ShippingCountry' => '',
      'ShippingCountryCode' => '',
  );
  public $mapping = array(
      'SLDESCP' => 'Name',
      'SLCODE'  => 'Customer_Code__c',
      'SMAN' => 'OwnerId',
      'SMAN_NAME' => 'SMAN_NAME__c',
      'DIVISION' => 'Division__c',
      'TYPE' => 'Type',
      'PRICE_LIST' => 'Price_List__c',
      'REGION' => 'Region__c',
      'AREA' => 'Area__c',
      'SUBAREA' => 'Sub_Area__c',
      'CURR' => 'CurrencyIsoCode',
      'AC_SOURCE' => 'AccountSource',
      'TEL1' => 'Phone',
      'WEBSITE' => 'Website',
      'CITY' => 'BillingCity',
      'CITY_NAME' => 'BillingCity',
      'COUNTRY' => 'BillingCountryCode',
      'COUNTRY_NAME' => 'BillingCountry',
      'POBOX' => 'BillingPostalCode',
      'ADDRESS1' => 'BillingStreet',
      'STATE' => 'BillingStateCode',
      'STATE_NAME' => 'BillingState',
      'SHIP_CITY' => 'ShippingCity',
      'SHIP_CITY_NAME' => 'ShippingCity',
      'SHIP_COUNTRY' => 'ShippingCountryCode',
      'SHIP_COUNTRY_NAME' => 'ShippingCountry',
      'SHIP_POBOX' => 'ShippingPostalCode',
      'SHIP_ADDRESS1' => 'ShippingStreet',
      'SHIP_STATE' => 'ShippingStateCode',
      'SHIP_STATE_NAME' => 'ShippingState',
      'MURASPEC_YN' => 'Muraspec__c',
      'BHS_YN' => 'BSH__c',
      'GOODRICH_YN' => 'Goodrich__c',
      'HITECH_YN' => 'Hi_Tech__c',
      'NEWMORE_YN' => 'Newmore__c',
      'VESCOM_YN' => 'Vescom__c',
      'EDGE_YN' => 'Edge__c',
      'DECORE_YN' => 'Decor__c',
      'CASANA_YN' => 'Casana__c',
      'SELTEX_YN' => 'Seltex__c',
      'DESCP' => 'Description',
      'SF_ID' => 'Id'
  );
  public $pricelist = array(
      'PRICE1' => 'Showroom Price',
      'PRICE2' => 'NGC',
      'PRICE3' => 'Wholesale Price'
  );
  public $contacts = array();

  public function set_pricebooks($pricebooks){
    $this->pricebooks = $pricebooks;
  }

  public function __construct($primary_keys = array()){
    if(count($primary_keys) > 0){
      $this->sql_fields['SLCODE'] = $primary_keys;
    }
  }

  public static function get_sf_fields(){
    $obj = new self();
    $keys = array_keys($obj->force_fields);
    return implode(',', $keys);
  }

  public function get_sf_field($field){
    return isset($this->force_fields[$field]) ? $this->force_fields[$field]: '';
  }

  public function set_sf_field($field, $value){
    $this->force_fields[$field] = $value;
  }

  public function get_status(){
    return intval($this->sql_fields['SYNCH_STATUS']);
  }

  public function get_sf_id(){
    return $this->sql_fields['SF_ID'];
  }

  public function get_owner_id(){
    return $this->sql_fields['SMAN'];
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
    return array('COMPANY' => $this->sql_fields['COMPANY'], 'SLCODE' => $this->sql_fields['SLCODE']);
  }

  public function convert_to_force(){
    foreach($this->mapping as $sql_field => $force_field){
      if($this->sql_fields[$sql_field]){
        switch($sql_field){
          case 'MURASPEC_YN':
          case 'BHS_YN':
          case 'GOODRICH_YN':
          case 'HITECH_YN':
          case 'NEWMORE_YN':
          case 'VESCOM_YN':
          case 'EDGE_YN':
          case 'DECORE_YN':
          case 'CASANA_YN':
          case 'SELTEX_YN':
            $this->force_fields[$force_field] = strtolower($this->sql_fields[$sql_field]) == 'y' ? TRUE : FALSE;
            break;
          case 'PRICE_LIST':
            if(isset($this->pricelist[$this->sql_fields[$sql_field]])){
              $this->force_fields[$force_field] = $this->pricelist[$this->sql_fields[$sql_field]];
            }
            break;
          case 'STATE':
            if($this->sql_fields['COUNTRY'] != 'UAE'){
              $this->force_fields[$force_field] = $this->sql_fields[$sql_field];
            }
            break;
          case 'SHIP_STATE':
            if($this->sql_fields['SHIP_COUNTRY'] != 'UAE'){
              $this->force_fields[$force_field] = $this->sql_fields[$sql_field];
            }
            break;
          case 'COUNTRY':
          case 'SHIP_COUNTRY':
            $this->force_fields[$force_field] = $this->_get_country_iso($this->sql_fields[$sql_field]);
            break;
          default :
            $this->force_fields[$force_field] = htmlentities($this->sql_fields[$sql_field]);
            break;
        }
      }
    }
  }

  public function _get_country_iso($str){
    switch(trim($str)){
      case 'UAE':
        return 'AE';
      case 'ON':
        return 'OM';
      case 'DH':
        return 'QA';
      case 'SJ':
        return 'SA';
      case 'IRN':
        return 'IR';
      case 'BN':
        return 'BH';
      case 'CE':
        return 'LK';
      default:
        return $str;
    }
  }

  public function convert_to_sql(){
    foreach($this->mapping as $sql_field => $force_field){
      if($this->force_fields[$force_field]){
        switch($sql_field){
          case 'MURASPEC_YN':
          case 'BHS_YN':
          case 'GOODRICH_YN':
          case 'HITECH_YN':
          case 'NEWMORE_YN':
          case 'VESCOM_YN':
          case 'EDGE_YN':
          case 'DECORE_YN':
          case 'CASANA_YN':
          case 'SELTEX_YN':
            $this->sql_fields[$sql_field] = 'Y';
            break;
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
    $rec->Data_Source__c = 'Beams ERP Records';
    foreach($this->force_fields as $key => $field){
      if(!empty($field)){
        $rec->$key = $field;
      }
    }
    return $rec;
  }

  public function get_sql_record(){
    $this->convert_to_sql();
    $this->sql_fields['SLCODE'] ++;
    $this->sql_fields['SYNCH_STATUS'] = '2';
    return $this->sql_fields;
  }

  public function compare_for_sql($sql_record){
    $this->set_sql_fields($sql_record);
    $record = array();
    foreach($this->mapping as $sql_field => $force_field){
      switch($sql_field){
        case 'MURASPEC_YN':
        case 'BHS_YN':
        case 'GOODRICH_YN':
        case 'HITECH_YN':
        case 'NEWMORE_YN':
        case 'VESCOM_YN':
        case 'EDGE_YN':
        case 'DECORE_YN':
        case 'CASANA_YN':
        case 'SELTEX_YN':
          if(get_boolean($this->sql_fields[$sql_field]) != $this->force_fields[$force_field]){
            $record[$sql_field] = get_yn($this->force_fields[$force_field]);
          }
          break;
        default :
          if($this->sql_fields[$sql_field] != $this->force_fields[$force_field]){
            $record[$sql_field] = $this->force_fields[$force_field];
          }
          break;
      }
    }
    $this->sql_fields['SYNCH_STATUS'] = '3';
    return $record;
  }

  public function compare_for_force($frecord){
    $this->set_sf_fields($frecord);
    $record = new stdClass();
    foreach($this->mapping as $sql_field => $force_field){
      switch($sql_field){
        case 'MURASPEC_YN':
        case 'BHS_YN':
        case 'GOODRICH_YN':
        case 'HITECH_YN':
        case 'NEWMORE_YN':
        case 'VESCOM_YN':
        case 'EDGE_YN':
        case 'DECORE_YN':
        case 'CASANA_YN':
        case 'SELTEX_YN':
          if(get_boolean($this->sql_fields[$sql_field]) != $this->force_fields[$force_field]){
            $record->$force_field = get_boolean($this->sql_fields[$sql_field]);
          }
          break;
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
