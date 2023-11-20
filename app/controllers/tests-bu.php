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
		$this->load->model('general_model');
		$this->load->model('reports_model');
		$this->load->model('email_model');
		$this->load->model('test_model');
		$this->load->library('common/user');
		$this->load->library('tagams');
		$this->load->library('generatepdf');	
		$this->load->library('tag_general');	
		$this->load->library('common/paginator'); 
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
    	$etype_num_rows = $this->test_model->test_runs_num_rows();	

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
		$data['test_runs'] = $this->test_model->test_runs($perPage, $offSet);

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
    public function test_client_view_details($id,$pull_group_id)
    {
    	$data['title'] = 'Drug Test Client Details';		
		$data['test_client_view_details'] = $this->test_model->test_client_view_details($pull_group_id);
    	$data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
    	$data['batch_count'] = $this->test_model->test_batch_run_num($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_client_view_details', $data);
        
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
		$start_page =  site_url('tests/test_selected_by_client/'.$id.'/'.$client_account_id.'');
		$page_url  =  $start_page.'/';     
		$data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,5);
		$data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
		// end pagination
		$data['client_account'] = $this->clients_model->get_client_account_by_client_id_row($client_account_id);
		$data['client'] = $this->clients_model->get_client_by_id($data['client_account']->client_id);
		$data['test_runs_details'] = $this->test_model->test_selected_by_client($id,$client_account_id,$perPage, $offSet);
    	$data['test_runs_by_id'] = $this->test_model->test_runs_by_id($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_selected_by_client', $data);
        
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
		$data['client_account'] = $this->clients_model->get_client_account_by_client_id_row($client_account_id);
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
		$data['test_not_selected_runs'] = $this->test_model->test_no_selected_emp_print($id);
		$data['test_run'] = $this->test_model->test_runs_by_id($id);
		$data['id'] = $id;
		$data['client_accounts'] = $this->clients_model->get_active_clients_by_pull_group_id($data['test_run']->pull_group_id);		
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_not_selected_emp_print', $data);
    	//$this->generatepdf->createPDF($html, $filename='TagAMS-Random-Eligible-Letter', false); 
    }


	public function test_no_active_emp_print($id){
		
		$data['title'] = 'Drug Test Details';
		$data['test_run'] = $this->test_model->test_runs_by_id($id);
		$data['id'] = $id;
		$data['client_accounts'] = $this->clients_model->get_active_clients_by_pull_group_id($data['test_run']->pull_group_id);		
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_no_active_emp_print', $data);
    	
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
    	$data['pull_group_test_runs'] = $this->test_model->test_runs();
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
    
     public function view_batch($id,$page=1)
    {
    	$data['title'] = 'View Batch Details';
		$data['test_run'] = $this->test_model->test_runs_by_id($id);
		
		$etype_num_rows = $this->test_model->test_batch_run_num($id);
	     // Generate pagination.
     $perPage = 10;
     // Handle pagination.
     if ($page==1) {
        $offSet = 0;
     } else {
        // $offSet = ($offSet - 1) * $perPage.
        $offSet = ($page - 1) * $perPage;
     }
     $start_page =  site_url('tests/view_batch/'.$id);

     $page_url  =  $start_page.'/';

     
     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,4);
     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
     // end pagination
     $data['batchs'] = $this->test_model->test_batch_run_by_id($id,$perPage, $offSet);
    	
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/tests/test_view_batch', $data);
        
    }
    
    public function send_batch_email_by_batch_id($id,$client_account_id){
    	 	$detail = $this->test_model->get_batch($id);
				// sending email
    	 			$data = array(
                        'subject' => 'Random Pull Email',
                        'run_id' => $detail->test_run_id,
                        'client_account_id' => $client_account_id,
                        'to' => 'khaja@sowedane.com',
                        'name' => $detail->client_name,
                        'template_path' => $this->preferences->type('system')->item('full_app_themesDir')."/email/random_pull_email"
                    );
                    $res = $this->email_model->send_grid_email($data);
                    $this->test_model->email_sent_batch($id);
               redirect('tests/view_batch/'.$detail->test_run_id);     
    }
} // Class end.
