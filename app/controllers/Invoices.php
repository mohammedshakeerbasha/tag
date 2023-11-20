<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Invoices extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('authorize_net');
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('employees_model');
		$this->load->model('general_model');
		$this->load->model('dashboard_model');
		$this->load->model('invoices_model');
		$this->load->model('batchinvoice_model');
		$this->load->model('test_model');
		$this->load->model('email_model');
		$this->load->model('payment_model');
		$this->load->library('common/user');
		$this->load->library('general');
		$this->load->library('common/paginator');  
    	$this->load->library('generatepdf');  
        $this->load->library("pagination");
		// $this->load->library('authorize_net');
		if (!$this->session->userID):
		redirect('auth');
		endif;
		
    }
	
	public function index(){
		
		$data['title'] = 'Clients';
    	$data['employees'] = $this->employees_model->get_employees();
    	$data['client_accounts'] = $this->clients_model->get_client_accounts();
    	$data['employees_statuss'] = $this->employees_model->get_employees_statuss();
    	$data['employees_categories'] = $this->employees_model->get_employees_categories();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/home', $data);
        
	}


    /**
     * App home.
     */
    public function generate_invoice()
    {
    	$data['title'] = 'Generate Invoice';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/generate_invoice', $data);
        
    }
    
    /**
     * App home.
     */
    public function list_invoice($page=1)
    {
		$status = 1;		
	    $data['title'] = 'List Invoice';
		if($this->input->get()){    	
    	 $etype_num_rows = $this->invoices_model->get_all_closed_invoices_for_paid_invoice_num_rows();
	     // Generate pagination.
	     $perPage = 15;
	     // Handle pagination.
	     if ($page==1) {
	        $offSet = 0;
	     } else {
	        // $offSet = ($offSet - 1) * $perPage.
	        $offSet = ($page - 1) * $perPage;
	     }
	     $FullURL = explode('?',$_SERVER['REQUEST_URI']);
		 $start_page =  site_url('invoices/list_invoice?'.$FullURL[1]);

	     $start_pages  = site_url('invoices/list_invoice');
	     $page_url  =  $start_pages.'/';

	     
	     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
	     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
	     // end pagination
	     $data['invoice_list'] = $this->invoices_model->get_all_closed_invoices_for_paid_invoice($perPage,$offSet);

	     $data['sum']=$this->invoices_model->get_sum_of_paidinvoicelist();
	     } else {
	     $data['invoice_list']=$this->invoices_model->get_recent_paidinvoicelist();
	     }
    	
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/list_invoice', $data);
    }
    
    public function unpaidinvoicelist($page=1)
    {
    	$data['title'] = 'List Unpaid Invoice';  
    	 $etype_num_rows = $this->invoices_model->get_all_closed_invoices_for_unpaid_invoice_num_rows();
	     // Generate pagination.
	     $perPage = 40;
	     // Handle pagination.
	     if ($page==1) {
	        $offSet = 0;
	     } else {
	        // $offSet = ($offSet - 1) * $perPage.
	        $offSet = ($page - 1) * $perPage;
	     }
	     $FullURL = explode('?',$_SERVER['REQUEST_URI']);
		 $start_page =  site_url('invoices/unpaidinvoicelist?'.$FullURL[1]);

	     $start_pages  = site_url('invoices/unpaidinvoicelist');
	     $page_url  =  $start_pages.'/';

	     
	     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
	     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
	     // end pagination
	     $data['invoice_list'] = $this->invoices_model->get_all_closed_invoices_for_unpaid_invoice($perPage,$offSet);
	     $data['sum']=$this->invoices_model->get_sum_of_unpaidinvoicelist(); 
	     //$data['invoice_list']=$this->invoices_model->get_recent_unpaidinvoicelist();
	     

    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/list_invoice_unpaid', $data);
        
    }

	/*Individual PDF & Print Invoice*/

 	public function pdf_invoice($invoice_id)
    {
		$data['title'] = 'Individual PDF Invoice';
	    $data['invoice_id'] = $invoice_id;
	    //$data['client_contact_id'] = $client_contact_id;
	    $data['invoice'] 		   = $this->invoices_model->get_invoice_by_id($invoice_id);
	    $data['client_account']    = $this->clients_model->get_client_account($data['invoice']->client_account_id);
        $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);
        //$data['client_address']    = $this->invoices_model->get_client_address_by_client_id_billing_pdf($data['client']->id);
		$data['client_contact']    = $this->invoices_model->get_contact_by_der_invoice_row($data['client']->id,$data['invoice']->client_account_id);
		
		if($data['client_account']->client_address_billing != null && $data['client_account']->client_address_billing != 0){
		 $data['client_address']  = $this->test_model->get_client_address_by_id($data['client_account']->client_address_billing);    
		}elseif($data['client_contact']->client_address_main != null && $data['client_contact']->client_address_main != 0){
		 $data['client_address'] = $this->test_model->get_client_address_by_id($data['client_contact']->client_address_main);                     
		}else{
		  $data['client_address']  = $this->clients_model->get_client_address_by_client_id_pdf($data['client']->id);                            
		}

		$data['invoice_details']   = $this->invoices_model->get_all_invoice_detail_by_invoiceid($invoice_id);
		$data['city'] 			   = $this->general_model->get_city($data['client_address']->city);
		$data['state'] 			   = $this->general_model->get_state($data['client_address']->state);
		$html = $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/pdf/individual_invoice_details', $data, true);
		$this->generatepdf->createPDF($html, $filename='PDF Invoice', false);	
    }

    public function print_invoice($invoice_id)
    {
		$data['title'] = 'Individual PDF Invoice';
	    $data['invoice_id'] = $invoice_id;
	    //$data['client_contact_id'] = $client_contact_id;
	    $data['invoice'] 		   = $this->invoices_model->get_invoice_by_id($invoice_id);
	    $data['client_account']    = $this->clients_model->get_client_account($data['invoice']->client_account_id);
        $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);
        //$data['client_address']    = $this->invoices_model->get_client_address_by_client_id_billing_pdf($data['client_account']->client_address_billing);
        $data['client_contact']    = $this->invoices_model->get_contact_by_der_invoice_row($data['client']->id,$data['invoice']->client_account_id);
		
        if($data['client_account']->client_address_billing != null && $data['client_account']->client_address_billing != 0){
		 $data['client_address']  = $this->test_model->get_client_address_by_id($data['client_account']->client_address_billing);    
		}elseif($data['client_contact']->client_address_main != null && $data['client_contact']->client_address_main != 0){
		 $data['client_address'] = $this->test_model->get_client_address_by_id($data['client_contact']->client_address_main);                     
		}else{
		  $data['client_address']  = $this->clients_model->get_client_address_by_client_id_pdf($data['client']->id);                            
		}

		$data['city'] 			   = $this->general_model->get_city($data['client_address']->city);
		$data['state'] 			   = $this->general_model->get_state($data['client_address']->state);	

		$data['invoice_details']   = $this->invoices_model->get_all_invoice_detail_by_invoiceid($invoice_id);
		
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/individual_invoice_details_print', $data);
    }



     public function send_email_invoice($invoice_id){

			$data['invoice'] 		   = $this->invoices_model->get_invoice_by_id($invoice_id);
		    $data['client_account']    = $this->clients_model->get_client_account($data['invoice']->client_account_id);
	        $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);  
			$this->invoices_model->send_email_invoice($invoice_id,$data['client_account']->client_id,$data['invoice']->client_account_id); 	 			
	  		redirect('invoices/unpaidinvoicelist');     
    }
	public function send_email_to_client_contacts($invoice_id){

		
		$data['invoice'] 		   = $this->invoices_model->get_invoice_by_id($invoice_id);
		$data['client_account']    = $this->clients_model->get_client_account($data['invoice']->client_account_id);
		$data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);  
		
		$client_contacts = $this->invoices_model->send_email_to_client_contacts($invoice_id,$data['client_account']->client_id,$data['invoice']->client_account_id); 	 		
		
		if(!empty($client_contacts)){
			$div = '<div>
			<p>This invoice has been emailed to these contacts: </p>
			<ol>';
			foreach ($client_contacts as $contact) {
				$div .='<li>'.$contact->first_name.' '.$contact->last_name.' - '.$contact->email.'</li>';
			}
			$div .=  '</ol></div>';
			echo $div;
		}else{
			echo "no contact found";
		}
		  exit;
}
    /**
     * App home.
     */
    public function client_payment()
    {
    	$data['title'] = 'Client Payments';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/client_payment', $data);
        
    }
    
    /**
     * App home.
     */
    public function crl_invoice_items()
    {
    	$data['title'] = 'CRL Invoice';
    	$data['invoices'] = $this->invoices_model->get_crl_invoice_items();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/crl_invoice_items', $data);
        
    }





    
     /**
     * App home.
     */
    public function crl_invoices($item_id)
    {
    	$data['title'] = 'CRL Invoice';
    	$data['invoices'] = $this->invoices_model->get_crl_invoices($item_id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/crl_invoices', $data);
        
    }
    
    public function upload_crl_invoice(){
    
    
   	if($_FILES['file']['tmp_name']){
	
	
   $file_name=$_FILES["file"]["name"];
   $id = $this->invoices_model->add_crl_invoice_item($this->input->post(),$file_name);
	if($id!=0){
	$handle = fopen($_FILES['file']['tmp_name'], "r");
	$data = fgetcsv($handle, 10000, ";"); 
	while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
		
		$this->invoices_model->add_crl_invoice($this->input->post(),$data,$id);
	
		}
   
   
  	$this->session->set_flashdata('crluploadSuccess', true);
  	} else {
  	$this->session->set_flashdata('crluploadFail', true);
  	}
  	
  	}
	redirect("invoices/crl_invoice_items");
    }
    
    
    public function upload_awsi_invoice(){
    
    
   	if($_FILES['file']['tmp_name']){
	
	
   $file_name=$_FILES["file"]["name"];
   
   $id = $this->invoices_model->add_awsi_invoice_item($this->input->post(),$file_name);
   if($id!=0){
	$handle = fopen($_FILES['file']['tmp_name'], "r");
	$data = fgetcsv($handle, 10000, ";"); 
	while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
		
		$this->invoices_model->add_awsi_invoice($this->input->post(),$data,$id);
	
		}
		$this->session->set_flashdata('awsiuploadSuccess', true);
  } else {
  	$this->session->set_flashdata('awsiuploadFail', true);
  	}
  	
  	}
	redirect("invoices/awsi_invoice_items");
    }
    
    
    /**
     * App home.
     */
    public function awsi_invoice_items()
    {
    	$data['title'] = 'CRL Invoice';
    	$data['invoices'] = $this->invoices_model->get_awsi_invoice_items();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/awsi_invoice_items', $data);
        
    }
    
    
    /**
     * App home.
     */
    public function awsi_invoices($item_id)
    {
    	$data['title'] = 'AWSI Invoice';
    	$data['invoices'] = $this->invoices_model->get_awsi_invoices($item_id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/awsi_invoices', $data);
        
    }

    public function awsi_invoice_print($invoiceid){        
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/prints/awsi_invoice_print');
    }
	

	//list line items against invoice id
    public function invoicedetails($invoiceid){  
		$data['title'] = 'Invoice Details';
    	$data['invoice_detail_list'] = $this->invoices_model->get_all_invoice_detail_by_invoiceid($invoiceid);
    	$data['invoiceid'] = $invoiceid;
    	$data['invoice_list'] = $this->invoices_model->get_invoice_by_id($invoiceid);
    	//echo '<pre>';print_r($data);exit();
    	$data['client_details'] = $this->clients_model->get_client($data['invoice_list']->clientID);

		$data['payment_methods']= $this->payment_model->get_client_payment_method_by_clientid($data['invoice_list']->clientID);
		

		if(isset($_GET['tab']) && !empty($_GET['tab'])){
			$tab = $_GET['tab'];
			$this->session->set_userdata('invoice_tab_name', $tab);
			$data['tabname'] = $this->session->userdata('invoice_tab_name');
		}
		if(isset($_GET['url']) && !empty($_GET['url'])){
			$backurl = $_GET['url'];
			$this->session->set_userdata('backurl', $backurl);
			$data['backurl'] = $this->session->userdata('backurl');
		}
			
		// if ($this->session->userdata('invoice_tab_name')) {
		// 	$data['tabname'] = $this->session->userdata('invoice_tab_name');
		// }
		// if ($this->session->userdata('invoice_tab_name')) {
		// 	$data['backurl'] = $this->session->userdata('backurl');
		// }
		
    	$data['client_account_details'] = $this->clients_model->get_client_account($data['invoice_list']->client_account_id);

        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/invoice_detail', $data);
    }

	//adding line items against invoice id
    public function addlineitems(){    
		$message = "Failed";
		$class = "error";
		$formdata = $this->input->post();
		if(!empty($formdata['invoice_amt'])){
			//adding detail in invoice_detail table
			$invoice_detail_id = $this->invoices_model->add_inline_item($formdata);
			
			if(!empty($invoice_detail_id)){

				//adding total amount/ status / invoice closing
				$invoiceid = $formdata['invoice_id'];
				$status = $close = 0;
				$totalDue = ($formdata['invoice_total'])+($formdata['invoice_amt']);
				$total_paid = $formdata['paid_total'];
				if($totalDue > $total_paid ){
					$status = 0;
					$close = 0;
				}else{
					$status = 1;
					$close = 1;
				}

				// update
				$this->invoices_model->update_invoice_on_addinglineitem($status,$close,$totalDue,$invoiceid);
				$message = "Line item added";
				$class = "success";

			}


		}
		
		// redirect('invoices/invoicedetails/'.$formdata['invoice_id']);
		redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class); 
    }
	public function applypayment(){
		$formdata = $this->input->post();
		$status = $close = 0;
		$message = 'failed';
		$class = 'error';
		
		$formdata['admin_user_id'] = $this->session->userID;

		
		if(!empty($formdata['payment_amt'])){
			$invoice_pay_id = $this->invoices_model->apply_payment($formdata);

			if(!empty($invoice_pay_id)){
				//update main invoice table
				//status, invoice close, total paid

				$invoiceid = $formdata['apply_pay_invoice_id'];
				$invoiceAmt = $this->invoices_model->get_amount_by_id($invoiceid);
				$invoiceAmt = $invoiceAmt->amount;
				
				$totalpaid = $this->get_total_invoice_paymentitem_amount($invoiceid);
				if($totalpaid >= $invoiceAmt ){
					$status =  1;
					$close =  1;
				}else{
					$status =  0;
					$close =  0;
				}

				$this->invoices_model->update_invoice_on_addpayment($status,$close,$totalpaid,$invoiceid);
				$message = 'success';
				$class = 'success';

			}
		}

		if($formdata['listtype'] == 'unpaid'){
			redirect('invoices/unpaidinvoicelist/'); 
		}else if($formdata['listtype'] == 'invoicedetail'){
			redirect('invoices/invoicedetails/'.$invoiceid.'?message='.$message.'&class='.$class); 
		}else if($formdata['listtype'] == 'invoicedetail_new'){
			redirect('invoices/unpaidinvoices/'.$invoiceid); 
		}else{
			redirect('invoices/list_invoice/'); 
		}
		
	}

	public function editstatus(){
		//update invoice table
		$formdata = $this->input->post();
		echo "<pre>";
		print_r($formdata);
		exit;
		$this->invoices_model->update_status($formdata);
		redirect('invoices/list_invoice');
	}

	//creating new invoice against client by admin
	public function addinvoice(){
		$formdata = $this->input->post();
		$invoice_id = $this->invoices_model->add_invoice($formdata);
		redirect('invoices/invoicedetails/'.$invoice_id); 
	}

	function getclientaccountlist($typedletter){
		$value ="";
        if(!empty($typedletter)){ 
            $clients = $this->invoices_model->get_allclient_by_search_text($typedletter);

			
			if(!empty($clients)){
				$client_id_arr = [];
				$i = 0;
				foreach ($clients as $client) {
					$client_id_arr[$i] = $client->id;
					$i++;
				}
				$clientsAccList = $this->invoices_model->get_activ_client_accountid_by_clientid($client_id_arr);
				$dropdown = "";
				
				if(!empty($clientsAccList)){
					$dropdown .= "<ul>";

					foreach($clientsAccList as $val){

						$originalString =$val->client_name; // Example string with a single comma
						$modifiedString = str_replace("'", "’", $originalString);


					$dropdown .= '<li onclick="' .attachvaluetoformfield.'('."'".$val->client_id."'".','."'".$val->client_account_id."',"."'".$modifiedString."'".')">'.$val->client_name.' - '.$val->client_account_id.'</a></li>';
				}
				$dropdown .= '</ul>';
            	echo $dropdown;;
				}
			}
		}
	}


	// payment via stored card by admin
	// payment via stored card by admin
	function paymentviastored(){
		include APPPATH.'libraries/Authnetcim.php';
		$formdata  = $this->input->post();
		$paymentmethodDetails = $this->payment_model->get_payment_method($formdata['paymentmthodid']);

	    if(!empty($paymentmethodDetails->profile_authorize_id) && !empty($paymentmethodDetails->payment_authorize_id))	{
			

		$billingtype = $paymentmethodDetails->billing_type;
		$billingtypeStr = "";
		if($billingtype == '1'){
			$billingtypeStr = "Credit Card";
		}
		if($billingtype == '2'){
			$billingtypeStr = "ACH";
		}

		// payment data to send over auth api
		$cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);

		$cim->setParameter('customerProfileId',$paymentmethodDetails->profile_authorize_id);
    	$cim->setParameter('customerPaymentProfileId', $paymentmethodDetails->payment_authorize_id);
    	$cim->setParameter('amount', $formdata['paydata_amount']);
		
		$cim->chargeCustomerProfile();

		// Try to AUTH_CAPTURE
		if( $cim->isSuccessful() )
		// if(true)
			{
				
				$str = "";
				// $n = 4;
				// $start = strlen($str) - $n;
				// $str1 = '';
				// for ($x = $start; $x < strlen($str); $x++) {
				//     $str1 .= $str[$x];
				// }
				$transId = "";
					
				// add entry in invoice_payment table and invoice table
				$payment['pay_client_id'] = $formdata['client_id'];
				$payment['pay_client_account_id'] = $formdata['client_account_id'];
				$payment['apply_pay_invoice_id'] = $formdata['invoice_id'];
				$payment['pay_date'] = date("Y-m-d");
				$payment['payment_amt'] = $formdata['paydata_amount'];
				$payment['payment_type'] = $billingtypeStr;
				$payment['online_payment_profile_id'] = $paymentmethodDetails->payment_authorize_id;
				$payment['online_authorize_net_reference'] = $transId;
				$payment['pay_ref'] = $str1;
				$payment['payment_description'] = 'Online '.$billingtypeStr.' Payment';
				$payment['pay_notes'] = '';

				$invoice_pay_id = $this->invoices_model->apply_payment($payment);

				if(!empty($invoice_pay_id)){

					// updating invoice table
					$invoiceid = $formdata['invoice_id'];//3
					$payment_amt = $formdata['paydata_amount'];//20
					$existingpaid = $formdata['paid_total'];//210

					$totalDue = $formdata['invoice_total']; //230
					$totalpaid = ($existingpaid)+($payment_amt);//230
					
					$status = $close = 0;
					if($totalpaid < $totalDue ){
						$status =  0;
						$close =  0;
					}
					if($totalpaid >= $totalDue ){
						$status =  1;
						$close =  1;
					}
					$this->invoices_model->update_invoice_on_addpayment($status,$close,$totalpaid,$invoiceid);

				}
				redirect('invoices/invoicedetails/'.$invoiceid."?message=success"); 
			}
			else
			{
				redirect('invoices/invoicedetails/'.$formdata['invoice_id']."?message=failed"); 
			}
		}
		redirect('invoices/invoicedetails/'.$formdata['invoice_id']."?message=failed"); 
	}


	public function batch_import_invoices($page=1){

	$data['title'] = 'Batch Import Invoices'; 

    	 $etype_num_rows = $this->invoices_model->batch_import_invoice_num_rows();
	     // Generate pagination.
	     $perPage = 15;
	     // Handle pagination.
	     if ($page==1) {
	        $offSet = 0;
	     } else {
	        // $offSet = ($offSet - 1) * $perPage.
	        $offSet = ($page - 1) * $perPage;
	     }
	     //$FullURL = explode('?',$_SERVER['REQUEST_URI']);
		 $start_page =  site_url('invoices/batch_import_invoices?'.$FullURL[1]);

	     $start_pages  = site_url('invoices/batch_import_invoices');
	     $page_url  =  $start_pages.'/';
	     
	     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
	     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
	     // end pagination
	     $data['invoice_list'] = $this->invoices_model->batch_import_invoice($perPage,$offSet);

		//$data['invoice_list']=$this->invoices_model->batch_import_invoice();
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/batch_invoices', $data);
	}

	public function batch_invoices_details($id)
    {

		$data['title'] = 'Batch Invoice Details';
	    $data['batch_count']  = $this->invoices_model->invoice_batch_run_num($id);
	    $data['batch_groups']  = $this->invoices_model->get_invoice_group_by_group_id($id);
	    $data['invoice_list'] = $this->invoices_model->invoice_count_by_batch_invoice_to_invoice_table_print($id);	
	    //echo '<pre>';print_r($data['invoice_list']);exit();	
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/batch_invoice_details', $data);
    }

	public function batch_invoices_details_print($id)
    {
		$data['title'] = 'Batch Invoice Details';
	    //$data['invoice_id'] = $invoice_id;
	    //$data['client_contact_id'] = $client_contact_id;

	    $data['invoice_list']	   = $this->invoices_model->invoice_count_by_batch_invoice_to_invoice_table_print($id);	
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/prints/batch_invoice_details_print', $data);
    }



    public function generate_batch_invoice($invoice_id){
    	$this->invoices_model->generate_batch_invoice($invoice_id);
    	$this->session->set_flashdata('email_sent', true);
		redirect('invoices/batch_invoices_details/'.$invoice_id.'/');
    }


    public function generate_batch_due_invoice($invoice_id){
    	$this->invoices_model->generate_batch_due_invoice($invoice_id);
    	//$this->session->set_flashdata('email_sent', true);
		redirect('invoices/batch_invoices_details/'.$invoice_id.'/');
    }



	public function view_batch_email_invoice()
    {
    		
    	$formdata = $this->input->get();
    	$data['title'] = 'View Batch Email Invoice';		
    	$data['batchs'] = $this->invoices_model->get_batch_invoice_by_group_id($formdata['batch_group_id'],$formdata['date']);    	
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/view_invoice_batch', $data);
        
    } 


    public function batch_invoices_details_email($id){

	$invoice_list = $this->invoices_model->invoice_count_by_batch_invoice_to_invoice_table_print($id);  
	  foreach($invoice_list as $detail){	  
	  	$this->send_bulk_email_invoice($detail->invoice_id);	   
		} 	
    $this->session->set_flashdata('email_sent', true);	
    redirect('invoices/batch_invoices_details/'.$id);     
    }

    public function send_bulk_email_invoice($invoice_id){

			$data['invoice'] 		   = $this->invoices_model->get_invoice_by_id($invoice_id);
		    $data['client_account']    = $this->clients_model->get_client_account($data['invoice']->client_account_id);
	        $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);  
			$this->invoices_model->send_email_invoice($invoice_id,$data['client_account']->client_id,$data['invoice']->client_account_id); 	 	     
    }

	function get_total_invoice_paymentitem_amount($invoiceid){
		$lineitems = $this->invoices_model->get_all_paid_amt($invoiceid);
		$total_Invoice_Amt = 0.0;
		if(!empty($lineitems)){
			foreach ($lineitems as $lineitem) {
				$total_Invoice_Amt = ($total_Invoice_Amt)+($lineitem->payment_amount);
			}
		}
		return $total_Invoice_Amt;
	}

	function clear_back_button_session(){
		$this->session->unset_userdata('invoice_tab_name');
		$this->session->unset_userdata('backurl');

		exit;
	}

	public function unpaidinvoices()
    {
    	$data['title'] = 'List Unpaid Invoice';  
		
		$data['invoice_list'] = $this->batchinvoice_model->get_all_closed_invoices_for_unpaid_invoice();
		// echo "<pre>"; 
		// print_r($data['invoice_list']); exit;
		$data['sum']=$this->batchinvoice_model->get_sum_of_unpaidinvoicelist(); 

    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/show_all_unpaid_invoices', $data);
        
    }

	public function print_allunpaidinvoice()
    {
		$data['title'] = 'Print All Unpaid Invoices';
	    $data['invoice_list'] = $this->batchinvoice_model->get_all_closed_invoices_for_unpaid_invoice();
		$data['sum'] = $this->batchinvoice_model->get_sum_of_unpaidinvoicelist(); 
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/allunpaid_invoices_print', $data);
    }


	function getclientlist_for_inoviedetailpage($typedletter){
		$value ="";
        if(!empty($typedletter)){ 
            $clients = $this->invoices_model->get_allclient_by_search_text($typedletter);			
			if(!empty($clients)){
				$dropdown = "";
				
				if(!empty($clients)){
					$dropdown .= "<select id='diff_client_ids' name='diff_client_ids' class='form-control' onchange='getclientaccounts_change()' ><option value=''>--Select Client--</option>";

					foreach($clients as $val){
					// $dropdown .= '<option value="'.$val->id.'" onclick="' .getclientaccounts.'('."'".$val->id."'".')">'.$val->client_name.' - '.$val->id.'</option>';
					$dropdown .= '<option value="'.$val->id.'">'.$val->client_name.' - '.$val->id.'</option>';
					
					}
					$dropdown .= '</select>';
            	
				}
				echo $dropdown;
			}
		}
	}

	function getclientaccountlist_for_inoviedetailpage($client_id){
		
        if(!empty($client_id)){ 
            $clients = $this->clients_model->get_client_account_by_client_id($client_id);
			$dropdown = ''; 
			if(!empty($clients)){
				
				$dropdown .= "<select id='diff_clientacc_ids' name='diff_clientacc_ids' class='form-control' onchange='getinvoiceids_change()' ><option value=''>--Select Client Account--</option>";
				foreach($clients as $val){
				$dropdown .= '<option value="'.$val->id.'">'.$val->account_reference.' - '.$val->id.'</option>';
				}
				$dropdown .= '</select>';
			}
			echo $dropdown;
			
			
		}
	}

	public function deleteinvoice(){
        $invoice_id = $this->input->get('invoiceid');   
		$invoiceRecord = $this->invoices_model->get_invoice_by_id($invoice_id);
		$invoiceRecord = json_decode(json_encode($invoiceRecord), true);
		if(!empty($invoiceRecord )){
			$deleteRecordId = $this->invoices_model->insert_invoice_deleted_record($invoiceRecord);
			if($deleteRecordId != ''){
				$delete = $this->invoices_model->delete_invoice_by_id($invoice_id);
				if($delete){
					redirect('invoices/list_invoice'); 
				}else{
					redirect('invoices/invoicedetails/'.$invoice_id.'?message=failed');
				}
			}
		}
    }


	function update_disable_latefee($invoiceid, $disableval){
		if($this->invoices_model->updatedisablefee($invoiceid, $disableval)){
			echo "success";
		}else{
			echo "failed";
		}
		exit;
	}

	// public function random_pull_group_stats(){
	// 	$data['title'] = 'Random Pull Group Stats';
	// 	$data['pull_groups'] = $this->general_model->pull_groups();
	// 	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/random_pull_group_stat', $data);
	// }

	// public function get_auth_code_by_transactionid($transId){
	// 	$authcode = "Not found";
	// 	if(!empty($transId)){
	// 		include APPPATH.'libraries/Authnetcim.php';
	// 		$cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);
	// 		$cim->setParameter('transid', $transId);
	// 		$cim->getauthcode_by_transactionid();
	// 		if( $cim->isSuccessful() ){
	// 			$authcode = $cim->getResponseCodeViaTransid();
	// 		}else{
	// 			$authcode = "Not found";
	// 		}
	// 	}
	// 	echo $authcode;
	// }

	
	public function client_view_dummy($id,$account_id)
    {  
      if($this->input->get('account_status')=='2'){
        $account_status=0;
      }elseif($this->input->get('account_status')=='1'){
        $account_status=1;
      }else{
        $account_status=1;
      }   
    	$data['title'] = 'Clients';
      $data['account_id'] = $account_id;
      $data['client_id'] = $id;
     
    	$data['client'] = $this->clients_model->get_client($id);
    	$data['states'] = $this->general_model->get_states('230');
    	$data['account_types'] = $this->general_model->account_types();
    	$data['client_account_num'] =  $this->clients_model->get_client_account_num_by_client_id($id);
    	$data['client_address_num'] =  $this->clients_model->get_client_address_num_by_client_id($id);
    	$data['client_contact_num'] =  $this->clients_model->get_client_contact_num_by_client_id($id);
    	$data['client_accounts'] =  $this->clients_model->get_client_account_by_client_id_and_status($id,$account_status); 
    	$data['client_addresss'] =  $this->clients_model->get_client_address_by_client_id($id); 
    	$data['client_contacts'] =  $this->clients_model->get_client_contact_by_client_id($id); 
      $data['client_labs'] =  $this->clients_model->pull_client_labs_by_client_id($id,$account_id);
      $data['client_notes'] =  $this->clients_model->get_client_notes($id);
      $data['client_account'] = $this->clients_model->get_client_account($account_id);
      $data['client_accounts_for_random'] = $this->clients_model->get_client_account_by_client_id_for_frontend_with_limit($id); 
      $data['client_contact_assign'] = $this->clients_model->pull_client_account_by_client_id($id);
      $data['account_num']=$account_id;
    	$data['labs'] =  $this->general_model->get_labs();
    	$data['pull_groups'] = $this->general_model->pull_groups();
      $data['pull_groups_zero'] = $this->general_model->pull_groups_with_random_pull_individual_account_zero();
      $data['pull_groups_one'] = $this->general_model->pull_groups_with_random_pull_individual_account_one();
    	$data['pgroup'] = $this->general_model->get_pull_group($data['client_account']->pull_group_id);
      $data['paid_invoice'] = $this->invoices_model->get_invoice_detail_by_client_account_id($id);
      $data['invoice_list'] = $this->invoices_model->get_invoice_detail_by_client_account_id_unpaid($id);
      $data['unpaid_invoice'] = $this->invoices_model->get_invoice_detail_by_client_account_id_unpaid($id);
      $data['old_client_accounts'] =  $this->clients_model->get_client_inactive_account_by_client_id($id);
	    $data['account_notes']=  $this->clients_model->get_client_account_notes($id,$account_id); 

      $data['client_invoice_detailslist'] = $this->invoices_model->get_all_invoice_detail_by_clientid($id);

	
      $data['test_results'] = $this->dashboard_model->get_alltest_results_by_clientid($id); 
      

      $data['client_payment_profile_ids'] = $this->payment_model->get_client_payment_method_by_clientid($id);
     

     
      //echo '<pre>';print_r($data['client_addresss']);exit();
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/dummyclientview', $data);
        
    }

	function sendcustomemailto_clientcontact(){
		$formdata = $this->input->post();
		echo "<pre>";
		print_r($formdata);
		exit;

	}

} // Class end.
