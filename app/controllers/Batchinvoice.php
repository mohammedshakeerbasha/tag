<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Batchinvoice extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('authorize_net');
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('employees_model');
		$this->load->model('general_model');
        $this->load->model('paymentTracking_model');
		$this->load->model('invoices_model');
		$this->load->model('lineitems_model');
		$this->load->model('test_model');
		$this->load->model('email_model');
		$this->load->model('payment_model');
		$this->load->model('batchinvoice_model');
		$this->load->library('common/user');
		$this->load->library('general');
		$this->load->library('common/paginator');  
    	$this->load->library('generatepdf');  
        $this->load->library("pagination");
		$this->load->library('authorize_net');
		if (!$this->session->userID):
		redirect('auth');
		endif;
		
    }

    function uploadinvoices(){
        $data['title'] = 'Upload Invoice';
        $data['invoice_template_type'] = $this->batchinvoice_model->get_invoice_template_type();
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/batch_invoices/uploadinvoice', $data);
    }

    function pastuploadedinvoices(){
        
        $data['title'] = 'Past Uploaded Invoices';
        $data['past_uploads'] = $this->batchinvoice_model->get_all_file_uploads();
        $this->load->view($this->preferences->type('system')->item('app_themesDir').'/'.$this->preferences->type('system')->item('app_themeDir') . '/batch_invoices/past_uploads', $data);

    }
    

/*******************************************************************************
 * @author: kanchan
 * generating batch invoice functionalities::start from here
 * CSV file upload : store_uploaded_invoice()
 * CSV file data entry : upload_sheetdata_escreen()
 * match and update : get_matched_record_for_escreen() 
 * calculation of prices : calculatePrice()
 * line item insertion : do_batch_lineitem_insertion()
 *******************************************************************************/

