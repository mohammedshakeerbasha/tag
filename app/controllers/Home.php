<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Home extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('dashboard_model');
		$this->load->model('email_model');
		$this->load->model('general_model');
		$this->load->model('clients_model');
		$this->load->model('invoices_model');
		$this->load->library('common/user');
		$this->load->library('general');
		$this->load->library('generatepdf');
		$this->load->model('test_model');
		$this->load->model('employees_model');
		$this->load->library('form_validation');
    }
    /**
     * App home.
     */
    public function index()
    {
    	if($this->session->userID){
    		redirect('admin');
    	} elseif($this->session->cuserID){
    	 redirect('dashboard');
    	} else {
    	$data['title'] = 'Home';

        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/signin', $data);
        }
    }
    
  /*  public function signin(){
    	 $this->config->load('modules/auth/config');
            $this->load->library('form_validation');
            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');
            $this->form_validation->set_rules('email', 'Email', 'trim|htmlspecialchars|required');
            $this->form_validation->set_rules('password', 'Password', 'trim|htmlspecialchars|required');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            if ($this->form_validation->run() == false) {
                $data['title'] = 'Sign in';
                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/signin', $data);
            } else {
                $user = $this->dashboard_model->signin($this->input->post(null, true));
                
                if (!$user) {
                    $this->session->set_flashdata('signinSuccess', false);
                    $this->session->set_flashdata('authIdentifierValue', $this->input->post('email', true));
				    redirect('signin');
                } else {
                	//echo '<pre>';print_r($user);exit();
				   // Make users signed in.

                    $this->user->client_signin($user->id,$user->client_id);
				 $this->dashboard_model->reset_password_log($user->id,'4');

                    // Redirect users to necessary places.
                  	redirect('dashboard');
                }
            }
    } */
    
    public function signin(){
    	 $this->config->load('modules/auth/config');
            $this->load->library('form_validation');
            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');
            $this->form_validation->set_rules('email', 'Email', 'trim|htmlspecialchars|required');
            $this->form_validation->set_rules('password', 'Password', 'trim|htmlspecialchars|required');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            if ($this->form_validation->run() == false) {
                $data['title'] = 'Sign in';
                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/signin', $data);
            } else {
                $user_count = $this->dashboard_model->num_signin($this->input->post(null, true));
              
                if ($user_count==0) {
                    $this->session->set_flashdata('signinSuccess', false);
                    $this->session->set_flashdata('authIdentifierValue', $this->input->post('email', true));
				    redirect('signin');
                } 
                elseif ($user_count==1) { 
                	$user = $this->dashboard_model->signin($this->input->post(null, true));
                    $this->user->client_signin($user->id,$user->client_id);
				 $this->dashboard_model->reset_password_log($user->id,'4');

                    // Redirect users to necessary places.
                  	redirect('dashboard');
                } 
                elseif ($user_count>1){ 
                	$data['title'] = 'Client Sign in';
                	$data['user'] = $this->dashboard_model->signin($this->input->post(null, true));
                	 
                	if($data['user']){
                	$data['clients'] = $this->clients_model->get_clients_contacts_by_email($data['user']->email);
                   $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/client_signin', $data);
                 	} else {
                 		$this->session->set_flashdata('signinSuccess', false);
                    $this->session->set_flashdata('authIdentifierValue', $this->input->post('email', true));
				    redirect('signin');
                 	}
                } 
                else {
                	$this->session->set_flashdata('signinSuccess', false);
                    $this->session->set_flashdata('authIdentifierValue', $this->input->post('email', true));
				    redirect('signin');
                }
            }
    }
    
    
    
     public function new_signin(){
    	 $this->config->load('modules/auth/config');
            $this->load->library('form_validation');
            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');
            $this->form_validation->set_rules('email', 'Email', 'trim|htmlspecialchars|required');
            $this->form_validation->set_rules('password', 'Password', 'trim|htmlspecialchars|required');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            if ($this->form_validation->run() == false) {
                $data['title'] = 'Sign in';
                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/new_signin', $data);
            } else {
                $user_count = $this->dashboard_model->num_signin($this->input->post(null, true));
               
                if ($user_count==0) {
                    $this->session->set_flashdata('signinSuccess', false);
                    $this->session->set_flashdata('authIdentifierValue', $this->input->post('email', true));
				    redirect('signin');
                } 
                elseif ($user_count==1) {
                	$user = $this->dashboard_model->signin($this->input->post(null, true));
                    $this->user->client_signin($user->id,$user->client_id);
				 $this->dashboard_model->reset_password_log($user->id,'4');

                    // Redirect users to necessary places.
                  	redirect('dashboard');
                } 
                elseif ($user_count>1){
                	$data['title'] = 'Client Sign in';
                	$data['user'] = $this->dashboard_model->signin($this->input->post(null, true));
                	
                	if($data['user']){
                	$data['clients'] = $this->clients_model->get_clients_contacts_by_email($data['user']->email);
                   $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/client_signin', $data);
                 	} else {
                 		$this->session->set_flashdata('signinSuccess', false);
                    $this->session->set_flashdata('authIdentifierValue', $this->input->post('email', true));
				    redirect('new_signin');
                 	}
                } 
                else {
                	$this->session->set_flashdata('signinSuccess', false);
                    $this->session->set_flashdata('authIdentifierValue', $this->input->post('email', true));
				    redirect('new_signin');
                }
            }
    }
    
    public function login_user($user_id,$client_id){
    
    $this->user->client_signin($user_id,$client_id);
				 $this->dashboard_model->reset_password_log($user_id,'4');
redirect('dashboard');
    
    }
    
    public function signout(){
     $this->user->client_signout();
     $this->session->set_flashdata('signoutSuccess', true);
     redirect('');
    }
    
    // reset
	public function reset(){

		if($this->session->client_userID){
			redirect('dashboard');
        } else { 
        	if ($this->input->post(null, true)) {
				$ck = $this->dashboard_model->check_user($this->input->post('email'));
				if($ck){
				 $hash = $this->dashboard_model->randomHash(25);
                 $this->dashboard_model->reset_email($this->input->post('email'),$hash);
				 $this->dashboard_model->reset_password_log($ck->id,'1');
				// sending email
    	 			$data = array(
                        'subject' => 'Your ' . $this->preferences->type('system')->item('app_name') . ' password reset link',
                        'password' => $hash,
                       // 'to' => 'shakeer@sowedane.com',
                        'to' => $this->input->post('email'),
                        'name' => $ck->first_name,
                        'template_path' => $this->preferences->type('system')->item('full_app_themesDir')."/email/reset_password"
                    );
                    $res = $this->email_model->send_grid_email($data);
                  /*
                   $message = 'You Requested for reset password, check your email';
                   $phone = '+17196856525';
                   $val = $this->general->send_sms($phone,$message);
				*/
                    // end here
				
				// Send password reset email.
               /* $this->load->library('email');

                $this->email->from($this->preferences->type('system')->item('app_email'), $this->preferences->type('system')->item('app_name'));
                $this->email->to($this->input->post('email'));
				$data['accountInfo'] = $ck;
				$data['randomIntegerNumber'] = $hash;
                $this->email->subject('Your ' . $this->preferences->type('system')->item('app_name') . ' password reset link');
                $this->email->message($this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/email/reset_password', $data, true));

                $this->email->send();
				
				*/
				
				
				 $this->session->set_flashdata('resetSuccess', true);
				 redirect('reset');
				} else {
				 $this->session->set_flashdata('resetSuccess', false);
                 redirect('reset');
				}
            } else {
            if($this->input->get()){ 
			$datas = array();
			$datas['title'] = 'Reset';
			$datas = $this->input->get();
			$email = $datas['email']; 
			$getuser = $this->dashboard_model->check_user($email);
			if($getuser){ 
			$this->dashboard_model->reset_password_log($getuser->id,'5');
		    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reset', $datas);

			 }
			 }else{
				$data['title'] = 'Reset';
                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reset', $data);
            }
            }
        }
	}
	
	public function reset_code(){
     $data['title'] = 'Reset Code';
     $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reset_code', $data);
    }
	
	public function set_password($str){

		 $this->config->load('modules/auth/config');
		// $this->form_validation->set_rules('password', 	'Password', 		'required|matches[repassword]|min_length[5]');

            $this->form_validation->set_rules('password', 'New password', 'trim|htmlspecialchars|min_length[' . $this->preferences->type('system')->item('users_minimumPasswordLength') . ']');
		$this->form_validation->set_rules('repassword',	'Confirm Password', 'required');
		$this->form_validation->set_message('min_length', '{field} must be at least {param} characters in length.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');
            $this->form_validation->set_message('alpha_numeric', '{field} can only contain alpha-numeric characters (A-Z a-z 0-9).');
            

		if ($this->form_validation->run() == FALSE)
		{
		$value = array();
		$value['title'] = 'Set Password';
		$value['confirm_key'] = $str;
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/set_password', $value);
		}
		else
		{
			$num_user = $this->dashboard_model->check_password_key($str);
			if($num_user==1){
			$user_id = $this->dashboard_model->set_password($this->input->post(),$str);
			} else {
			$user_id = $this->dashboard_model->set_password_many($this->input->post(),$str);
			}
			if($user_id){
			$value = array();
			$value['title'] = 'Register Sucess';
			$value['sucess'] = 1;
			$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/sucess', $value);
			} else {
			$value = array();
			$value['title'] = 'Register Failed';
			$value['sucess'] = 0;
			$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/sucess', $value);

			}
		}

	}
	
	
	public function payment_test()
	{
		// Authorize.net lib
		$this->load->library('authorize_net');

		$auth_net = array(
			'x_card_num'			=> '4111111111111111', // Visa
			'x_exp_date'			=> '12/24',
			'x_card_code'			=> '123',
			'x_description'			=> 'A test transaction',
			'x_amount'				=> '20',
			'x_first_name'			=> 'John',
			'x_last_name'			=> 'Doe',
			'x_address'				=> '123 Green St.',
			'x_city'				=> 'Lexington',
			'x_state'				=> 'KY',
			'x_zip'					=> '40502',
			'x_country'				=> 'US',
			'x_phone'				=> '555-123-4567',
			'x_email'				=> 'test@example.com',
			'x_customer_ip'			=> $this->input->ip_address(),
			);
		$this->authorize_net->setData($auth_net);

		// Try to AUTH_CAPTURE
		if( $this->authorize_net->authorizeAndCapture() )
		{
			echo '<h2>Success!</h2>';
			echo '<p>Transaction ID: ' . $this->authorize_net->getTransactionId() . '</p>';
			echo '<p>Approval Code: ' . $this->authorize_net->getApprovalCode() . '</p>';
		}
		else
		{
			echo '<h2>Fail!</h2>';
			// Get error
			echo '<p>' . $this->authorize_net->getError() . '</p>';
			// Show debug data
			$this->authorize_net->debug();
		}
	}
	
	
	
	public function send_sms(){
	 $message = 'Test message';
     $phone = '+17196856525';
     $val = $this->general->send_sms($phone,$message);
     
	}
	
	public function random_pull($id,$client_id)
    {
    	$data['title'] = 'PDF - Selected Employee Filter By Client Details';	    	
		$this->generatepdf->selected_pdf($id,$client_id);			
				
    }
	

    public function selected_print_letter($id)
    {
    	$data['title'] = 'Drug Test Details';
    	$data['batch'] = $this->test_model->get_batch_by_hash($id); 
		$data['test_selected_runs'] = $this->test_model->test_selected_details_print($data['batch']->test_run_id);
		$data['test_run'] = $this->test_model->test_runs_by_id($data['batch']->test_run_id);		
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/email_letter/selected_print', $data);    	
    }


	public function not_selected_print_letter($id)
    {
    	$data['title'] = 'Drug Test Details';	
    	$data['batch'] = $this->test_model->get_batch_by_hash($id); 	
		$data['test_run'] = $this->test_model->test_runs_by_id($data['batch']->test_run_id);
		$data['id'] = $data['batch']->test_run_id;
		$data['client_accounts'] = $this->clients_model->get_active_clients_by_pull_group_id($data['test_run']->pull_group_id);		
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/email_letter/not_selected_emp_print', $data);    	
    }


	public function no_active_emp_print_letter($id){
		
		$data['title'] = 'Drug Test Details';
		$data['batch'] = $this->test_model->get_batch_by_hash($id); 	
		$data['test_run'] = $this->test_model->test_runs_by_id($data['batch']->test_run_id);
		$data['id'] = $data['batch']->test_run_id;
		$data['client_accounts'] = $this->clients_model->get_active_clients_by_pull_group_id($data['test_run']->pull_group_id);		
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/email_letter/no_active_emp_print', $data);
    	
	}

	public function pdf_invoice($invoice_id,$client_contact_id)
    {
		$data['title'] = 'Individual PDF Invoice';
	    $data['invoice_id'] = $invoice_id;	    
	    $data['invoice'] 		   = $this->invoices_model->get_invoice_by_id($invoice_id);
	    $data['client_account']    = $this->clients_model->get_client_account($data['invoice']->client_account_id);
        $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);
        //$data['client_address']    = $this->invoices_model->get_client_address_by_client_id_billing_pdf($data['client']->id);
		$data['client_contact']    = $this->invoices_model->get_contact_by_invoice_contact_id($data['client']->id,$data['invoice']->client_account_id,$client_contact_id);
		
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


	public function print_invoice($invoice_id,$client_contact_id)
    {
		$data['title'] = 'Individual PDF Invoice';
	    $data['invoice_id'] = $invoice_id;	   
	    $data['invoice'] 		   = $this->invoices_model->get_invoice_by_id($invoice_id);
	    $data['client_account']    = $this->clients_model->get_client_account($data['invoice']->client_account_id);
        $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id);
        //$data['client_address']    = $this->invoices_model->get_client_address_by_client_id_billing_pdf($data['client']->id);
		$data['client_contact']    = $this->invoices_model->get_contact_by_invoice_contact_id($data['client']->id,$data['invoice']->client_account_id,$client_contact_id);
		
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
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/individual_invoice_details_print', $data);
    }






} // Class end.
