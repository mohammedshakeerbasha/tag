<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// include("./vendor/autoload.php"); 
// require 'vendor/autoload.php';


class Payment extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('authorize_net');
        $this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('employees_model');
		$this->load->model('dashboard_model');
		$this->load->model('paymentTracking_model');
		$this->load->model('payment_model');
		$this->load->model('general_model');
		$this->load->model('invoices_model');
		//$this->load->model('email_model');
		$this->load->library('common/user');
		$this->load->library('general');
		$this->load->library('authorize_net');
		if (!$this->session->userID):
		redirect('auth');
		endif;
    }
    public function index()
    {
        $data['title'] = 'Payment';
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/payments/payment', $data);
    }
	
	//payment via credit card
	public function payment(){
		$class="";
		$message = "";
        $formdata = $this->input->post();
		$paymentmethodid = '';

		if(($formdata['paydata_cc_no'] == '') && ($formdata['paydata_ach_no'] != "")){
			$formdata['paydata_cc_no'] = $formdata['paydata_ach_no'];
		}

		

		$descForTransaction = '';
		$invoicePaymentDes = $this->descriptionfor_invoice_payment_table($formdata,'2');
		
		//last four digit card number
		$str = $this->get_last_fourdigit_cardnumber($formdata['paydata_cc_no']);

		//payment process based on ach/credit
		if($formdata['billing_type'] == 1){
			$descForTransaction = 'Online Credit Card Payment';
			$dataArr =  $this->auth_payment_transaction_via_card($formdata,$descForTransaction);
			$transId = $dataArr['transId'];
			$paymentResponse = $dataArr['responsecode'];
			$bt = "CC";
			$formdata['btInt'] = 1;
		}else{
			$descForTransaction = 'Online ACH Payment';
			$dataArr = $this->achpaymentmanual($formdata);
			$transId = $dataArr['transid'];
			$paymentResponse = $dataArr['paymentResponse'];
			$bt = "ACH";
			$formdata['btInt'] = 3;
		}
		
		// Table updation after successfully  payment
		if(!empty($transId)){

			//check if user has opt to store profile ;; for credit card
			if(isset($formdata['store_profile']) && $formdata['store_profile'] == '1' && $formdata['billing_type'] == 1){	
				$paymentmethodid = $this->store_payment_profile($transId,$formdata);
				//store payment id for client account for respective invoice id
				if($paymentmethodid !=""){
              
					// update client_account_billing_profile_id field in client_accounts table
					$this->invoices_model->update_client_account_billing_profile_id($formdata['client_account_id'], $paymentmethodid);
				  }
			}
			// ach and store profile if choosen
			if(isset($formdata['store_profile']) && $formdata['store_profile'] == '1' && $formdata['billing_type'] == 2){	
				$paymentmethodid = $this->store_payment_profile_for_ach($formdata);
				if($paymentmethodid !=""){
              
					// update client_account_billing_profile_id field in client_accounts table
					$this->invoices_model->update_client_account_billing_profile_id($formdata['client_account_id'], $paymentmethodid);
				  }
				
			}
			
			$formdata['paymentRef'] = $bt.' : XXXX'.substr($formdata['paydata_cc_no'],-4).' - Authorize.net Profile: '.$transId;

			// add entry in invoice_payment table and invoice table
			$invoice_pay_id = $this->update_invoicepayment_table_on_payment_transaction($formdata,$transId,$invoicePaymentDes,$paymentResponse);
			
			$message = "Payment Success";
			$class = 'success';
            // update invoice table
			if(!empty($invoice_pay_id)){
				$this->update_invoice_table_on_payment_transaction($formdata);
			}

		}else {
			// $this->authorize_net->debug();
			$class = 'error';
			$message = "Payment Failed";
			
		} 

		
		// table updation::end
		if($formdata['user_type'] == 'admin'){
			redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class); 
		}else{
			redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class); 
		}
		 
    }
	
	function paymentviastored(){
		include APPPATH.'libraries/Authnetcim.php';
		$formdata  = $this->input->post();
		
		$paymentmethodDetails = $this->payment_model->get_payment_method($formdata['paymentmthodid']);
		$formdata['paydata_cc_no'] = $paymentmethodDetails->credit_card;
		$invoicePaymentDes = $this->descriptionfor_invoice_payment_table($formdata,'1');

	    if(!empty($paymentmethodDetails->profile_authorize_id) && !empty($paymentmethodDetails->payment_authorize_id))	{
						
		$billingtype = $paymentmethodDetails->billing_type;

		$billingtypeStr = "";
		if($billingtype == '1'){
			$billingtypeStr = "Credit Card";
			$paymentRef = 'CC : XXXX'.substr($formdata['paydata_cc_no'],-4).' - Authorize.net Profile: '.$paymentmethodDetails->payment_authorize_id;
			$storedType = 7;
		}
		if($billingtype == '2'){
			$billingtypeStr = "ACH";
			$paymentRef = 'ACH : XXXX'.substr($formdata['paydata_cc_no'],-4).' - Authorize.net Profile: '.$paymentmethodDetails->payment_authorize_id;
			$storedType = 8;
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
				
				$transId = $cim->getTransId();
				$paymentResponse = $cim->getPaymentResponse();
					
				// add entry in invoice_payment table and invoice table
				$payment['pay_client_id'] = $formdata['client_id'];
				$payment['pay_client_account_id'] = $formdata['client_account_id'];
				$payment['apply_pay_invoice_id'] = $formdata['invoice_id'];
				$payment['date_of_payment'] = date("Y-m-d");
				$payment['payment_amt'] = $formdata['paydata_amount'];
				$payment['payment_type'] = $storedType;
				$payment['online_payment_profile_id'] = $paymentmethodDetails->payment_authorize_id;
				$payment['online_authorize_net_reference'] = $transId;
				$payment['pay_ref'] = $paymentRef;
				$payment['payment_description'] = $invoicePaymentDes;
				$payment['pay_notes'] = '';
				$payment['admin_user_id'] = $this->session->userID;
				$payment['auth_payment_response'] = $paymentResponse;

				$invoice_pay_id = $this->invoices_model->apply_payment($payment);

				if(!empty($invoice_pay_id)){

					// // updating invoice table
					// $invoiceid = $formdata['invoice_id'];//3
					// $payment_amt = $formdata['paydata_amount'];//20
					// $existingpaid = $formdata['paid_total'];//210

					// $totalDue = $formdata['invoice_total']; //230
					// // $totalpaid = ($existingpaid)+($payment_amt);//230
					// $totalpaid = $this->get_total_invoice_paymentitem_amount($invoiceid);
					
					// $status = $close = 0;
					// if($totalpaid < $totalDue ){
					// 	$status =  0;
					// 	$close =  0;
					// }
					// if($totalpaid >= $totalDue ){
					// 	$status =  1;
					// 	$close =  1;
					// }
					$status = $close = 0;
					$invoiceid = $formdata['invoice_id'];
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

				}
				$class = 'success';
				redirect('invoices/invoicedetails/'.$invoiceid."?message=success&class=".$class); 

			}
			else
			{
				$class = 'error';
				$message = $cim->getResponse();
				$formdata['failed_msg'] = $message;
				$id = $this->track_failed_payment($formdata);
				redirect('invoices/invoicedetails/'.$formdata['invoice_id']."?message=".$message."&class=".$class); 

			}
		}
		$message = 'No Payment Profile ID found';
        $class = 'error';
        $formdata['failed_msg'] = $message;
        $id = $this->track_failed_payment($formdata);
        redirect('invoices/invoicedetails/'.$formdata['invoice_id']."?message=".$message."&class=".$class); 

	}


	//at the time of payment if user wants to store and create profile for filled credit card details
	//using this inside invoice detail page for client
	public function store_payment_profile($transId, $formdata)
    {
		
		$profile_id = '';
		$payment_profile_id = '';
		$pay_method_id = '';

		if($formdata['user_type'] == 'admin' ){
			$formdata['admin_user_id'] = $this->session->userID;			
		}else{
			$id = $this->session->client_userID;
			$data['client_contact'] = $this->dashboard_model->get_client_contact($id);
		}
		include APPPATH.'libraries/Authnetcim.php';  
		
		$cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);

		// $cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_DEVELOPMENT_SERVER);
		$cim->setParameter('transactionid',$transId);

	
		$cim->createcustomeprofileoutoftransaction();
		if ($cim->isSuccessful())
    	{
			$payment_profile_id =  $cim->getPaymentId(); 
    		$profile_id = $cim->getProfileID();
		}

		if(!empty($payment_profile_id) && !empty($profile_id)){
			$formdata['payment_profile_id'] = $payment_profile_id;
			$formdata['customer_profile_id'] = $profile_id;			
			$pay_method_id = $this->payment_model->store_customer_payment_profile($formdata,$data['client_contact']);			
		}

		return $pay_method_id;
        
    }

	public function update_invoicepayment_table_on_payment_transaction($formdata,$transId,$invoicePaymentDes,$responsecode){
		$payment['pay_client_id'] = $formdata['client_id'];
		$payment['pay_client_account_id'] = $formdata['client_account_id'];
		$payment['apply_pay_invoice_id'] = $formdata['invoice_id'];
		$payment['date_of_payment'] = date("Y-m-d");
		$payment['payment_amt'] = $formdata['paydata_amount'];
		$payment['payment_type'] = $formdata['btInt'];
		$payment['online_authorize_net_reference'] = $transId;
		$payment['pay_ref'] = $formdata['paymentRef'];
		$payment['payment_description'] = $invoicePaymentDes;
		$payment['pay_notes'] = '';
		$payment['admin_user_id'] = $this->session->userID;	
		$payment['auth_payment_response'] = $responsecode;	
		$invoice_pay_id = $this->invoices_model->apply_payment($payment);
		return $invoice_pay_id;
	}

	public function update_invoice_table_on_payment_transaction($formdata){

		$invoiceid = $formdata['invoice_id'];//3
		$payment_amt = $formdata['paydata_amount'];//20
		$existingpaid = $formdata['paid_total'];//210

		// $totalDue = $formdata['invoice_total']; //230
		// // $totalpaid = ($existingpaid)+($payment_amt);//230
		// $totalpaid = $this->get_total_invoice_paymentitem_amount($invoiceid);
		
		// $status = $close = 0;
		// if($totalpaid < $totalDue ){
		// 	$status =  0;
		// 	$close =  0;
		// }
		// if($totalpaid == $totalDue ){
		// 	$status =  1;
		// 	$close =  1;
		// }

		$status = $close = 0;
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
	}

	//payment via credit or ach card
	public function auth_payment_transaction_via_card($formdata,$desc){
		$transId = "";
		$auth_net = array(
			'x_card_num'			=>$formdata['paydata_cc_no'], // Visa
			'x_exp_date'			=>$formdata['paydata_exp_month'].$formdata['paydata_exp_year'],//'12/14',
			'x_card_code'			=>$formdata['paydata_cvv'],//'123',
			'x_description'			=>$desc,
			'x_amount'				=>$formdata['paydata_amount'],//'20',
			'x_first_name'			=>$formdata['firstname'],//'John',
			'x_last_name'			=>$formdata['lastname'],//'Doe',
			'x_address'				=>$formdata['paydata_address'],//'123 Green St.',
			'x_city'				=>$formdata['paydata_city'],//'Lexington',
			'x_state'				=>$formdata['paydata_state'],//'KY',
			'x_zip'					=>$formdata['paydata_zip'],//'40502',
			'x_country'				=>$formdata['paydata_country'],//'US',
			'x_phone'				=>$formdata['paydata_phone'],//'555-123-4567',
			'x_email'				=>$formdata['paydata_email'],//'test@example.com',
			'x_customer_ip'			=>$this->input->ip_address(),
			);
		$this->authorize_net->setData($auth_net);
		if( $this->authorize_net->authorizeAndCapture() )
		{ 
            $transId = $this->authorize_net->getTransactionId();
            $responsecode = $this->authorize_net->responsecode_number();
			$dataArr = [
				'transId' =>$transId,
				'responsecode' =>$responsecode
			];
			$class ='success';
			return $dataArr;
		}else{
			$message = $this->authorize_net->getError();
			$class ='error';
			$formdata['failed_msg'] = $message;
			$id = $this->track_failed_payment($formdata);
			redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class);
		}

		
	}

	public function get_last_fourdigit_cardnumber($cardNumber){
		$n = 4;
		$start = strlen($cardNumber) - $n;
		$fourdigit = '';
		for ($x = $start; $x < strlen($str); $x++) {
			$fourdigit .= $str[$x];
		}

		return $fourdigit;

	}

	public function descriptionfor_invoice_payment_table($formdata,$paymentType){

		$type = 'Credit Card';
		$card = substr($formdata['paydata_cc_no'],-4);
		$date = date('Y-m-d');
		$user = 'NA';

		if($formdata['billing_type'] == '2'){
			$type = 'ACH';
		}

		if($formdata['user_type'] == 'admin' ){
			$id = $this->session->userID;
			$data = $this->user_model->get_user_by_id($id);
			$user = $data->firstName.' '.$data->surname;
		}else{
			$id = $this->session->client_userID; 
			$data = $this->dashboard_model->get_client_contact($id);
			$user = $data->first_name.' '.$data->last_name;
		}

		if($paymentType == '1' ){
			$desc = 'Online '.$type.' Payment (Stored Profile) - '.$date.' - '.$card.' - '.$user;
		}else{
			$desc = 'Online '.$type.' Payment - '.$date.' - '.$card.' - '.$user;
		}

		return $desc;
	}


	//lists all  invoice details against client and client_acc_id
	public function addlineitems(){
		
		$formdata = $this->input->post();
		$formdata['admin_user_id'] = $this->session->userID;	

		if(!empty($formdata['invoice_amt'])){
			// echo "test"; exit;
			$invoice_detail_id = $this->invoices_model->add_inline_item_emptyinvoice($formdata);

			if($invoice_detail_id !=""){
				// echo $invoice_detail_id; exit;
				redirect('clients/account_view/'.$this->input->post('client_id_name').'/'.$this->input->post('client_account_id_name'));
			}
			// echo "failed"; exit;
		}
		
		
	}


	public function achpaymentmanual($formdata){
		$transid = '';
		include APPPATH.'libraries/Authnetcim.php';  
		$cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);
		
		$cim->setParameter('atype',$formdata['ach_acc_type']);
		$cim->setParameter('routingNumber',$formdata['routing_number']);
		$cim->setParameter('accountNumber',$formdata['paydata_cc_no']);
		$cim->setParameter('nameOnAccount',$formdata['name_on_account']);
		$cim->setParameter('amount',$formdata['paydata_amount']);

		$cim->paymentviaAchDetails();
		if ($cim->isSuccessful())
    	{
			$transid =  $cim->getTransId();
			$paymentResponse = $cim->getPaymentResponse();
			$dataArr = [
				'transid'=>$transid,
				'paymentResponse'=>$paymentResponse
			];
			return $dataArr;
		}else{
			$message = $cim->getResponse();
			$class = 'error';
			$formdata['failed_msg'] = $message;
			$id = $this->track_failed_payment($formdata);
			redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class); 
		}
		
	}


	public function update_invoice_payment(){
		
		
		$formdata = $this->input->post();
		$message = "";
		
		if($formdata['upType'] == 2 || $formdata['upType'] == 4 || $formdata['upType'] == 5 || $formdata['upType'] == 6 || $formdata['upType'] == 9 || $formdata['upType'] == 10  ){
			if($this->payment_model->update_invoice_payment($formdata)){
				$allPayment = $this->invoices_model->get_all_paid_amt($formdata['invoice_id']);
				if(!empty($allPayment)){
					// calculate all payment of that invoice_id
					$total_paid = 0;
					foreach ($allPayment as $payment) {
						$total_paid = ($total_paid)+($payment->payment_amount);
					}

					$data['total_paid'] = $total_paid;
					$data['invoice_id'] = $formdata['invoice_id'];
					//update invoice table with updated total paid
					if($this->payment_model->update_invoice_on_editpayment($data)){
						$message = "success";
						$class="success";
					}else{
						$message = "total paid not updated";
						$class="error";
					}
				}else{
					$class="error";
					$message = "failed";
				}
				
			}
		}

		if($formdata['paytype_hidden'] == 7 || $formdata['paytype_hidden'] == 8 || $formdata['paytype_hidden'] == 1 || $formdata['paytype_hidden'] == 3 ){
			
			//for live ach/credit/stored credit/stored ach
			if($this->payment_model->update_invoice_payment_notes_reference($formdata)){
				$message = "success";
				$class="success";
			}else{
				$class="error";
				$message = "failed";
			}
			
		}
		
		redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class); 
		
	}


	public function delete_invoicepayment(){
		$formdata = $this->input->post();
		$message = "failed";
		$class="error";
		$invoicePaymentRecord = $this->payment_model->payment_mdetail_by_id($formdata['invoice_payment_id']);
		$invoicePaymentRecord = json_decode(json_encode($invoicePaymentRecord), true);
		if(!empty($invoicePaymentRecord)){

			//insert invoice payment record to tag_invoice_details_deleted_records before deleting it
			$deleteRecordId = $this->payment_model->insert_invoice_detail_deleted_record($invoicePaymentRecord);
			
			if($deleteRecordId != ''){

				//delete invoice payment
				if($this->payment_model->delete_invoice_payment($formdata['invoice_payment_id'])){
					$message = "payment deleted but invoice payment amount not updated";
					// $invoice = $this->invoices_model->get_invoice_by_id($formdata['invoice_id']);

					$total_paid = $this->get_total_invoice_paymentitem_amount($formdata['invoice_id']);//($invoice->total_paid_amount)-($formdata['total_paid']);
					$formdata['total_paid'] = $total_paid;
					if($this->payment_model->update_invoice_on_editpayment($formdata)){
						$message = "success";
						$class="success";
					}
				}
			}
		}
		redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class); 

	}
	

	public function store_payment_profile_for_ach($formdata)
    {
		$profile_id = '';
		$payment_id = '';

		$formdata['customer_profile_id'] = "";
        $formdata['payment_profile_id'] = "";

		if($formdata['user_type'] == 'admin' ){
			$formdata['admin_user_id'] = $this->session->userID;	
			$payment_id = $this->payment_model->store_customer_payment_profile($formdata,"");		
		}else{
			$id = $this->session->client_userID;
			$clientcontact = $this->dashboard_model->get_client_contact($id);
			$payment_id = $this->payment_model->store_customer_payment_profile($formdata,$clientcontact);
		}

        if($payment_id){

        //   include APPPATH.'libraries/Authnetcim.php';

          $cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);
		
          $email_address = $formdata['paydata_email'];
          $description   = 'Billing Profile';
          $customer_id   = 'tagams-'.$payment_id;
  
          // Create the profile
          $cim->setParameter('email', $email_address);
          $cim->setParameter('description', $description);
          $cim->setParameter('merchantCustomerId', $customer_id);
          $cim->createCustomerProfile();

		  
          if ($cim->isSuccessful())
          {
            $profile_id = $cim->getProfileID();
            $this->payment_model->update_profile_authorise_id($payment_id,$profile_id);

				if($formdata['ach_acc_type']=='checking' || $formdata['ach_acc_type']=='savings'){
					$echeck_type  = 'PPD';
					$customer_type= "individual";
				} else {
					$echeck_type  = 'CCD';
					$customer_type= "business";
				}
  
			   // Create the Payment Profile
			   $cim->setParameter('customerType', $customer_type);
			   $cim->setParameter('customerProfileId', $profile_id);
			   $cim->setParameter('billToFirstName', $formdata['firstname']);
			   $cim->setParameter('billToLastName',  $formdata['lastname']);
			   $cim->setParameter('billToAddress',  $formdata['paydata_address']);
			   $cim->setParameter('billToCity',  $formdata['paydata_city']);
			   $cim->setParameter('billToState', $formdata['paydata_state']);
			   $cim->setParameter('billToZip', $formdata['paydata_zip']);
			   $cim->setParameter('billToCountry', '');
			   $cim->setParameter('billToPhoneNumber', $formdata['paydata_phone']);
			   //$cim->setParameter('billToFaxNumber', $b_fax_number);
			   $cim->setParameter('accountType', $formdata['ach_acc_type']);
			   $cim->setParameter('nameOnAccount', $formdata['name_on_account']);
			   $cim->setParameter('echeckType', $echeck_type);
			   $cim->setParameter('bankName', $formdata['bank_name']);
			   $cim->setParameter('routingNumber', $formdata['routing_number']);
			   $cim->setParameter('accountNumber', $formdata['paydata_ach_no']);
		   
			   $cim->createCustomerPaymentProfile('check');
			  
			  if ($cim->isSuccessful()){
				  $payment_profile_id = $cim->getPaymentProfileId();
				  $this->payment_model->update_payment_authorise_id($payment_id,$payment_profile_id);
				  $this->session->set_flashdata('payment_method_sucess', true);
			  } else{
				  $cim->setParameter('customerProfileId', $profile_id);
				  $cim->deleteCustomerProfile();
				  $this->payment_model->delete_payment_m($payment_id);
				  $this->session->set_flashdata('bank_payment_method_fail', true);
				  $payment_id = "";
			  }
          	}
        }
		return $payment_id;    
        
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

	function track_failed_payment($formdata)
	{
		
		if($formdata['user_type'] == 'admin' ){
			$formdata['admin_user_id'] = $this->session->userID;	
		}else{
			$id = $this->session->client_userID;
			$clientcontact = $this->dashboard_model->get_client_contact($id);
			$formdata['client_contact_id'] = $clientcontact->id;
		}
		$id = $this->paymentTracking_model->track_failed_payment($formdata);
		return $id;
		
	}
	
	
} // Class end.