function batch_invoice_processing(){
    
    $postdata = $this->input->post();
    $matchedResult = '';
    $matchFoundCOCList = [];
    $updatedMatchList = [];
    $message = '';
    if($_FILES){

        //upload file to server as well as insert new record in tag_invoice_fileupload
        // $fileupload_id = $this->store_uploaded_invoice($_FILES,$postdata);
        $fileupload_id = 26; //$this->store_uploaded_invoice($_FILES,$postdata);

        if($fileupload_id != ''){
            $message = "file metadata is stored in fileupload table";

            // for escreen
            //uploading sheet records in table tag_escreen_invoice_upload_details
            //and returning array of coc values
            //status = 0 - uploaded date into table escreen
            $match_keyword_arr = $this->upload_sheetdata_escreen($_FILES,$fileupload_id); 
            
            //if match_keyword_arr is not null then running a match process against all those match_keyword_arr untill found match 
            //returning all recored which got a match
            //status = 1 - match
            if(count($match_keyword_arr) != 0){
                $message = $this->get_matched_record_for_escreen($match_keyword_arr);
            exit;
                //price calculation and invoice_detail table insertion
                //fetching records by ccf which status = 2

                // $getmatchedRec = $this->batchinvoice_model->get_matched_data_by_cocarr($match_keyword_arr['speciman_id']);
                // if(count($getmatchedRec) != 0){
                //     $message = $this->do_batch_lineitem_insertion($getmatchedRec);
                // }

            }else{
                $message = 'File metadata uploaded but failed to insert file records in escreen upload detail table';
            }  
        }else{
            $message = "Failed to store file metadata in file upload table";
        }
    }else{
        $message = "No File Chosen";
    }
    echo $message;
    exit;
    redirect('/batchinvoice/uploadinvoices?message'.$message);
}


    function store_uploaded_invoice($file,$postdata)
    {  
        $id = '';
        if($file['file']['tmp_name']){
            $file_name = $file["file"]["name"];            
            $id = $this->batchinvoice_model->add_invoice_item($postdata);
            
            if($id!=''){
                $this->load->library('upload');
                $config['upload_path'] = './upload/batch_invoices';
                $config['allowed_types'] = 'csv|xlsx';
                $config['max_size'] = '1000';
                $this->upload->initialize($config);

                if (!$this->upload->do_upload('file')) {
                    $error = array('error' => $this->upload->display_errors());
                    $message= "failed to upload file in server";
                } else {
                    $datainfo = array('upload_data' => $this->upload->data());
                    $image_full_path = $datainfo['upload_data']['file_name'];
                    $this->batchinvoice_model->update_invoice_file_upload($id,$image_full_path);
                }
            }
        }
        return $id;
        // redirect("batchinvoice/uploadinvoices?message=".$message);
    }

    //to upload all data from sheet to table
    //return match_keywords_arr to perform different process of match
    function upload_sheetdata_escreen($file,$escreen_invoice_tableid){
        $emptyArray = [];
        $status = config_item('BATCH_INVOICE_UPLOADED'); // 0
        if($file['file']['type'] != 'text/csv'){
            echo "fileupload xlsx - inprogress ";
            exit;
        }else{
            $handle = fopen($file['file']['tmp_name'], "r");
            $data = fgetcsv($handle, 10000, ";"); 
            $i = 0;
            $batchData = [];
            $match_keywords_arr = [];
            // $posting_date = '';
            // $collection_date = '';
            
            while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {

                // if(isset($data[0]) && !empty($data[0])){
                //     $posting_date = $this->convert_date_in_dbformat($data[0]);
                // }

                // if(isset($data[5]) && !empty($data[5])){
                //     $collection_date = $this->convert_date_in_dbformat($data[5]);
                // }
               
                $posting_date = isset($data[0]) ? $this->convert_date_in_dbformat($data[0]) : '';
                $collection_date = isset($data[5]) ? $this->convert_date_in_dbformat($data[5]) : '';
                

                
                $batchData[$i] = [
                    'posting_date' =>$posting_date,
                    'escreen_invoice_id' =>$escreen_invoice_tableid,
                    'escreen_invoice_no' =>$data[1],
                    'coc' =>$data[2],
                    'donor_name' =>$data[3],
                    'last_4_ssn' =>$data[4],
                    'collection_date' =>$collection_date,
                    'sell_to_customer_name' =>$data[7],
                    'sell_to_customer_no' =>$data[6],
                    'description' => $data[8],
                    'amount' =>substr($data[9], 1),
                    'tax' =>substr($data[10], 1),
                    'total' =>substr($data[11], 1),
                    'master' =>$data[12],
                    'sub' =>$data[13],
                    'internal_account' =>$data[14],
                    'electronic_client_id' =>$data[15],
                    'name' =>$data[16],
                    'primary_clinic' =>$data[17],
                    'primary_clinic_2' =>$data[18],
                    'primary_network' =>$data[19],
                    'collect_clinic_id' =>$data[20],
                    'collect_clinic' =>$data[21],
                    'collect_network' =>$data[22],
                    'other_id' =>$data[23],
                    'reference_id' =>$data[24],
                    'reason_for_test' =>$data[25],
                    'lab_account' =>$data[26],
                    'cost_center' =>$data[27],
                    'confirmation_bar_code' =>$data[28],
                    'external_donor_id' =>$data[29],
                    'purchase_order_no' =>$data[30],
                    'UDEF_data_1' =>$data[31],
                    'custom_field_1' =>$data[32],
                    'custom_field_2' =>$data[33],
                    'custom_field_3' =>$data[34],
                    'custom_field_4' =>$data[35],
                    'custom_field_5' =>$data[36],
                    'custom_field_6' =>$data[37],
                    'custom_field_7' =>$data[38],
                    'accession_no' =>$data[39],
                    'component_id' =>$data[40],
                    'combined_master_sub' =>$data[12]."-".$data[13],
                    'status' =>$status,
                    'date_of_service' => $collection_date,
                    'tag_ams_cost' => substr($data[11], 1),


                ];

                $match_keywords_arr['speciman_id'][$i] = $data[2]; //coc
                $match_keywords_arr['combined_master_sub'][$i] = $data[12]."-".$data[13]; //master-sub
                $match_keywords_arr['sell_to_customer_name'][$i] = $data[7]; //sell_to_customer_name
                $i++;
            }

            if(count($batchData) != 0){
                return $match_keywords_arr;
                // if($this->batchinvoice_model->batch_insert_escreen_csv($batchData)){
                //     return $match_keywords_arr;
                // }   
            }              
        }  
           
        return $emptyArray;
    }

    function convert_date_in_dbformat($date){
        $dateArr = explode("-",$date);
        if(count($dateArr) == 3){
            return $dateArr[2]."-".$dateArr[0]."-".$dateArr[1];
        }else{
            return $date;
        }
    }
    function get_donor_name_first($name){
        $donor = '';
        $nameArr = explode(",",$name);
        if(count($nameArr) > 0){
             $donor = $nameArr[count($nameArr) - 1];
        }
        return $donor;
    }
    function get_donor_name_last($name){
        $donor = '';
        $nameArr = explode(",",$name);
        if(count($nameArr) > 0){
             $donor = $nameArr[0];
        }
        return $donor;
    }
    function get_final_amount($total, $description, $client_acc_id){
        // $description = 'alcohol';
        // $finalAmount = ($total)+10;
        // Check if the word exists in the sentence
        if (strpos($description, "physical") !== false) {
            $finalAmount = ($total)+(20);
        }else if(strpos($description, "alcohol") !== false){

            if($total <= 40){
                $finalAmount = 45;
            }else{
                $finalAmount = round((($total)+6)/5)*5 ;
            }

        }else if(strpos($description, "urine") !== false){

            if($total <= 34){
                $finalAmount = 85;
            }else{
                $finalAmount = round((($total)+53)/5,0)*5 ;
            }

        }else if(strpos($description, "ecup") !== false){
            if($client_acc_id == '5347'){
                $finalAmount = 60;
            }else{
                if($total <= 34){
                    $finalAmount = 85;
                }else{
                    $finalAmount = round((($total)+53)/5)*5 ;
                }
            }
        }else{
            $finalAmount = ($total)+10;
        }
        echo $finalAmount;
        // return $finalAmount;
        
    }



    //match uploaded data from test upload table and return matched record to update
    //match process
    //this function contain all steps for matching 
    //return all matched result
    function get_matched_record_for_escreen($batchDataArr){

        /**
         * Match Records:: Round 1
         */
        $ccfstring = '(';
        $count = count($batchDataArr['speciman_id']);
        $p = 1;
        foreach ($batchDataArr['speciman_id'] as $key => $value) {
            if($p >= $count){
                $ccfstring .="'". $value."')";
            }else{
                $ccfstring .="'". $value."',";
            }
            $p++;
        } 
        $message = '';
        // updating table tag_escreen_invoice_upload_details whichever got matched against those coc ::round 1
        $matchedDataViaCOC = $this->batchinvoice_model->get_matched_data($ccfstring); 

        if(count($matchedDataViaCOC) != 0){
            $message = 'File Uploaded and Ran a Match';
            $dataArr = [];
            $i = 0; 
            //do a batch update

            foreach ($matchedDataViaCOC as $match) {
                if($match->reason_for_test == 0){
                    $dec ="Breath Alcohol Test"; 
                }else{
                    $dec ="Urine Drug Test";
                }
                $lastFourChars = "";
                if (strlen($match->employee_id) >= 4) {
                    $lastFourChars = substr($match->employee_id, -4);
                } 
               
                $donor_name_first = isset($match->donor_name) ? $this->get_donor_name_first($match->donor_name) : '';
                $donor_name_last = isset($match->donor_name) ? $this->get_donor_name_last($match->donor_name) : '';
                $amount = isset($match->total) ? $this->get_final_amount($match->total,$match->upload_desc,$match->client_account_id) : '';
                $dataArr[$i] = [
                    'coc' => $match->ccf,
                    'client_id' =>$match->client_id,
                    'client_account_id' =>$match->client_account_id,
                    'tag_ams_employee_id' =>$match->employee_id,
                    'description_test_reason' =>$match->test_type_description,
                    'description_test_description' =>$dec,
                    'description_emp_ident' =>$lastFourChars,
                    'final_amount' =>$amount,
                    'donor_first_name' =>$donor_name_first,
                    'donor_last_name' =>$donor_name_last,
                    'status' => config_item('BATCH_INVOICE_MATCH_AND_UPDATED') //1
                ];
                $i++;
            }

            if($this->batchinvoice_model->batch_update_by_ccf($dataArr)){
                $message = 'File Uploaded and Found Match and Updated Matched Records';
            }else{
                $message = "File Uploaded and Found Match But failed to update";
            } 
        }

         /**
         * Match Records:: Round 2
         * if in above process, could't found matches for some records
         * first will update client id , client account id and tag employee id
         * then with help of tag employee id will get donor first name and donor last name
         * 
         */
         

            $employee_status = 1;
            $getAllMatchedClientAccId = $this->batchinvoice_model->get_matched_record_to_left_invoice_uploads($combined_master_sub, $employee_status);
            
            if(count($getAllMatchedClientAccId) != 0){
                $message = 'File Uploaded and Ran a Match';
                $dataArr = [];
                $i = 0; 
                foreach ($getAllMatchedClientAccId as $match) {

                    if($match->last_4_ssn == substr($match->temp_ssn,-4)){
                        $dataArr[$i] = [
                            'client_id' =>$match->client_id,
                            'client_account_id' =>$match->emp_client_acc_id,
                            'tag_ams_employee_id' =>$match->temp_id,
                            'donor_first_name' =>$match->donor_first_name,
                            'donor_last_name' =>$match->donor_last_name,
                            'id' =>$match->escreen_id,
                            'status' =>config_item('BATCH_INVOICE_MATCH_AND_UPDATED') //1
                        ];
                        $i++;
                    }elseif($match->last_4_ssn == substr($match->employee_id,-4)){
                        $dataArr[$i] = [
                            'client_id' =>$match->client_id,
                            'client_account_id' =>$match->emp_client_acc_id,
                            'tag_ams_employee_id' =>$match->temp_id,
                            'donor_first_name' =>$match->donor_first_name,
                            'id' =>$match->escreen_id,
                            'status' =>config_item('BATCH_INVOICE_MATCH_AND_UPDATED') //1
                        ];
                        $i++;
                    }

                }
                
                if($this->batchinvoice_model->batch_update_by_escreenid($dataArr)){
                    $message = 'File Uploaded and Found Match and Updated Matched Records';
                }else{
                    $message = "File Uploaded and Found Match But failed to update";
                } 
            }

            echo $message;
        }

    function do_batch_lineitem_insertion($matchedLineItems){
        echo "<pre>";
        print_r($matchedLineItems);
        exit;
        $batchlineItem = [];
        $i = 0;
        foreach ($matchedLineItems as $lineitem) {
            $ename_first = 'NA';
            $ename_last = 'NA';
            $emp_name = $this->batchinvoice_model->get_employname_byid($lineitem->tag_ams_employee_id);
            if(!empty($emp_name)){
                $ename_first = $emp_name->first_name;
            }
            if(!empty($emp_name)){
                $ename_last = $emp_name->last_name;
            }
            $description = $ename_first."|".$ename_last."|".$lineitem->coc."|".$lineitem->description."|".$lineitem->description."|".$lineitem->reason_for_test."|";
            // $description = $lineitem->coc."|".$lineitem->description."|".$lineitem->description."|".$lineitem->reason_for_test."|";

            $price = $lineitem->total;

            $price = $this->calculatePrice($price); 

            $batchlineItem[$i] = [
                'invoice_detail_description' => $description,
                'amount' =>$price,
                'client_id' =>$lineitem->client_id,
                'client_account_id' =>$lineitem->client_account_id,
            ];
            $i++;
        }
        
        if(!empty($batchlineItem)){
            if($this->batchinvoice_model->batch_insert_lineitem($batchlineItem)){
                $message = 'success';
            }else{
                $message = "Failed to insert record in invoice_detail_table";
            } 
        }

        return $message;

    }

    function calculatePrice($price) {
        if($price <= 35){
            $total = 85;
        }else{
            $total = ((ceil((($price)-35)/5))*50)+($price);
            $total = ceil($total/5) * 5;
        }
        return $total;
        // echo $total;
    }
    

    function batch_payment_process($batch_generated_id){
        $getDataToBatchPayment = $this->batchinvoice_model->getBatchInvoices($batch_generated_id);
        // echo "<pre>";
        // print_r($getDataToBatchPayment);
        // exit;
        if(!empty($getDataToBatchPayment)){            
            include APPPATH.'libraries/Authnetcim.php';
            $cim = new Authnetcim($this->config->item('api_login_id'),$this->config->item('api_transaction_key'),Authnetcim::USE_PRODUCTION_SERVER);
            $batchXML = '';
            
            foreach ($getDataToBatchPayment as $batch) {
                $cim->setParameter('customerProfileId',$batch->profile_authorize_id);
                $cim->setParameter('customerPaymentProfileId', $batch->payment_authorize_id);
                $cim->setParameter('amount', $batch->amount);
                // $cim->setParameter('amount', '0.5');
                $cim->chargeCustomerProfile();
                if($cim->isSuccessful()){
                    $transId = $cim->getTransId();
                    #update Payment related table
                    $this->update_tables_after_batchpayment_success($batch, $transId);
                }else{
                    #tag_failed_payment_records
                    $errorid = $this->track_failed_payment($batch);
                }
            }
            $message = "success";
        }else{
            // $message = "No Defaul Billing Profiles Found!";
            $message = "failed";
        }
        redirect('invoices/batch_invoices_details/'.$batch_generated_id.'?message='.$message);
    }
  
  
    function update_tables_after_batchpayment_success($paymentmethodDetails,$transId){
        
        $invoicePaymentDes = $this->descriptionfor_invoice_payment_table($paymentmethodDetails,'1');
        
        $billingtype = $paymentmethodDetails->billing_type;
        $billingtypeStr = "";
        if($billingtype == '1'){
            $billingtypeStr = "Credit Card";
            $paymentRef = 'CC : XXXX'.substr($invoicePaymentDes->credit_card,-4).' - Authorize.net Profile: '.$paymentmethodDetails->payment_authorize_id;
            $storedType = 7;
        }
        if($billingtype == '2'){
            $billingtypeStr = "ACH";
            $paymentRef = 'ACH : XXXX'.substr($paymentmethodDetails->credit_card,-4).' - Authorize.net Profile: '.$paymentmethodDetails->payment_authorize_id;
            $storedType = 8;
        }

        // add entry in invoice_payment table and invoice table
        $payment['pay_client_id'] = $paymentmethodDetails->invoice_client_id;
        $payment['pay_client_account_id'] = $paymentmethodDetails->invoice_client_account_id;
        $payment['apply_pay_invoice_id'] = $paymentmethodDetails->invoice_id;
        $payment['pay_date'] = date("Y-m-d");
        $payment['payment_amt'] = $paymentmethodDetails->amount;
        $payment['payment_type'] = $storedType;
        $payment['online_payment_profile_id'] = $paymentmethodDetails->payment_authorize_id;
        $payment['online_authorize_net_reference'] = $transId;
        $payment['pay_ref'] = $paymentRef;
        $payment['payment_description'] = $invoicePaymentDes;
        $payment['pay_notes'] = 'Batch Payment';
        $payment['admin_user_id'] = $this->session->userID;

        //inserting payment record in invoice_payment table
        $invoice_pay_id = $this->invoices_model->apply_payment($payment);
       
        if(!empty($invoice_pay_id)){

            // updating invoice table
            $invoiceid = $paymentmethodDetails->invoice_id;
            $payment_amt = $paymentmethodDetails->amount;
            $existingpaid = $paymentmethodDetails->total_paid_amount;

            $totalDue = $paymentmethodDetails->amount;
            $totalpaid = $this->get_total_invoice_paymentitem_amount($invoiceid);
            
            $status = $close = 0;
            if($totalpaid < $totalDue ){
                $status =  0;
                $close =  0;
            }
            if($totalpaid >= $totalDue ){
                $status =  1;
                $close =  1;
            }
            $this->invoices_model->update_invoice_on_addpayment($status,$close,$totalpaid,$invoiceid);

            //send mail once paid
            $this->send_email_invoice($invoiceid);
        }

	}


    // function updateclientids(){
    //     $batch_generated_id = "1017";
    //     $dataArr =[];
    //     $a =  $this->batchinvoice_model->update_data($batch_generated_id);
    //     foreach ($a as $match) {
    //         $dataArr[$i] = [
    //             'clientID' =>$match->client_id,
    //             'client_account_id' =>$match->client_account_id,
    //         ];
    //         $i++;
    //     }
    //     if($this->batchinvoice_model->batch_update_by_x($dataArr)){
    //         $message = 'File Uploaded and Found Match and Updated Matched Records';
    //     }else{
    //         $message = "File Uploaded and Found Match But failed to update";
    //     } 
        
    // }


    public function descriptionfor_invoice_payment_table($formdata,$paymentType){

		$type = 'Credit Card';
		$card = substr($formdata->credit_card,-4);
		$date = date('Y-m-d');
		$user = 'NA';

		if($formdata->billing_type == '2'){
			$type = 'ACH';
		}

        $id = $this->session->userID;
        $data = $this->user_model->get_user_by_id($id);
        $user = $data->firstName.' '.$data->surname;

		
		if($paymentType == '1' ){
			$desc = 'Online '.$type.' Payment (Stored Profile) - '.$date.' - '.$card.' - '.$user;
		}else{
			$desc = 'Online '.$type.' Payment - '.$date.' - '.$card.' - '.$user;
		}

		return $desc;
	}

    function get_total_invoice_paymentitem_amount($invoiceid){
		$lineitems = $this->invoices_model->get_all_paid_amt($invoiceid);
		$total_Invoice_Amt = 0.0;
		if(!empty($lineitems)){
			foreach ($lineitems as $lineitem) {
				$total_Invoice_Amt = ($total_Invoice_Amt)+($lineitem->payment_amount);
			}
		}
		return $total_Invoice_Amt;
	}

    function track_failed_payment($formdata)
	{
        $formdata['admin_user_id'] = $this->session->userID;
		$id = $this->paymentTracking_model->track_failed_payment($formdata);
		return $id;
		
	}
    function viewbatchpaymentresult($batch_generated_id)
	{
        $data['title'] = "Batch Payment Results";
		$data['batchPaidInvoices'] = $this->batchinvoice_model->getBatchInvoiceDetailsByStatus($batch_generated_id,1);
        
		$data['batchFailedInvoices'] = $this->batchinvoice_model->getFaileBatchpaymentRecord($batch_generated_id);
      
        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/view_batch_payment_details', $data);
	}

    public function send_email_invoice($invoice_id){

        $data['invoice'] 		   = $this->invoices_model->get_invoice_by_id($invoice_id);
        $data['client_account']    = $this->clients_model->get_client_account($data['invoice']->client_account_id);
        $data['client']            = $this->clients_model->get_client_by_id($data['client_account']->client_id); 
        
        $this->batchinvoice_model->send_email_invoice($invoice_id,$data['client_account']->client_id,$data['invoice']->client_account_id); 	 			
        //   redirect('invoices/unpaidinvoicelist');     
    }

} // Class end.
