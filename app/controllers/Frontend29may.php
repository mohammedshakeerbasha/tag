<?php
defined('BASEPATH') OR exit('No direct script access allowed');



class Frontend extends MY_Controller
{
 
	function __construct()
    {
        parent::__construct();
        $this->config->load('authorize_net');
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('general_model');
		$this->load->model('dashboard_model');
		$this->load->model('frontend_model');
		$this->load->model('employees_model');
		$this->load->model('test_model');
		$this->load->model('email_model');
		$this->load->model('payment_model');
		$this->load->library('common/user');
        $this->load->library('common/paginator'); 
        $this->load->model('reports_model');
        $this->load->library('tagams');
        $this->load->library('generatepdf');  
        $this->load->library('tag_general');
 		//$this->load->library('authorize_net');	
		if (!$this->session->client_userID):
		redirect('signin');
		endif;
		
    }

	/**
     * App home.
     */
    public function not_processed()
    {
    	$data['title'] = 'Not processed tests';
    	$data['not_processeds'] = $this->frontend_model->get_not_processed_lists();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/not_processed', $data);
        
    }
    
    /**
     * App home.
     */
    public function all_processed()
    {
    	$data['title'] = 'Processed Tests';
    	$data['all_processeds'] = $this->frontend_model->get_all_processed_lists();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/all_processed', $data);
        
    } 
    
    /**
     * App home.
     */
    public function list_payment_method()
    {

        $cid = $this->session->clientID; 
        $data['client'] = $this->clients_model->get_client($cid);
    	$id = $this->session->client_userID;
    	$data['title'] = 'Payment Methods';
		$data['client_contact'] = $this->dashboard_model->get_client_contact($id);
		$data['id'] = $id;
		$data['payment_methods'] = $this->payment_model->get_client_payment_method_by_clientid( $cid);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/payment_method/list_payment_method', $data);
        
    }
    
