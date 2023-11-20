<?php
class Cronjob extends MY_Controller
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

    function addlatefees(){
        
        $dataArr = [];
        $invoice_id = '';
        $unpaid_invoices_all = $this->batchinvoice_model->get_due_invoices();
        $unpaid_invoices_with_marklatefee_lineitem = $this->batchinvoice_model->get_due_invoices_with_latefeelineitem();

        $arrInvoiceIdWithLatefee = [];
        $k = 0;
        foreach ($unpaid_invoices_with_marklatefee_lineitem as $invoices) {
            $arrInvoiceIdWithLatefee[$k] = $invoices->id;
            $k++;
        }

        $batchlineItem = [];
        $batchupdateInvoices = [];
        $b = 0;
        $invoiceid = '';
        foreach ($unpaid_invoices_all as $invoice) {
            if($invoice->id == 3){
                if($invoice->id != $invoiceid){
                    if(!(in_array($invoice->id, $arrInvoiceIdWithLatefee, $strict))){
                            $price = ($invoice->main_amount)*(0.015);
                            $batchlineItem[$b] = [
                            'invoice_detail_description' => '60 Day Late Fee On Remaining Balance 1.5%',
                            'amount' =>$price,
                            'client_id' =>$invoice->clientId,
                            'client_account_id' =>$invoice->client_account_id,
                            'invoice_id' =>$invoice->id,
                            'mark_as_latefee' =>1,
                            'date_of_service' =>date("Y-m-d"),
                            ];

                            $updatedAmt =  ($price)+($invoice->main_amount);
                            $batchupdateInvoices[$b] = [
                            'id' => $invoice->id,
                            'amount' =>$updatedAmt,
                            ];

                            $b++;
                    }
                }
                $invoiceid = $invoice->id;
            }
            
        }
        if(!empty($batchlineItem)){
            $this->batchinvoice_model->batch_insert_invoice_details($batchlineItem);
        }
        if(!empty($batchupdateInvoices)){
            $this->batchinvoice_model->batch_update_by_invoiceid($batchupdateInvoices);
        }
       
       
echo "updated records for invoiceid: 3";
        
    }
}

?>