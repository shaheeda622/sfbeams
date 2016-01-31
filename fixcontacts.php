<?php

require_once ('helpers.php');
require_once ('account.php');
require_once ('contact.php');
require_once ('product.php');
require_once ('sql_init.php');
require_once ('salesforce_ins.php');


$ms_sql = new SQL_C();
$force = new salesforce_ins();

$ms_con = $ms_sql->get_connection();

if($ms_con && $force->get_connection()){
  echo "Connection established.<br />";

  $query1 = 'SELECT c.*,a.SF_ID as account_id,a.SMAN as owner_id from contacts c LEFT JOIN account a ON c.SLCODE = a.SLCODE WHERE c.SYNCH_STATUS IN (0,1)';
  $res1 = sqlsrv_query($ms_con, $query1);
  $contacts = array();
  while($row = sqlsrv_fetch_array($res1, SQLSRV_FETCH_ASSOC)){		
	$contact = new contact();
	$contact->set_sql_fields($row);
	if(! empty($row['account_id']))
		$contact->set_account($row['account_id']);
	if(! empty($row['owner_id']))
		$contact->set_owner_id($row['owner_id']);
	if($row['CONTACT_LAST_NAME'])
	$contacts[] = $contact;
  }
  echo count($contacts);
  $chunks = array_chunk($contacts, 1);
  foreach($chunks as $chunk){
	$contacts = $force->insert_batch('Contact', $chunk);
	foreach($contacts as $c){
		if($c->force_fields['Id']){			
			$ms_sql->update_contact_status($c->get_primary_keys(), '4', $c->force_fields['Id']);
		}
	}
  }  
}
else{
  echo "Connection could not be established.";
}