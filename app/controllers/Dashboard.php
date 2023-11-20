<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Dashboard extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
        $this->config->load('authorize_net');
		$this->config->load('modules/auth/config');
		$this->load->model('frontend_model');
		$this->load->model('general_model');
		$this->load->model('dashboard_model');
    $this->load->model('client_test_model');
    $this->load->model('client_paymentTracking_model');
		$this->load->model('client_payment_model');
		$this->load->model('client_employees_model');     
		$this->load->model('email_model');      
    $this->load->model('employees_model'); 
		$this->load->library('common/user');
    $this->load->library('generatepdf');
    $this->load->library('common/user');
    $this->load->library('common/paginator');
    $this->load->library('authorize_net');
    $this->load->library("pagination");
    $this->load->library('tag_general');

		if (!$this->session->client_userID):
		redirect('signin');
		endif;
		
    }

	public function index(){
		$id = $this->session->clientID;
    //print_r($id);exits();
		$data['title'] = 'Clients Dashboard';
		$data['client'] = $this->frontend_model->get_client($id);
    $userid=$this->session->client_userID;
    $employee=$this->client_employees_model->get_employee_by_id($userid);
    $data['pending_employees'] = $this->client_employees_model->get_pending_employees_by_client_id_num_rows($id);  
    $data['unpaid_invoice_count'] = $this->frontend_model->get_all_unpaid_amt_by_client_id_num_rows($id);     
    $data['client_account_type']=    $this->client_employees_model->get_account_from_account_table_by_account_id($employee->client_account_id);
    $data['pull_group_id']=$this->client_employees_model->get_pull_group_id_by_client_id($id);
    $data['random_pull_duration'] = $this->frontend_model->get_random_pulls_within_thirty_days($data['pull_group_id']->pull_group_id); 
    $data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid);
    $data['client_accounts'] = $this->frontend_model->get_client_account_by_client_id_row($id);
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/dashboard', $data);
      
	}
	
	public function communication_preference(){
	
		if($this->input->post()){
			$this->frontend_model->communication_preference($this->input->post());
		}
		redirect('dashboard');
	}
	 public function edit_profile(){ 
    	$id = $this->session->client_userID;
    	$data['title'] = 'Home';
	   	$data['client_contact'] = $this->dashboard_model->get_client_contact($id);
		  $data['id'] = $id;
		  if ($this->input->post(null, true)) {
		   $this->dashboard_model->edit_profile($this->input->post(),$id);
         $config['upload_path'] = './upload/profile_images_upload/';
         $config['allowed_types'] = 'gif|jpg|jpeg|JPG|png|PNG|JPEG';
         $config['max_size'] = 5120;
               if ($_FILES['uploadFile']['name']) {
                $secure_directory = 'upload/profile_images_upload';
                $upload_file = $secure_directory;
                if (!is_dir($upload_file)) {
                    mkdir("./" . $upload_file, 0777);
                }
                $config['upload_path']          = $upload_file;
                $config['allowed_types']        = 'gif|jpg|png|pdf|doc|docx|jpeg';
                $config['max_size']             = 1000000;
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('uploadFile')) {
                }else{
                    $data = $this->upload->data();
                    $document_path          = $upload_file . '/' . $data['file_name'];
                    $this->dashboard_model->update_profile_pic($document_path,$id);
                }
            }
	   $this->session->set_flashdata('msg','<div class="alert alert-success">Updated successfully</div>');
     redirect('dashboard/edit_profile'); 
		 }
		  $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/edit_profile', $data);
    }
    
    /**
     * App home.
     */
    public function client_view()
    {
    	$id = $this->session->clientID;
    	$data['title'] = 'Clients';
    	$data['client'] = $this->frontend_model->get_client($id);
    	$data['states'] = $this->general_model->get_states('230');
    	$data['account_types'] = $this->general_model->account_types();
    	$data['client_account_num'] =  $this->frontend_model->get_client_account_num_by_client_id($id);
    	$data['client_address_num'] =  $this->frontend_model->get_client_address_num_by_client_id($id);
    	$data['client_contact_num'] =  $this->frontend_model->get_client_contact_num_by_client_id($id);
    	$data['client_accounts'] =  $this->frontend_model->get_client_account_by_client_id($id);
    	$data['client_addresss'] =  $this->frontend_model->get_client_address_by_client_id($id);
    	$data['client_contacts'] =  $this->frontend_model->get_client_contact_by_client_id($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/client_view', $data);        
    }
    
    
    /**
     * App home.
     */
    public function client_contacts()
    {
    	$id = $this->session->clientID; 
    	$data['title'] = 'Clients';
    	$data['client'] = $this->frontend_model->get_client($id); 
    	$data['client_contacts'] =  $this->frontend_model->get_client_contact_by_client_id($id);
      $data['client_addresss'] =  $this->frontend_model->get_client_address_by_client_id($id); 
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/client_contacts', $data);
        
    }
    
     /**
     * App home.
     */
    public function client_address()
    {
    	$id = $this->session->clientID; 
    	$data['title'] = 'Clients';
    	$data['client'] = $this->frontend_model->get_client($id);
    	$data['states'] = $this->general_model->get_states('230');
    	$data['client_address'] =  $this->frontend_model->get_client_address_by_client_id($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/client_address', $data);
        
    }
    
    
    /**
     * App home.
     */
    public function edit_address($id)
    {
    	$data['title'] = 'Edit Clients Contact';
    	if($this->input->post()){
    	$cl = $this->frontend_model->edit_address_status($this->input->post(),$id);
    	if($cl){
    	$this->session->set_flashdata('edit_address', true);
    	} else {
    	$this->session->set_flashdata('edit_address_fail', true);
    	}
    	}
		redirect('dashboard/client_address');
    }
    
     public function add_contact($id)
    {  
    	$data['title'] = 'Add Clients Contact';
    	if($this->input->post()){ 
      $client = $this->frontend_model->get_client_by_der($id); 
      if(count($client)>$this->input->post('der') && $this->input->post('der')!='0'){
      $this->session->set_flashdata('der_exits', true);
      redirect('dashboard/client_contacts');
      }else{
    	$cl = $this->frontend_model->add_contact($this->input->post(),$id);
    	if($cl){
    	$this->session->set_flashdata('add_contact', true);
    	} else {
    	$this->session->set_flashdata('add_contact_fail', true);
    	}     
      
      redirect('dashboard/client_contacts');
    	}
    }
    }
    
    /**
     * App home.
     */
    public function edit_contact($id,$contact_id)
    {
    	$data['title'] = 'Edit Clients Contact';
    	if($this->input->post()){ 
      $client = $this->frontend_model->get_client_by_der($id); 
      if(count($client)>$this->input->post('der') && $this->input->post('der')!='0'){
      $this->session->set_flashdata('der_exits', true);
      redirect('dashboard/client_contacts');
      }else{
    	$cl = $this->frontend_model->edit_contact($this->input->post(),$contact_id);
    	if($cl){
    	$this->session->set_flashdata('edit_contact', true);
    	} else {
    	$this->session->set_flashdata('edit_contact_fail', true);
    	}
		redirect('dashboard/client_contacts');
    	}
    }
    }

      public function add_client_account($id)
    { 
      $data['title'] = 'Add Clients Account';
      if($this->input->post()){
      $cl = $this->frontend_model->add_account($this->input->post(),$id);
      if($cl){
      $this->session->set_flashdata('add_account', true);
      } else {
      $this->session->set_flashdata('add_account_fail', true);
      }
      }
    redirect('dashboard');
    }



     public function edit_client_account($id,$account_id)
    {
      $data['title'] = 'Edit Clients Account';
      if($this->input->post()){
      $cl = $this->frontend_model->edit_account($this->input->post(),$account_id);
      if($cl){
      $this->session->set_flashdata('edit_account', true);
      } else {
      $this->session->set_flashdata('edit_account_fail', true);
      }
      }
    redirect('dashboard');
    }
    
    
    /**
     * App home.
     */
    public function edit_contact_communication($id,$contact_id)
    {
    	$data['title'] = 'Edit Clients Contact';
    	if($this->input->post()){
    	$cl = $this->frontend_model->edit_contact_communication($this->input->post(),$contact_id);
    	if($cl){
    	$this->session->set_flashdata('edit_contact', true);
    	} else {
    	$this->session->set_flashdata('edit_contact_fail', true);
    	}
    	}
		redirect('dashboard/client_contacts');
    }
     
 
 
    public function employees($page=1){
      $data['title'] = 'Employees'; 
  		$data['id'] = $this->session->clientID; 
      $data['userid']=$this->session->client_userID; 
      $userid=$this->session->client_userID; 
      $id = $this->session->clientID;    
      $data['client'] = $this->frontend_model->get_client($id);
      $data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid);
      $etype_num_rows = $this->client_employees_model->get_employees_by_client_id_num_rows($id);

      // Generate pagination.
      $perPage = 15;
      // Handle pagination.
      if ($page == 1) {
      $offSet = 0;
      } else {
      $offSet = ($page - 1) * $perPage;
      }

      $start_pages = base_url() . "dashboard/employees";
      $page_url = $start_pages . '/';
  
 
      if (!empty($etype_num_rows)) {
        $data['pagination'] = $this->paginator->newpagination($start_page, $page_url, $etype_num_rows, $perPage, 3);
        $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows, $perPage);
      }else{
            $data['pagination'] = '';
      $data['paginationInfo'] = '';

      }


      $data['employees'] = $this->client_employees_model->get_employees_by_client_id_with_limit($id,$perPage, $offSet);
      $data['client_accounts'] = $this->frontend_model->pull_client_account_by_client_id($id);
    	$data['employees_statuss'] = $this->client_employees_model->get_employees_statuss();
    	$data['employees_categories'] = $this->client_employees_model->get_employees_categories();
    	$data['account_types'] = $this->frontend_model->account_types();
      $data['client_account_types']=$this->frontend_model->get_total_account_by_client_id($id);
      $data['matched_account']=$this->dashboard_model->get_account_type_match($id);
   	  $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/employees', $data);
	}


    public function pending_employees($page=1){

      $data['id'] = $this->session->clientID; 
      $data['userid']=$this->session->client_userID;  
      $userid=$this->session->client_userID;    
      $id = $this->session->clientID;    
      $data['title'] = 'Pending Employees'; 
      $etype_num_rows = $this->client_employees_model->pull_pending_employees_by_client_id_num_rows($id);
       // Generate pagination.
      $perPage = 15;
      // Handle pagination.
      if ($page==1) {
        $offSet = 0;
      } else {
        // $offSet = ($offSet - 1) * $perPage.
        $offSet = ($page - 1) * $perPage;
     } 
     $start_pages  = base_url() . "dashboard/pending_employees";
     $page_url  =  $start_pages.'/';
     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
     // end pagination 
     $data['employees'] = $this->client_employees_model->pull_pending_employees_by_client_id_limit($id,$perPage,$offSet);
      $data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid); 
      //echo '<pre>';print_r($data);exit();    
      $data['client'] = $this->frontend_model->get_client($id);
      $data['client_accountss'] = $this->frontend_model->get_client_accounts();
      $data['client_accounts'] = $this->frontend_model->pull_client_account_by_client_id($id);
      $data['employees_statuss'] = $this->client_employees_model->get_employees_statuss();
      $data['employees_categories'] = $this->client_employees_model->get_employees_categories();
      $data['account_types'] = $this->frontend_model->account_types();
      $data['client_account_types']=$this->frontend_model->get_total_account_by_client_id($id);
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/pending_employees', $data);
  }





    /**
     * App home.
     */
    public function add_employee()
    {
    	$id = $this->session->clientID;
      $userid = $this->session->client_userID;
    	$data['title'] = 'Add Employee';
      $data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid);
    	$data['client_accounts'] = $this->frontend_model->get_client_account_by_client_id($id);
    	$data['employees_statuss'] = $this->client_employees_model->get_employees_statuss();
    	$data['employees_categories'] = $this->client_employees_model->get_employees_categories();
    	$data['employee_account_types'] = $this->frontend_model->account_types();
      $data['client_account_type']=$this->frontend_model->get_client_account_by_client_id_row($id);
      $data['matched_account']=$this->dashboard_model->get_account_type_match($id);
     // echo '<pre>'; print_r($data);exit();
      $data['back_link'] = base_url('dashboard/employees?account_type='.$this->input->get('account_type').'&status='.$this->input->get('status'));
    	if($this->input->post()){
    	$res = $this->client_employees_model->add_employee($this->input->post());
    	if($res){
    	$this->session->set_flashdata('add_employee', true);
    	} else {
    	$this->session->set_flashdata('add_employee_fail', true);
    	}
    	redirect('dashboard/employees?account_type='.$this->input->get('account_type').'&status='.$this->input->get('status'));
    	}
		  $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/add_employee', $data);
        
    }
    
    /**
     * App home.
     */
    public function edit_employee($id)
    {
      $client_id = $this->session->clientID;
      $data['title'] = 'Edit Employee';
      $data['client_accounts'] = $this->frontend_model->get_client_account_by_client_id($id);
      $data['employees_statuss'] = $this->client_employees_model->get_employees_statuss();
      $data['employees_categories'] = $this->client_employees_model->get_employees_categories();
      $data['employee'] = $this->client_employees_model->get_employee($id);
      $data['random_pull_test'] =$this->frontend_model->get_random_pull($data['employee']->employee_id);
      $data['random_drug_pull_test'] =$this->frontend_model->get_random_drug_pull($data['employee']->employee_id);
      $data['employee_account_types'] = $this->frontend_model->account_types();
      $data['client_accounts'] = $this->frontend_model->pull_client_account_by_client_id($client_id); 
      $data['randomdate'] = $this->frontend_model->get_eligible_random_date_for_employee($client_id);
      $data['client_account_type']=$this->frontend_model->get_client_account_by_client_id_row($client_id);
      if($this->input->get()){ 
        $data['back_link'] = base_url('dashboard/employees?account_type='.$this->input->get('account_type').'&status='.$this->input->get('status'));
      }else{
        $data['back_link'] = base_url('dashboard/employees');
      }
      if($this->input->post()){
      $data['employee'] = $this->client_employees_model->get_employee($id);
      $res = $this->client_employees_model->edit_employee($this->input->post(),$id);
      if($res){
      $this->client_employees_model->edit_employee_log($data['employee'],$this->input->post(),$client_id,$this->session->client_userID);
      if($data['employee']->employees_status=='2' && $this->input->post('employees_status')=='1'){ 
        $this->client_employees_model->employee_status_history($id,$data['employee']->inactive_date,$data['employee']->date_entered,$data['employee']->employees_status,$this->input->post('employees_status')); }
      $this->session->set_flashdata('edit_employee', true);
      } else {
      $this->session->set_flashdata('edit_employee_fail', true);
      }
       if($this->input->get()){ 
      redirect('dashboard/employees?account_type='.$this->input->get('account_type').'&status='.$this->input->get('status'));
      }else{
        redirect('dashboard/employees');
      }
      }
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/edit_employee', $data);
    }

    public function update_pending_employee($id)
    {
      $client_id = $this->session->clientID;
      $data['title'] = 'Edit Pending Employee';
      $data['client_accounts'] = $this->frontend_model->get_client_account_by_client_id($id);
      $data['employees_statuss'] = $this->client_employees_model->get_employees_statuss();
      $data['employees_categories'] = $this->client_employees_model->get_employees_categories();
      $data['employee'] = $this->client_employees_model->get_employee($id);
      $data['employee_account_types'] = $this->frontend_model->account_types();
      $data['client_accounts'] = $this->frontend_model->pull_client_account_by_client_id($client_id); 
      $data['randomdate'] = $this->frontend_model->get_eligible_random_date_for_employee($client_id); 
      $data['back_link'] = base_url('dashboard/pending_employees?account_type='.$this->input->get('account_type').'&status='.$this->input->get('status'));
      if($this->input->post()){
      $res = $this->client_employees_model->edit_employee($this->input->post(),$id);
      if($res){
      $this->client_employees_model->edit_employee_log($data['employee'],$this->input->post(),$client_id,$this->session->client_userID);
      $this->session->set_flashdata('edit_employee', true);
      } else {
      $this->session->set_flashdata('edit_employee_fail', true);
      }
      redirect('dashboard/pending_employees?account_type='.$this->input->get('account_type').'&status='.$this->input->get('status'));
      }
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/edit_pending_employee', $data);
    }


    //Resources links and documents


    public function resource_links(){
    $id = $this->session->clientID;
    $data['title'] = 'Resources Links';
    $data['get_links_category'] = $this->dashboard_model->get_link_category();   
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/resource_links', $data);
  }

    public function resource_documents(){      
      $data['title'] = 'Resources Documents';
      $data['get_docs_category'] = $this->dashboard_model->get_doc_category(); 
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/resource_documents', $data);
  }

