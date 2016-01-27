<?php

require_once ('helpers.php');
require_once ('account.php');
require_once ('contact.php');
require_once ('product.php');
require_once ('sql_init.php');
require_once ('salesforce_ins.php');

$tac = FALSE;

$ms_sql = new SQL_C();
$force = new salesforce_ins();

if($ms_sql->get_connection() && $force->get_connection()){
  echo "Connection established.<br />";

  $force_records = $tac ? $force->get_records() : array();
  $sql_records = $tac ? $ms_sql->get_records() : array();

  $acc_slcode = $ms_sql->get_primary_key('account', 'SLCODE');
  $con_slcode = $ms_sql->get_primary_key('contacts', 'SLCODE');

  $account = new account($acc_slcode);
  foreach($force_records as $record){
    if(isset($record->Id)){
      $account->set_sf_fields($record);
      $existing_record = $ms_sql->get_record_sf_id('account', $record->Id);
      if($existing_record){
        $ms_sql->update('account', $account->compare_for_sql($existing_record), $record->Id);
      }
      else{
        $ms_sql->insert('account', $account->get_sql_record());
      }
      $contact = new contact(array($account->get_primary_keys()['SLCODE'], 0));
    }
    else{
      $contact = new contact(array($con_slcode, 0));
    }
    foreach($record->Contacts as $rec){
      $contact->set_sf_fields($rec);
      $existing_record = $ms_sql->get_record_sf_id('contacts', $rec->Id);
      if($existing_record){
        $ms_sql->update('contacts', $contact->compare_for_sql($existing_record), $rec->Id);
      }
      else{
        $ms_sql->insert('contacts', $contact->get_sql_record());
      }
    }
  }

  foreach($sql_records as $company => $slcode){
    $insert_array = array();
    $update_array = array();
    foreach($slcode as $record){
      $account_id = FALSE;
      if($record['Account']){
        $account = new account();
        $account->set_sql_fields($record['Account']);
        if($account->get_status() == 1){
          $account_id = $force->insert('Account', array($account->get_force_object()));
          if($account_id){
            $ms_sql->update_account_status($account->get_primary_keys(), '0', $account_id);
          }
        }
        elseif($account->get_status() == 2){
          $account_id = $force->update('Account', array($account->get_force_object()));
          if($account_id){
            $ms_sql->update_contact_status($account->get_primary_keys(), '0', $account_id);
          }
        }
      }
      if($record['Contact']){
        foreach($record['Contact'] as $c){
          $contact = new contact();
          $contact->set_sql_fields($c);
          if($contact->get_status() == 1){
            if($account_id){
              $contact->set_account($account_id);
              $contact->set_owner_id($account->get_owner_id());
            }
            $contact_id = $force->insert('Contact', array($contact->get_force_object()));
            if($contact_id){
              $ms_sql->update_contact_status($contact->get_primary_keys(), '0', $contact_id);
            }
          }
          elseif($contact->get_status() == 2){
            $contact_id = $force->update('Contact', array($contact->get_force_object()));
            if($contact_id){
              $ms_sql->update_contact_status($contact->get_primary_keys(), '0', $contact_id);
            }
          }
        }
      }
    }
  }

  $sql_products = $ms_sql->get_products();
  $force_products = $force->get_products();
  $product_stk = $ms_sql->get_primary_key('productmaster', 'STKCODE');
  $product = new product($product_stk);
  foreach($force_products as $prd){
    $product->set_sf_fields($prd);
    $sql_rec = FALSE;
    foreach(array('PRICE1', 'PRICE2', 'PRICE3') as $price){
      if(isset($prd->$price)){
        $existing_record = $ms_sql->get_record_sf_id('productmaster', $prd->Id, array('PRICE_LIST' => $price));
        if($existing_record){
          if(!$sql_rec){
            $sql_rec = $product->compare_for_sql($existing_record);
          }
          $sql_rec['PRICE_LIST'] = $price;
          $sql_rec['PRICE'] = floatval($prd->$price);
          $ms_sql->update('productmaster', $sql_rec, $prd->Id, array('PRICE_LIST' => $price));
        }
        else{
          if(!$sql_rec){
            $sql_rec = $product->get_sql_record();
          }
          $sql_rec['PRICE_LIST'] = $price;
          $sql_rec['PRICE'] = floatval($prd->$price);
          $ms_sql->insert('productmaster', $sql_rec);
        }
      }
    }
  }

  $pricebooks = $force->get_pricebooks();
  foreach($sql_products as $prd){
    $product = new product();
    $product->set_sql_fields($prd);
    if($product->get_status() == 1){
      $product_id = $force->insert('Product2', array($product->get_force_object()));
      if($product_id){
        $pricebook_entry = new stdClass();
        $pricebook_entry->Pricebook2Id = $pricebooks['Standard'];
        $pricebook_entry->Product2Id = $product_id;
        $pricebook_entry->UnitPrice = $product->get_price();
        $force->insert('PricebookEntry', array($pricebook_entry));
        $pricebook_entry = new stdClass();
        $pricebook_entry->Pricebook2Id = $pricebooks[$product->get_pricelist()];
        $pricebook_entry->Product2Id = $product_id;
        $pricebook_entry->UnitPrice = $product->get_price();
        $pricebook_entry->UseStandardPrice = TRUE;
        $force->insert('PricebookEntry', array($pricebook_entry));
        $ms_sql->update_product_status($product->get_primary_keys(), '4', $product_id);
      }
    }
    elseif($product->get_status() == 2){
      $product_id = $force->update('Product2', array($product->get_force_object()));
      if($product_id){
        $ms_sql->update_product_status($product->get_primary_keys(), '4', $product_id);
      }
    }
  }
}
else{
  echo "Connection could not be established.";
}