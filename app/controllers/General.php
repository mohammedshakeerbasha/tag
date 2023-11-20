<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class General extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('general_model');
        $this->load->model('dashboard_model');
		$this->load->model('employees_model');
		$this->load->model('test_model');
		$this->load->library('common/user');
        $this->load->library('tag_general'); 
		/*if (!$this->session->userID):
		redirect('auth');
		endif;
		*/
    }



    /**
     * App home.
     */
    public function account_type()
    {
    	$data['title'] = 'Home';
    	$data['account_types'] = $this->general_model->account_types();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/general/home', $data);
        
    }
    
     /**
     * App home.
     */
    public function edit_account_type($id)
    {
    	if($this->input->post()){
    	$acc = $this->general_model->edit_account_type($this->input->post(),$id);
    	
    	$this->session->set_flashdata('edit_account_type', true);
    	}
    	redirect('general/account_type');
    }
    
    /**
     * App home.
     */
    public function add_account_type()
    {
    	if($this->input->post()){
    	$acc = $this->general_model->add_account_type($this->input->post());
    	
    	$this->session->set_flashdata('add_account_type', true);
    	}
    	redirect('general/account_type');
    }
    
    
     /**
     * App home.
     */
    public function pull_groups()
    {
    	$data['title'] = 'Pull Groups';
    	$data['pull_groups'] = $this->general_model->get_all_pull_groups();
    	$data['account_types'] = $this->general_model->account_types();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/general/pull_groups', $data);
        
    }
    
     /**
     * App home.
     */
    public function edit_pull_group($id)
    {
    	if($this->input->post()){
    	$acc = $this->general_model->edit_pull_group($this->input->post(),$id);
    	
    	$this->session->set_flashdata('edit_pull_group', true);
    	}
    	redirect('general/pull_groups');
    }
    
    /**
     * App home.
     */
    public function add_pull_group()
    {
    	if($this->input->post()){
    	$acc = $this->general_model->add_pull_group($this->input->post());
    	
    	$this->session->set_flashdata('add_pull_group', true);
    	}
    	redirect('general/pull_groups');
    }
    
    /**
     * App home.
     */
    public function employees_category()
    {
    	$data['title'] = 'Employees Category';
    	$data['employees_categories'] = $this->employees_model->get_employees_categories();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/general/employees_category', $data);
        
    }
    
     /**
     * App home.
     */
    public function edit_employees_category($id)
    {
    	if($this->input->post()){
    	$acc = $this->general_model->edit_employees_category($this->input->post(),$id);
    	
    	$this->session->set_flashdata('edit_employees_category', true);
    	}
    	redirect('general/employees_category');
    }
    
    /**
     * App home.
     */
    public function add_employees_category()
    {
    	if($this->input->post()){
    	$acc = $this->general_model->add_employees_category($this->input->post());
    	
    	$this->session->set_flashdata('add_employees_category', true);
    	}
    	redirect('general/employees_category');
    }
    
    /**
     * App home.
     */
    public function employees_status()
    {
    	$data['title'] = 'Employees Status';
    	$data['employees_statuss'] = $this->employees_model->get_employees_statuss();
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/general/employees_status', $data);
        
    }
    
     /**
     * App home.
     */
    public function edit_employees_status($id)
    {
    	if($this->input->post()){
    	$acc = $this->general_model->edit_employees_status($this->input->post(),$id);
    	
    	$this->session->set_flashdata('edit_employees_status', true);
    	}
    	redirect('general/employees_status');
    }
    
    /**
     * App home.
     */
    public function add_employees_status()
    {
    	if($this->input->post()){
    	$acc = $this->general_model->add_employees_status($this->input->post());
    	
    	$this->session->set_flashdata('add_employees_status', true);
    	}
    	redirect('general/employees_status');
    }
    
    /**
     * App home.
     */
    public function get_city_by_state_id($id)
    {  ?>
        <label><strong>Select City</strong></label><span style="color:red"><small>  (*Required)</small></span>
    	<select class="form-control select2" required name="city">
			<option value=""> Select City </option>
			<?php 
			$cities = $this->general_model->get_cities($id);
			foreach($cities  as $citi){ ?>
			<option value="<?php echo $citi->id; ?>">
			<?php echo $citi->name; ?>
			</option>
			<?php }?>
		</select>
    <?php }

    public function pull_city_by_state_id($id)
    {  ?>
        <script>
                $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Choose one',
            searchInputPlaceholder: 'Search'
        });
        $('.select2-no-search').select2({
            minimumResultsForSearch: Infinity,
            placeholder: 'Choose one'
        });
    });
        </script>
        <label><strong>Select City</strong></label><span style="color:red"><small>  (*Required)</small></span>
        <select class="form-control select2" required name="city">
            <option value=""> Select City </option>
            <?php 
            $cities = $this->general_model->get_cities($id);
            foreach($cities  as $citi){ ?>
            <option value="<?php echo $citi->id; ?>">
            <?php echo $citi->name; ?>
            </option>
            <?php }?>
        </select>
    <?php }
    
     public function get_city_by_state_id_for_edit($id)
    {  ?>
        <label><strong>Select City</strong></label><span style="color:red"><small>  (*Required)</small></span>
        <select class="form-control select2" required name="editcity">
            <option value=""> Select City </option>
            <?php 
            $cities = $this->general_model->get_cities($id);
            foreach($cities  as $citi){ ?>
            <option value="<?php echo $citi->id; ?>">
            <?php echo $citi->name; ?>
            </option>
            <?php }?>
        </select>
    <?php }
     /**
     * App home.
     */
     
   public function get_status($id,$emp_id)
    {   
        $employee = $this->employees_model->get_employee($emp_id); 
        $randomdate = $this->general_model->get_eligible_random_date_for_employee($employee->client_id);
        //print_r($employee);exit();
        if($id=='2'){ 
    $date = date("m/d/Y");
     ?>  
                <div class="form-row">
                <div class="form-group col-4">
                <label><strong> Date Entered</strong></label><span style="color:red"><small>  (*Required)</small></span>
                <input type="text" required  value="<?php echo $this->tag_general->us_date($employee->date_entered); ?>" name="date_entered" placeholder="Date Entered" class="form-control fc-datepicker" id="date_entered" >
                <small style="font-size:72%;">(Only change to a date that is after the most recent random pull)</small><br><?php if($randomdate){ ?>
                <small style="color:red">(Warning: Most recent eligible random pull date : <?php echo date('m-d-Y',strtotime($randomdate->pull_date_time)); ?>)</small>
                <?php }else{?>
                <small style="color:red">No past random pulls for this client account</small>
                <?php } ?>
                </div>
                <div class="form-group col-4">
                <label><strong>Select Reason</strong></label>
                    <select class="form-control select2" required name="reason">
                        <option value=""> Select Reason </option>
                        <option value="Medical Leave">Medical Leave</option>
                         <option value="N/A">N/A</option>
                         <option value="No Longer in a Safety Sensitive Position">No Longer in a Safety Sensitive Position</option>
                         <option value="No Longer Employed">No Longer Employed</option>
                          <option value="No Longer Eligible">No Longer Eligible</option>
                       <option value="Not Hired">Not Hired</option>
                       <option value="Temporary Leave">Temporary Leave</option>
                    </select> 
                </div>
                <div class="form-group col-4">
                <label><strong>Inactive Date</strong></label><span style="color:red"><small>  (*Required)</small></span>
                <input type="text"   value="<?php echo $date; ?>"  name="inactivedate"  id="inactivedate" placeholder="Inactive Date" class="form-control fc-datepicker" >
                </div>
                </div>
                <script>
                        
                        // Datepicker
                        $(function(e) {

                        $('#dateMask').mask('99/99/9999');
                        $('.fc-datepicker').datepicker({
                            showOtherMonths: true,
                            selectOtherMonths: true
                        });           
                        });
                </script> 
    <?php }elseif($id=='1'){ 
    $date = date("m/d/Y"); ?>
                                            <div class="form-group col-4">
                                            <label ><strong style="background-color:#FFFF00;">Date Entered</strong></label>
                                            <span style="color:red"><small>  (*Required)</small></span>
                                            <input type="text" required  value="<?php echo $this->tag_general->us_date($date); ?>" 
                                            name="date_entered" placeholder="Date Entered" class="form-control fc-datepicker" id="date_entered" >
                                            <small style="font-size:72%;">(Only change to a date that is after the most recent random pull)</small><br>
                                            <small style="font-size:72%;color:red;">Please make sure to change the employee’s updated Date Entered since they have now been reactivated</small><br>
                                            <?php if($randomdate){ ?>
                                            <small style="color:red">(Warning: Most recent eligible random pull date : <?php echo date('m-d-Y',strtotime($randomdate->pull_date_time)); ?>)</small>
                                            <?php }else{?>
                                                <small style="color:red">No past random pulls for this client account</small>

                                            <?php } ?>
                                        </div>

    <?php  } }
     /**
     * App home.
     */

 public function checkRandomDate()
{  
    
$randomdate = $this->input->post('randomdate'); 
$date_entered = $this->input->post('date_entered'); 
$rdate=$this->tag_general->us_date_db($randomdate); 
$edate=$this->tag_general->us_date_db($date_entered);
print_r($rdate.'rdate and edate'. $edate);

if($edate>=$rdate){
   $data=$this->input->post();
   echo $data;
} 

 
} 


      public function get_pending_employee_status($id,$emp_id)
    {  ?>
          
                <?php

      $employees_categories = $this->employees_model->get_employees_categories();
      $employee = $this->employees_model->get_employee($emp_id); 
      $randomdate = $this->general_model->get_eligible_random_date_for_employee($employee->client_id); 
        if($id=='1'){ ?> 



<script>

// Using jQuery
$('#date_entered').on('blur', function() {
  // Perform Ajax request here 
// Get the date values from the input elements
  var date1Value = document.getElementById('randomdate').value;
  var date2Value = document.getElementById('date_entered').value;

  // Create date objects from the input values
  var date1 = new Date(date1Value);
  var date2 = new Date(date2Value);

  // Compare the dates
  if (date1 > date2) { 
        var button = document.getElementById('but');     
      button.click();
  } else if (date1 < date2) {
    console.log("Date 1 is less than Date 2");
  } else { 
        var button = document.getElementById('but');     
      button.click();
  }
}); 
 
$(document).ready(function(){
    $('#date_entered').mask('99/99/9999'); 
});  

 // Datepicker
                        // $(function(e) {

                        // $('#dateMask').mask('99/99/9999');
                        // $('.fc-datepicker').datepicker({
                        //     showOtherMonths: true,
                        //     selectOtherMonths: true
                        // });           
                        // });

function focusOn(){
    document.getElementById("date_entered").focus();

    }
            </script>
            <a class="btn btn-light-gradient btn-block" id="but" style="margin-top:27px;display: none; " data-bs-target="#employee_id_match" data-bs-toggle="modal" data-bs-toggle="modal tooltip" data-bs-placement="top" title="Edit Account" >Check Availabity</a>  
<!-- employee ID match modal -->
    <div class="modal" id="employee_id_match">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content modal-content-demo">
                 
                        <div class="modal-header">
                        <h6 class="modal-title">Date Entered Validation Error </h6>
        <button aria-label="Close" class="close" data-bs-dismiss="modal" onclick="focusOn()" type="button">
            <span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body" >
                    <h5>You must enter a date that is after the most recent random pull date of: <?php echo $this->tag_general->us_date($randomdate->pull_date_time) ; ?></h5>
                                    
                                    
                    </div>
                     
                </div>
            </div>
        </div>
<!-- End employee ID match modal -->


                        <div class="form-row">
                        <div class="form-group col-4">
                        <label><strong> Employee Id</strong></label><span style="color:red"><small>  (*Required)</small></span>
                        <input type="text" required value="<?php echo $employee->employee_id ?>" name="employee_id" class="form-control" id="inputName" placeholder="Employee ID">
                        </div>
                        <div class="form-group col-4">
                        <label><strong> Employee Category</strong></label><span style="color:red"><small>  (*Required)</small></span>
                        <select class="form-control select2" required  name="employees_category"  >
                        <option value=""> Select Employee Category </option>
                        <?php foreach($employees_categories as $employees_category){ ?>
                        <option value="<?php echo $employees_category->id; ?>" <?php if($employee->employees_category==$employees_category->id){ echo 'selected'; } ?>>
                        <?php echo $employees_category->title; ?>
                        </option>
                        <?php } ?>
                        </select>
                        </div>
                        <div class="form-group col-4">
                       <label><strong style="background-color:#FFFF00;"> Date Entered</strong></label><span style="color:red"><small>  (*Required)</small></span>
<input type="text"   value="<?php echo $this->tag_general->us_date($employee->date_entered); ?>" 
name="date_entered"  id="date_entered" placeholder="MM/DD/YYYY" class="form-control fc-datepicker" >
<small style="font-size:72%">(Only change to a date that is after the most recent random pull)</small><br>
                                            <small style="font-size:72%;color:red;">Please make sure to change the employee’s updated Date Entered since they have now been reactivated</small><br>
                                            <?php if($randomdate){ ?>
<input type="hidden" name="randomdate" id="randomdate" value="<?php echo $this->tag_general->us_date($randomdate->pull_date_time) ; ?>" >
                                        <small style="color:red">(Warning: Most recent eligible random pull date : <?php echo $this->tag_general->us_date($randomdate->pull_date_time) ; ?>)</small>
                                            <?php }else{?>
                                                <small style="color:red">No past random pulls for this client account</small>

                                            <?php } ?>
                        </div>

                        </div>

     
    <?php }elseif($id=='2'){ 
    $date = date("m-d-Y");
     ?>  
                            <div class="form-row">
                            <div class="form-group col-4">
                            <label><strong> Employee Id</strong></label> 
                            <input type="text"   value="<?php echo $employee->employee_id ?>" name="employee_id" class="form-control" id="inputName" placeholder="Employee ID">
                            </div>
                            <div class="form-group col-4">
                            <label><strong> Employee Category</strong></label> 
                            <select class="form-control select2"    name="employees_category"  >
                            <option value=""> Select Employee Category </option>
                            <?php foreach($employees_categories as $employees_category){ ?>
                            <option value="<?php echo $employees_category->id; ?>" <?php if($employee->employees_category==$employees_category->id){ echo 'selected'; } ?>>
                            <?php echo $employees_category->title; ?>
                            </option>
                            <?php } ?>
                            </select>
                            </div>
                            <div class="form-group col-4">
                            <label><strong> Date Entered</strong></label> 
                            <input type="text"   value="<?php echo $this->tag_general->us_date($employee->date_entered); ?>" name="date_entered"  id="date_entered" placeholder="Date Entered" class="form-control fc-datepicker" >
                           </div>
                            </div>  
                        
                            <div class="form-row">
                            <div class="form-group col-4">
                            <label><strong>Select Reason</strong></label><span style="color:red"><small>  (*Required)</small></span>
                            <select class="form-control select2" required name="reason">
                                  <option value=""> Select Reason </option>
                                   <option value="Not Hired">Not Hired</option>
                                   <option value="Medical Leave">Medical Leave</option>
                                   <option value="Temporary Leave">Temporary Leave</option>
                                   <option value="No Longer Employed">No Longer Employed</option>
                                   <option value="No Longer Eligible">No Longer Eligible</option>  
                                   <option value="N/A">N/A</option> 
                              </select> 
                              </div>
                              <div class="form-group col-4">
                              <label><strong>Inactive Date</strong></label><span style="color:red"><small>  (*Required)</small></span>
                              <input type="text"   value="<?php echo $date ?>"  name="inactivedate"  id="inactivedate" placeholder="Inactive Date" class="form-control fc-datepicker" >
                              </div>
                              </div>
 
                             <script>
                                    
                                    // Datepicker
                                    $(function(e) {

                                    $('#dateMask').mask('99/99/9999');
                                    $('.fc-datepicker').datepicker({
                                        showOtherMonths: true,
                                        selectOtherMonths: true
                                    });           
                                    });
                            </script>



 <?php }elseif($id=='3'){ ?>


 <div class="form-row">
                                <div class="form-group col-4">
                                <label><strong> Employee Id</strong></label> 
                                <input type="text"   value="<?php echo $employee->employee_id ?>" name="employee_id" class="form-control" id="inputName" placeholder="Employee ID">
                                </div>
                                <div class="form-group col-4">
                                <label><strong> Employee Category</strong></label> 
                                <select class="form-control select2"    name="employees_category"  >
                                <option value=""> Select Employee Category </option>
                                <?php foreach($employees_categories as $employees_category){ ?>
                                <option value="<?php echo $employees_category->id; ?>" <?php
                                 if($employee->employees_category==$employees_category->id){ echo 'selected'; } ?>>
                                <?php echo $employees_category->title; ?>
                                </option>
                                <?php } ?>
                                </select>
                                </div>
                                <div class="form-group col-4">
                                <label><strong> Date Entered</strong></label> 
                                <input type="text"   value="<?php echo $this->tag_general->us_date($employee->date_entered); ?>" name="date_entered"  id="date_entered" placeholder="Date Entered" class="form-control fc-datepicker" >
                                </div>
                                </div>  


 <?php } } 
    
  /**
     * App home.
     */


      public function get_account($id)
    {  ?> 
        <select class="form-control select2" required name="account">
            <option value=""> Select Account </option>
            <?php 
            $accounts = $this->general_model->get_account($id);

            foreach($accounts  as $account){ ?>
            <option value="<?php echo $account->id; ?>">
            <?php echo $account->account_reference; ?>
            </option>
            <?php }?>
        </select>
    <?php }
    
     /**
     * App home.
     */


    public function email_setting()
    {
    	$data['title'] = 'Email Setting';
    	$data['email_setting'] = $this->general_model->get_setting();
    	if($this->input->post()){
    	$this->general_model->update_setting($this->input->post());
    	$this->session->set_flashdata('update_setting', true);
    	redirect('general/email_setting');
    	}
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/general/email_setting', $data);
        
    }
    
     /**
     * App home.
     */
    public function sms_setting()
    {
    	$data['title'] = 'SMS Setting';
    	$data['sms_setting'] = $this->general_model->get_setting();
    	if($this->input->post()){
    	$this->general_model->update_sms_setting($this->input->post());
    	$this->session->set_flashdata('update_setting', true);
    	redirect('general/sms_setting');
    	}
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/general/sms_setting', $data);
        
    }
    
    
     /**
     * App home.
     */
    public function labs()
    {
    	$data['title'] = 'Labs';
    	$data['labs'] = $this->general_model->get_labs();
    	$data['states'] = $this->general_model->get_states('230');
    	$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/general/labs', $data);
        
    }
    
     /**
     * App home.
     */
    public function edit_lab($id)
    {
    	if($this->input->post()){
    	$acc = $this->general_model->edit_lab($this->input->post(),$id);
    	
    	$this->session->set_flashdata('edit_lab', true);
    	}
    	redirect('general/labs');
    }
    
    /**
     * App home.
     */
    public function add_lab()
    {
    	if($this->input->post()){
    	$acc = $this->general_model->add_lab($this->input->post());
    	
    	$this->session->set_flashdata('add_lab', true);
    	}
    	redirect('general/labs');
    }
    

    public function get_lab_vendor_details($type,$Id){
        $lab_data = $this->general_model->get_vendor('tag_client_labs',$Id);
        if($type=='Lab'){ ?>
        <label><strong>Vendor</strong></label> 
        <select class="form-control select2"   name="lab_id">
        <option value=""> Select Lab Vendor </option> 
        <?php   $tag_labs = $this->general_model->get_vendors('tag_labs');
        foreach ($tag_labs as $tag_labs_data) { ?>
        <option value="<?php echo $tag_labs_data->id; ?>"><?php echo $tag_labs_data->title; ?></option>
        <?php } ?>
        </select>
        <?php }if($type=='MRO'){ ?>
        <label><strong>Vendor</strong></label> 
        <select class="form-control select2"   name="mro_id" >
        <option value=""> Select MRO Vendor </option> 
        <?php   $tag_mro = $this->general_model->get_vendors('tag_mro');
        foreach ($tag_mro as $tag_mros) { ?>
        <option value="<?php echo $tag_mros->id; ?>"><?php echo $tag_mros->abbreviation; ?></option>
        <?php } ?>
        </select>
        <?php }if($type=='Clinic'){ ?>
        <label><strong>Vendor</strong></label> 
        <select class="form-control select2"   name="clinic_id">
        <option value=""> Select Clinic Vendor </option> 
        <?php   $tag_clinics = $this->general_model->get_vendors('tag_clinics');
        foreach ($tag_clinics as $tag_clinicss) { ?>
        <option value="<?php echo $tag_clinicss->id; ?>"><?php echo $tag_clinicss->clinic_name; ?></option>
        <?php } ?>
        </select>
        <?php }  
              }
    

     public function get_lab_vendor_details_for_add($type){ 
        if($type=='Lab'){ ?>
        <label><strong>Vendor</strong></label> 
        <select class="form-control select2"   name="lab_id">
        <option value=""> Select Lab Vendor </option> 
        <?php   $tag_labs = $this->general_model->get_vendors('tag_labs');
        foreach ($tag_labs as $tag_labs_data) { ?>
        <option value="<?php echo $tag_labs_data->id; ?>"><?php echo $tag_labs_data->title; ?></option>
        <?php } ?>
        </select>
        <?php }if($type=='MRO'){ ?>
        <label><strong>Vendor</strong></label> 
        <select class="form-control select2"   name="mro_id" >
        <option value=""> Select MRO Vendor </option> 
        <?php   $tag_mro = $this->general_model->get_vendors('tag_mro');
        foreach ($tag_mro as $tag_mros) { ?>
        <option value="<?php echo $tag_mros->id; ?>"><?php echo $tag_mros->abbreviation; ?></option>
        <?php } ?>
        </select>
        <?php }if($type=='Clinic'){ ?>
        <label><strong>Vendor</strong></label> 
        <select class="form-control select2"   name="clinic_id">
        <option value=""> Select Clinic Vendor </option> 
        <?php   $tag_clinics = $this->general_model->get_vendors('tag_clinics');
        foreach ($tag_clinics as $tag_clinicss) { ?>
        <option value="<?php echo $tag_clinicss->id; ?>"><?php echo $tag_clinicss->clinic_name; ?></option>
        <?php } ?>
        </select>
        <?php }  
              }


    public function getstatusreason($id){       
         $data = $this->test_model->get_test_upload();
        if($id==2){ ?>
           <style type="text/css">
    .select2-dropdown
    { z-index:99999; }
</style>
<script>$(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Choose one',
            searchInputPlaceholder: 'Search'
        });
        $('.select2-no-search').select2({
            minimumResultsForSearch: Infinity,
            placeholder: 'Choose one'
        });
    });</script> 
        <label><strong>CCF</strong></label> 
        <select class="form-control select2"   name="ccf">
        <option value=""> Select CCF </option> 
        <?php 
        foreach ($data as $cont) { ?>
        <option value="<?php echo $cont->id; ?>"><?php echo $cont->CCF; ?></option>
        <?php } ?>
        </select>
        <?php }if($id==3){ ?>
              <style type="text/css">
    .select2-dropdown
    { z-index:99999; }
</style>
<script>$(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Choose one',
            searchInputPlaceholder: 'Search'
        });
        $('.select2-no-search').select2({
            minimumResultsForSearch: Infinity,
            placeholder: 'Choose one'
        });
    });</script>

        <label><strong>Status Reason</strong></label> 
        <select class="form-control select2"   name="status_reason" >
        <option value=""> Select status reason </option> 
        <option value="1">Medical Leave</option>
        <option value="2">No Longer In A Safety Sensitive Position</option>
        <option value="3">No Longer Employed</option>
        <option value="4">Not Hired</option>
        <option value="5">Temporary Leave</option>
        </select>
        <?php } 
    }


    public function getRandomSelection($id,$emp_id){

        $test_type=$this->dashboard_model->get_test_type_by_id($id);
        $details=$this->dashboard_model->get_data_to_add_alcohol_test($emp_id);
        $desc=$test_type->test_type_description;
        if($desc=='Random Selection'){
            ?>
            <script>$(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Choose one',
            searchInputPlaceholder: 'Search'
        });
        $('.select2-no-search').select2({
            minimumResultsForSearch: Infinity,
            placeholder: 'Choose one'
        });
    });</script>

        <label><strong>Update Random Selection</strong></label> 
        <?php if($details){ ?>
        <select class="form-control select2"   name="random_selection" required>
        <?php }else{?>
        <select class="form-control select2"   name="random_selection" >
        <?php } ?>
        <option value=""> Select status reason </option> 
        <?php 
        foreach($details as $detail){
         $test_run_data=$this->dashboard_model->get_test_by_id_in_years_range($detail->test_run_id);
           if($test_run_data!=''){ ?>
        <option value="<?php echo $detail->id; ?>"><?php echo $this->tag_general->us_date($test_run_data->pull_date_time) ?></option>
        <?php } } ?>
        </select>

            <?php

        }


    }
} // Class end.
