<?php

//echo intval('-1');
//echo utcDateFormat(time() - 600);

//$var = '3½ IC GRANT CITY MART - MADEENA LAST EXIT';
//echo htmlentities($var);

function crashbug($val){
  if(is_array($val) OR is_object($val)){
    echo '<pre>';
    print_r($val);
    echo '</pre>';
  }
  else{
    echo $val;
  }
  die();
}

function log_force_data($data, $text = '', $include_date = FALSE){
  $weekOfMonth = ceil((date('d', time()) - date('w', time()) - 1) / 7) + 1;
  $logfile = 'salesforce_log-' . date('Y-m', time()) . '-week-' . $weekOfMonth . '.txt';
  file_put_contents($logfile, ($include_date ? "\n\n" . date('l jS \of F Y h:i:s A') : '') . "\n$text" . print_r($data, TRUE), FILE_APPEND);
}

function log_sql_data($data, $text = '', $include_date = FALSE){
  $weekOfMonth = ceil((date('d', time()) - date('w', time()) - 1) / 7) + 1;
  $logfile = 'mssql_log-' . date('Y-m', time()) . '-week-' . $weekOfMonth . '.txt';
  file_put_contents($logfile, ($include_date ? "\n\n" . date('l jS \of F Y h:i:s A') : '') . "\n$text" . print_r($data, TRUE), FILE_APPEND);
}

function get_boolean($string){
  return strtolower($string) == 'y' ? TRUE : FALSE;
}

function get_yn($boolean){
  return $boolean ? 'Y' : 'N';
}

function utcDateFormat($time = FALSE){
  $date_updated = gmdate('Y-m-d H:i:s', $time ? : time());
  $date_arr = explode(' ', $date_updated);
  $date_formatted = implode('T', $date_arr);
  $time_zone = hourDiff(date_default_timezone_get());
  return $date_formatted . $time_zone;
}

function hourDiff($timezone){
  $timezone_offset = getTimezoneOffset($timezone);
  $utc_offset = getTimezoneOffset('UTC');
  $main_value = ($timezone_offset == 0) ? (($utc_offset < 1) ? abs($utc_offset) : -$utc_offset ) : ($timezone_offset + abs($utc_offset));
  $hour_diff = $main_value / 3600;
  //changing hours diff into required format used by api i.e. +0500, -0200 etc
  $hour_abs = abs($hour_diff);
  $min_diff = ($hour_abs - floor($hour_abs)) * 60;
  $hour_format = (floor($hour_abs) * 100) + $min_diff;
  $hour_format = (($hour_abs) < 10) ? '0' . abs($hour_format) : abs($hour_format);
  $hour_format = ($hour_diff >= 0) ? '+' . $hour_format : '-' . $hour_format;
  return $hour_format;
}

function getTimezoneOffset($timezone = FALSE){
  $system_timezone = date_default_timezone_get();
  /* get current timestamp */
  $current_time = time();
  /* set script to output timezone */
  date_default_timezone_set($timezone);
  /* get time in string in desired timezone */
  $time_string = date("Y-m-d G:i:s", $current_time);
  /* revert back to system timezone */
  date_default_timezone_set($system_timezone);
  /* compute offset */
  $offset = strtotime($time_string) - $current_time;
  return $offset;
}
