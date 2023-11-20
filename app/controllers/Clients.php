<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Clients extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('payment_model');
		$this->load->model('general_model');
		$this->load->model('dashboard_model');
    $this->load->model('employees_model');
    $this->load->model('invoices_model');
		$this->load->model('email_model');
		$this->load->library('common/user');
		$this->load->library('common/paginator');  
    $this->load->library('generatepdf');  
        $this->load->library("pagination");
		/* if (!$this->session->userID):
		redirect('auth');
		endif; */
		
    }



    /**
     * App home.
     */
    public function index($page=1)
    {
    	$data['title'] = 'Clients';
    	$data['pull_groups'] =  $this->general_model->pull_groups();
    	if($this->input->get()){
    	
    	 $etype_num_rows = $this->clients_model->get_clients_search_num_rows();
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
	 $start_page =  site_url('clients/index?'.$FullURL[1]);

     $start_pages  = site_url('clients/index');
     $page_url  =  $start_pages.'/';

     
     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
     // end pagination
     $data['clients'] = $this->clients_model->get_clients_search($perPage, $offSet);
     
	
	
    	//$data['clients'] = $this->clients_model->get_clients_search();
    	} else {
    	$data['clients']='';
    	}
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/home', $data);
        
    }
    
    /**
     * App home.
     */
    public function add_client()
    {
    	$data['title'] = 'Add Clients';
    	if($this->input->post()){
    	$this->clients_model->add_client($this->input->post());
    	$this->session->set_flashdata('add_client', true);
    	}
    redirect('clients/index?s='.$this->input->post('s').'&st='.$this->input->post('st'));
    }
    
    /**
     * App home.
     */
    public function edit_client($id)
    {
    	$data['title'] = 'Edit Clients';
    	if($this->input->post()){
    	$this->clients_model->edit_client($this->input->post(),$id);
    	$this->session->set_flashdata('edit_client', true);
    	}
		redirect('clients/index?s='.$this->input->post('s').'&st='.$this->input->post('st'));
    }
    
    
    /**
     * App home.
     */
    public function view($id,$client_id)
    {  

    	$data['title'] = 'Clients';
    	$data['client'] = $this->clients_model->get_client($id);
    	$data['states'] = $this->general_model->get_states('230');
    	$data['account_types'] = $this->general_model->account_types();
    	$data['client_account_num'] =  $this->clients_model->get_client_account_num_by_client_id($id);
    	$data['client_address_num'] =  $this->clients_model->get_client_address_num_by_client_id($id);
    	$data['client_contact_num'] =  $this->clients_model->get_client_contact_num_by_client_id($id);
    	$data['client_accounts'] =  $this->clients_model->get_client_account_by_client_id($id);
    	$data['client_addresss'] =  $this->clients_model->get_client_address_by_client_id($id);
    	$data['client_contacts'] =  $this->clients_model->get_client_contact_by_client_id($id);
    	$data['client_labs'] =  $this->clients_model->get_client_labs_by_client_id($id);
      $data['client_notes'] =  $this->clients_model->get_client_notes($id);
    	$data['labs'] =  $this->general_model->get_labs();
    	$data['pull_groups'] = $this->general_model->pull_groups();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/view', $data);
        
    }
    
    /** 
     * App home.
     */
    public function account_view($id,$account_id)
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

      // test results:: start
	
      $data['test_results'] = $this->dashboard_model->get_alltest_results_by_clientid($id); 
      // $data['test_type'] = $this->dashboard_model->get_list_of_test_type_not_null(); 


      // echo "<pre>";
      // print_r($data['test_results']); exit;
      // test results:: end

      $data['client_payment_profile_ids'] = $this->payment_model->get_client_payment_method_by_clientid($id);
     

     
      //echo '<pre>';print_r($data['client_addresss']);exit();
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/view', $data);
        
    }
    

      public function account(){
          if($this->input->post()){ 
          redirect('clients/account_view/'.$this->input->post('id').'/'.$this->input->post('account_id'));
        }
      }
    /**
     * App home.
     */
    public function add_contact($id,$account_id)
    { 
    	$data['title'] = 'Add Clients Contact';
    	if($this->input->post()){
    	$cl = $this->clients_model->add_contact($this->input->post(),$id);
    	if($cl){
    	$this->session->set_flashdata('add_contact', true);
    	} else {
    	$this->session->set_flashdata('add_contact_fail', true);
    	}     
      
    	}
    redirect('clients/account_view/'.$id.'/'.$account_id);
    }
    
    /**
     * App home.
     */
    public function edit_contact($id,$contact_id,$account_id)
    {
    	$data['title'] = 'Edit Clients Contact';
    	if($this->input->post()){
    	$cl = $this->clients_model->edit_contact($this->input->post(),$contact_id);
    	if($cl){
    	$this->session->set_flashdata('edit_contact', true);
    	} else {
    	$this->session->set_flashdata('edit_contact_fail', true);
    	} 
      }
		redirect('clients/account_view/'.$id.'/'.$account_id);
    }
    
    /**
     * App home.
     */
    public function add_client_address($id,$account_id)
    {
    	$data['title'] = 'Add Clients Address';
    	if($this->input->post()){
    	$cl = $this->clients_model->add_address($this->input->post(),$id);
    	if($cl){
    	$this->session->set_flashdata('add_address', true);
    	} else {
    	$this->session->set_flashdata('add_address_fail', true);
    	}
    	}
    	redirect('clients/account_view/'.$id.'/'.$account_id);
       
   }
    
    /**
     * App home.
     */
    public function edit_client_address($id,$account_id,$address_id)
    {
    	$data['title'] = 'Edit Clients Address';
    	if($this->input->post()){
    	$cl = $this->clients_model->edit_address($this->input->post(),$address_id);
    	if($cl){
    	$this->session->set_flashdata('edit_address', true);
    	} else {
    	$this->session->set_flashdata('edit_address_fail', true);
    	}
    	}
     	redirect('clients/account_view/'.$id.'/'.$account_id);
      
    }
    
    /**
     * App home.
     */
    public function add_client_account($id,$account_id)
    {
    	$data['title'] = 'Add Clients Account';
    	if($this->input->post()){
    	$cl = $this->clients_model->add_account($this->input->post(),$id);
    	if($cl){
    	$this->session->set_flashdata('add_account', true);
    	} else {
    	$this->session->set_flashdata('add_account_fail', true);
    	}
    	}
		redirect('clients/account_view/'.$id.'/'.$account_id);
    }
    
    /**
     * App home.
     */
    public function edit_client_account($id,$account_id)
    {
    	$data['title'] = 'Edit Clients Account';
    	if($this->input->post()){
    	$cl = $this->clients_model->edit_account($this->input->post(),$account_id);
    	if($cl){
    	$this->session->set_flashdata('edit_account', true);
    	} else {
    	$this->session->set_flashdata('edit_account_fail', true);
    	}
    	}
		redirect('clients/account_view/'.$id.'/'.$account_id);
    }
    
    /**
     * App home.
     */
    public function add_client_lab($id,$account_id)
    {
    	$data['title'] = 'Add Clients Lab';
    	if($this->input->post()){
    	$cl = $this->clients_model->add_client_lab($this->input->post(),$id,$account_id);
    	if($cl){
    	$this->session->set_flashdata('add_lab', true);
    	} else {
    	$this->session->set_flashdata('add_lab_fail', true);
    	}
    	}
		redirect('clients/account_view/'.$id.'/'.$account_id);
    }
    
    /**
     * App home.
     */
    public function edit_client_lab($client_id,$account_id,$lab_id)
    {
    	$data['title'] = 'Edit Clients Lab';
    	if($this->input->post()){
    	$cl = $this->clients_model->edit_client_lab($this->input->post(),$lab_id);
    	if($cl){
    	$this->session->set_flashdata('edit_lab', true);
    	} else {
    	$this->session->set_flashdata('edit_lab_fail', true);
    	}
    	}if($this->input->post('account_type')){
		  redirect('clients/account_view/'.$client_id.'/'.$account_id.'?status'.$this->input->post('status').'&account_type'.$this->input->post('account_type'));
      }else{
      redirect('clients/account_view/'.$client_id.'/'.$account_id);

    }
    }
    
    // Upload Client
    public function upload_client(){
    
    	if($_FILES['file']['tmp_name']){
	
	
   $file_name=$_FILES["file"]["name"];
   
	$handle = fopen($_FILES['file']['tmp_name'], "r");
	$data = fgetcsv($handle, 10000, ";"); 
	while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
		
		$this->clients_model->upload_client($data);
	
		}
   }
   $this->session->set_flashdata('add_client', true);
   redirect('clients');
    
    }


    public function visited_to_change_password() {

      $data['title'] = 'Visited to Change password page';
      $data['login_logs'] =  $this->clients_model->get_login_logs('1');
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/login_logs/visit', $data);

    }

    public function requested_to_change_password() {

      $data['title'] = 'Requested to Change password page';
      $data['login_logs'] =  $this->clients_model->get_login_logs('2');
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/login_logs/password_request', $data);

    }

    public function changed_password() {

      $data['title'] = 'Changed password';
      $data['login_logs'] =  $this->clients_model->get_login_logs('3');
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/login_logs/password_changed', $data);

    }

    public function logged_in_portal($page=1) {

      $data['title'] = 'Logged In Details'; 
      $project_num_rows = $this->clients_model->get_login_logs_num('4');
        // Generate pagination.
        $perPage = 25;
        // Handle pagination.
        if ($page==1) {
            $offSet = 0;
        } else {
            // $offSet = ($offSet - 1) * $perPage.
            $offSet = ($page - 1) * $perPage;
        }
        
        $start_page = base_url() . "/clients/logged_in_portal";
        $page_url  = $start_page.'/';  
        $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$project_num_rows,$perPage,3);
        $data['paginationInfo'] = $this->paginator->newpaginationInfo($project_num_rows,$perPage);
        // end pagination
       $data['login_logs'] =  $this->clients_model->get_portal_logs('4',$perPage, $offSet);
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/login_logs/logged_in_portal', $data);

    }

    public function visited_to_reset_page(){

      $data['title'] = 'Logged In Details';
      $data['login_logs'] =  $this->clients_model->get_login_logs('5');
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/login_logs/visited_to_reset_page', $data);


    }
    
    public function login_as_client($id){
    $user = $this->dashboard_model->get_client_contact($id);
    //echo '<pre>';print_r($user);exit(); 
    $this->user->client_signin($user->id,$user->client_id);
    redirect('dashboard');
    }

    public function employee_roster($client_id,$page=1){ 

      $data['title'] = 'Employee Roster';  
      $data['client_accounts'] = $this->clients_model->get_client_account_by_client_id($client_id);
      $data['old_client_accounts'] = $this->clients_model->get_client_account_by_client_id($client_id);
      $data['employees_statuss'] = $this->employees_model->get_employees_statuss();
      $data['employees_categories'] = $this->employees_model->get_employees_categories();
      $data['account_types'] = $this->general_model->account_types(); 


      $etype_num_rows = $this->employees_model->get_employees_by_client_id_num_rows_for_admin($client_id);

      // Generate pagination.
      $perPage = 15;
      // Handle pagination.
      if ($page == 1) {
      $offSet = 0;
      } else {
      $offSet = ($page - 1) * $perPage;
      }

      $start_pages = base_url() . "clients/employee_roster/$client_id";
      $page_url = $start_pages . '/';
      $data['pagination'] = '';
      $data['paginationInfo'] = '';
 
      if (!empty($etype_num_rows)) {
      $data['pagination'] = $this->paginator->newpagination($start_page, $page_url, $etype_num_rows, $perPage, 4);
      $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows, $perPage);
      }

      $data['employees'] = $this->employees_model->get_employees_by_client_id_with_limit_for_admin($client_id,$perPage, $offSet);

    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/client_employees', $data);

    }

      /**
     * Add Employee (Employee Roster).
     */
    public function add_employee($client_id)
    {
      $data['title'] = 'Add Employee';
      if($this->input->post()){
        $this->employees_model->add_employee($this->input->post());
        $this->session->set_flashdata('add_employee', true);
      redirect('clients/employee_roster/'.$client_id.'?status='.$this->input->get('status').'&account_type='.$this->input->get('account_type'));
      }else{        
      $data['employees'] = $this->employees_model->get_employees_by_client_id($client_id);
      $data['client_accounts'] = $this->clients_model->get_client_account_by_client_id($client_id);
      $data['old_client_accounts'] = $this->clients_model->get_client_account_by_client_id($client_id);
      $data['employees_statuss'] = $this->employees_model->get_employees_statuss();
      $data['employees_categories'] = $this->employees_model->get_employees_categories();
      $data['account_types'] = $this->general_model->account_types();

      $data['back_link'] = base_url('clients/employee_roster/'.$client_id.'?status='.$this->input->get('status').'&account_type='.$this->input->get('account_type')); 
 $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/add_employee', $data);

      }
    }
    
    /**
     * Edit Employee (Employee Roster).
     */
     public function edit_employee($id,$client_id)
    {
      $data['title'] = 'Edit Employee';
      if($this->input->post()){
        $data['employee'] = $this->employees_model->get_employee($id);
        $res = $this->employees_model->edit_employee($this->input->post(),$id);
        if($res){ 
        if($data['employee']->employees_status=='2' && $this->input->post('employees_status')=='1'){ 
        $this->employees_model->employee_status_history($id,$data['employee']->inactive_date,$data['employee']->date_entered,$data['employee']->employees_status,$this->input->post('employees_status'));
            }
        $this->session->set_flashdata('edit_employee', true);
        } else {
        $this->session->set_flashdata('edit_employee_fail', true);
        } 
      redirect('clients/employee_roster/'.$client_id.'?status='.$this->input->get('status').'&account_type='.$this->input->get('account_type')); 
      }else{ 
        $data['employee'] = $this->employees_model->get_employee($id);
        $data['random_pull_test'] =$this->clients_model->get_random_pull($data['employee']->employee_id);
        $data['random_drug_pull_test'] =$this->clients_model->get_random_drug_pull($data['employee']->employee_id); 
      $data['randomdate'] = $this->general_model->get_eligible_random_date_for_employee($data['employee']->client_id);  
        $data['clients'] = $this->clients_model->get_clients();
        $data['client_accounts'] = $this->clients_model->get_client_account_by_client_id($client_id);
        $data['employees_statuss'] = $this->employees_model->get_employees_statuss();
        $data['employees_categories'] = $this->employees_model->get_employees_categories();


      $data['back_link'] = base_url('clients/employee_roster/'.$client_id.'?status='.$this->input->get('status').'&account_type='.$this->input->get('account_type')); 
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/edit_employee', $data);

      }
    }




    function add_client_account_notes($client_id,$account_id){
      
      if($this->input->post()){
       // print_r($this->input->post());exit();
      $cl = $this->clients_model->add_client_account_notes($this->input->post(),$client_id,$account_id);
      if($cl){
      $this->session->set_flashdata('notes_added', true);
      }  
      }
    redirect('clients/account_view/'.$client_id.'/'.$account_id);

    }

     function add_client_notes(){
     // print_r($_POST);exit();
      
      if($this->input->post()){
       // print_r($this->input->post());exit();
      $cl = $this->clients_model->add_client_notes($this->input->post());
      if($cl){
      // Return a response
      echo 'Form submitted successfully';
      }  
      }
    redirect('clients/account_view/'.$this->input->post('client_id').'/'.$this->input->post('account_num'));

    }

     public function edit_contact_communication($id,$contact_id,$account_id)
    {
      $data['title'] = 'Edit Clients Contact';
      if($this->input->post()){
      $cl = $this->clients_model->edit_contact_communication($this->input->post(),$contact_id);
      if($cl){
      $this->session->set_flashdata('edit_contact', true);
      } else {
      $this->session->set_flashdata('edit_contact_fail', true);
      }
      }
    redirect('clients/account_view/'.$id.'/'.$account_id);
    }


    public function search_by_contact_info($page=1){

    $data['title'] = 'Search by Clients Info';
       if($this->input->get()){
     $etype_num_rows = $this->clients_model->get_clients_search_num_rows_by_contact_info();
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
     $start_page =  site_url('clients/search_by_contact_info?'.$FullURL[1]);
     $start_pages  = site_url('clients/search_by_contact_info');
     $page_url  =  $start_pages.'/';
     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
     // end pagination
     $data['clients'] = $this->clients_model->get_clients_search_by_contact_info($perPage, $offSet);
      } else {
      $data['clients']='';
      }
     // print_r($data);exit();
     $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/search_by_contact', $data);

    }

    public function search_by_lab_account($page=1){
       $data['title'] = 'Search by Lab Account';
       if($this->input->get()){
     $etype_num_rows = $this->clients_model->get_clients_search_num_rows_by_lab_account();
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
     $start_page =  site_url('clients/search_by_lab_account?'.$FullURL[1]);
     $start_pages  = site_url('clients/search_by_lab_account');
     $page_url  =  $start_pages.'/';
     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
     // end pagination
     $data['clients'] = $this->clients_model->get_clients_search_by_lab_account($perPage, $offSet);
      } else {
      $data['clients']='';
      }
     // print_r($data);exit();
     $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/search_by_lab_account', $data);

    }

      public function employee_roster_export_excel($client_id,$account_type,$status){
            
         $data['title']= 'Statement';
          //print_r($values);
          $this->load->library('excel');
          $this->excel->setActiveSheetIndex(0);
          $this->excel->getActiveSheet()->setTitle('account_statement');          
          $this->excel->getActiveSheet()->SetCellValue('A1', 'Last Name');
                    $this->excel->getActiveSheet()->SetCellValue('B1', 'First Name');
                    $this->excel->getActiveSheet()->SetCellValue('C1', 'MI');           
                    $this->excel->getActiveSheet()->SetCellValue('D1', 'Employee ID');
                    $this->excel->getActiveSheet()->SetCellValue('E1', 'Category');
                    $this->excel->getActiveSheet()->SetCellValue('F1', 'Location');
                    $this->excel->getActiveSheet()->SetCellValue('G1', 'Account');
                    $this->excel->getActiveSheet()->SetCellValue('H1', 'Date Entered');
                    $this->excel->getActiveSheet()->SetCellValue('I1', 'Status');
                    if($status!=1){
                    $this->excel->getActiveSheet()->SetCellValue('J1', 'Inactive Date');
                    $this->excel->getActiveSheet()->SetCellValue('K1', 'Inactive Reason');
                    }
                    $erow = 2;
                    $s = 1;
        $data = $this->clients_model->get_client_employee_roaster_excel_export($client_id,$account_type,$status);
        $client=$this->clients_model->get_client($client_id);       
      
              foreach($data as $row) {

               $employees_categories = $this->employees_model->get_employees_categories_by_id($row->employees_category);
              $status = $this->employees_model->get_employees_status_by_id($row->employees_status);
                        $de = explode(" ",$row->date_entered);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $erow, $row->last_name);
                        $this->excel->getActiveSheet()->SetCellValue('B' . $erow, $row->first_name);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $erow, $row->middle_name);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $erow, '...'.substr($row->employee_id,-4));
                        $this->excel->getActiveSheet()->SetCellValue('E' . $erow, $employees_categories->title);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $erow, $row->location);
                        $this->excel->getActiveSheet()->SetCellValue('G' . $erow, $row->mode);
                        $this->excel->getActiveSheet()->SetCellValue('H' . $erow, $this->tag_general->us_date($de[0]));
                        $this->excel->getActiveSheet()->SetCellValue('I' . $erow, $status->title);
                         if($row->employees_status!=1){
                        $this->excel->getActiveSheet()->SetCellValue('J' . $erow, $this->tag_general->us_date($row->inactive_date));
                        $this->excel->getActiveSheet()->SetCellValue('K' . $erow, $row->reason_for_inactive);
                        }
                        $s++;$erow++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
                    if($status!=1){
                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
                    $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
                    }
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'employee_roster_details of '.$client->client_name .'-'. date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
redirect('clients/employee_roster/'.$client_id.'?status=.'.$status.'&account_type='.$account_type);
                    
       
    }

     public function employee_roster_export_pdf($client_id,$account_type,$status){
        
         $data['title']= 'Statement';  
      $data['employee_export'] = $this->clients_model->get_client_employee_roaster_excel_export($client_id,$account_type,$status);         
        $data['client']=$this->clients_model->get_client($data['employee_export']->client_id); 
        $data['acc_type']=$account_type; $data['status']=$status;
         $filename = 'employee_roster_details of '.date('Y_m_d_H_i_s');                  
         $html = $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/employee_roster_export_pdf', $data, true);
         $this->generatepdf->createPDF($html, $filename, false);

      }

      public function assign_contact($contact_id,$client_id,$client_account_id){

        $data['title']= 'Assign Contact';
        $data['back_link'] = base_url('clients/account_view/'.$client_id.'/'.$client_account_id.'?status='.$this->input->get('status').'&account_type='.$this->input->get('account_type'));
        if($this->input->post()){ 
        $res = $this->clients_model->add_assign_contact($this->input->post(),$contact_id,$client_id);
        if($res){ 
        $this->session->set_flashdata('add_assign', true);
        } else {
        $this->session->set_flashdata('add_assign_fail', true);
        }
        redirect('clients/assign_contact/'.$contact_id.'/'.$client_id.'/'.$client_account_id.'?status='.$this->input->get('status').'&account_type='.$this->input->get('account_type')); 
        }else{
        $data['client_contact']=$this->dashboard_model->get_client_contact($contact_id);
        $data['client']=$this->clients_model->get_client($client_id);       
        $data['assign_contact'] = $this->clients_model->get_client_contact_assign_by_contact_id_client_id($contact_id,$client_id); 
        $data['client_accounts']=$this->clients_model->get_all_client_account_by_client_id($client_id);
        $data['contact_id']=$contact_id;
        //echo '<pre>';print_r($data);exit();
         $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/assign_contact', $data);
        }
        }
 
       public function delete_assigned_contact($id,$table)
       {
       $assign_contact = $this->clients_model->get_assign_contact_by_id($id); 
       if($table='client_assign'){
       $table='client_contact_assign';
       }
       $res = $this->clients_model->delete_data($table,$id);
       if($res){ 
       $this->session->set_flashdata('delete_assign', true);
       } else {
       $this->session->set_flashdata('delete_fail', true);
       }
       redirect('clients/assign_contact/'.$assign_contact->client_contact_id.'/'.$assign_contact->client_id.'/'.$assign_contact->client_account_id.'?status='.$this->input->get('status').'&account_type='.$this->input->get('account_type')); 
       }


      public function manual_drug_test($client_account_id){
        $data['title'] = 'Manual Drug Test';
        $data['manual_drug_tests'] = $this->test_model->manual_drug_test($client_account_id);
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/manual_test/home', $data);

      }



       public function communication_log($source,$client_id){
       // print_r($source);exit();
      if($source=='sms_log'){
      $data['logs']=$this->clients_model->get_communication_log_data('tag_batch_sms_log',$client_id);
      }elseif($source=='invoice_email_log'){
      $data['logs']=$this->clients_model->get_communication_log_data('tag_batch_invoice_email_run',$client_id);
      }elseif($source=='invoice_print_log'){
      $data['logs']=$this->clients_model->get_communication_log_data('tag_batch_invoice_print_run',$client_id);
      }elseif($source=='test_print_log'){
      $data['logs']=$this->clients_model->get_communication_log_data('tag_test_batch_print',$client_id);
      }elseif($source=='test_email_log'){
      $data['logs']=$this->clients_model->get_communication_log_data('tag_test_batch_run',$client_id);
      } 
      $data['source']=$source;
      $data['back_link'] = base_url('clients/account_view/'.$client_id.'/'.$this->input->get('account_id'));
  
       $data['title'] = 'Communication Logs'; 
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/clients/communication_logs', $data);


    }




        

} // Class end.