    public function automatic_invoice_payment(){
    
        if($this->input->post()){
            $this->clients_model->automatic_invoice_payment($this->input->post());
        }
        redirect('frontend/list_payment_method');
    }

    
     /**
     * App home.
     */
    public function add_payment_method()
    {
    	
    	$id = $this->session->client_userID;
    	$data['title'] = 'Add Payment Methods';
		$data['client_contact'] = $this->dashboard_model->get_client_contact($id);
		if($this->input->post()){
		$payment_id = $this->payment_model->add_payment_method($this->input->post(),$data['client_contact']);
		if($payment_id){
		include APPPATH.'libraries/Authnetcim.php';
		$cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);
		$email_address = $this->input->post('email');
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
    	}
    				$customer_type= "individual";
    				$b_first_name   = $this->input->post('first_name');
   					$b_last_name    = $this->input->post('last_name');
    				$b_address      = $this->input->post('address');
    				$b_city         = $this->input->post('city');
    				$b_state        = $this->input->post('state');
    				$b_zip          = $this->input->post('zipcode');
    				$b_country      = 'US';
    				$b_phone_number = $this->input->post('phone');
    				//$b_fax_number   = '123';//$this->input->post('fax');
    				$credit_card    = $this->input->post('credit_card_no');
    				$expiration     = $this->input->post('exp_year').'-'.$this->input->post('exp_month');
 
    			// Create the Payment Profile
    				$cim->setParameter('customerType', $customer_type);
    				$cim->setParameter('customerProfileId', $profile_id);
    				$cim->setParameter('billToFirstName', $b_first_name);
    				$cim->setParameter('billToLastName', $b_last_name);
    				$cim->setParameter('billToAddress', $b_address);
    				$cim->setParameter('billToCity', $b_city);
    				$cim->setParameter('billToState', $b_state);
    				$cim->setParameter('billToZip', $b_zip);
    				$cim->setParameter('billToCountry', $b_country);
    				$cim->setParameter('billToPhoneNumber', $b_phone_number);
    				//$cim->setParameter('billToFaxNumber', $b_fax_number);
    				$cim->setParameter('cardNumber', $credit_card);
    				$cim->setParameter('expirationDate', $expiration);
    
    
    				$cim->createCustomerPaymentProfile();
 					
 					
    				if ($cim->isSuccessful())
    					{
        				$payment_profile_id = $cim->getPaymentProfileId();
        				 $this->payment_model->update_payment_authorise_id($payment_id,$payment_profile_id);
    					$this->session->set_flashdata('payment_method_sucess', true);
    					} else{
                        $cim->setParameter('customerProfileId', $profile_id);
						$cim->deleteCustomerProfile();
						$this->payment_model->delete_payment_m($payment_id);
						$this->session->set_flashdata('payment_method_fail', true);
                        }
 
			}
		
            
            redirect('frontend/list_payment_method');
        }
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/payment_method/add_payment_method', $data);
        
    }

     public function add_payment_ach()
    {
        
        $id = $this->session->client_userID;
        $data['title'] = 'Add Payment Methods';
        $data['client_contact'] = $this->dashboard_model->get_client_contact($id);
        if($this->input->post()) {
        $payment_id = $this->payment_model->add_payment_method($this->input->post(),$data['client_contact']);
            
        if($payment_id){
        include APPPATH.'libraries/Authnetcim.php';
        $cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);
        $email_address = $this->input->post('email');
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
        }
                    
                    
                    $b_first_name   = $this->input->post('first_name');
                    $b_last_name    = $this->input->post('last_name');
                    $b_address      = $this->input->post('address');
                    $b_city         = $this->input->post('city');
                    $b_state        = $this->input->post('state');
                    $b_zip          = $this->input->post('zipcode');
                    $b_country      = 'US';
                    $b_phone_number = $this->input->post('phone');
                    $b_fax_number   = '12231321';//$this->input->post('fax');
                    $account_type    = $this->input->post('account_type');
                    $account_name     = $this->input->post('account_name');
                    if($account_type=='checking' || $account_type=='savings'){
                    $echeck_type  = 'PPD';
                    $customer_type= "individual";
                    } else {
                    $echeck_type  = 'CCD';
                    $customer_type= "business";
                    }
                   // $echeck_type     = $this->input->post('echeck_type');
                    $bank_name     = $this->input->post('bank_name');
                    $routing_number     = $this->input->post('routing_number');
                    $account_number     = $this->input->post('account_number');
                    $dl_state     = $this->input->post('dl_state');
                    $dl_number     = $this->input->post('dl_number');
                    $dl_dob     = $this->input->post('dl_dob');
 
                // Create the Payment Profile
                    $cim->setParameter('customerType', $customer_type);
                    $cim->setParameter('customerProfileId', $profile_id);
                    $cim->setParameter('billToFirstName', $b_first_name);
                    $cim->setParameter('billToLastName', $b_last_name);
                    $cim->setParameter('billToAddress', $b_address);
                    $cim->setParameter('billToCity', $b_city);
                    $cim->setParameter('billToState', $b_state);
                    $cim->setParameter('billToZip', $b_zip);
                    $cim->setParameter('billToCountry', $b_country);
                    $cim->setParameter('billToPhoneNumber', $b_phone_number);
                    //$cim->setParameter('billToFaxNumber', $b_fax_number);
                    $cim->setParameter('accountType', $account_type);
                    $cim->setParameter('nameOnAccount', $account_name);
                    $cim->setParameter('echeckType', $echeck_type);
                    $cim->setParameter('bankName', $bank_name);
                    $cim->setParameter('routingNumber', $routing_number);
                    $cim->setParameter('accountNumber', $account_number);
                   // $cim->setParameter('dlState', $dl_state);
                    //$cim->setParameter('dlNumber', $dl_number);
                    //$cim->setParameter('dlDateOfBirth', $dl_dob);
    
    
                    $cim->createCustomerPaymentProfile('check');
                    
                    if ($cim->isSuccessful())
                        {
                        $payment_profile_id = $cim->getPaymentProfileId();
                         $this->payment_model->update_payment_authorise_id($payment_id,$payment_profile_id);
                        $this->session->set_flashdata('payment_method_sucess', true);
                        } else{
                        $cim->setParameter('customerProfileId', $profile_id);
						$cim->deleteCustomerProfile();
						$this->payment_model->delete_payment_m($payment_id);
						$this->session->set_flashdata('bank_payment_method_fail', true);
                        }

            }
            
            $this->session->set_flashdata('payment_method', true);
            redirect('frontend/list_payment_method');
        }
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/payment_method/add_payment_method', $data);
        
    }

	
    
    public function delete_payment_method($id){
    	$profile = $this->payment_model->get_payment_method($id);
    	include APPPATH.'libraries/Authnetcim.php';
		$cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);
		$cim->setParameter('customerProfileId', $profile->profile_authorize_id);
		$cim->deleteCustomerProfile();
		if ($cim->isSuccessful())
    					{
        				$this->payment_model->delete_payment_method($id);
        				$this->session->set_flashdata('payment_method', true);
    					}
		
    	//$cim->setParameter('customerProfileId', $profile_id);
    	redirect('frontend/list_payment_method');
    						
    }
    
    public function make_payment_method_default($id){
    $this->payment_model->make_payment_method_default($id);
    	redirect('frontend/list_payment_method');
    }
     /**
     * App home.
     */
    public function list_invoice()
    {
    	$data['title'] = 'List Invoice';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/invoices/list_invoice', $data);
        
    }
    
     /**
     * App home.
     */
    public function paid_invoice()
    {
    	$data['title'] = 'List Invoice';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/invoices/pay_list_invoice', $data);
        
    }
    
     /**
     * App home.
     */
    public function my_report()
    {
    	$data['title'] = 'Client Reports';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/reports/client_report', $data);
        
    }
    
    /**
     * App home.
     */
    public function drug_test()
    {
    	$data['title'] = 'Drug Test';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/reports/drug_test', $data);
        
    }
    
    /**
     * App home.
     */
    public function add_client_address()
    {
        $data['title'] = 'Add Clients Address';
        if($this->input->post()){
            $id=$this->session->clientID;
        $cl = $this->clients_model->add_address($this->input->post(),$id);
        if($cl){
        $this->session->set_flashdata('add_address', true);
        } else {
        $this->session->set_flashdata('add_address_fail', true);
        }
      redirect('dashboard/client_address');
        }else{
 
        $data['back_link']=base_url('/dashboard/client_address');
        $data['states'] = $this->general_model->get_states('230'); 
     $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/add_address', $data);
            }
     
    }
   
    
    /**
     * App home.
     */
    public function edit_client_address($address_id)
    {
    	$data['title'] = 'Edit Clients Address';
    	if($this->input->post()){
       
    	$address = $this->clients_model->get_client_address_by_id($address_id);
    	
    	$city = $this->general_model->get_city($address->city);
    	$state = $this->general_model->get_state($address->state);
    	
    	
    	$ncity = $this->general_model->get_city($this->input->post('city'));
    	$nstate = $this->general_model->get_state($this->input->post('state'));
    	
    	 $cid = $this->session->clientID; 
        $client = $this->clients_model->get_client($cid);
    	
    	$client_contact = $this->dashboard_model->get_client_contact($this->session->client_userID);
    	$data = array(
                        'subject' => 'Address change by ' . $client_contact->first_name.' '.$client_contact->last_name ,
                        'address' => $address,
                        'to' => $this->user->settings('smtp_from_email'),
                        'change_address' => $this->input->post(),
                        'ncity' =>$ncity->name,
                        'client' =>$client->client_name,
                        'nstate' => $nstate->name,
                        'client_contact' => $client_contact,
                        'template_path' => $this->preferences->type('system')->item('full_app_themesDir')."/email/change_address"
                    );
                    $res = $this->email_model->send_grid_email($data);
        $cl = $this->clients_model->edit_address($this->input->post(),$address_id);
    	if($cl){
    	$this->session->set_flashdata('edit_address', true);
    	} else {
    	$this->session->set_flashdata('edit_address_fail', true);
    	}
      redirect('dashboard/client_address');
    	}else{
        $id = $this->session->clientID; 
        $data['title'] = 'Clients';
        $data['address_id']=$address_id;
        $data['client'] = $this->clients_model->get_client($id);
        $data['back_link']=base_url('/dashboard/client_address');
        $data['states'] = $this->general_model->get_states('230');
        $data['address'] =  $this->clients_model->get_client_address_by_id($address_id);
     $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/edit_address', $data);
         }
      
    }
    
   

   public function client_test_run($page=1)
    {
        $data['title'] = 'Drug Test Run List';   

    $client_id=$this->session->clientID;    
    $userid=$this->session->client_userID; 
    $data['client']=$this->clients_model->get_client($client_id);  
    $etype_num_rows = $this->clients_model->get_client_account_by_client_id_for_frontend_num_rows_for_frontend($client_id);
       // Generate pagination.
     $perPage = 15;
     // Handle pagination.
     if ($page==1) {
        $offSet = 0;
     } else {
        // $offSet = ($offSet - 1) * $perPage.
        $offSet = ($page - 1) * $perPage;
     } 
     $start_pages  = site_url('frontend/client_test_run');
     $page_url  =  $start_pages.'/';
     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
     // end pagination
     $data['perPage'] = $perPage;
     $data['offSet'] = $offSet;
     $data['client_accounts'] = $this->clients_model->get_client_account_by_client_id_for_frontend_with_limit_for_frontend($perPage, $offSet,$client_id); 
    //echo '<pre>'; print_r($data);exit();
      $data['client_contact_assign']= $this->clients_model->get_client_contact_assign_by_contact_id($userid);
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/client_test_run', $data);
    }

     /**
    * Test Client View Details.
    */
    public function test_client_view_details($id,$pull_group_id)
    {
        $userid=$this->session->client_userID; 
        $data['title'] = 'Drug Test Client Details';        
       $data['test_client_view_details'] = $this->frontend_model->test_client_view_details($pull_group_id);
       $data['client_contact_assign']= $this->clients_model->get_client_contact_assign_by_contact_id($userid); 
        $data['test_runs_by_id'] = $this->frontend_model->test_runs_by_id($id);
        $data['batch_count'] = $this->frontend_model->test_batch_run_num($id);
        //echo '<pre>';print_r($data);exit();
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/test_client_view_details', $data);
        
    }


      /**
    * Test Details.
    */
    public function test_details($id,$page=1)
    {
        $data['title'] = 'Drug Test Details';
        $etype_num_rows = $this->frontend_model->test_details_num_rows($id);
        // Generate pagination.
        $perPage = 10;
        // Handle pagination.
            if ($page==1) {
              $offSet = 0;
                } else {
              // $offSet = ($offSet - 1) * $perPage.
              $offSet = ($page - 1) * $perPage;
            }
        $start_page =  site_url('frontend/test_details/'.$id.'');
        $page_url  =  $start_page.'/';     
        $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
        $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
        // end pagination
        $data['test_runs_details'] = $this->frontend_model->test_details($id,$perPage, $offSet);
        $data['test_runs_by_id'] = $this->frontend_model->test_runs_by_id($id);
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/test_details', $data);
        
    }


      /**
    * Test Eligible Details.
    */
    public function test_eligible_details($id,$page=1)
    {
        $data['title'] = 'Drug Test Details';
        $etype_num_rows = $this->frontend_model->test_eligible_details_num_rows($id);
        // Generate pagination.
        $perPage = 10;
        // Handle pagination.
            if ($page==1) {
              $offSet = 0;
                } else {
              // $offSet = ($offSet - 1) * $perPage.
              $offSet = ($page - 1) * $perPage;
            }
        $start_page =  site_url('frontend/test_eligible_details/'.$id.'');
        $page_url  =  $start_page.'/';     
        $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,4);
        $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
        // end pagination
        $data['test_eligible_runs'] = $this->frontend_model->test_eligible_details($id,$perPage, $offSet);
        $data['test_runs_by_id'] = $this->frontend_model->test_runs_by_id($id);
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/test_eligible_details', $data);
        
    }

   /**
    * Selected Filter By Client Details.
    */
    public function test_selected_by_client($id,$client_account_id,$page=1)
    {
        $data['title'] = 'Drug Test Details';
        $etype_num_rows = $this->test_model->test_selected_by_client_num_rows($id,$client_account_id);
        // Generate pagination.
        $perPage = 10;
        // Handle pagination.
            if ($page==1) {
              $offSet = 0;
                } else {
              // $offSet = ($offSet - 1) * $perPage.
              $offSet = ($page - 1) * $perPage;
            }
        $start_page =  site_url('frontend/test_selected_by_client/'.$id.'/'.$client_account_id.'');
        $page_url  =  $start_page.'/';     
        $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,5);
        $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
        // end pagination
        $data['client_account'] = $this->clients_model->get_client_account($client_account_id);
        $data['client'] = $this->clients_model->get_client_by_id($data['client_account']->client_id);
        $data['test_runs_details'] = $this->test_model->test_selected_by_client($id,$client_account_id,$perPage, $offSet);
        $data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/test_selected_by_client', $data);
        
    }


   /**
    * Eligible Filter By Client Details.
    */
    public function test_eligible_by_client($id,$client_account_id,$page=1)
    {
        $data['title'] = 'Drug Test Details';
        $etype_num_rows = $this->test_model->test_eligible_by_client_num_rows($id,$client_account_id);
        // Generate pagination.
        $perPage = 10;
        // Handle pagination.
            if ($page==1) {
              $offSet = 0;
                } else {
              // $offSet = ($offSet - 1) * $perPage.
              $offSet = ($page - 1) * $perPage;
            }
        $start_page =  site_url('frontend/test_eligible_by_client/'.$id.'/'.$client_account_id.'');
        $page_url  =  $start_page.'/';     
        $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,5);
        $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
        // end pagination
        $data['client_account'] = $this->clients_model->get_client_account($client_account_id);
        $data['client'] = $this->clients_model->get_client_by_id($data['client_account']->client_id);
        $data['test_eligible_runs'] = $this->test_model->test_eligible_by_client($id,$client_account_id,$perPage, $offSet);
        $data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/test_eligible_by_client', $data);
        
    }

    /*Selected Filter By Client Details(PDF Generate)*/
    public function test_pdf_selected_by_client($id,$client_account_id)
    {
        $data['title'] = 'PDF - Selected Employee Filter By Client Details';
        $this->generatepdf->selected_pdf($id,$client_account_id);
    }


    /*Eligible Filter By Client Details(PDF Generate)*/

    public function test_pdf_eligible_by_client($id,$client_account_id)
    {
        $data['title'] = 'PDF - Eligible Employee Filter By Client Details';     
        $this->generatepdf->eligible_pdf($id,$client_account_id);  
    }


    /*Not Selected Filter By Client Details(PDF Generate)*/
    public function test_pdf_not_selected_by_client($id,$client_account_id)
    {
        $data['title'] = 'PDF - Eligible Employee Filter By Client Details';            
        $this->generatepdf->no_selected_emp_pdf($id,$client_account_id); 
    }

    /*No Active Employee Filter By Client Details(PDF Generate)*/
    public function test_pdf_no_active_emp($id,$client_account_id)
    {
        $data['title'] = 'PDF - Eligible Employee Filter By Client Details';                
        $this->generatepdf->no_active_emp_pdf($id,$client_account_id);  
    }

    public function export_client_random_pull($test_run_id,$client_account_id){        

         $data['title']= 'Statement';
          //print_r($values);
          $this->load->library('excel');
          $this->excel->setActiveSheetIndex(0);
          $this->excel->getActiveSheet()->setTitle('account_statement');                    
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Name');
                    $this->excel->getActiveSheet()->SetCellValue('B1', 'Category');
                    $this->excel->getActiveSheet()->SetCellValue('C1', 'ID');                       
                    $this->excel->getActiveSheet()->SetCellValue('D1', 'Substance');
                    $this->excel->getActiveSheet()->SetCellValue('E1', 'Alcohol');
                    $this->excel->getActiveSheet()->SetCellValue('F1', 'Location');  
                   
                    $erow = 2;
                    $s = 1;  
                    $id=$this->session->clientID; 
                    
                    $test_runs_details = $this->test_model->test_selected_by_client_pdf($test_run_id,$client_account_id);
         
                    $client = $this->clients_model->get_client_by_id($id); 
      
                foreach($test_runs_details as $test_runs_detail) { 
                    $emp_count = $this->test_model->get_employee_by_run_id_num($test_runs_by_id->id,$test_runs_detail->employee_id);
                    $employee_cat  =  $this->employees_model->get_employees_categories_by_id($test_runs_detail->emp_cat);
                    $employee  =  $this->employees_model->get_employee_by_id($test_runs_detail->employee_id);

 
                        $this->excel->getActiveSheet()->SetCellValue('A' . $erow, $test_runs_detail->emp_last_name.', '.$test_runs_detail->emp_first_name);
                       
                        $this->excel->getActiveSheet()->SetCellValue('B' . $erow, $employee_cat->title);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $erow, '...'.substr($test_runs_detail->emp_ID, -4));
                        if($test_runs_detail->test_run_type == 1){
                        $this->excel->getActiveSheet()->SetCellValue('D' . $erow, 'Urine Test');
                        }
                        if($test_runs_detail->test_run_type == 2){                      
                        $this->excel->getActiveSheet()->SetCellValue('E' . $erow, 'Breath Test');
                        }
                        $this->excel->getActiveSheet()->SetCellValue('F' . $erow, $employee->location);
                       
                        $s++;$erow++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30); 
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'TagAMS-Random-Letter' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                        redirect('frontend/client_test_run');
    }

 

     public function download_past_two_years(){
          $client_id=$this->session->clientID;
        $client_userID=$this->session->client_userID; 
         if($this->input->post()){ 
        $client_accounts=array(); 
        $client_accounts = $this->clients_model->get_client_account_by_client_id_and_custom_date_for_export($this->input->post('from_date'),$this->input->post('to_date'),$client_id);
        }else{  
        $client_accounts=array(); 
        $client_accounts = $this->clients_model->get_client_account_by_client_id_for_export($client_id); 
        }
       
        $randompath='downloaded_records/'.$client_userID.'-random_pull_records';
        $directoryName = $randompath;
        /* Check if the directory already exists. */
        if(!is_dir($directoryName)){
            /* Directory does not exist, so lets create it. */
        mkdir($directoryName, 0755, true);
        }else{  
        $files = glob('./downloaded_records/'.$client_userID.'-random_pull_records/*.*');
        foreach($files as $file){
        if(is_file($file))
        unlink($file);
        }
        $path   = './downloaded_records/'.$client_userID.'-random_pull_records'; 
        rmdir($path);
        mkdir($directoryName, 0755, true);
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
        $ip = $_SERVER['REMOTE_ADDR'];
        }      
        $values = array(
        'client_id' => $client_id, 
        'client_contact_id' => $client_userID,
        'ip' => $ip
        );
        $insert_log = $this->frontend_model->downloading_report_log($values); 
        $clientname='';$acc_id='';
        foreach($client_accounts as $account){
        $str=$account->client_name;
        $clientname = preg_replace('/[^a-zA-Z0-9_ -]/s',' ',$str);
        //$clientname= $this->clean($account->client_name); 
        $this->multiple_exports($account->client_id,$account->quarter,$account->year,$account->id,$clientname,$account->pull_group_id,$account->test_run_id,$randompath); 
        $this->multiple_pdf_exports($account->client_id,$account->quarter,$account->year,$account->id,$clientname,$account->pull_group_id,$account->test_run_id,$randompath); 
        $this->random_selections_download($account->client_id,$account->quarter,$account->year,$account->id,$clientname,$account->pull_group_id,$account->test_run_id,$randompath);
        $name=   $account->client_name; 
        $acc_id= $account->id;
        }
        //$this->download_folder();
        $randompath='downloaded_records/'.$client_userID.'-random_pull_records';
        $this->load->helper('url'); 
        $this->load->library('zip'); 
        if(!$this->input->post()){ 
        $filename = $name.'-'.$acc_id.'-Past-2-Years-Random-Pulls.zip';
        }else{
        $filename = $name.'-'.$acc_id.'-Past-Random-Pulls.zip';            
        }
        $path = $randompath; 
        $this->zip->read_dir($path); 
        $this->zip->download($filename); 
    } 


        // Delete Directory
        public function delete_directory($folderName)
        { 
        //print_r($folderName);exit();
        $this->load->helper('file'); // Load codeigniter file helper
        $dir_path  = './downloaded_records/'.$folderName; // For check folder exists
        $del_path  = './downloaded_records/'.$folderName; // For Delete folder
        if(is_dir($dir_path))
        {
        delete_files($del_path, true); // Delete files into the folder
        rmdir($del_path); // Delete the folder
        return true;
        }
        return false;
        }


    public function multiple_pdf_exports($client_id,$quarter,$year,$account_id,$client_name,$pull_group_id,$test_run_id,$randompath){ 
                
                $data['test_runs_details'] = $this->clients_model->get_eligible_for_two_years($client_id,$quarter,$year,$account_id,$client_name,$pull_group_id,$test_run_id);       
                $data['test_runs_by_id']   = $this->test_model->test_runs_by_id($test_run_id);  
                $data['pull_group']        = $this->general_model->get_pull_group($data['test_runs_by_id']->pull_group_id);
                $data['client_account']    = $this->clients_model->get_client_account($account_id);
                $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);
                $data['client_address']    = $this->clients_model->get_client_address_by_client_id_pdf($data['client']->id);
                $data['client_contact']    = $this->clients_model->get_client_by_der_pdf($data['client']->id);
                $data['employee']          = $this->employees_model->get_employee_by_client_id($data['client']->id);
                $data['city']              = $this->general_model->get_city($data['client_address']->city);
                $data['state']             = $this->general_model->get_state($data['client_address']->state);


                if(count($data['test_runs_details'])>0){ 
                $html = $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/pdf/pdf_two_years', $data,true);
                $this->generatepdf->createPDF_to_server($html, $filename='Eligible-List-' .$client_name.'-'. $account_id.'-'.$quarter.'-'.$year,$randompath); 
                }else{  
                $html = $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/pdf/test_pdf_no_active_emp', $data, true);
                $this->generatepdf->createPDF_to_server($html, $filename='Eligible-List-' .$client_name.'-'. $account_id.'-'.$quarter.'-'.$year,$randompath);
                }
           }


    public function multiple_exports($client_id,$quarter,$year,$account_id,$client_name,$pull_group_id,$test_run_id,$randompath){
        $test_runs_details = $this->clients_model->get_eligible_for_two_years($client_id,$quarter,$year,$account_id,$client_name,$pull_group_id,$test_run_id);
    //   echo '<pre>'; print_r($test_runs_details);
        if(count($test_runs_details)>0){
        $this->load->library('excel');
        $this->excel->setActiveSheetIndex(0);
        $this->excel->getActiveSheet()->setTitle('Random Selections');  
        $this->excel->getActiveSheet()->SetCellValue('A1', 'Name');
        $this->excel->getActiveSheet()->SetCellValue('B1', 'Category');
        $this->excel->getActiveSheet()->SetCellValue('C1', 'ID');
        $this->excel->getActiveSheet()->SetCellValue('D1', 'Location'); 
        $erow = 2;
        $s = 1;
                   
        $client_accounts_id='';
        foreach($test_runs_details as $test_runs_detail){ 
        $employee_cat   =  $this->employees_model->get_employees_categories_by_id($test_runs_detail->emp_cat); 
        if($test_runs_detail->emp_middle_initial){
        $this->excel->getActiveSheet()->SetCellValue('A' . $erow, $test_runs_detail->emp_last_name.', '.$test_runs_detail->emp_first_name.' '.$test_runs_detail->emp_middle_initial.'.');
        }else{
        $this->excel->getActiveSheet()->SetCellValue('A' . $erow, $test_runs_detail->emp_last_name.', '.$test_runs_detail->emp_first_name);
        }
        $this->excel->getActiveSheet()->SetCellValue('B' . $erow, $employee_cat->title);
        $this->excel->getActiveSheet()->SetCellValue('C' . $erow,  '...'.substr($test_runs_detail->emp_ID, -5)); 
        $this->excel->getActiveSheet()->SetCellValue('D' . $erow, $test_runs_detail->emp_location);  
        $s++;$erow++;
        $client_accounts_id=$test_runs_detail->client_account_id;
        } 
        $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
        $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);  
        $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); 
        $filename = 'Eligible-List -' .$client_name.'-'. $account_id.'-'.$quarter.'-'.$year.'.xlsx';
        $path = './'.$randompath.'/'.$filename;
        $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $objWriter->save($path); 
        }

    }

    public function random_selections_download($client_id,$quarter,$year,$account_id,$client_name,$pull_group_id,$test_run_id,$randompath){
 
       $data['test_runs_details'] = $this->test_model->test_selected_by_client_pdf($test_run_id,$account_id); 
       if(count($data['test_runs_details'])>0){
       $data['test_runs_by_id']   = $this->test_model->test_runs_by_id($test_run_id);  
       $data['pull_group']        = $this->general_model->get_pull_group($data['test_runs_by_id']->pull_group_id);
       $data['client_account']    = $this->clients_model->get_client_account($account_id);
       $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);
       $data['client_address']    = $this->clients_model->get_client_address_by_client_id_pdf($data['client']->id);
       $data['client_contact']    = $this->clients_model->get_client_by_der_pdf($data['client']->id);
       $data['employee']          = $this->employees_model->get_employee_by_client_id($data['client']->id);
       $data['city']              = $this->general_model->get_city($data['client_address']->city);
       $data['state']             = $this->general_model->get_state($data['client_address']->state);   
       $html = $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/pdf/test_pdf_selected_by_client', $data, true);  
       $this->generatepdf->createPDF_to_server($html, $filename='Selected Letter-' .$client_name.'-'. $account_id.'-'.$quarter.'-'.$year,$randompath); 

       $this->load->library('excel');
       $this->excel->setActiveSheetIndex(0);
       $this->excel->getActiveSheet()->setTitle('Selected Letter');
       $this->excel->getActiveSheet()->SetCellValue('A1', 'Name');
       $this->excel->getActiveSheet()->SetCellValue('B1', 'Category');
       $this->excel->getActiveSheet()->SetCellValue('C1', 'ID');
       $this->excel->getActiveSheet()->SetCellValue('D1', 'Substance'); 
       $this->excel->getActiveSheet()->SetCellValue('E1', 'Alcohol');
       $this->excel->getActiveSheet()->SetCellValue('F1', 'Location');
                   $erow = 2;
                    $s = 1;
                   
       $client_accounts_id='';
       $test_runs_details = $this->test_model->test_selected_by_client_pdf($test_run_id,$account_id);
       foreach($test_runs_details as $test_runs_detail){ 
       $emp_count= $this->test_model->get_employee_by_run_id_num($test_runs_detail->test_run_id,$test_runs_detail->employee_id);
       $employee_cat  =  $this->employees_model->get_employees_categories_by_id($test_runs_detail->emp_cat);
       $employee       =  $this->employees_model->get_employee_by_id($test_runs_detail->employee_id); 
       $this->excel->getActiveSheet()->SetCellValue('A' . $erow, $test_runs_detail->emp_last_name.', '.$test_runs_detail->emp_first_name.' '.$test_runs_detail->emp_middle_initial.'.');
       $this->excel->getActiveSheet()->SetCellValue('B' . $erow, $employee_cat->title);
       $this->excel->getActiveSheet()->SetCellValue('C' . $erow,  '...'.substr($test_runs_detail->emp_ID, -5));
       if($test_runs_detail->test_run_type == 1){
       $this->excel->getActiveSheet()->SetCellValue('D' . $erow, 'Urine Test');  
       }
       if($test_runs_detail->test_run_type == 2){
       $this->excel->getActiveSheet()->SetCellValue('E' . $erow, 'Breath Test');
       }
       $this->excel->getActiveSheet()->SetCellValue('F' . $erow, $employee->location);                       
                        $s++;$erow++;
       $client_accounts_id=$test_runs_detail->client_account_id;
       } 
    
                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(20); 
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30); 
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30); 
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); 
        $filename = 'Selected Letter -' .$client_name.'-'. $client_accounts_id.'-'.$quarter.'-'.$year;
                    $this->load->helper('excel');
                    create_multiple_excel($this->excel, $filename,$randompath);
        }else{
        $data['test_runs_by_id']   = $this->test_model->test_runs_by_id($test_run_id);  
        $data['client_account']    = $this->clients_model->get_client_account($account_id);
        $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);
        $data['client_address']    = $this->clients_model->get_client_address_by_client_id_pdf($data['client']->id);
        $data['client_contact']    = $this->clients_model->get_client_by_der_pdf($data['client']->id);
        $data['employee']          = $this->employees_model->get_employee_by_client_id($data['client']->id);
        $data['city']              = $this->general_model->get_city($data['client_address']->city);
        $data['state']             = $this->general_model->get_state($data['client_address']->state); 
        $html = $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/pdf/test_pdf_not_selected_by_client', $data, true);
        $this->generatepdf->createPDF_to_server($html, $filename='No Employees Selected Letter-' .$client_name.'-'. $account_id.'-'.$quarter.'-'.$year,$randompath);  
        }



    }
 
} // Class end.
