<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tests extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('employees_model');
		$this->load->model('dashboard_model');
		$this->load->model('frontend_model');
		$this->load->model('general_model');
		$this->load->model('reports_model');
		$this->load->model('email_model');
		$this->load->model('invoices_model');
		$this->load->model('test_model');
		$this->load->library('common/user');
		$this->load->library('tagams');
		$this->load->library('generatepdf');	
		$this->load->library('tag_general');
		$this->load->library('general');	
		$this->load->library('common/paginator'); 
        $this->load->library("pagination");
	// 	if (!$this->session->userID):
	// 	redirect('auth');
	// 	endif;
		
    }
	
	public function index(){
		
		$data['title'] = 'Clients';
    	$data['employees'] = $this->employees_model->get_employees();
    	$data['client_accounts'] = $this->clients_model->get_client_accounts();
    	$data['employees_statuss'] = $this->employees_model->get_employees_statuss();
    	$data['employees_categories'] = $this->employees_model->get_employees_categories();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/home', $data);
        
	}
	
	public function drug_tests(){		
		$data['title'] = 'Drug Tests';
    	$data['pull_groups'] = $this->general_model->pull_groups();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/home', $data);
        
	}


    /**
     * App home.
     */
    public function all_test_run($page=1)
    {
    	$data['title'] = 'Drug Test Run List';
    	$etype_num_rows = $this->test_model->test_runs_with_num_rows();	

		// Generate pagination.
		$perPage = 10;
		// Handle pagination.
			if ($page==1) {
			  $offSet = 0;
				} else {
			  // $offSet = ($offSet - 1) * $perPage.
			  $offSet = ($page - 1) * $perPage;
			}
		$start_page =  site_url('tests/all_test_run/');
		$page_url  =  $start_page.'/';     
		$data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
		$data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
		// end pagination	
		$data['test_runs'] = $this->test_model->test_runs_with_limit($perPage, $offSet);

    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_runs', $data);
    }
    

    /**
    * Test Details.
    */
    public function test_details($id,$page=1)
    {
    	$data['title'] = 'Drug Test Details';
		$etype_num_rows = $this->test_model->test_details_num_rows($id);
		// Generate pagination.
		$perPage = 10;
		// Handle pagination.
			if ($page==1) {
			  $offSet = 0;
				} else {
			  // $offSet = ($offSet - 1) * $perPage.
			  $offSet = ($page - 1) * $perPage;
			}
		$start_page =  site_url('tests/test_details/'.$id.'');
		$page_url  =  $start_page.'/';     
		$data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
		$data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
		// end pagination
		$data['test_runs_details'] = $this->test_model->test_details($id,$perPage, $offSet);
    	$data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_details', $data);
        
    }
    

    /**
    * Test Eligible Details.
    */
    public function test_eligible_details($id,$page=1)
    {
    	$data['title'] = 'Drug Test Details';
		$etype_num_rows = $this->test_model->test_eligible_details_num_rows($id);
		// Generate pagination.
		$perPage = 10;
		// Handle pagination.
			if ($page==1) {
			  $offSet = 0;
				} else {
			  // $offSet = ($offSet - 1) * $perPage.
			  $offSet = ($page - 1) * $perPage;
			}
		$start_page =  site_url('tests/test_eligible_details/'.$id.'');
		$page_url  =  $start_page.'/';     
		$data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,4);
		$data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
		// end pagination
		$data['test_eligible_runs'] = $this->test_model->test_eligible_details($id,$perPage, $offSet);
    	$data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_eligible_details', $data);
        
    }

    /**
    * Test Client View Details.
    */
    public function test_client_view_details($id,$pull_group_id,$page=1)
    {
    	$data['title'] = 'Drug Test Client Details';		
		
		$etype_num_rows = $this->test_model->test_client_view_details_num_rows($pull_group_id);
		// Generate pagination.
		$perPage = 10;
		// Handle pagination.
			if ($page==1) {
			  $offSet = 0;
				} else {
			  // $offSet = ($offSet - 1) * $perPage.
			  $offSet = ($page - 1) * $perPage;
			}
		$start_page =  site_url('tests/test_client_view_details/'.$id.'/'.$pull_group_id.'');
		$page_url  =  $start_page.'/';     
		$data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,5);
		$data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
		// end pagination
		
		$data['test_client_view_details'] = $this->test_model->test_client_view_details($pull_group_id,$perPage, $offSet);
		
		
    	$data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
    	$data['batch_count'] = $this->test_model->test_batch_run_num($id);
    	$data['button_details'] = $this->test_model->get_track_button_details($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_client_view_details', $data);
        
    }


     /**
    * Test Client View Details.
    */
    public function test_client_view_details_front($clientid,$client_account_id,$test_run_id,$pull_group_id)
    {
    	//print_r($clientid.'-'.$id.'-'.$pull_group_id);exit();
        
       $assign=$this->clients_model->get_client_contact_by_client_id_for_contact_assign($clientid); 

       $data['title'] = 'Drug Test Client Details';        
       $data['test_client_view_details'] = $this->frontend_model->test_client_view_details_admin($pull_group_id,$clientid,$client_account_id);
       $data['client_contact_assign']= $this->clients_model->get_client_contact_assign_by_contact_id($assign->id); 
       //echo '<pre>';print_r($data);exit();
	     $data['test_runs_by_id'] = $this->frontend_model->test_runs_by_id($test_run_id);
	     $data['batch_count'] = $this->frontend_model->test_batch_run_num($test_run_id);
	     $data['back_link'] = base_url('clients/account_view/'.$this->input->get('client_id').'/'.$this->input->get('account_id')); 
        //echo '<pre>';print_r($data);exit();
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_individual_client_details', $data);
        
    }



    /**
    * Selected Filter By Client Details.
    */
    public function test_selected_by_client($id,$client_account_id,$page=1)
    {
    	$data['title'] = 'Drug Test Details';
		$etype_num_rows = $this->test_model->test_selected_by_client_num_rows($id,$client_account_id);
		// Generate pagination. 
		$perPage = 15;
		// Handle pagination.
			if ($page==1) {
			  $offSet = 0;
				} else {
			  // $offSet = ($offSet - 1) * $perPage.
			  $offSet = ($page - 1) * $perPage;
			}
		$start_page =  site_url('tests/test_selected_by_client/'.$id.'/'.$client_account_id.'');
		$page_url  =  $start_page.'/';     
		$data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,5);
		$data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
		// end pagination
		$data['client_account'] = $this->clients_model->get_client_account($client_account_id);
		$data['client'] = $this->clients_model->get_client_by_id($data['client_account']->client_id);
		$data['test_runs_details'] = $this->test_model->test_selected_by_client($id,$client_account_id,$perPage, $offSet);
    	$data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_selected_by_client', $data);
        
    }


     public function update_selection($id){ 
    // 	print_r($this->input->post());exit();
    $this->test_model->update_selection_data($this->input->post(),$id);
    redirect('tests/test_selected_by_client/'.$this->input->post('test_run_id').'/'.$this->input->post('client_account_id')); 
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
		$start_page =  site_url('tests/test_eligible_by_client/'.$id.'/'.$client_account_id.'');
		$page_url  =  $start_page.'/';     
		$data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,5);
		$data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
		// end pagination
		$data['client_account'] = $this->clients_model->get_client_account($client_account_id);
		$data['client'] = $this->clients_model->get_client_by_id($data['client_account']->client_id);
		$data['test_eligible_runs'] = $this->test_model->test_eligible_by_client($id,$client_account_id,$perPage, $offSet);
    	$data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_eligible_by_client', $data);
        
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








    /**
    * Test Eligible Details Print.
    */
    public function test_eligible_details_print($id)
    {
    	$data['title'] = 'Drug Test Details';
		$data['test_eligible_runs'] = $this->test_model->test_eligible_details_print($id);
		$data['test_run'] = $this->test_model->test_runs_by_id($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_eligible_details_print', $data);
    	//$this->generatepdf->createPDF($html, $filename='TagAMS-Random-Eligible-Letter', false); 
    }


    /**
    * Test Selected Details Print.
    */
    public function test_selected_details_print($id)
    {
    	$data['title'] = 'Drug Test Details';
		$data['test_selected_runs'] = $this->test_model->test_selected_details_print($id);
		$data['test_run'] = $this->test_model->test_runs_by_id($id);		
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_selected_details_print', $data);
    	//$this->generatepdf->createPDF($html, $filename='TagAMS-Random-Eligible-Letter', false); 
    }


    /**
    * Test No Selected Employee Print.
    */
    public function test_no_selected_emp_print($id)
    {
    	$data['title'] = 'Drug Test Details';
		//$data['test_not_selected_runs'] = $this->test_model->test_no_selected_emp_print($id);
		$data['test_run'] = $this->test_model->test_runs_by_id($id);
		$data['id'] = $id;
		$data['client_accounts'] = $this->clients_model->get_active_clients_by_pull_group_id($data['test_run']->pull_group_id);
		$data['url'] = "/tests/test_client_view_details/".$id."/".$data['test_run']->pull_group_id;
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_not_selected_emp_print', $data);
    	//$this->generatepdf->createPDF($html, $filename='TagAMS-Random-Eligible-Letter', false); 
    }


	public function test_no_active_emp_print($id){
		
		$data['title'] = 'Drug Test Details';
		$data['test_run'] = $this->test_model->test_runs_by_id($id);
		$data['id'] = $id;
		$data['client_accounts'] = $this->clients_model->get_active_clients_by_pull_group_id($data['test_run']->pull_group_id);
		$data['url'] = "/tests/test_client_view_details/".$id."/".$data['test_run']->pull_group_id;
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_no_active_emp_print', $data);
    	
	}


	public function print_reminder_letter($pull_group_id){
		$data['title'] = 'Reminder Letter';
		$data['pull_group_id'] = $pull_group_id;
		$data['print_reminder_letters'] = $this->clients_model->get_active_clients_by_pull_group_id($pull_group_id);		
		$data['test_run'] = $this->test_model->get_test_run_by_pull_group_id($pull_group_id); 
		//echo '<pre>';print_r($data);exit(); 
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reminder_letters/reminder_letter_print', $data);
    }
    

    public function email_reminder_letter($pull_group_id){
		$data['title'] = 'Reminder Letter';
		$data['pull_group_id'] = $pull_group_id;
		$data['email_reminder_letters'] = $this->clients_model->get_active_client_account_by_pull_group_id($pull_group_id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reminder_letters/reminder_letter_email', $data);
    }

     public function send_digital_notifications($pull_group_id){			


			$this->test_model->send_reminder_sms($pull_group_id);   
			$this->test_model->send_email_reminder_letters($pull_group_id); 
			$this->test_model->track_buttons($pull_group_id); 	 
			//$data['test_run'] = $this->test_model->get_test_run_by_pull_group_id($pull_group_id); 			
	  		redirect('tests/drug_tests/');     
    }
    

    /**
     * App home.
     */
    public function test_uploads()
    {
    	$data['title'] = 'All Drug Test Uploads';
    	$data['test_uploads'] = $this->test_model->test_uploads();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_uploads', $data);
        
    }
    
    public function run_test($pull_group_id){
    if($this->input->post()){
    $this->test_model->run_test($this->input->post(),$pull_group_id);
    redirect('tests/drug_tests');
    }
    redirect('tests/drug_tests');
    }

    public function schedule_test($pull_group_id){
    if($this->input->post()){
    $this->general_model->add_pull_group_schedule($this->input->post(),$pull_group_id);
    redirect('tests/drug_tests');
    }
    redirect('tests/drug_tests');
    }

    
    public function drug_test_list($test_id,$group_id){
		
		$data['title'] = 'Drug Tests';
		$data['group_id'] = $group_id;
		$data['test_id'] = $test_id;
		$data['test_run'] = $this->test_model->get_test_run($test_id);
		$data['pull_group'] = $this->general_model->get_pull_group($group_id);
    	$data['test_employees'] = $this->test_model->get_test_employees($test_id);
    	$data['clients'] = $this->clients_model->get_clients_by_pull_group_id($group_id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/employee_list', $data);
        
	}
    
    public function alcohol_test_list($test_id,$group_id){
		
		$data['title'] = 'Alcohol Tests';
		$data['group_id'] = $group_id;
		$data['test_id'] = $test_id;
		$data['test_run'] = $this->test_model->get_test_run($test_id);
		$data['pull_group'] = $this->general_model->get_pull_group($group_id);
    	$data['test_employees'] = $this->test_model->get_test_employees($test_id);
    	$data['clients'] = $this->clients_model->get_clients_by_pull_group_id($group_id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/employee_list', $data);
        
	}
	
	/**
     * App home.
     */
    public function upload_test($id,$emp_id)
    {
    	$this->test_model->upload_test($this->input->post(),$id,$emp_id);
    	if($this->input->post('drug')!=0){
    	redirect('tests/drug_test_list/'.$this->input->post('test_id').'/'.$this->input->post('group_id'));
    	} else {
    	redirect('tests/alcohol_test_list/'.$this->input->post('test_id').'/'.$this->input->post('group_id'));
    	}
    	
    }
    
    
    // Matched List
    public function matched_test_list(){
		$data['title'] = 'Drug Tests';
    	$data['matched_tests'] = $this->reports_model->matched_test_list();
    	$data['pull_group_test_runs'] = $this->test_model->matched_test_runs();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/matched_test_list', $data);
	}
	
	 // Un matched List
    public function unmatched_test_list($page=1){
		$data['title'] = 'Drug Tests';
		
		$etype_num_rows = $this->reports_model->unmatched_test_list_num_rows();
	     // Generate pagination.
     $perPage = 10;
     // Handle pagination.
     if ($page==1) {
        $offSet = 0;
     } else {
        // $offSet = ($offSet - 1) * $perPage.
        $offSet = ($page - 1) * $perPage;
     }
     $start_page =  site_url('tests/unmatched_test_list');

     $page_url  =  $start_page.'/';

     
     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
     // end pagination
     $data['matched_tests'] = $this->reports_model->unmatched_test_list($perPage, $offSet);
     
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/unmatched_test_list', $data);
	}

	//add employee
	public function add_employee($id,$type){
	$emp_id = $this->employees_model->add_report_employee($id,$type);
	if($emp_id){
	$this->reports_model->add_employee_id_to_report($id,$type,$emp_id);
	$this->session->set_flashdata('add_employee', true);
	} else {
	$this->session->set_flashdata('client_exist', true);
	}
	redirect('tests/unmatched_test_list');
	}
	
	public function select_employee($id,$type,$emp_id){
	$this->reports_model->add_employee_id_to_report($id,$type,$emp_id);
	$this->session->set_flashdata('add_employee', true);
	redirect('tests/unmatched_test_list');
	}

	//add employee
	public function confirm_test($id,$emp_id,$type){
	$this->test_model->confirm_test($this->input->post(),$id,$emp_id,$type);
	$this->reports_model->confirm_test_to_report($id,$type);
	$this->session->set_flashdata('confirm_test', true);
	redirect('tests/matched_test_list');
	}  


    //Batch functions
    public function generate_batch($id,$pull_group_id)
    {
    	$data['title'] = 'Generate Batch';	    	
		$this->test_model->generate_batch($id,$pull_group_id);
		redirect('tests/test_client_view_details/'.$id.'/'.$pull_group_id);
				
    }
    
     public function view_batch($id)
    {
    	$data['title'] = 'View Batch Details';
		$data['test_run'] = $this->test_model->test_runs_by_id($id);
   
     $data['batchs'] = $this->test_model->test_batch_run_by_id($id);
    	
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_view_batch', $data);
        
    }
    
    public function send_batch_email_by_batch_id($id,$test_run_id){
    				$this->test_model->send_email_letters($id);    	 			
            	redirect('tests/view_batch/'.$test_run_id);     
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
                        redirect('tests/test_client_view_details/'.$test_run_id.'/'.$client_account_id);
    }

	public function view_specimen_results($page=1){
		
		$data['title'] = 'Specimen ID History Search';
		$etype_num_rows = $this->test_model->get_specimenid_results_num_rows($this->input->get('specimen_id'));

		// Generate pagination.
		$perPage = 15;
		// Handle pagination.
		if ($page == 1) {
		    $offSet = 0;
		} else {
		    $offSet = ($page - 1) * $perPage;
		}

		$start_pages = base_url() . "tests/view_specimen_results";
		$page_url = $start_pages . '/';
		$data['pagination'] = '';
		$data['paginationInfo'] = '';

		if (!empty($etype_num_rows)) {
		    $data['pagination'] = $this->paginator->newpagination($start_page, $page_url, $etype_num_rows, $perPage, 3);
		    $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows, $perPage);
		}

		$data['specimen_id'] = array();
		if ($this->input->get('specimen_id')) {
		    $results = $this->test_model->get_specimenid_results_with_limit($this->input->get('specimen_id'), $perPage, $offSet);
		    if ($results) {
		        $data['results'] = $results;
		    }
		}

		$data['test_type'] = $this->dashboard_model->get_list_of_test_type_not_null();
		$data['client_contact_assign'] = [];
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/view_specimen_results', $data);
	
	   }
   

	   public function specimen_raw_data($id,$report_id){

		   if($id === '1'){
		   	$data['title'] = 'CRL Raw Data';
			  	$data['data'] = $this->test_model->get_raw_data_crl($report_id); 
			  	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/raw_crl_results', $data);
		   }

		   if($id === '2'){	   	
		   	$data['title'] = 'ESCREEN Raw Data';
			  	$data['data'] = $this->test_model->get_raw_data_escreen($report_id); 
			  	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/raw_escreen_results', $data);

		   }

		   if($id === '3'){
		   	$data['title'] = 'AWSI Raw Data';
			  	$data['data'] = $this->test_model->get_raw_data_awsi($report_id); 
			  	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/raw_awsi_results', $data);

		   }

	   }
	
	   public function add_test_result($client_id){
			
			$data['title'] = 'Add New Test Result';
			$data['clients'] = $this->clients_model->get_client_account_by_client_id($client_id); 
			// Get employees based on selected client account
			$selected_client_account_id = $this->input->post('client_account_id');
			$employees = $this->test_model->fetchEmployeeByClientAccount($selected_client_account_id);
			$data['employees'] = $employees;
			$data['drug_lists'] = $this->test_model->get_drug_test_list();
			$data['alcohol_lists'] = $this->test_model->get_alcohol_test_list();
			$data['test_type_lists'] = $this->test_model->get_test_type_list();
			$data['client_id'] = $client_id;

	    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/add_test_result', $data);
	        
		}



		public function showEmployees()
		{			

	   $data['selected_client_account_id'] = $this->input->post('id');
	   $data['client_id'] = $this->input->post('client_id');		 
		$data['employees'] = $this->test_model->fetchEmployeeByClientAccount($selected_client_account_id);
		$data['employees_categories'] = $this->employees_model->get_employees_categories();
		$data['employees_statuss'] = $this->employees_model->get_employees_statuss();
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/employee_search', $data);
		}




		public function search($page) {
		    // Get the search parameters from the AJAX request
		    $name = $this->input->post('name');
		    $employeeId = $this->input->post('employee_id');
		    $status = $this->input->post('status');
		    $client_account_id = $this->input->post('client_account_id');
		    $page = $this->input->post('page'); // New: Get the current page number

		    // Set the number of results per page
		    $resultsPerPage = 10;

		    // Calculate the offset based on the current page and results per page
		    $offset = ($page - 1) * $resultsPerPage;

		    // Get paginated search results
		    $results = $this->test_model->searchEmployees($client_account_id, $name, $employeeId, $status, $resultsPerPage, $offset);

		    // Get the total count of employees
		    $totalEmployees = $this->test_model->countEmployees($client_account_id, $name, $employeeId, $status);

		    // Calculate the total number of pages
		    $totalPages = ceil($totalEmployees / $resultsPerPage);

		    // Pass the results, total pages, and current page to the view
		    $data['results'] = $results;
		    $data['totalPages'] = $totalPages;
		    $data['currentPage'] = $page;

		    // Return the search results as a JSON response
		    echo json_encode($data);
		}

		public function add_employee_test(){

			$date=date("m-d-Y H:i:s a");
			$data = array(
					'client_id' => $this->input->post('client_id'),
					'client_account_id' => $this->input->post('client_account_id'),
					'first_name' => $this->input->post('first_name'),
					'middle_name' => $this->input->post('middle_name'),
					'last_name' => $this->input->post('last_name'),
					'employees_status' => $this->input->post('employees_status'),
					'employees_category' => $this->input->post('employees_category'),
					'employee_account_type' => $this->input->post('employee_account_type'),
					'email' => $this->input->post('email'),
					'phone' => $this->input->post('phone'),
					'dob' => $this->tag_general->us_date_db($this->input->post('dob')),
					'location' => $this->input->post('location'),
					'ssn' => $this->input->post('ssn'),
					'dl_no' => $this->input->post('dl_no'),
					'dl_state' =>$this->input->post('dl_state'),
					'notes'=>$this->input->post('notes'),
					'date_entered ' => $this->tag_general->us_date_db($this->input->post('date_entered')),
					'employee_id' => $this->input->post('employee_id')
					);	

        $employee_id = $this->test_model->add_employee($data);

        if ($employee_id) {
            $response = array(
                'status' => 'success',
                'message' => 'Employee added successfully.',
                'employee_id' => $employee_id
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Failed to add employee.'
            );
        }

        echo json_encode($response);

		}






		/*public function searchEmployees()
		{			
		  // Get search keyword and selected client account ID from the AJAX request
        $search_keyword = $this->input->post('search_keyword');
        $selected_client_account_id = $this->input->post('selected_client_account_id');
		  $employees = $this->test_model->fetchEmployeeByNameClientAccount($selected_client_account_id,$search_keyword);		

			// Return the filtered employee list as JSON response
			echo json_encode($employees);
		}*/
    
} // Class end.