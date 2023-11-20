<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Reports extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('employees_model');
		$this->load->model('general_model');
		$this->load->model('reports_model');
		//$this->load->model('email_model');
		$this->load->library('common/user');
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


    /**
     * App home.
     */
    public function client_report()
    {
    	$data['title'] = 'Client Reports';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/client_report', $data);
        
    }
    
    public function awsi_report()
    {
    	$data['title'] = 'AWSI Reports';
    	$data['awsi_reports'] = $this->reports_model->awsi_reports();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/awsi_report', $data);
        
    }
    
    public function awsi_report_detail($id)
    {
    	$data['title'] = 'AWSI Reports';
    	$emp = $this->reports_model->check_employees($id);
    	$data['awsi_report'] = $this->reports_model->get_awsi_report($id);
    	$data['awsi_details'] = $this->reports_model->get_awsi_details($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/awsi_report_details', $data);
    }
    
    public function escreen_report()
    {
    	$data['title'] = 'eScreen Reports';
    	$data['escreen_reports'] = $this->reports_model->escreen_reports();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/escreen_report', $data);
    }
    
     public function escreen_report_detail($id)
    { 
    	$data['title'] = 'eScreen Reports';
    	$data['escreen_report'] = $this->reports_model->get_escreen_report($id);
    	$data['escreen_details'] = $this->reports_model->get_escreen_details($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/escreen_report_details', $data);
    }
    
    public function crl_report()
    {
    	$data['title'] = 'CRL & Quest Reports';
    	$data['crl_reports'] = $this->reports_model->crlquest_reports();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/crl_report', $data);
        
    }
    
    public function crlquest_report_detail($id)
    {
    	$data['title'] = 'CRL Quest Reports';
    	$data['crlquest_report'] = $this->reports_model->get_crlquest_report($id);
    	$data['crlquest_details'] = $this->reports_model->get_crlquest_details($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/crl_report_details', $data);
    }
    /**
     * App home.
     */
    public function drug_test()
    {
    	$data['title'] = 'Drug Test';
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/drug_test', $data);
        
    }

     public function escreen_api_report()
    {
    	$data['title'] = 'eScreen Api Reports';
    	$data['escreen_reports'] = $this->reports_model->escreen_api_reports();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/escreen_api_report', $data);
    }

     public function escreen_api_report_detail($id)
    {
    	$data['title'] = 'eScreen Api Reports';
    	$data['escreen_report'] = $this->reports_model->get_escreen_api_report($id);
    	$data['escreen_details'] = $this->reports_model->get_escreen_api_details($id);
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/reports/escreen_api_report_details', $data);
    }
    
    public function escreen_export(){
    
         $filename =  'escreen_report'.date('Y-m-d').".xls";
       		// Download file
       		
			header("Content-Disposition: attachment; filename=\"$filename\"");
			header("Content-Type: application/vnd.ms-excel");
			
			 //echo 'S No' . "\t";
			 
			 
  			
				echo 'ID' . "\t";
				echo 'Confirmed' . "\t";
				echo 'escreenID' . "\t";
				echo 'Disposition' . "\t";
				echo 'Confirmation Number' . "\t";
				echo 'Dilute' . "\t";
				echo 'Escreen client account' . "\t";
				echo 'Escreen client sub account' . "\t";
				echo 'Cost Center' . "\t";
				echo 'Lab Name' . "\t";
				echo 'Lab Account' . "\t";
				echo 'Client name' . "\t";
				echo 'Location' . "\t";
				echo 'Collection Site' . "\t";
				echo 'Collection Network' . "\t";
				echo 'Collection Phone' . "\t";
				echo 'Accession num' . "\t";
				echo 'Donor Name' . "\t";
				echo 'SSN' . "\t";
				echo 'Other ID' . "\t";
				echo 'Other ID Type' . "\t";
				echo 'DOB' . "\t";
				echo 'Home Phone' . "\t";
				echo 'Work Phone' . "\t";
				echo 'Chain Custody' . "\t";
				echo 'Collection Date' . "\t";
				echo 'Collection Time' . "\t";
				echo 'Lab received date' . "\t";
				echo 'Lab received time' . "\t";
				echo 'Lab Report Date' . "\t";
				echo 'Lab report time' . "\t";
				echo 'Verification Date' . "\t";
				echo 'Verification time' . "\t";
				echo 'Reason test' . "\t";
				echo 'Specimen collector' . "\t";
				echo 'Regulation' . "\t";
				echo 'COC confirmation date' . "\t";
				echo 'COC confirmation operator' . "\t";
				echo 'MRO Name' . "\t";
				echo 'MRO Address' . "\t";
				echo 'MRO City' . "\t";
				echo 'MRO State' . "\t";
				echo 'MRO Zip' . "\t";
				echo 'MRO Phone' . "\t";
				
				echo 'Analyte Disposition' . "\t";
				echo 'Analyte Panel' . "\t";
				echo 'Analyte ID' . "\t";
				echo 'Analyte Name' . "\t";
				echo 'Analyte Specimen' . "\t";
				echo 'Analyte Screening' . "\t";
				echo 'Analyte Confirmation' . "\t";
			
		
	  		// Write data to file
	  		
			$flag = false;
			
			$data = $this->reports_model->get_escreen_export();
			
			 foreach($data as $row) {
			
			 if($row->confirmed==0){
			  $confirmed = 'No';
			 } else {
			  $confirmed = 'Yes';
			 }
			
			 
			 $analytes = $this->reports_model->get_escreen_analytes($row->id,$row->escreen_id);
			 
			 foreach($analytes as $analyte){
			 $dis[] = $analyte->disposition;
			 $pan[] = $analyte->panel_id;
			 $ana[] = $analyte->analyte_id;
			 $ana_name[] = $analyte->analyte_name;
			 $spec[] = $analyte->specimen_type;
			 $screen[] = $analyte->screening_cut_off_value.' '.$analyte->screening_cut_off_unit;
			 $conf[] = $analyte->confirmation_cut_off_value.' '.$analyte->confirmation_cut_off_unit;
			 }
			 
			 
			 
			 
			 
				if (!$flag) {
					// display field/column names as first row
					echo "\r\n";
					$flag = true;
				}
				
				//echo implode("\t", array_values($row)) . "\r\n";
				
				//echo $row['id'] . "\t";
			
				echo ltrim($row->id) . "\t";
				echo ltrim($confirmed) . "\t";
				echo ltrim($row->escreenID) . "\t";
				echo ltrim($row->disposition) . "\t";
				echo ltrim($row->confirmation_number) . "\t";
				
				echo ltrim($row->dilute) . "\t";
				
				echo ltrim($row->escreen_client_account) . "\t";
				echo ltrim($row->escreen_client_sub_account) . "\t";
				echo ltrim($row->cost_center) . "\t";
				echo ltrim($row->lab_name) . "\t";
				echo ltrim($row->lab_account) . "\t";
				echo ltrim($row->client_name) . "\t";
				echo ltrim($row->location) . "\t";
				echo ltrim($row->collection_site) . "\t";
				echo ltrim($row->collection_network) . "\t";
				
				echo ltrim($row->collection_phone) . "\t";
				echo ltrim($row->accession_num) . "\t";
				echo ltrim($row->donor_name) . "\t";
				echo ltrim($row->ssn) . "\t";
				echo ltrim($row->other_id) . "\t";
				echo ltrim($row->other_id_type) . "\t";
				echo ltrim($row->dob) . "\t";
				echo ltrim($row->home_phone) . "\t";
				echo ltrim($row->work_phone) . "\t";
				
				echo ltrim($row->chain_custody) . "\t";
				echo ltrim($row->collection_date) . "\t";
				echo ltrim($row->collection_time) . "\t";
				echo ltrim($row->lab_received_date) . "\t";
				echo ltrim($row->lab_received_time) . "\t";
				echo ltrim($row->lab_report_date) . "\t";
				echo ltrim($row->lab_report_time) . "\t";
				echo ltrim($row->verification_date) . "\t";
				echo ltrim($row->verification_time) . "\t";
				
				echo ltrim($row->reason_test) . "\t";
				echo ltrim($row->specimen_collector) . "\t";
				echo ltrim($row->regulation) . "\t";
				echo ltrim($row->coc_confirmation_date) . "\t";
				echo ltrim($row->coc_confirmation_operator) . "\t";
				echo ltrim($row->mro_name) . "\t";
				echo ltrim($row->mro_address) . "\t";
				echo ltrim($row->mro_city) . "\t";
				echo ltrim($row->mro_state) . "\t";
				echo ltrim($row->mro_zip) . "\t";
				echo ltrim($row->mro_phone) . "\t";
				
				echo implode('|',$dis) . "\t";
				echo implode('|',$pan) . "\t";
				echo implode('|',$ana) . "\t";
				echo implode('|',$ana_name) . "\t";
				echo implode('|',$spec) . "\t";
				echo implode('|',$screen) . "\t";
				echo implode('|',$conf) . "\t";
				

			echo "\r\n";
			
			
			unset($dis);
			unset($pan);
			unset($ana);
			unset($ana_name);
			unset($spec);
			unset($screen);
			unset($conf);
            	}
        exit;
    
    
    }
    
    
    public function awsi_export(){
    
         $filename =  'awsi_report'.date('Y-m-d').".xls";
       		// Download file
       		
			header("Content-Disposition: attachment; filename=\"$filename\"");
			header("Content-Type: application/vnd.ms-excel");
			
			 //echo 'S No' . "\t";
			 
			 
  			
				echo 'ID' . "\t";
				echo 'Confirmed' . "\t";
				echo 'awsiID' . "\t";
				echo 'Customer Name' . "\t";
				echo 'Customer address' . "\t";
				echo 'Customer address2' . "\t";
				echo 'Customer City' . "\t";
				echo 'Customer State' . "\t";
				echo 'Customer Zip' . "\t";
				echo 'Collected' . "\t";
				echo 'MRO Released' . "\t";
				echo 'Lab ID' . "\t";
				echo 'Lab Account' . "\t";
				echo 'SSN' . "\t";
				echo 'Donor Name' . "\t";
				echo 'Location Code' . "\t";
				echo 'Reason test' . "\t";
				echo 'Industry' . "\t";
				echo 'Result' . "\t";
				echo 'Collection site name' . "\t";
				echo 'Collection address' . "\t";
				echo 'Collection city' . "\t";
				echo 'Collection state' . "\t";
				echo 'Collection zip' . "\t";
				echo 'Collection phone' . "\t";
				echo 'Collection fax' . "\t";
				echo 'Drug tested' . "\t";
				echo 'Lab received date' . "\t";
				echo 'Lab received' . "\t";
				echo 'Lab Released' . "\t";
				echo 'Accession' . "\t";
				echo 'Test panel name' . "\t";
				echo 'Lab name' . "\t";
				echo 'Prepared by' . "\t";
				echo 'Collection site id' . "\t";
				echo 'Analyte ID' . "\t";
				echo 'Analyte Name' . "\t";
				echo 'Analyte Qty' . "\t";
				echo 'Analyte Qty Value' . "\t";
				echo 'Analyte Result' . "\t";
			
		
	  		// Write data to file
	  		
			$flag = false;
			
			$data = $this->reports_model->get_awsi_export();
			
			 foreach($data as $row) {
			
			 if($row->confirmed==0){
			  $confirmed = 'No';
			 } else {
			  $confirmed = 'Yes';
			 }
			
			 
			 $analytes = $this->reports_model->get_awsi_analytes($row->id,$row->awsi_id);
			 
			 foreach($analytes as $analyte){
			 $ana[] = $analyte->analyte_id;
			 $ana_name[] = $analyte->analyte_name;
			 $qty_val[] = $analyte->analyte_quantity_value;
			 $qty[] = $analyte->analyte_quantity;
			 $res[] = $analyte->result;
			 }
			 
			 
			 
			 
			 
				if (!$flag) {
					// display field/column names as first row
					echo "\r\n";
					$flag = true;
				}
				
				//echo implode("\t", array_values($row)) . "\r\n";
				
				//echo $row['id'] . "\t";
			
				echo ltrim($row->id) . "\t";
				echo ltrim($confirmed) . "\t";
				echo ltrim($row->awsiID) . "\t";
				echo ltrim($row->customer_name) . "\t";
				
				echo ltrim($row->customer_address) . "\t";
				
				echo ltrim($row->customer_address2) . "\t";
				echo ltrim($row->customer_city) . "\t";
				echo ltrim($row->customer_state) . "\t";
				echo ltrim($row->customer_zip) . "\t";
				echo ltrim($row->collected) . "\t";
				echo ltrim($row->mro_released) . "\t";
				echo ltrim($row->spec_lab_id) . "\t";
				echo ltrim($row->lab_account) . "\t";
				echo ltrim($row->ssn) . "\t";
				
				echo ltrim($row->donor_name) . "\t";
				echo ltrim($row->location_code) . "\t";
				echo ltrim($row->reason_test) . "\t";
				echo ltrim($row->industry) . "\t";
				echo ltrim($row->result) . "\t";
				echo ltrim($row->collection_site_name) . "\t";
				echo ltrim($row->collection_address) . "\t";
				echo ltrim($row->collection_city) . "\t";
				echo ltrim($row->collection_state) . "\t";
				
				echo ltrim($row->collection_zip) . "\t";
				echo ltrim($row->collection_phone) . "\t";
				
				echo ltrim($row->collection_fax) . "\t";
				echo ltrim($row->drug_tested) . "\t";
				echo ltrim($row->lab_received) . "\t";
				echo ltrim($row->lab_released) . "\t";
				echo ltrim($row->accession) . "\t";
				echo ltrim($row->test_panel_name) . "\t";
				echo ltrim($row->lab_name) . "\t";
				
				echo ltrim($row->prepared_by) . "\t";
				echo ltrim($row->collection_site_id) . "\t";
				
				
				echo implode('|',$ana) . "\t";
				echo implode('|',$ana_name) . "\t";
				echo implode('|',$qty) . "\t";
				echo implode('|',$qty_val) . "\t";
				echo implode('|',$res) . "\t";
				

			echo "\r\n";
			
			
			unset($res);
			unset($qty);
			unset($ana);
			unset($ana_name);
			unset($qty_val);
			
            	}
        exit;
    
    
    }
    
    
    
    public function crl_export(){
    	
    	
         $filename =  'crl_report'.date('Y-m-d').".xls";
       		// Download file
       		
			header("Content-Disposition: attachment; filename=\"$filename\"");
			header("Content-Type: application/vnd.ms-excel");
			
			 //echo 'S No' . "\t";
			 
			 
  			
				echo 'ID' . "\t";
				echo 'Confirmed' . "\t";
				echo 'ReferenceTestID' . "\t";
				echo 'PrimaryID' . "\t";
				echo 'Alternate ID' . "\t";
				echo 'Customer Name' . "\t";
				echo 'Company ID' . "\t";
				echo 'Regulatory Mode' . "\t";
				echo 'Employee Category' . "\t";
				echo 'Collection Date' . "\t";
				echo 'Scheduled Date' . "\t";
				echo 'Reason Test' . "\t";
				echo 'Test Type' . "\t";
				echo 'DOT Test' . "\t";
				echo 'Test Sample ID' . "\t";
				echo 'Collection Status' . "\t";
				echo 'Specimen ID' . "\t";
				echo 'Lab ID' . "\t";
				echo 'Lab Account' . "\t";
				echo 'Lab Access No' . "\t";
				echo 'Lab Specimen ID' . "\t";
				echo 'Lab Result' . "\t";
				echo 'Lab Received Date' . "\t";
				echo 'Lab Result Date' . "\t";
				echo 'MRO Officer' . "\t";
				echo 'Result' . "\t";
				echo 'MRO Result Date' . "\t";
				echo 'MRO Image Name' . "\t";
				
				
				echo 'Subsatance Code' . "\t";
				echo 'Substance Lab Result' . "\t";
				echo 'Substance Med Officer Result' . "\t";
				echo 'Analyte Qty Value' . "\t";
				echo 'Analyte Result' . "\t";
			
		
	  		// Write data to file
	  		
			$flag = false;
			
			$data = $this->reports_model->get_crl_export();
			
			 foreach($data as $rows) {
			
			$row = $this->reports_model->get_crlquest_detail($rows->id);
			
			 if($row->confirmed==0){
			  $confirmed = 'No';
			 } else {
			  $confirmed = 'Yes';
			 }
			
			 
			 $analytes = $this->reports_model->get_crlquest_analytes($row->id,$row->crlquest_id);
			 
			 foreach($analytes as $analyte){
			 $code[] = $analyte->substance_code;
			 $res[] = $analyte->substance_lab_result;
			 $med_res[] = $analyte->substance_med_officer_result;
			 $qty[] = $analyte->substance_measurement;
			 }
			 
			 
			 
			 
			 
				if (!$flag) {
					// display field/column names as first row
					echo "\r\n";
					$flag = true;
				}
				
				//echo implode("\t", array_values($row)) . "\r\n";
				
				//echo $row['id'] . "\t";
			
				echo ltrim($row->id) . "\t";
				echo ltrim($confirmed) . "\t";
				echo ltrim($row->ReferenceTestID) . "\t";
				echo ltrim($row->PrimaryID_Type) . "\t";
				
				echo ltrim($row->alternate_id) . "\t";
				
				echo ltrim($row->person_name) . "\t";
				echo ltrim($row->company_id_value) . "\t";
				echo ltrim($row->regulatory_mode) . "\t";
				echo ltrim($row->employee_category) . "\t";
				echo ltrim($row->collection_date) . "\t";
				echo ltrim($row->scheduled_date) . "\t";
				echo ltrim($row->reason_test) . "\t";
				echo ltrim($row->attribute) . "\t";
				echo ltrim($row->dot_test) . "\t";
				
				echo ltrim($row->test_sample_id) . "\t";
				echo ltrim($row->collection_status) . "\t";
				echo ltrim($row->specimen_id) . "\t";
				echo ltrim($row->lab_id) . "\t";
				echo ltrim($row->lab_account) . "\t";
				echo ltrim($row->lab_access_no) . "\t";
				echo ltrim($row->lab_specimen_id) . "\t";
				echo ltrim($row->lab_result) . "\t";
				echo ltrim($row->lab_receive_date) . "\t";
				
				echo ltrim($row->lab_result_date) . "\t";
				echo ltrim($row->med_officer_id) . "\t";
				
				echo ltrim($row->med_officer_result) . "\t";
				echo ltrim($row->med_officer_result_date) . "\t";
				echo ltrim($row->mrorep_image_name) . "\t";
				
				
				
				echo implode('|',$code) . "\t";
				echo implode('|',$res) . "\t";
				echo implode('|',$med_res) . "\t";
				echo implode('|',$qty) . "\t";
				
				

			echo "\r\n";
			
			
			unset($res);
			unset($qty);
			unset($ana);
			unset($ana_name);
			unset($qty_val);
			
            	}
        exit;
    
    
    }

     public function escreen_api_export(){
    
         $filename =  'escreen_api_report'.date('Y-m-d').".xls";
       		// Download file
       		
			header("Content-Disposition: attachment; filename=\"$filename\"");
			header("Content-Type: application/vnd.ms-excel");
			
			 //echo 'S No' . "\t";
			 
			 
  			
				echo 'ID' . "\t";
				echo 'Confirmed' . "\t";
				echo 'escreenID' . "\t";
				echo 'Disposition' . "\t";
				echo 'Confirmation Number' . "\t";
				echo 'Dilute' . "\t";
				echo 'Escreen client account' . "\t";
				echo 'Escreen client sub account' . "\t";
				echo 'Cost Center' . "\t";
				echo 'Lab Name' . "\t";
				echo 'Lab Account' . "\t";
				echo 'Client name' . "\t";

				echo 'Ecup Collection' . "\t"; 
	 			echo 'Internal Client Id' . "\t"; 
				echo 'Electronic Client Id'  . "\t";
				echo 'External Donor Id'  . "\t"; 

				echo 'Location' . "\t";
				echo 'Collection Site' . "\t";
				echo 'Collection Network' . "\t";
				echo 'Collection Phone' . "\t";
				echo 'Accession num' . "\t";
				echo 'Donor Name' . "\t";
				echo 'SSN' . "\t";
				echo 'Other ID' . "\t";
				echo 'Other ID Type' . "\t";
				echo 'DOB' . "\t";
				echo 'Home Phone' . "\t";
				echo 'Work Phone' . "\t";
				echo 'Chain Custody' . "\t";
				echo 'Collection Date' . "\t";
				echo 'Collection Time' . "\t";
				echo 'Lab received date' . "\t";
				echo 'Lab received time' . "\t";
				echo 'Lab Report Date' . "\t";
				echo 'Lab report time' . "\t";
				echo 'Verification Date' . "\t";
				echo 'Verification time' . "\t";
				echo 'Reason test' . "\t";
				echo 'Specimen collector' . "\t";
				echo 'Regulation' . "\t";
				echo 'COC confirmation date' . "\t";
				echo 'COC confirmation operator' . "\t";
				echo 'MRO Name' . "\t";
				echo 'MRO Address' . "\t";
				echo 'MRO City' . "\t";
				echo 'MRO State' . "\t";
				echo 'MRO Zip' . "\t";
				echo 'MRO Phone' . "\t";
				
				echo 'Analyte Disposition' . "\t";
				echo 'Analyte Panel' . "\t";
				echo 'Analyte ID' . "\t";
				echo 'Analyte Name' . "\t";
				echo 'Analyte Specimen' . "\t";
				echo 'Analyte Screening' . "\t";
				echo 'Analyte Confirmation' . "\t";
			
		
	  		// Write data to file
	  		
			$flag = false;
			
			$data = $this->reports_model->get_escreen_api_export();
			
			 foreach($data as $row) {
			
			 if($row->confirmed==0){
			  $confirmed = 'No';
			 } else {
			  $confirmed = 'Yes';
			 }
			
			 
			 $analytes = $this->reports_model->get_escreen_api_analytes($row->id,$row->escreen_id);
			 
			 foreach($analytes as $analyte){
			 $dis[] = $analyte->disposition;
			 $pan[] = $analyte->panel_id;
			 $ana[] = $analyte->analyte_id;
			 $ana_name[] = $analyte->analyte_name;
			 $spec[] = $analyte->specimen_type;
			 $screen[] = $analyte->screening_cut_off_value.' '.$analyte->screening_cut_off_unit;
			 $conf[] = $analyte->confirmation_cut_off_value.' '.$analyte->confirmation_cut_off_unit;
			 }
			 
			 
			 
			 
			 
				if (!$flag) {
					// display field/column names as first row
					echo "\r\n";
					$flag = true;
				}
				
				//echo implode("\t", array_values($row)) . "\r\n";
				
				//echo $row['id'] . "\t";
			
				echo ltrim($row->id) . "\t";
				echo ltrim($confirmed) . "\t";
				echo ltrim($row->escreenID) . "\t";
				echo ltrim($row->disposition) . "\t";
				echo ltrim($row->confirmation_number) . "\t";
				
				echo ltrim($row->dilute) . "\t";
				
				echo ltrim($row->escreen_client_account) . "\t";
				echo ltrim($row->escreen_client_sub_account) . "\t";
				echo ltrim($row->cost_center) . "\t";
				echo ltrim($row->lab_name) . "\t";
				echo ltrim($row->lab_account) . "\t";
				echo ltrim($row->client_name) . "\t";

				echo ltrim($row->ecup_collection) . "\t";
				echo ltrim($row->internal_client_id) . "\t";
				echo ltrim($row->electronic_client_id) . "\t";
				echo ltrim($row->external_donor_id) . "\t";

				echo ltrim($row->location) . "\t";
				echo ltrim($row->collection_site) . "\t";
				echo ltrim($row->collection_network) . "\t";
				
				echo ltrim($row->collection_phone) . "\t";
				echo ltrim($row->accession_num) . "\t";
				echo ltrim($row->donor_name) . "\t";
				echo ltrim($row->ssn) . "\t";
				echo ltrim($row->other_id) . "\t";
				echo ltrim($row->other_id_type) . "\t";
				echo ltrim($row->dob) . "\t";
				echo ltrim($row->home_phone) . "\t";
				echo ltrim($row->work_phone) . "\t";
				
				echo ltrim($row->chain_custody) . "\t";
				echo ltrim($row->collection_date) . "\t";
				echo ltrim($row->collection_time) . "\t";
				echo ltrim($row->lab_received_date) . "\t";
				echo ltrim($row->lab_received_time) . "\t";
				echo ltrim($row->lab_report_date) . "\t";
				echo ltrim($row->lab_report_time) . "\t";
				echo ltrim($row->verification_date) . "\t";
				echo ltrim($row->verification_time) . "\t";
				
				echo ltrim($row->reason_test) . "\t";
				echo ltrim($row->specimen_collector) . "\t";
				echo ltrim($row->regulation) . "\t";
				echo ltrim($row->coc_confirmation_date) . "\t";
				echo ltrim($row->coc_confirmation_operator) . "\t";
				echo ltrim($row->mro_name) . "\t";
				echo ltrim($row->mro_address) . "\t";
				echo ltrim($row->mro_city) . "\t";
				echo ltrim($row->mro_state) . "\t";
				echo ltrim($row->mro_zip) . "\t";
				echo ltrim($row->mro_phone) . "\t";
				
				echo implode('|',$dis) . "\t";
				echo implode('|',$pan) . "\t";
				echo implode('|',$ana) . "\t";
				echo implode('|',$ana_name) . "\t";
				echo implode('|',$spec) . "\t";
				echo implode('|',$screen) . "\t";
				echo implode('|',$conf) . "\t";
				

			echo "\r\n";
			
			
			unset($dis);
			unset($pan);
			unset($ana);
			unset($ana_name);
			unset($spec);
			unset($screen);
			unset($conf);
            	}
        exit;
    
    
    }
    
    public function view_pdf($id){
    $crlquest_detail = $this->reports_model->get_crlquest_detail($id);
    	$pdf_decoded = base64_decode ($crlquest_detail->mrorep_image_data);
//Write data back to pdf file
$upload_path = './upload/cdi_pdf/'.$crlquest_detail->alternate_id.'.pdf';
$pdf = fopen ($upload_path,'w');
fwrite ($pdf,$pdf_decoded);
$this->reports_model->update_report_pdf($upload_path,$id);
//close output file
fclose ($pdf);

redirect('reports/crlquest_report_detail/'.$crlquest_detail->crlquest_id);
    }
    
   
} // Class end.
