<?php

class contact{

  public $sql_fields = array(
      'COMPANY' => '01',
      'SLCODE' => '',
      'SRNO' => '',
      'CONTACT_FIRST_NAME' => '',
      'CONTACT_LAST_NAME' => '',
      'NATIONALITY_CODE' => '',
      'NATIONALITY' => '',
      'DOB' => '',
      'MOVED_YN' => '',
      'EMAIL' => '',
      'CONTACT_TELNO1' => '',
      'MOBILENO' => '',
      'MOBILENO2' => '',
      'LINKEDIN' => '',
      'MAIL_CITY' => '',
      'MAIL_CITY_NAME' => '',
      'MAIL_COUNTRY' => '',
      'MAIL_COUNTRY_NAME' => '',
      'MAIL_POBOX' => '',
      'MAIL_ADDRESS1' => '',
      'MAIL_STATE' => '',
      'MAIL_STATE_NAME' => '',
      'SYNCH_STATUS' => '',
      'SF_ID' => ''
  );
  public $force_fields = array(
      'Id' => '',
      'OwnerId' => '',
      'FirstName' => '',
      'LastName' => '',
      'AccountId' => '',
      'Title' => '',
      'Birthdate' => '',
      'Nationality__c' => '',
      'Moved__c' => FALSE,
      'Email' => '',
      'Phone' => '',
      'MobilePhone' => '',
      'Mobile_2__c' => '',
      'Linkedin__c' => '',
      'MailingStreet' => '',
      'MailingCity' => '',
      'MailingPostalCode' => '',
      'MailingState' => '',
      'MailingStateCode' => '',
      'MailingCountry' => '',
      'MailingCountryCode' => ''
  );
  public $mapping = array(
      'CONTACT_FIRST_NAME' => 'FirstName',
      'CONTACT_LAST_NAME' => 'LastName',
      'DOB' => 'Birthdate',
      'NATIONALITY' => 'Nationality__c',
      'MOVED_YN' => 'Moved__c',
      'EMAIL' => 'Email',
      'CONTACT_TELNO1' => 'Phone',
      'MOBILENO' => 'MobilePhone',
      'MOBILENO2' => 'Mobile_2__c',
      'LINKEDIN' => 'Linkedin__c',
      'MAIL_CITY' => 'MailingCity',
      'MAIL_CITY_NAME' => 'MailingCity',
      'MAIL_COUNTRY' => 'MailingCountryCode',
      'MAIL_COUNTRY_NAME' => 'MailingCountry',
      'MAIL_POBOX' => 'MailingPostalCode',
      'MAIL_ADDRESS1' => 'MailingStreet',
      'MAIL_STATE' => 'MailingStateCode',
      'MAIL_STATE_NAME' => 'MailingState',
      'SF_ID' => 'Id'
  );

  public function __construct($primary_keys = array()){
    if(count($primary_keys) > 0){
      $this->sql_fields['SLCODE'] = $primary_keys[0];
      $this->sql_fields['SRNO'] = $primary_keys[1];
    }
  }

  static public function get_sf_fields(){
    $obj = new self();
    $keys = array_keys($obj->force_fields);
    return implode(',', $keys);
  }

  public function get_status(){
    return intval($this->sql_fields['SYNCH_STATUS']);
  }

  public function get_sf_id(){
    return $this->sql_fields['SF_ID'];
  }

  public function set_owner_id($id){
    $this->force_fields['OwnerId'] = $id;
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
    return array('COMPANY' => $this->sql_fields['COMPANY'], 'SLCODE' => $this->sql_fields['SLCODE'], 'SRNO' => $this->sql_fields['SRNO']);
  }

  public function set_account($account_id){
    $this->force_fields['AccountId'] = $account_id;
  }

  public function convert_to_force(){
    foreach($this->mapping as $sql_field => $force_field){
      if($this->sql_fields[$sql_field]){
        switch($sql_field){
          case 'MOVED_YN':
            $this->force_fields[$force_field] = get_boolean($this->sql_fields[$sql_field]);
            break;
          case 'DOB':
            $this->force_fields[$force_field] = $this->sql_fields[$sql_field]->format('Y-m-d');
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
      if($this->force_fields[$force_field]){
        switch($sql_field){
          case 'MOVED_YN':
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
    $this->sql_fields['SRNO'] ++;
    $this->sql_fields['SYNCH_STATUS'] = '2';
    return $this->sql_fields;
  }

  public function compare_for_sql($sql_record){
    $this->set_sql_fields($sql_record);
    $record = array();
    foreach($this->mapping as $sql_field => $force_field){
      switch($sql_field){
        case 'MOVED_YN':
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
        case 'MOVED_YN':
          if(get_boolean($this->sql_fields[$sql_field]) != $this->force_fields[$force_field]){
            $record->$force_field = get_yn($this->sql_fields[$sql_field]);
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
