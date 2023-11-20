<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Finencial extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('employees_model');
		$this->load->model('general_model');
		$this->load->model('invoices_model');
		//$this->load->model('email_model');
		$this->load->library('common/user');
		$this->load->library('general');
		if (!$this->session->userID):
		redirect('auth');
		endif;
		
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
    public function paidinvoice()
    {
        // $id = $this->session->client_userID; 
        // echo $id; exit;
		// $data = $this->dashboard_model->get_client_contact($id);
        exit;
		$status = 1;
    	$data['title'] = 'Paid Invoices';
    	$data['invoice_list'] = $this->invoices_model->get_all_closed_invoices();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/list_invoice', $data);
    }
    
    public function unpaidinvoicelist()
    {
    	$data['title'] = 'Unpaid Invoices';
    	$data['invoice_list'] = $this->invoices_model->get_unpaid_invoices();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/list_invoice_unpaid', $data);
        
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

    	$data['invoice_list'] = $this->invoices_model->get_invoice_by_id($invoiceid);

    	$data['client_details'] = $this->clients_model->get_client($data['invoice_list']->clientID);

    	$data['client_account_details'] = $this->clients_model->get_client_account($data['invoice_list']->client_account_id);

        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/invoice_detail', $data);
    }

	//adding line items against invoice id
    public function addlineitems(){    
		
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

			}


		}
		
		redirect('invoices/invoicedetails/'.$formdata['invoice_id']); 
    }
	public function applypayment(){
		$formdata = $this->input->post();
				
		if(!empty($formdata['payment_amt'])){
			$invoice_pay_id = $this->invoices_model->apply_payment($formdata);

			if(!empty($invoice_pay_id)){
				//update main invoice table
				//status, invoice close, total paid
				$invoiceid = $formdata['apply_pay_invoice_id'];
				$payment_amt = $formdata['payment_amt'];
				$existingpaid = $formdata['total_paid'];
				$totalDue = $formdata['totaldue'];
				$totalpaid = ($existingpaid)+($payment_amt);
				$status = $close = 0;
				if($totalpaid < $totalDue ){
					$status =  0;
					$close =  0;
				}
				if($totalpaid == $totalDue ){
					$status =  1;
					$close =  1;
				}

				$this->invoices_model->update_invoice_on_addpayment($status,$close,$totalpaid,$invoiceid);


			}
		}

		
		redirect('invoices/list_invoice/'); 
	}

	public function editstatus(){
		//update invoice table
		$formdata = $this->input->post();
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
				$clientsAccList = $this->invoices_model->get_client_accountid_by_clientid($client_id_arr);
				$dropdown = "";
				
				if(!empty($clientsAccList)){
					$dropdown .= "<ul>";

					foreach($clientsAccList as $val){
					$dropdown .= '<li onclick="' .attachvaluetoformfield.'('."'".$val->client_id."'".','."'".$val->client_account_id."',"."'".$val->client_name."'".')">'.$val->client_name.'</a></li>';
				}
				$dropdown .= '</ul>';
            	echo $dropdown;;
				}
			}
		}
	}


} // Class end.
