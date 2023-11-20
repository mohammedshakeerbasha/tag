<?php

defined('BASEPATH') OR exit('No direct script access allowed');











class Employees extends MY_Controller

{



	function __construct()

    {

        parent::__construct();

		$this->config->load('modules/auth/config');

		$this->load->model('clients_model');

		$this->load->model('employees_model');

		$this->load->model('general_model');

		$this->load->model('email_model');

		$this->load->library('common/user');

		$this->load->library('common/paginator'); 

		if (!$this->session->userID):

		redirect('auth');

		endif;

		

    }

	

	public function index(){

    	

    	$data['title'] = 'Employees';

        if($this->input->get()){

        // $etype_num_rows = $this->employees_model->get_employees_num_rows();

    	//      // Generate pagination.

        // $perPage = 10;

        // // Handle pagination.

        // if ($page==1) {

        //     $offSet = 0;

        // }else{

        //     // $offSet = ($offSet - 1) * $perPage.

        //     $offSet = ($page - 1) * $perPage;

        // }

        // $FullURL = explode('?',$_SERVER['REQUEST_URI']);

        // $start_page =  site_url('employees/index?'.$FullURL[1]);

        // $start_pages  = site_url('employees/index');

        // $page_url  =  $start_pages.'/';

        // $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$etype_num_rows,$perPage,3);

        // $data['paginationInfo'] = $this->paginator->newpaginationInfo($etype_num_rows,$perPage);

         // end pagination

        $data['employees'] = $this->employees_model->pull_employee();

		$data['clients'] = $this->clients_model->get_clients();

		$data['client_accounts'] = $this->clients_model->get_client_accounts();

        $data['employees_statuss'] = $this->employees_model->get_employees_statuss();

        $data['employees_categories'] = $this->employees_model->get_employees_categories();

        //print_r($data);exit();

        } else {

        $data['employees']='';

        $data['clients'] = $this->clients_model->get_clients();

        $data['client_accounts'] = $this->clients_model->get_client_accounts();

        $data['employees_statuss'] = $this->employees_model->get_employees_statuss();

        $data['employees_categories'] = $this->employees_model->get_employees_categories();

        }

    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/home', $data);

        

	}







    /**

     * App home.

     */

    public function add_employee()

    {

    	$data['title'] = 'Add Employee';

        $link='?name='.$this->input->get('name').'&email='.$this->input->get('email').'&phone='.$this->input->get('phone').'&employee_id='.$this->input->get('employee_id').'&client_id='.$this->input->get('client_id').'&account='.$this->input->get('account').'&employees_status='.$this->input->get('employees_status').'&employees_category='.$this->input->get('employees_category');



        $data['back_link'] = base_url('employees'.$link);

    	if($this->input->post()){

    	$this->employees_model->add_employee($this->input->post());

    	$this->session->set_flashdata('add_employee', true);       



        redirect('employees'.$link);

    	}else{

        $data['employees'] = $this->employees_model->get_employees_by_client_id($this->input->get('client_id'));

        $data['client_accounts'] = $this->clients_model->get_client_account_by_client_id($this->input->get('client_id'));

        $data['old_client_accounts'] = $this->clients_model->get_client_account_by_client_id($this->input->get('client_id'));

        $data['employees_statuss'] = $this->employees_model->get_employees_statuss();

        $data['employees_categories'] = $this->employees_model->get_employees_categories();

        $data['account_types'] = $this->general_model->account_types(); 

        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/add_employee', $data);

        }

    } 

    

    /**

     * App home.

     */

    public function edit_employee($id)

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

        $link='?name='.$this->input->get('name').'&email='.$this->input->get('email').'&phone='.$this->input->get('phone').'&employee_id='.$this->input->get('employee_id').'&client_id='.$this->input->get('client_id').'&account='.$this->input->get('account').'&employees_status='.$this->input->get('employees_status').'&employees_category='.$this->input->get('employees_category');

