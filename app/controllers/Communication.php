<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Communication extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('general_model');
		$this->load->model('dashboard_model');
		$this->load->model('employees_model');
		$this->load->model('email_model');
		$this->load->model('communication_model');
		$this->load->library('common/user');
		if (!$this->session->userID):
		redirect('auth');
		endif;
		
    }

	public function index(){
		$data['title'] = 'Clients Dashboard';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/dashboard', $data);
      
	}
	
	public function email_template(){
    	$data['title'] = 'Home';
		$data['id'] = $id;
		/* if ($this->input->post(null, true)) {
		   $this->dashboard_model->edit_profile($this->input->post(),$id);
		   $this->session->set_flashdata('msg','<div class="alert alert-success">Updated successfully</div>');	
       	   redirect('dashboard/edit_profile'); 
		 }*/
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/communication/email_template', $data);
    }
    
    /**
     * App home.
     */
    public function bulk_email()
    {
    	$data['title'] = 'Bulk Email';
    	$data['client_contacts'] =  $this->clients_model->get_client_contacts();
    	if($this->input->post()){
    	$this->session->set_flashdata('msg','<div class="alert alert-success">Email added successfully</div>');	
       	   redirect('communication/bulk_email'); 
    	}
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/communication/bulk_email', $data);
        
    }
    
    
    
    public function sms_template(){
		$data['title'] = 'Clients';
    	
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/communication/sms_template', $data);
        
	}


    /**
     * App home.
     */
    public function bulk_sms()
    {
    	$data['title'] = 'Bulk SMS';
    	$data['client_contacts'] =  $this->clients_model->get_client_contacts();
    	if($this->input->post()){
    	redirect('communication/bulk_sms');
    	}
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/communication/bulk_sms', $data);
        
    } 
  
} // Class end.
