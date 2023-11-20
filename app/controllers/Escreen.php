<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Escreen extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('api_model');
		
		
    }

	public function index(){
     //Get the raw POST data from PHP's input stream.
//This raw data should contain XML.
$postData = trim(file_get_contents('php://input'));


//Use internal errors for better error handling.
libxml_use_internal_errors(true);


//Parse the POST data as XML.
$xml = simplexml_load_string($postData);


//If the XML could not be parsed properly.
if($xml === false) {
    //Send a 400 Bad Request error.
    header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request", true, 400);
    //Print out details about the error and kill the script.
    foreach(libxml_get_errors() as $xmlError) {
        echo $xmlError->message . "\n";
    }
    exit;
}
$res = $this->api_model->api_escreen_xml($xml);
if($res){
echo'<eScreenData TransmissionID="'.$xml->attributes()->TransmissionID.'" status="S"></eScreenData>';
} else {
echo'<eScreenData TransmissionID="'.$xml->attributes()->TransmissionID.'" status="F"></eScreenData>';
}

//exit(json_encode(array('TransmissionID'=>,'status'=>'S')));  
	}
	
	

  
} // Class end.