public function view_client_employee_log_history($emp_id){ 
    $data['log_history'] =  $this->client_employees_model->get_logs_history($emp_id); 
    $data['employee']=$this->client_employees_model->get_employee_by_id($emp_id);
     if($this->input->get()){ 
        $data['back_link'] = base_url('dashboard/employees?account_type='.$this->input->get('account_type').'&status='.$this->input->get('status'));
      }else{
        $data['back_link'] = base_url('dashboard/employees');
      }
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/each_employee_log_history', $data);

    }
  

     public function edit_status($id){

        $data['title'] = 'Pending Employees';
        if($this->input->post()){
        $cl = $this->client_employees_model->edit_employee($this->input->post(),$id); 
        if($cl){
        $this->session->set_flashdata('pending_status', true);
        } else {
        $this->session->set_flashdata('pending_status_fail', true);
        }
        }
        redirect('dashboard/pending_employees');

     }

     public function employee_roster_export($account_type,$status){
         
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
                   // $this->excel->getActiveSheet()->SetCellValue('J1', 'Inactive Date');
                    //$this->excel->getActiveSheet()->SetCellValue('K1', 'Inactive Reason');
                   
                    $erow = 2;
					          $s = 1;
                    $data = $this->dashboard_model->get_client_employee_export($account_type,$status);       
      
       				foreach($data as $row) {

      			  $employees_categories = $this->client_employees_model->get_employees_categories_by_id($row->employees_category);
     					$status = $this->client_employees_model->get_employees_status_by_id($row->employees_status);
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
                       // $this->excel->getActiveSheet()->SetCellValue('J' . $erow, $row->inactive_date);
                        //$this->excel->getActiveSheet()->SetCellValue('K' . $erow, $row->reason_for_inactive);
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
                   // $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
                   // $this->excel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'employee_roster_details' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
		                redirect('dashboard/employees');
       
    }


 
    public function employee_roster_export_pdf($account_type,$status){
        
         $data['title']= 'Statement';  
        $data['employee_export'] = $this->dashboard_model->get_client_employee_export($account_type,$status);         
        $data['client']=$this->frontend_model->get_client($data['employee_export']->client_id); 
        $data['acc_type']=$account_type; $data['status']=$status;
         $filename = 'employee_roster_details of '.$data['client']->client_name .'-'.date('Y_m_d_H_i_s');                  
         $html = $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/employee_roster_export_pdf', $data, true);
         $this->generatepdf->createPDF($html, $filename, false);

      }


      public function selected_print_letter($id)
        {
          $data['title'] = 'Drug Test Details';
          $data['batch'] = $this->test_model->get_batch_by_hash($id); 
          $data['test_selected_runs'] = $this->test_model->selected_email_print_accountid($data['batch']->test_run_id,$data['batch']->client_account_id);
          $data['test_run'] = $this->test_model->test_runs_by_id($data['batch']->test_run_id);    
          $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/email_letter/selected_print', $data);      
        }


      public function not_selected_print_letter($id)
        {
          $data['title'] = 'Drug Test Details'; 
          $data['batch'] = $this->test_model->get_batch_by_hash($id);   
          $data['test_run'] = $this->test_model->test_runs_by_id($data['batch']->test_run_id);
          $data['id'] = $data['batch']->test_run_id;
  		  $data['client_accounts'] = $this->frontend_model->get_active_clients_by_pull_group_id_client_id($data['test_run']->pull_group_id,$data['batch']->client_id,$data['batch']->client_account_id);  
        //echo '<pre>'; print_r($data);exit(); 
          $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/email_letter/not_selected_emp_print', $data);      
        }


      public function no_active_emp_print_letter($id){
        
        $data['title'] = 'Drug Test Details';
        $data['batch'] = $this->test_model->get_batch_by_hash($id);   
        $data['test_run'] = $this->test_model->test_runs_by_id($data['batch']->test_run_id);
        $data['id'] = $data['batch']->test_run_id;
        $data['client_account'] = $this->frontend_model->get_active_clients_by_pull_group_id_test($data['test_run']->pull_group_id,$data['batch']->client_id,$data['batch']->client_account_id); 
        //echo '<pre>'; print_r($data);exit();   
          $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/email_letter/no_active_emp_print', $data);
          
      }


  public function email_reminder_letter($pull_group_id,$client_contact_id){
    $data['title'] = 'Reminder Letter';
    $data['pull_group_id'] = $pull_group_id;
    $data['client_contact_id'] = $client_contact_id;
    $data['email_reminder_letters'] = $this->frontend_model->get_active_client_account_by_pull_group_id($pull_group_id);
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reminder_letters/reminder_letter_email', $data);
    }


     public function assign_contact($contact_id){

        $data['title']= 'Assign Contact';
        $data['back_link'] = base_url('dashboard/client_contacts');
        $client_id=$this->session->clientID;
        if($this->input->post()){ 
        $res = $this->frontend_model->add_assign_contact($this->input->post(),$contact_id,$client_id);
        if($res){ 
        $this->session->set_flashdata('add_assign', true);
        } else {
        $this->session->set_flashdata('add_assign_fail', true);
        }
        redirect('dashboard/assign_contact/'.$contact_id); 
        }else{
        $data['client_contact']=$this->dashboard_model->get_client_contact($contact_id);
        $data['client']=$this->frontend_model->get_client($client_id);       
        $data['assign_contact'] = $this->frontend_model->get_client_contact_assign_by_contact_id_client_id($contact_id,$client_id); 
        $data['contact_id']=$contact_id;
        $data['client_accounts']=$this->frontend_model->get_all_client_account_by_client_id($client_id);
        //echo '<pre>';print_r($data);exit();
         $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/assign_contact', $data);
        }
        }
    
       public function delete_assigned_contact($id,$contact_id)
       {
       $assign_contact = $this->frontend_model->get_assign_contact_by_id($id); 
       $table='client_contact_assign';
       $res = $this->frontend_model->delete_data($table,$id);
       if($res){ 
       $this->session->set_flashdata('delete_assign', true);
       } else {
       $this->session->set_flashdata('delete_fail', true);
       }
       redirect('dashboard/assign_contact/'.$contact_id); 
       }


       public function paidinvoice() {
          $client_id=$this->session->clientID; 
          
          $userid=$this->session->client_userID;
          $data['title'] = 'Paid Invoice Lists';        
          $data['test_client_view_details'] = $this->frontend_model->get_all_paid_amt_by_client_id($client_id);

          $data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid); 
          
          $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/invoices/paidinvoice', $data);
          
      }
       public function unpaidinvoice() {       
          $client_id=$this->session->clientID; 
          // echo $client_id;
          $userid=$this->session->client_userID;
          // echo $userid; exit;
          $data['title'] = 'Unpaid Invoice List';   
          // $client_account_id = $this->frontend_model->get_client_account_by_client_id_row($client_id);  
          // $data['test_client_view_details'] = $this->frontend_model->get_unpaid_invoices_by_client_account_id($client_account_id->id);      
          // echo "<pre>";
          // print_r($data['client_account_id']) ;
          // exit; 
          $data['test_client_view_details'] = $this->frontend_model->get_all_unpaid_amt_by_client_id($client_id);         
            
                   
          $data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid); 
          $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/invoices/unpaidinvoicelist', $data);
      }


      public function invoicedetails($invoiceid){  
        // $client_id = "226";
        // $userid= "56073";
        $client_id = $this->session->clientID; 
        $userid = $this->session->client_userID;

// getting payment method list
$data['payment_methods']= $this->client_payment_model->get_client_payment_method_by_clientid($client_id);

        $data['title'] = 'Invoice Details';
          $data['invoice_detail_list'] = $this->frontend_model->get_all_invoice_detail_by_invoiceid($invoiceid);
    
          $data['invoice_list'] = $this->frontend_model->get_invoice_by_id($invoiceid);
    
          $data['client_details'] = $this->frontend_model->get_client($data['invoice_list']->clientID);
    
          $data['client_account_details'] = $this->frontend_model->get_client_account($data['invoice_list']->client_account_id);
    
            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/invoices/invoice_detail', $data);
        }


 public function mis_home(){        
      $id = $this->session->clientID;
      $client_id = $this->session->clientID; 
      $userid = $this->session->client_userID;
      $data['client'] = $this->frontend_model->get_client($id);
      $data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid);
      $data['client_accounts'] = $this->frontend_model->pull_client_account_by_client_id($id);
      $data['client_account_types']=$this->frontend_model->get_total_account_by_client_id($id);
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/mis/home', $data);
  }

 public function mis_report($id){     
      $client_id = $this->session->clientID; 
      $userid = $this->session->client_userID;
      $data['test_uploads'] = $this->test_model->test_eligible_by_client_account_id($id);
      //$data['client'] = $this->frontend_model->get_client($id);
      //$data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid);
      //$data['client_accounts'] = $this->frontend_model->pull_client_account_by_client_id($id);
      //$data['client_account_types']=$this->frontend_model->get_total_account_by_client_id($id);
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/mis/report', $data);
  }


  /*Individual PDF & Print Invoice*/

  public function pdf_invoice($invoice_id)
    {
    $data['title'] = 'Individual PDF Invoice';
    $data['invoice_id'] = $invoice_id;
    //$data['client_contact_id'] = $client_contact_id;
    $data['invoice']       = $this->frontend_model->get_invoice_by_id($invoice_id);
    $data['client_account']    = $this->frontend_model->get_client_account($data['invoice']->client_account_id);
    $data['client']            = $this->frontend_model->get_client_by_id($data['client_account']->client_id);
    //$data['client_address']    = $this->frontend_model->get_client_address_by_client_id_billing_pdf($data['client']->id);
    $data['client_contact']    = $this->frontend_model->get_contact_by_der_invoice_row($data['client']->id,$data['invoice']->client_account_id);
    
   if($data['client_account']->client_address_billing != null && $data['client_account']->client_address_billing != 0){
    $data['client_address']  = $this->test_model->get_client_address_by_id($data['client_account']->client_address_billing);    
    }elseif($data['client_contact']->client_address_main != null && $data['client_contact']->client_address_main != 0){
    $data['client_address'] = $this->test_model->get_client_address_by_id($data['client_contact']->client_address_main);                     
    }else{
    $data['client_address']  = $this->frontend_model->get_client_address_by_client_id_pdf($data['client']->id);                            
    }

    $data['invoice_details']   = $this->frontend_model->get_all_invoice_detail_by_invoiceid($invoice_id);
    $data['city']          = $this->general_model->get_city($data['client_address']->city);
    $data['state']         = $this->general_model->get_state($data['client_address']->state);
    $html = $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/pdf/individual_invoice_details_client', $data, true);
    $this->generatepdf->createPDF($html, $filename='PDF Invoice', false); 
    }

     public function print_invoice($invoice_id)
    {
      $data['title'] = 'Individual PDF Invoice';
      $data['invoice_id'] = $invoice_id;
      //$data['client_contact_id'] = $client_contact_id;
      $data['invoice']       = $this->frontend_model->get_invoice_by_id($invoice_id);
      $data['client_account']    = $this->frontend_model->get_client_account($data['invoice']->client_account_id);
      $data['client']            = $this->frontend_model->get_client_by_id($data['client_account']->client_id);
      //$data['client_address']    = $this->frontend_model->get_client_address_by_client_id_billing_pdf($data['client']->id);
      $data['client_contact']    = $this->frontend_model->get_contact_by_der_invoice_row($data['client']->id,$data['invoice']->client_account_id);

      if($data['client_account']->client_address_billing != null && $data['client_account']->client_address_billing != 0){
      $data['client_address']  = $this->test_model->get_client_address_by_id($data['client_account']->client_address_billing);    
      }elseif($data['client_contact']->client_address_main != null && $data['client_contact']->client_address_main != 0){
      $data['client_address'] = $this->test_model->get_client_address_by_id($data['client_contact']->client_address_main);                     
      }else{
      $data['client_address']  = $this->frontend_model->get_client_address_by_client_id_pdf($data['client']->id);                            
      }

      $data['invoice_details']   = $this->frontend_model->get_all_invoice_detail_by_invoiceid($invoice_id);
      $data['city']          = $this->general_model->get_city($data['client_address']->city);
      $data['state']         = $this->general_model->get_state($data['client_address']->state); 
      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/invoices/individual_invoice_details_print', $data);
      }


     public function send_email_invoice($invoice_id){

      $data['invoice']       = $this->frontend_model->get_invoice_by_id($invoice_id);
        $data['client_account']    = $this->frontend_model->get_client_account($data['invoice']->client_account_id);
          $data['client']            = $this->frontend_model->get_client_by_id($data['client_account']->client_id);  
      $this->frontend_model->send_email_invoice($invoice_id,$data['client_account']->client_id,$data['invoice']->client_account_id);        
        redirect('dashboard/unpaidinvoice');     
    }


     public function view_test_results($page=1){
     $data['title'] = 'Test Results';  
     $client_id=$this->session->clientID;  
      $userid=$this->session->client_userID;   
     $etype_num_rows = $this->dashboard_model->get_test_results_num_rows($client_id);
       // Generate pagination.
     $perPage = 15;
     // Handle pagination.
     if ($page==1) {
        $offSet = 0;
     } else {
        // $offSet = ($offSet - 1) * $perPage.
        $offSet = ($page - 1) * $perPage;
     } 
     $start_pages  = base_url() . "dashboard/view_test_results";
     $page_url  =  $start_pages.'/';
     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);
     $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);
     // end pagination
     $data['client_accounts'] = $this->dashboard_model->get_test_results_with_limit($perPage, $offSet,$client_id); 
     $data['test_type'] = $this->dashboard_model->get_list_of_test_type_not_null(); 
  //  echo '<pre>'; print_r($data);exit();
    $data['alcohol_test_result'] = $this->dashboard_model->get_alcohol_test_result();
    $data['drug_test_result'] = $this->dashboard_model->get_drug_test_result();
      $data['client_contact_assign']= $this->frontend_model->get_client_contact_assign_by_contact_id($userid);
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/tests/view_test_results', $data);
        
    }


    //payment via credit card(client)
    public function payment(){
      $class="";
      $message = "";
      $formdata = $this->input->post();
      $formdata['date_of_payment'] = date('Y-m-d');
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
      // $transId= '44022735689';
      // Table updation after successfully  payment
      if(!empty($transId)){
  
        //check if user has opt to store profile ;; for credit card
        if(isset($formdata['store_profile']) && $formdata['store_profile'] == '1' && $formdata['billing_type'] == 1){	
            $paymentmethodid = $this->store_payment_profile($transId,$formdata);

            //store payment id for client account for respective invoice id
            if($paymentmethodid !=""){
              
              // update client_account_billing_profile_id field in client_accounts table
              $this->frontend_model->update_client_account_billing_profile_id($formdata['client_account_id'], $paymentmethodid);
            }
        }
        
        // ach and store profile if choosen
        if(isset($formdata['store_profile']) && $formdata['store_profile'] == '1' && $formdata['billing_type'] == 2){	
          $paymentmethodid = $this->store_payment_profile_for_ach($formdata);
          
          // update client_account_billing_profile_id field in client_accounts table
          $this->frontend_model->update_client_account_billing_profile_id($formdata['client_account_id'], $paymentmethodid);

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
        redirect('dashboard/invoicedetails/'.$formdata['invoice_id'].'?message='.$message."&class=".$class); 
      }
	
    function paymentviastored(){
      
      include APPPATH.'libraries/Authnetcim.php';
  
      $formdata  = $this->input->post();
      
      $paymentmethodDetails = $this->client_payment_model->get_payment_method($formdata['paymentmthodid']);
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
          if( $cim->isSuccessful() ) {
            
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
            $payment['creator_contact_id'] = $this->session->client_userID;;
            $payment['auth_payment_response'] = $paymentResponse;
            $invoice_pay_id = $this->frontend_model->apply_payment($payment);
    
            if(!empty($invoice_pay_id)){
    
              // updating invoice table
              $status = $close = 0;
              $invoiceid = $formdata['invoice_id'];
              $invoiceAmt = $this->frontend_model->get_amount_by_id($invoiceid);
              $invoiceAmt = $invoiceAmt->amount;
              $totalpaid = $this->get_total_invoice_paymentitem_amount($invoiceid);
              if($totalpaid >= $invoiceAmt ){
                $status =  1;
                $close =  1;
              }else{
                $status =  0;
                $close =  0;
              }
              $this->frontend_model->update_invoice_on_addpayment($status,$close,$totalpaid,$invoiceid);
    
            }
            $class = 'success';
            redirect('dashboard/invoicedetails/'.$invoiceid."?message=success&class=".$class); 
          }
          else
          {
            $class = 'error';
            $message = $cim->getResponse();
            $formdata['failed_msg'] = $message;
            $id = $this->track_failed_payment($formdata);
            redirect('dashboard/invoicedetails/'.$formdata['invoice_id']."?message=".$message."&class=".$class); 
          }
        }
        $message = 'No Payment Profile ID found';
        $class = 'error';
        $formdata['failed_msg'] = $message;
        $id = $this->track_failed_payment($formdata);
        redirect('dashboard/invoicedetails/'.$formdata['invoice_id']."?message=".$message."&class=".$class); 
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
			$clientcontact = $this->dashboard_model->get_client_contact($id);
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
			$pay_method_id = $this->client_payment_model->store_customer_payment_profile($formdata,$clientcontact);			
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
      $payment['creator_contact_id'] = $this->session->client_userID;
      $payment['auth_payment_response'] = $responsecode;	
      $invoice_pay_id = $this->frontend_model->apply_payment($payment);
      return $invoice_pay_id;
    }

	public function update_invoice_table_on_payment_transaction($formdata){

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
		// if($totalpaid == $totalDue ){
		// 	$status =  1;
		// 	$close =  1;
		// }

		// $this->frontend_model->update_invoice_on_addpayment($status,$close,$totalpaid,$invoiceid);

    $status = $close = 0;
					$invoiceid = $formdata['invoice_id'];
					$invoiceAmt = $this->frontend_model->get_amount_by_id($invoiceid);
					$invoiceAmt = $invoiceAmt->amount;
					$totalpaid = $this->get_total_invoice_paymentitem_amount($invoiceid);
					if($totalpaid >= $invoiceAmt ){
						$status =  1;
						$close =  1;
					}else{
						$status =  0;
						$close =  0;
					}
					$this->frontend_model->update_invoice_on_addpayment($status,$close,$totalpaid,$invoiceid);
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
      return $dataArr;
		}else{
			$message = $this->authorize_net->getError();
			$class ='error';
			$formdata['failed_msg'] = $message;
			$id = $this->track_failed_payment($formdata);
			redirect('dashboard/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class);
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
			$desc = 'Online '.$type.' Payment (Stored Profile) - '.$date.' - ['.$card.'] - ['.$user.']';
		}else{
			$desc = 'Online '.$type.' Payment - '.$date.' - ['.$card.'] - ['.$user.']';
		}

    return $desc;
	}


  public function export_view_test_results() {  
   
     // echo '<pre>';print_r($client_accounts);exit();      

         $data['title']= 'Test Results';
          //print_r($values);
          $this->load->library('excel');
          $this->excel->setActiveSheetIndex(0);
          $this->excel->getActiveSheet()->setTitle('Test_results');                    
                    $this->excel->getActiveSheet()->SetCellValue('A1', 'Name');
                    $this->excel->getActiveSheet()->SetCellValue('B1', 'Category');
                    $this->excel->getActiveSheet()->SetCellValue('C1', 'Employee ID');                       
                    $this->excel->getActiveSheet()->SetCellValue('D1', 'Account Reference');
                    $this->excel->getActiveSheet()->SetCellValue('E1', 'Test Type');
                    $this->excel->getActiveSheet()->SetCellValue('F1', 'Test Reason'); 
                    $this->excel->getActiveSheet()->SetCellValue('G1', 'Drug Result'); 
                    $this->excel->getActiveSheet()->SetCellValue('H1', 'Alcohol Result'); 
                    $this->excel->getActiveSheet()->SetCellValue('I1', 'Specimen ID');  ; 
                    $this->excel->getActiveSheet()->SetCellValue('J1', 'Date');  
                   
                    $erow = 2;
                    $s = 1;  
                    $client_id=$this->session->clientID;  
      $client_accounts = $this->dashboard_model->get_test_results_for_export($client_id); 
      $test_type = $this->dashboard_model->get_list_of_test_type_not_null();  
      $client_contact_assign = $this->frontend_model->pull_client_account_by_client_id($client_id); 
      
               
                foreach ($client_accounts as $client_account) {
               $account = $this->frontend_model->get_client_account($client_account->client_account_id);
              $test_type = $this->test_model->get_test_type_by_id_row($client_account->test_type_id);
              $drug_result=$this->dashboard_model->get_drug_test_result_by_id($client_account->drug_test_result);
              $alcohol_result=$this->dashboard_model->get_alcohol_test_result_by_id($client_account->alcohol_test_result);
              $employee_cat=$this->client_employees_model->get_employees_categories_by_id($client_account->employee_cat_id);  
 
      $this->excel->getActiveSheet()->SetCellValue('A' . $erow, $client_account->last_name.', '.$client_account->first_name);
                       
      $this->excel->getActiveSheet()->SetCellValue('B' . $erow, $employee_cat->title);
      $this->excel->getActiveSheet()->SetCellValue('C' . $erow, '...'.substr($client_account->emp_Id, -5));
      $this->excel->getActiveSheet()->SetCellValue('D' . $erow, $account->account_reference.'-'. $account->id);
      $this->excel->getActiveSheet()->SetCellValue('E' . $erow, $test_type->title);
       
                        if($client_account->reason_for_test == 1){
                        $this->excel->getActiveSheet()->SetCellValue('F' . $erow, 'Drug');
                        }elseif($client_account->reason_for_test == 0){                      
                        $this->excel->getActiveSheet()->SetCellValue('F' . $erow, 'Alcohol');
                        }elseif($client_account->reason_for_test == 2){                      
                        $this->excel->getActiveSheet()->SetCellValue('F' . $erow, 'Both');
                        }elseif($client_account->reason_for_test == 3){                      
                        $this->excel->getActiveSheet()->SetCellValue('F' . $erow, 'Not Indicated');
                        }


      $this->excel->getActiveSheet()->SetCellValue('G' . $erow, $drug_result->description);
      $this->excel->getActiveSheet()->SetCellValue('H' . $erow, $alcohol_result->description);
      $this->excel->getActiveSheet()->SetCellValue('I' . $erow, $client_account->CCF);
      $this->excel->getActiveSheet()->SetCellValue('J' . $erow, $this->tag_general->us_date($client_account->date_taken)); 

                        $s++;$erow++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                    $this->excel->getActiveSheet()->getColumnDimension('F')->setWidth(10); 
                    $this->excel->getActiveSheet()->getColumnDimension('G')->setWidth(40); 
                    $this->excel->getActiveSheet()->getColumnDimension('H')->setWidth(40); 
                    $this->excel->getActiveSheet()->getColumnDimension('I')->setWidth(30); 
                    $this->excel->getActiveSheet()->getColumnDimension('J')->setWidth(30); 
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $filename = 'TagAMS-Test-Results' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                   if($this->input->get()){ 
            redirect('dashboard/export_view_test_results?account_type='.$this->input->get('account_type').'&type='.$this->input->get('type').'&reason='.$this->input->get('reason').'&year='.$this->input->get('year') ); 
             }else{ 
            redirect('dashboard/export_view_test_results/');
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
      if ($cim->isSuccessful()) {
        $transid =  $cim->getTransId();
			  $paymentResponse = $cim->getPaymentResponse();
			  $dataArr = [
				'transid'=>$transid,
				'paymentResponse'=>$paymentResponse
			  ];
			  return $dataArr;
      } else {
        
        $message = $cim->getResponse();
        $class = 'error';
        $formdata['failed_msg'] = $message;
        $id = $this->track_failed_payment($formdata);
        redirect('dashboard/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class); 
      }
    }


    public function store_payment_profile_for_ach($formdata){
		
      $profile_id = '';
      $payment_id = '';

      $formdata['customer_profile_id'] = "";
      $formdata['payment_profile_id'] = "";

      $id = $this->session->client_userID;
      
      $clientcontact = $this->dashboard_model->get_client_contact($id);
      $payment_id = $this->client_payment_model->store_customer_payment_profile($formdata,$clientcontact);

        if($payment_id){
          // include APPPATH.'libraries/Authnetcim.php';
          $cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);

          $email_address = $formdata['paydata_email'];
          $description   = 'Billing Profile';
          $customer_id   = 'tagams-'.$payment_id;
  
          // Create the profile
          $cim->setParameter('email', $email_address);
          $cim->setParameter('description', $description);
          $cim->setParameter('merchantCustomerId', $customer_id);
          $cim->createCustomerProfile();
          
          if ($cim->isSuccessful()){
            $profile_id = $cim->getProfileID();
            $this->client_payment_model->update_profile_authorise_id($payment_id,$profile_id);
          }

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
             
          if ($cim->isSuccessful()) {
            $payment_profile_id = $cim->getPaymentProfileId();
            $this->client_payment_model->update_payment_authorise_id($payment_id,$payment_profile_id);
            $this->session->set_flashdata('payment_method_sucess', true);
          } else {
            $cim->setParameter('customerProfileId', $profile_id);
            $cim->deleteCustomerProfile();
            $this->client_payment_model->delete_payment_m($payment_id);
            $this->session->set_flashdata('bank_payment_method_fail', true);
          }
        }      
        return $payment_id;
    }

    function track_failed_payment($formdata) {
		
		if($formdata['user_type'] == 'admin' ){
			$formdata['admin_user_id'] = $this->session->userID;	
		}else{
			$id = $this->session->client_userID;
			$clientcontact = $this->dashboard_model->get_client_contact($id);
			$formdata['client_contact_id'] = $clientcontact->id;
		}
		$id = $this->client_paymentTracking_model->track_failed_payment($formdata);
		return $id;
		
	}

  function get_total_invoice_paymentitem_amount($invoiceid){
		$lineitems = $this->frontend_model->get_all_paid_amt($invoiceid);
		$total_Invoice_Amt = 0.0;
		if(!empty($lineitems)){
			foreach ($lineitems as $lineitem) {
				$total_Invoice_Amt = ($total_Invoice_Amt)+($lineitem->payment_amount);
			}
		}
		return $total_Invoice_Amt;
	}
  

public function checkEmployeeMatches()
{ 
//print_r($_POST);exit();
?>
<script>
    $('#employeeMatch').DataTable({
    responsive: true,
    language: {
    searchPlaceholder: 'Search Records',
    sSearch: '',
    lengthMenu: '_MENU_',
    }
    });
</script>
    <?php
    $last_name = $this->input->post('last_name'); 
    $name = $this->input->post('first_name');
    $client_account_id = $this->session->clientID;
    $fname=substr($name,0,1); 
    if($fname!=''){
        $firstname=$fname;
    }elseif($fname==''){
        $firstname=substr($name,0,3);
    }
    if($last_name!=''){
    $lname=strlen($last_name); 
    if($lname=='4'){
        $lastName=substr($last_name,0,4); 
    }elseif($lname>4){
        $lastName=$last_name; 
    } 
    }
    $employees = $this->client_employees_model->get_matched_employees($firstname,$lastName,$client_account_id);
  //  print_r($employees);exit();
    if($lastName!='' ){
    $query=  '<div class="table-responsive">
                <table class="table text-md-nowrap" id="employeeMatch">
                <div class="row">
                <div class="col-sm-12 col-md-6" style="margin-bottom:-3px;">
                <label><strong>Records Per Page</strong></label>
                </div>
               <div class="col-sm-12 col-md-6 text-right" style="margin-bottom:-3px;">
               <label><strong>Search Records</strong></label>
               </div>
               </div>
               <thead>
               <tr>
               <th class="wd-15p border-bottom-0">First Name</th>
               <th class="wd-15p border-bottom-0">Last Name</th>
               <th class="wd-15p border-bottom-0">Employee ID</th>
               <th class="wd-15p border-bottom-0">Status</th>
               <th class="wd-15p border-bottom-0">Date Entered</th>
               <th class="wd-10p border-bottom-0">Action</th>
               </tr>
               </thead>
               <tbody>';
            foreach($employees as $employee){ 
            $status = $this->client_employees_model->get_employees_status_by_id($employee->employees_status);
    $query .=  '<tr>
               <td>'.$employee->first_name.'</td>
               <td>'.$employee->last_name.'</td>
               <td>'.$employee->employee_id.'</td>
               <td>'.$status->title.'</td>                             
               <td>'.$this->tag_general->us_date($employee->date_entered).'</td>
               <td><a class="btn ripple btn-primary btn-sm" href='.base_url("dashboard/edit_employee/".$employee->id."").' data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Employee"><i class="fas fa-search"></i></a></td>
               </tr>'; }
    $query .=  '</tbody>
               </table>
               </div> ';
           } 
    echo $query;
}


public function checkEmployeeIDMatches(){
?>
    <script>
        $('#employe').DataTable({
        responsive: true,
        language: {
        searchPlaceholder: 'Search Records',
        sSearch: '',
        lengthMenu: '_MENU_',
        }

    });

    </script>

    <?php
    $employee_id = $this->input->post('employee_id'); 
    $client_account_id = $this->input->post('client_account_id');
    $employees = $this->client_employees_model->get_employees_id_match($employee_id,$client_account_id);
  //  print_r($employees);exit();
    if($employee_id!=''){
    $query=  '<div class="table-responsive">
                <table class="table text-md-nowrap" id="employe">
                <div class="row">
                <div class="col-sm-12 col-md-6" style="margin-bottom:-3px;">
                <label><strong>Records Per Page</strong></label>
                </div>
               <div class="col-sm-12 col-md-6 text-right" style="margin-bottom:-3px;">
               <label><strong>Search Records</strong></label>
               </div>
               </div>
               <thead>
               <tr>
           <th class="wd-15p border-bottom-0">First Name</th>
               <th class="wd-15p border-bottom-0">Last Name</th>
               <th class="wd-15p border-bottom-0">Employee ID</th>
               <th class="wd-15p border-bottom-0">Status</th>
               <th class="wd-15p border-bottom-0">Date Entered</th>
               <th class="wd-10p border-bottom-0">Action</th>
               </tr>
               </thead>
               <tbody>';
            foreach($employees as $employee){ 
            $status = $this->client_employees_model->get_employees_status_by_id($employee->employees_status);
            $query .=  '<tr>
           <td>'.$employee->first_name.'</td>
               <td>'.$employee->last_name.'</td>
               <td>'.$employee->employee_id.'</td>
               <td>'.$status->title.'</td>                             
               <td>'.$this->tag_general->us_date($employee->date_entered).'</td>
               <td><a class="btn ripple btn-primary btn-sm" href='.base_url("dashboard/edit_employee/".$employee->id."").' data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Employee"><i class="fas fa-search"></i></a></td>

               </tr>'; }
    $query .=  '</tbody>
               </table>
               </div> ';
           } 
        echo $query;
}





     public function checkEmployeeMatchesForValidation()

    {  
    $last_name = $this->input->post('last_name'); 
    $name = $this->input->post('first_name');
    $client_account_id = $this->input->post('client_account_id');
    $fname=substr($name,0,1); 
    if($fname!=''){
    $firstname=$fname;
    }elseif($fname==''){
    $firstname=substr($name,0,3);
    }
    if($last_name!=''){
    $lname=strlen($last_name); 
    if($lname=='4'){
    $lastName=substr($last_name,0,4); 
    }elseif($lname>4){
    $lastName=$last_name; 
    } 
    }
    if($lastName!='' ){
    $employees = $this->client_employees_model->get_matched_employees_num_rows($firstname,$lastName,$this->input->post('client_account_id'));
    }  
    if($employees>0){
    echo ($employees);
    }else{
        return false;
    }
    }



     public function checkEmployeeMatchesIDForValidation()
    { 

    $employee_id = $this->input->post('employee_id'); 
    $client_account_id = $this->input->post('client_account_id');
    if($employee_id!='' ){
    $employees = $this->client_employees_model->get_employees_id_match_num_rows($employee_id,$client_account_id);
    } 
    if($employees>0){
    echo ($employees);
    }else{
        return false;
    }
    }

     

    public function add_alcohol_test($emp_id){ 
    $data['log_history'] =  $this->client_employees_model->get_logs_history($emp_id); 
    $data['employee']=$this->client_employees_model->get_employee_by_id($emp_id);
    $data['client_account']=$this->client_employees_model->get_client_account_id($data['employee']->client_account_id);
    $data['alcohol_result']=$this->dashboard_model->get_alcohol_test_result_show_to_client();
    $data['test_type']=$this->dashboard_model->get_list_of_test_type_not_null();
     if($this->input->get()){ 
        $data['back_link'] = base_url('dashboard/employees?account_type='.$this->input->get('account_type').'&status='.$this->input->get('status'));
      }else{
        $data['back_link'] = base_url('dashboard/employees');
      }
    // echo '<pre>';print_r($data);exit();
    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/frontend/add_alcohol_test', $data);

    }
  

    public function insert_alcohol_test_record(){

          $cont=$this->dashboard_model->check_in_test_upload_table($this->input->post('employee_id'));
          $config['upload_path'] = './upload/alcohol_test_result_documents/';
          $config['allowed_types'] = 'gif|jpg|jpeg|JPG|png|PNG|JPEG';
          $config['max_size'] = 5120;
          $this->load->library('upload', $config);
          $dataInfo = array();
          $files = $_FILES;
          if (!empty($files['file']['name'])) {
             $secure_directory =  './upload/alcohol_test_result_documents/';
                $upload_file = $secure_directory;
                if (!is_dir($upload_file)) {
                    mkdir("./" . $upload_file, 0777);
                }
            $_FILES['file']['name']; 
                if (!empty($files['file']['name'])) {
                    $_FILES['file']['name'] = $files['file']['name'];
                    $_FILES['file']['type'] = $files['file']['type'];
                    $_FILES['file']['tmp_name'] = $files['file']['tmp_name'];
                    $_FILES['file']['error'] = $files['file']['error'];
                    $_FILES['file']['size'] = $files['file']['size'];
                    $this->upload->initialize($config);
                    $this->upload->do_upload('file');
                    $dataInfo = $this->upload->data();                 
                }            
           }
          if($cont>0){  
           $employee=$this->client_employees_model->get_employee_by_id($this->input->post('employee_id'));
           $query= '
          <input type="hidden" name="file" value="upload/alcohol_test_result_documents/'.$dataInfo['file_name'].'">
          <input type="hidden" name="employee_id" value='.$employee->id.' >
          <input type="hidden" name="client_id" value='.$employee->client_id.' >
          <input type="hidden" name="client_account_id" value='.$employee->client_account_id.'>
          <input type="hidden" name="emp_cat" value='.$employee->employees_category.' >
          <input type="hidden" name="first_name" value='. $employee->first_name.'>
          <input type="hidden" name="last_name" value='.$employee->last_name.'>
          <input type="hidden" name="emp_id" value='.$employee->employee_id.'>
          <input type="hidden" name="create_type" value="1"> 
          <input type="hidden" name="reason_for_test" value="0">
          <input type="hidden" name="prepared_by" value='.$this->session->client_userID.' > 
          <input type="hidden" name="date_taken" value='.$this->input->post('date_taken').'> 
          <input type="hidden" name="alcohol_result" value='.$this->input->post('alcohol_result').'> 
          <input type="hidden" name="test_type" value='.$this->input->post('test_type').'> 
          <input type="hidden" name="alcohol_value" value='.$this->input->post('alcohol_value').'> 
          <input type="hidden" name="random_selection" value='.$this->input->post('random_selection').'> 


               

           <div class="table-responsive">
                <table class="table text-md-nowrap" id="employe">
                 
               <thead>
               <tr>
                <th class="wd-20p border-bottom-0">First Name</th>
               <th class="wd-20p border-bottom-0">Last Name</th>
               <th class="wd-15p border-bottom-0">Specimen ID</th>
               <th class="wd-25p border-bottom-0">Alcohol Test Result</th>
               <th class="wd-20p border-bottom-0">Date Taken</th> 
               </tr>
               </thead>
               <tbody>'; 

        $content=$this->dashboard_model->check_in_test_upload_table_result($this->input->post('employee_id'));
        foreach($content as $employee){ 
        $alcohol=$this->dashboard_model->get_alcohol_test_result_by_id($data->alcohol_test_result);
            $query .=  '<tr>
           <td>'.$employee->first_name.'</td>
               <td>'.$employee->last_name.'</td>
               <td>'.$employee->CCF.'</td>
               <td>'.$alcohol->description.'</td>                                 
               <td>'.$this->tag_general->us_date($employee->date_taken).'</td>

               </tr>'; }
        $query .=  '</tbody>
               </table>
               </div> ';

        echo $query;

        }else{
           $config['upload_path'] = './upload/alcohol_test_result_documents/';
          $config['allowed_types'] = 'gif|jpg|jpeg|JPG|png|PNG|JPEG|pdf|doc|docx|txt|xls|xlsx|ppt|pptx';
          $config['max_size'] = 5120;
          $this->load->library('upload', $config);
          $dataInfo = array();
          $files = $_FILES;
          if (!empty($files['file']['name'])) {
             $secure_directory =  './upload/alcohol_test_result_documents/';
                $upload_file = $secure_directory;
                if (!is_dir($upload_file)) {
                    mkdir("./" . $upload_file, 0777);
                }
            $_FILES['file']['name']; 
                if (!empty($files['file']['name'])) {
                    $_FILES['file']['name'] = $files['file']['name'];
                    $_FILES['file']['type'] = $files['file']['type'];
                    $_FILES['file']['tmp_name'] = $files['file']['tmp_name'];
                    $_FILES['file']['error'] = $files['file']['error'];
                    $_FILES['file']['size'] = $files['file']['size'];
                    $this->upload->initialize($config);
                    $this->upload->do_upload('file');
                    $dataInfo = $this->upload->data();                 
                }            
           }
    $image_name='upload/alcohol_test_result_documents/'.$dataInfo['file_name']; 
    $returndata=$this->dashboard_model->insert_alcohol_data_in_test_upload_table($this->input->post(),$image_name); 
        echo $returndata; 
      }
      }

    public function add_alcohol_data(){ 
      $this->dashboard_model->insert_alcohol_data_in_test_upload_table($this->input->post());
      redirect('dashboard/employees');
    }

} // Class end.
