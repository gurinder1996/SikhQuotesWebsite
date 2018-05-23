<?php

//
// config
// --------------------------------------------------
//

  /**
   * subscribe method, choose one
   */

  // 'mailchimp' or 'txt'
  $METHOD = 'mailchimp';

  /**
   * if `$METHOD = 'mailchimp'` - mailchimp (recommend)
   */

  // mailchimp api key - http://admin.mailchimp.com/account/api/
  $MC_API_KEY = '1234567890abcdefghti-us10';

  // mailchimp list id - http://admin.mailchimp.com/lists/
  $MC_LIST_ID = '12345abcde';

  /*
   * if `$METHOD = 'txt'` - save email list to txt file
   */

  // subscribe list txt file path and name
  // email list will save into this file, so the txt file path / name should be special for more safe
  $FILE_PATH = 'subscribe.txt';

//
// script
// --------------------------------------------------
//

  if($_SERVER['REQUEST_METHOD'] != 'POST') {
    die('An error occurred. Please try again later.');
  } else {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
      die('Only allow access via AJAX');
    } else {
      if ($METHOD == 'mailchimp') {
        if (empty($MC_API_KEY) || empty($MC_LIST_ID)) {
          die(json_encode(array('type' => 'error', 'msg' => '<i class="error"></i> Please check php config field.')));
        } else {
          require_once('vendor/mailchimp/MCAPI.class.php');
          extract($_POST, EXTR_PREFIX_ALL, 'form');

          $api = new MCAPI($MC_API_KEY);

          if ($api->listSubscribe($MC_LIST_ID, $form_email) !== true) {
            if ($api->errorCode == 104) {
              $msg = '<i class="error"></i> Invalid API KEY';
            } else if ($api->errorCode == 200) {
              $msg = '<i class="error"></i> Invalid LIST ID';
            } else if ($api->errorCode == 214) {
              $msg = '<i class="error"></i> Subscription already exists';
            } else {
              $msg = $api->errorMessage;
            }
            die(json_encode(array('type' => 'error', 'msg' => $msg)));
          } else {
            die(json_encode(array('type' => 'success', 'msg' => '<i class="success"></i> We just sent you a confirmation email')));
          }
        }
      } else if ($METHOD == 'txt') {
        if (empty($FILE_PATH)) {
          die(json_encode(array('type' => 'error', 'msg' => '<i class="error"></i> Please check php config field.')));
        } else {
          extract($_POST, EXTR_PREFIX_ALL, 'form');

          if (strpos(file_get_contents($FILE_PATH), $form_email) == true) {
            die(json_encode(array('type' => 'error', 'msg' => '<i class="error"></i> Subscription already exists.')));
          } else {
            file_put_contents($FILE_PATH, date("Y-m-d H:i:s").' - '.$form_email."\n", FILE_APPEND | LOCK_EX); // LOCK_EX available since PHP 5.1.0+
            die(json_encode(array('type' => 'success', 'msg' => '<i class="success"></i> You have been successfully subscribed.')));
          }
        }
      }
    }
  }
?>