        redirect('employees'.$link);

        }else{

        $data['employee'] = $this->employees_model->get_employee($id);   

        $data['random_pull_test'] =$this->clients_model->get_random_pull($data['employee']->employee_id);

        $data['random_drug_pull_test'] =$this->clients_model->get_random_drug_pull($data['employee']->employee_id); 

        $data['randomdate'] = $this->general_model->get_eligible_random_date_for_employee($data['employee']->client_id);   

        $data['clients'] = $this->clients_model->get_clients();

        $data['client_accounts'] = $this->clients_model->get_client_account_by_client_id($this->input->get('client_id'));

        $data['employees_statuss'] = $this->employees_model->get_employees_statuss();

        $data['employees_categories'] = $this->employees_model->get_employees_categories();

        $link='?name='.$this->input->get('name').'&email='.$this->input->get('email').'&phone='.$this->input->get('phone').'&employee_id='.$this->input->get('employee_id').'&client_id='.$this->input->get('client_id').'&account='.$this->input->get('account').'&employees_status='.$this->input->get('employees_status').'&employees_category='.$this->input->get('employees_category');

        $data['back_link'] = base_url('employees'.$link);

        $data['get_link'] = $link;

        

        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/edit_employee', $data);

        }

    }



    public function view_log_history(){ 

       

      $emp_id=$this->uri->segment(3); 

      $data['title'] = 'View Log History';

        // end pagination

      if($this->input->get('account_type')){

      $employee_details=$this->employees_model->get_employee_by_id($emp_id);

        $link=$employee_details->client_id.'?status='.$this->input->get('status').'&account_type='.$this->input->get('account_type');



        $data['back_link'] = base_url('clients/employee_roster/'.$link);

      }else{

         $link='?name='.$this->input->get('name').'&email='.$this->input->get('email').'&phone='.$this->input->get('phone').'&employee_id='.$this->input->get('employee_id').'&client_id='.$this->input->get('client_id').'&account='.$this->input->get('account').'&employees_status='.$this->input->get('employees_status').'&employees_category='.$this->input->get('employees_category');



        $data['back_link'] = base_url('employees'.$link);

      }

      $data['log_history'] =  $this->employees_model->get_logs_history($emp_id); 

      $data['status_history'] =  $this->employees_model->get_status_history($emp_id);

     $data['employees_statuss'] = $this->employees_model->get_employees_statuss();

      $data['employee']=$this->employees_model->get_employee_by_id($emp_id);

      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/log_history', $data);





    }



    public function pending_employees($page=1){

    

     $data['title'] = 'Pending Employees'; 

     if($this->input->get()){

     $project_num_rows = $this->employees_model->get_pending_employees_num_rows_for_admin();

        // Generate pagination.

     $perPage = 10;

        // Handle pagination.

     if ($page==1) {

     $offSet = 0;

     } else {

     // $offSet = ($offSet - 1) * $perPage.

     $offSet = ($page - 1) * $perPage;

     }

     $start_page = base_url() . "/employees/pending_employees";

     $page_url  = $start_page.'/';  

     $data['pagination'] = $this->paginator->newpagination($start_page,$page_url,$project_num_rows,$perPage,3);

     $data['paginationInfo'] = $this->paginator->newpaginationInfo($project_num_rows,$perPage);

     // end pagination

     $data['pending_employees'] =  $this->employees_model->get_pending_employees_for_admin($perPage, $offSet); 

     $data['clients'] = $this->clients_model->get_clients();

     $data['client_accounts'] = $this->clients_model->get_client_accounts();

     $data['employees_statuss'] = $this->employees_model->get_employees_statuss();

     $data['employees_categories'] = $this->employees_model->get_employees_categories(); 

     }else{

     $data['pending_employees'] =  ''; 

     $data['clients'] = $this->clients_model->get_clients();

     $data['client_accounts'] = $this->clients_model->get_client_accounts();

     $data['employees_statuss'] = $this->employees_model->get_employees_statuss();

     $data['employees_categories'] = $this->employees_model->get_employees_categories();

     }

     $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/pending_employees', $data);

        

    }



    public function edit_status($id){

      $data['title'] = 'Pending Employees';

      if($this->input->post()){

      $cl = $this->employees_model->edit_employee($this->input->post(),$id); 

      if($cl){

      $this->session->set_flashdata('pending_status', true);

      } else {

      $this->session->set_flashdata('pending_status_fail', true);

      }

      }

      redirect('employees/pending_employees');



    }



     public function update_pending_employee($id)

    {

      $client_id = $this->session->clientID;

      $data['title'] = 'Edit Pending Employee'; 





      $data['client_accounts'] = $this->clients_model->get_client_account_by_client_id($id);

      $data['employees_statuss'] = $this->employees_model->get_employees_statuss();

      $data['employees_categories'] = $this->employees_model->get_employees_categories();

      $data['employee'] = $this->employees_model->get_employee($id); 

      $data['clients'] = $this->clients_model->get_clients(); 

      $data['employee_account_types'] = $this->general_model->account_types();

      $data['client_accounts'] = $this->clients_model->pull_client_account_by_client_id($this->input->get('client_id')); 

      $data['randomdate'] = $this->general_model->get_eligible_random_date_for_employee($data['employee']->client_id);

      $data['back_link'] = base_url('employees/pending_employees?client_id='.$this->input->get('client_id').'&status='.$this->input->get('status'));

      if($this->input->post()){

      $res = $this->employees_model->edit_employee($this->input->post(),$id);

      if($res){

      $this->employees_model->edit_employee_log($data['employee'],$this->input->post(),$client_id,$this->session->client_userID);

      $this->session->set_flashdata('edit_employee', true);

      } else {

      $this->session->set_flashdata('edit_employee_fail', true);

      }

      redirect('employees/pending_employees?client_id='.$this->input->get('client_id').'&status='.$this->input->get('status'));

        }



      $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/employees/edit_pending_employees', $data);

      }





      public function delete_employee($emp_id){



        $res=$this->employees_model->delete_employee($emp_id);

        if($res){

         $link='?name='.$this->input->get('name').'&email='.$this->input->get('email').'&phone='.$this->input->get('phone').'&employee_id='.$this->input->get('employee_id').'&client_id='.$this->input->get('client_id').'&account='.$this->input->get('account').'&employees_status='.$this->input->get('employees_status').'&employees_category='.$this->input->get('employees_category');

        redirect('employees'.$link);

        }





      }




} // Class end.

