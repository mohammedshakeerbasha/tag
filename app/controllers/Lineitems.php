<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Lineitems extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('authorize_net');
		$this->config->load('modules/auth/config');
		$this->load->model('clients_model');
		$this->load->model('employees_model');
		$this->load->model('general_model');
		$this->load->model('invoices_model');
		$this->load->model('lineitems_model');
		$this->load->model('test_model');
		$this->load->model('email_model');
		$this->load->model('payment_model');
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
    
  
	//list line items against invoice id
    public function invoicedetails($invoiceid){  
		$data['title'] = 'Invoice Details';
    	$data['invoice_detail_list'] = $this->invoices_model->get_all_invoice_detail_by_invoiceid($invoiceid);
    	$data['invoiceid'] = $invoiceid;
    	$data['invoice_list'] = $this->invoices_model->get_invoice_by_id($invoiceid);
    	//echo '<pre>';print_r($data);exit();
    	$data['client_details'] = $this->clients_model->get_client($data['invoice_list']->clientID);

		$data['payment_methods']= $this->payment_model->get_client_payment_method_by_clientid($data['invoice_list']->clientID);

    	$data['client_account_details'] = $this->clients_model->get_client_account($data['invoice_list']->client_account_id);

        $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/invoices/invoice_detail', $data);
    }

	//adding line items against invoice id
    public function addlineitems(){    
		$message = "Failed";
		$class = "error";
		
		$formdata = $this->input->post();
		if(!empty($formdata['invoice_amt'])){
			//adding detail in invoice_detail table
			$invoice_detail_id = $this->invoices_model->add_inline_item($formdata);
			
			if(!empty($invoice_detail_id)){

				//adding total amount/ status / invoice closing
				$invoiceid = $formdata['invoice_id'];
				$status = $close = 0;
				$totalDue = ($formdata['invoice_total'])+($formdata['invoice_amt']);
				$total_paid = $formdata['paid_total'];
				if($totalDue > $total_paid ){
					$status = 0;
					$close = 0;
				}else{
					$status = 1;
					$close = 1;
				}

				// update
				$this->invoices_model->update_invoice_on_addinglineitem($status,$close,$totalDue,$invoiceid);
				$message = "Line item added";
				$class = "success";

			}


		}
		
		redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class);
		
    }
	

	public function editstatus(){
		//update invoice table
		$formdata = $this->input->post();
		$this->invoices_model->update_status($formdata);
		redirect('invoices/list_invoice');
	}


    public function delete_lineitem(){
        $formdata = $this->input->post();     
		$lineitemRecord = $this->lineitems_model->get_lineitemdetail_by_id($formdata['lineitem_id']);
		$lineitemRecord = json_decode(json_encode($lineitemRecord), true);
		if(!empty($lineitemRecord )){
			//insert line item record to tag_invoice_details_deleted_records befor deleting it
			$deleteRecordId = $this->lineitems_model->insert_invoice_detail_deleted_record($lineitemRecord);
			
			if($deleteRecordId != ''){
				$delete = $this->lineitems_model->delete_lineitem_by_id($formdata['lineitem_id']);
				if($delete){
				
					//update invoice table
					$invoiceid = $formdata['invoice_id'];
					$status = $close = 0;
					$total_Invoice_Amt = $this->get_total_lineitems_amount($invoiceid);

					$total_paid = $formdata['paid_total'];
					if($total_Invoice_Amt >= $total_paid ){
						$status = 0;
						$close = 0;
					}else{
						$status = 1;
						$close = 1;
					}
					$message = "Line item deleted";
					$class = "success";
					$update = $this->invoices_model->update_invoice_on_addinglineitem($status,$close,$total_Invoice_Amt,$formdata['invoice_id']);
				}else{
					$message = "failed";
					$class = "error";
				}
			}
		}
        redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message.'&class='.$class); 
        // redirect('invoices/invoicedetails/'.$formdata['invoice_id']); 
    }


    public function editlineitem(){
        $formdata = $this->input->post();    
		
		// echo "<pre>";
		// print_r($formdata);
		//  exit;

// Array
// (
//     [lineitem_id] => 284391
//     [invoice_id] => 4
//     [lineitem_amount] => 80.00
//     [description] => this is testing purpose only
//     [paid_total] => 57.50
// )
        $update = $this->lineitems_model->update_lineitem_by_id($formdata);
        if($update){
            
            //update invoice table
            $invoiceid = $formdata['invoice_id'];
            $status = $close = 0;
            $total_Invoice_Amt = $this->get_total_lineitems_amount($invoiceid);
            $total_paid = $formdata['paid_total'];

            if($total_Invoice_Amt <= $total_paid ){
                $status = 1;
                $close = 1;
            }else{
                $status = 0;
                $close = 0;
            }
            $message = "success";
            $update = $this->invoices_model->update_invoice_on_addinglineitem($status,$close,$total_Invoice_Amt,$formdata['invoice_id']);
        }else{
            $message = "failed";
        }
        redirect('invoices/invoicedetails/'.$formdata['invoice_id'].'?message='.$message); 
    }

	

	public function getclientAcc(){
		$clientid = $this->input->get('client_id');
		$clientaccid = $this->input->get('ccid');

		
		
		$clients = $this->lineitems_model->getclientacclist_by_clientid($clientid,$clientaccid);
		
			if(!empty($clients)){
				$client_id_arr = [];
				$i = 0;
				foreach ($clients as $client) {
					$client_id_arr[$i] = $client->client_id;
					$i++;
				}
				$clientsAccList = $this->lineitems_model->get_client_accountid_by_clientid($client_id_arr);
				
				
				if(!empty($clientsAccList)){
					$dropdown .= "<option>Select Client Account</option>";

					foreach($clientsAccList as $val){
						if($val->id != $clientaccid){

							$dropdown .= '<option value="'.$val->id.'">'.$val->account_reference.' - '.$val->id.'</option>';

						}
				}
            	
				}
			}else{
				// append_client_list_trans
				$dropdown = "<option>No Client Account Found</option>";
				 
			}
			echo $dropdown; exit;
		
	}
	public function gettransferinvoiceid(){
		
		$clientid = $this->input->get('client_id');
		$clientaccid = $this->input->get('ccid');

			$dropdown = "";
            $clients = $this->lineitems_model->get_allinvoice_by_clientid_accid($clientid,$clientaccid);
		// 	echo "<pre>";
		// print_r($clients); exit;
					
			if(!empty($clients)){
				$client_id_arr = [];
				$i = 0;
				$dropdown .= "<select name='transfer_invoice_id' class='form-control' id='transfer_invoiceid'>";

				foreach($clients as $val){
					// if($val->id != $clientaccid){
						$dropdown .= '<option value="'.$val->id.'">'.$val->id.' - '.$val->invoice_date.'</option>';
					// }
				}
				$dropdown .= '</select>';
		}else{
			// append_client_list_trans
			$dropdown = "No Invoice ID Found";
			 
		}

		echo $dropdown; exit;
	}


	public function transfer_invoice(){
		$formdata = $this->input->post();

		// echo "<pre>";
		// print_r($formdata);
		// exit;
		$message = "failed";

		// moving line item in diff client diff acc:: start
		if(isset($formdata['diffclient']) && !empty($formdata['diffclient']) && $formdata['diffclient'] == 'diffclient'){
			$this->transfer_invoice_diif_client($formdata);
		}

		// moving line item in same client diff acc:: start
		if(isset($formdata['sameclient']) && !empty($formdata['sameclient']) && $formdata['sameclient'] == 'sameclient'){
			$update = $this->lineitems_model->transfer_line_item($formdata);
			$transferredAmtToBeAddedToInvId = $this->invoices_model->get_invoice_by_id($formdata['invoice_id']);
			$newAmtToBeAddedToInvIdAmt = $this->get_total_lineitems_amount($formdata['invoice_id']);
			$total_paid1 = $transferredAmtToBeAddedToInvId->total_paid_amount;
			$updateOld = $this->updateInvoiceTable(0,0,$newAmtToBeAddedToInvIdAmt,$formdata['invoice_id'],$total_paid1);

			$transferredInvIdData = $this->invoices_model->get_invoice_by_id($formdata['transfer_invoice_id']);
			$transferedAmt = $this->get_total_lineitems_amount($formdata['transfer_invoice_id']);
			$total_paid2 = $transferredInvIdData->total_paid_amount;
			$updateNew = $this->updateInvoiceTable(0,0,$transferedAmt,$formdata['transfer_invoice_id'],$total_paid2);
			
		}
		redirect('invoices/invoicedetails/'.$formdata['invoice_id']); 
	}

	public function transfer_invoice_diif_client($formdata){
		
		//  moving line item in diff client diff acc:: end:: start
		$update = $this->lineitems_model->transfer_line_item_diff_client($formdata);
		
		if(isset($post['moveaspending']) && !empty($post['moveaspending']) && $post['moveaspending'] == 'moveaspending'){
			// inv id = 1
			$transferredAmtToBeAddedToInvId = $this->invoices_model->get_invoice_by_id($formdata['invoice_id']);
			$newAmtToBeAddedToInvIdAmt = $this->get_total_lineitems_amount($formdata['invoice_id']);
			$total_paid1 = $transferredAmtToBeAddedToInvId->total_paid_amount;
			$updateOld = $this->updateInvoiceTable(0,0,$newAmtToBeAddedToInvIdAmt,$formdata['invoice_id'],$total_paid1);
			
        }else{
			// inv id = 1
            $transferredAmtToBeAddedToInvId = $this->invoices_model->get_invoice_by_id($formdata['invoice_id']);
			$newAmtToBeAddedToInvIdAmt = $this->get_total_lineitems_amount($formdata['invoice_id']);
			$total_paid1 = $transferredAmtToBeAddedToInvId->total_paid_amount;
			$updateOld = $this->updateInvoiceTable(0,0,$newAmtToBeAddedToInvIdAmt,$formdata['invoice_id'],$total_paid1);

			// inv id = 4
			$transferredInvIdData = $this->invoices_model->get_invoice_by_id($formdata['transfer_invoice_id']);
			$transferedAmt = $this->get_total_lineitems_amount($formdata['transfer_invoice_id']);
			$total_paid2 = $transferredInvIdData->total_paid_amount;
			$updateNew = $this->updateInvoiceTable(0,0,$transferedAmt,$formdata['transfer_invoice_id'],$total_paid2);
        }
		
		//moving line item in diff client diff acc:: end

		redirect('invoices/invoicedetails/'.$formdata['invoice_id']); 
	}

	function updateInvoiceTable($status,$close,$amount,$invoiceid,$total_paid){	
		if($amount >= $total_paid ){
			$status = 0;
			$close = 0;
		}else{
			$status = 1;
			$close = 1;
		}
		$update = $this->invoices_model->update_invoice_on_addinglineitem($status,$close,$amount,$invoiceid);
		return true;
	}

	function appendtestanalyte(){
		$testuploadid = $this->input->get('id');
		$analyteDetail = $this->lineitems_model->get_anaylyte_details($testuploadid);
		
		if($analyteDetail){
$data = '<table style="width: 100%;" class="table text-md-nowrap" id="testresultpoptable">
<tr>
	<th>Analyte Name</th>
	<th>MIS Analyte Value</th>
	<th>Quantity</th>
	<th>Value</th>
	<th>Measurement</th>
	<th>Result</th>
</tr>';
			foreach ($analyteDetail as $detail) {
				$analyteName = $this->lineitems_model->get_anaylyte_name($detail->analyte_id);
				$result = $this->lineitems_model->get_anaylyte_result($detail->result);
				$data .= '
				<tr>
					<td>'.$analyteName->title.'</td>
					<td>'.$analyteName->mis_analyte_value.'</td>
					<td>'.$detail->analyte_quantity.'</td>
					<td>'.$detail->analyte_quantity_value.'</td>
					<td>'.$detail->analyte_measurement.'</td>
					<td>'.$result->title.'</td>
				</tr>
			'; 
			}
			
$data .= '</table>';
echo $data; exit;
		}else{
			echo "No Records Found!";exit;
		}
		
	}

	function get_total_lineitems_amount($invoiceid){
			$lineitems = $this->lineitems_model->get_total_amt_of_lineitems_invoiceid($invoiceid);
			$total_Invoice_Amt = 0.0;
			if(!empty($lineitems)){
				foreach ($lineitems as $lineitem) {
					$total_Invoice_Amt = ($total_Invoice_Amt)+($lineitem->amount);
				}
			}
			return $total_Invoice_Amt;
	}

	


	

} // Class end.
