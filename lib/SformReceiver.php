<?php
/*
  Sformレシーバクラス

*/

class SformReceiver {

  private $_curl;
  private $_csrfToken;
  private $_cookieFile;
  private $_config;

  function __construct()
  {
    error_log("construct");
    $this->_config = parse_ini_file('SformReceiverConfig.ini');
    
    $this->_curl = curl_init();
    
    $this->_cookieFile = "/tmp/sform.cookie." . uniqid();
    $authdata = json_encode(Array("email"=>$this->_config['Username'], "group"=>$this->_config['Group'],"password"=>$this->_config['Password']));

	error_log($this->_config['Username'] . ' / ' . $this->_config['Group'] . ' / ' .$this->_config['Password']);
    // Get csrfToken
    curl_setopt($this->_curl, CURLOPT_URL, $this->_config['BaseURL'].'/auth');
    curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($this->_curl, CURLOPT_COOKIEJAR, $this->_cookieFile);
    $this->_csrfToken = str_replace('"','',curl_exec($this->_curl));

    // Authentication
    curl_setopt($this->_curl, CURLOPT_POST, true);
    curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $authdata);
    curl_setopt($this->_curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Csrf-Token: ".$this->_csrfToken));
    curl_setopt($this->_curl, CURLOPT_FOLLOWLOCATION, true);
    
    $result = curl_exec($this->_curl);
  }

  public function receive(){
    error_log("receive");
    if($_POST['mode'] == 'validate'){
      $this->validateForm($_POST);
    } else if($_POST['mode'] == 'save'){
      $this->saveForm($_POST);
    }
  }

  public function getForm($formid){
    error_log("getForm");
    $data = json_encode(Array("formid"=>$formid, "receiverPath"=>$this->_config['ReceiverPath']));
    curl_setopt($this->_curl, CURLOPT_URL, $this->_config['BaseURL'].'/getform');
    curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($this->_curl);
    $res = json_decode($result);
    echo $res;
  }
  
  public function validateForm($postdata){
    error_log("validateForm");
    $postdata['receiverPath'] = $this->_config['ReceiverPath'];
    $data = json_encode($postdata);
    curl_setopt($this->_curl, CURLOPT_URL, $this->_config['BaseURL'].'/validate/');
    curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($this->_curl);
    $res = json_decode($result);
    echo $result;

  }
  
  public function saveForm($postdata){
    $data = json_encode($postdata);
    curl_setopt($this->_curl, CURLOPT_URL, $this->_config['BaseURL'].'/save/');
    curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($this->_curl);
    $res = json_decode($result);
    echo $result;
  }

  function __destruct()
  {
    curl_close($this->_curl);
    unlink($this->_cookieFile);
  }



}
