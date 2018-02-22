<?php
require_once 'lib/SformReceiver.php';

$receiver = new SformReceiver();

if(isset($_sform_form_id)){
  $receiver->getForm($_sform_form_id);
} else {
  print 'valiable "$_sform_form_id" IS NOT SET.';
}
