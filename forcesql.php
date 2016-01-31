<?php

require_once ('helpers.php');
require_once ('account.php');
require_once ('contact.php');
require_once ('product.php');
require_once ('sql_init.php');
require_once ('salesforce_ins.php');

$tac = TRUE;

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
    $insert_accounts = array();
    $update_accounts = array();
    foreach($slcode as $record){
      if($record['Account']){
        $account = new account();
        $account->set_sql_fields($record['Account']);
        if($record['Contact']){
          foreach($record['Contact'] as $c){
            $contact = new contact();
            $contact->set_sql_fields($c);
            $account->contacts[] = $contact;
          }
        }
        if($account->get_status() == 0){
          $insert_accounts[] = $account;
        }
        elseif($account->get_status() == 1){
          $update_accounts[] = $account;
        }
      }
      if(count($insert_accounts) >= 200){ // do insert handling
        insert_accounts($force, $ms_sql, $insert_accounts);
        $insert_accounts = array();
      }
      if(count($update_accounts) >= 200){ // do update handling
        update_accounts($force, $ms_sql, $update_accounts);
        $update_accounts = array();
      }
    }
    if(count($insert_accounts) > 0){ // do insert handling
      insert_accounts($force, $ms_sql, $insert_accounts);
    }
    if(count($update_accounts) > 0){ // do update handling
      update_accounts($force, $ms_sql, $update_accounts);
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
  $insert_products = array();
  $update_products = array();
  foreach($sql_products as $prd){
    $product = new product();
    $product->set_sql_fields($prd);
    if($product->get_status() == 0){
      $insert_products[] = $product;
    }
    elseif($product->get_status() == 1){
      $update_products[] = $product;
    }
    if(count($insert_products) >= 200){ // do insert handling
      insert_products($force, $ms_sql, $pricebooks, $insert_products);
      $insert_products = array();
    }
    if(count($update_products) >= 200){ // do update handling
      update_products($force, $ms_sql, $pricebooks, $update_products);
      $update_products = array();
    }
  }
  if(count($insert_products) > 0){ // do insert handling
    insert_products($force, $ms_sql, $pricebooks, $insert_products);
  }
  if(count($update_products) > 0){ // do update handling
    update_products($force, $ms_sql, $pricebooks, $update_products);
  }
}
else{
  echo "Connection could not be established.";
}

function insert_accounts($force, $ms_sql, $insert_accounts){
  $accounts = $force->insert_batch('Account', $insert_accounts);
  $contacts = array();
  foreach($accounts as $account){
    if($account->force_fields['Id']){
      foreach($account->contacts as $contact){
        $contact->set_account($account->force_fields['Id']);
        $contact->set_owner_id($account->get_owner_id());
        $contacts[] = $contact;
        if(count($contacts) >= 200){
          $contacts = $force->insert_batch('Contact', $contacts);
          foreach($contacts as $c){
            if($c->force_fields['Id']){
              $ms_sql->update_contact_status($c->get_primary_keys(), '4', $c->force_fields['Id']);
            }
          }
          $contacts = array();
        }
      }
      $ms_sql->update_account_status($account->get_primary_keys(), '4', $account->force_fields['Id']);
    }
  }
  if(count($contacts) > 0){
    $contacts = $force->insert_batch('Contact', $contacts);
    foreach($contacts as $c){
      if($c->force_fields['Id']){
        $ms_sql->update_contact_status($c->get_primary_keys(), '4', $c->force_fields['Id']);
      }
    }
  }
}

function update_accounts($force, $ms_sql, $update_accounts){
  $accounts = $force->update_batch('Account', $update_accounts);
  $contacts = array();
  foreach($accounts as $account){
    if($account->force_fields['Id']){
      foreach($account->contacts as $contact){
        $contact->set_account($account->force_fields['Id']);
        $contact->set_owner_id($account->get_owner_id());
        $contacts[] = $contact;
        if(count($contacts) >= 200){
          $contacts = $force->update_batch('Contact', $contacts);
          foreach($contacts as $c){
            if($c->force_fields['Id']){
              $ms_sql->update_contact_status($c->get_primary_keys(), '4', $c->force_fields['Id']);
            }
          }
          $contacts = array();
        }
      }
      $ms_sql->update_account_status($account->get_primary_keys(), '4', $account->force_fields['Id']);
    }
  }
  if(count($contacts) > 0){
    $contacts = $force->update_batch('Contact', $contacts);
    foreach($contacts as $c){
      if($c->force_fields['Id']){
        $ms_sql->update_contact_status($c->get_primary_keys(), '4', $c->force_fields['Id']);
      }
    }
  }
}

function insert_products($force, $ms_sql, $pricebooks, $insert_products){
  $products = $force->insert_batch('Product2', $insert_products);
  $spb_entries = $pb_entries = array();
  foreach($products as $product){
    $pricelist = $product->get_pricelist();
    if($product->force_fields['Id'] && $pricelist){
      $pricebook_entry = new stdClass();
      $pricebook_entry->Pricebook2Id = $pricebooks['Standard'];
      $pricebook_entry->Product2Id = $product->force_fields['Id'];
      $pricebook_entry->UnitPrice = $product->get_price();
      $spb_entries[] = $pricebook_entry;
      $pricebook_entry = new stdClass();
      $pricebook_entry->Pricebook2Id = $pricebooks[$pricelist];
      $pricebook_entry->Product2Id = $product->force_fields['Id'];
      $pricebook_entry->UnitPrice = $product->get_price();
      $pricebook_entry->UseStandardPrice = TRUE;
      $pb_entries[] = $pricebook_entry;
      if(count($spb_entries) >= 200){
        $force->insert_batch('PricebookEntry', $spb_entries);
        $force->insert_batch('PricebookEntry', $pb_entries);
        $spb_entries = $pb_entries = array();
      }
      $ms_sql->update_product_status($product->get_primary_keys(), '4', $product->force_fields['Id']);
    }
  }
  if(count($spb_entries) > 0){
    $force->insert_batch('PricebookEntry', $spb_entries);
    $force->insert_batch('PricebookEntry', $pb_entries);
  }
}

function update_products($force, $ms_sql, $pricebooks, $update_products){
  $products = $force->update_batch('Product2', $update_products);
  $spb_entries = $pb_entries = array();
  foreach($products as $product){
    $pricelist = $product->get_pricelist();
    if($product->force_fields['Id'] && $pricelist){
      $pricebook_entry = new stdClass();
      $pricebook_entry->Pricebook2Id = $pricebooks['Standard'];
      $pricebook_entry->Product2Id = $product->force_fields['Id'];
      $pricebook_entry->UnitPrice = $product->get_price();
      $spb_entries[] = $pricebook_entry;
      $pricebook_entry = new stdClass();
      $pricebook_entry->Pricebook2Id = $pricebooks[$pricelist];
      $pricebook_entry->Product2Id = $product->force_fields['Id'];
      $pricebook_entry->UnitPrice = $product->get_price();
      $pricebook_entry->UseStandardPrice = TRUE;
      $pb_entries[] = $pricebook_entry;
      if(count($spb_entries) >= 200){
        $force->update_batch('PricebookEntry', $spb_entries);
        $force->update_batch('PricebookEntry', $pb_entries);
        $spb_entries = $pb_entries = array();
      }
      $ms_sql->update_product_status($product->get_primary_keys(), '4', $product->force_fields['Id']);
    }
  }
  if(count($spb_entries) > 0){
    $force->update_batch('PricebookEntry', $spb_entries);
    $force->update_batch('PricebookEntry', $pb_entries);
  }
}
