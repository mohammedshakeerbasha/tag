<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Calling common adminend header view file.
$this->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/header'); ?>
<style type="text/css">
	a.btn.btn-primary.btn-sm {
    margin: 3px 0px;
}
</style>

<!-- container -->
<div class="container-fluid">
	<div class="col-lg-12  mt-3">
		<div class="card">
			<div class="card-body">
				<div class="tabs-menu ">
					<ul class="nav nav-tabs profile navtab-custom panel-tabs">
						<li class="">
							<a href="#succesResult" data-bs-toggle="tab" aria-expanded="true"  class="active" > 
								<span class="visible-xs">
								<i class="fas fa-inbox tx-16 me-1"></i> </span>
								<span class="hidden-xs">Paid Invoices</span> 
							</a>
						</li>
						<li class="">
							<a href="#failedResult" data-bs-toggle="tab" aria-expanded="false"> 
								<span class="visible-xs">
								<i class="fas fa-inbox tx-16 me-1"></i> </span>
								<span class="hidden-xs">Failed Payment</span> 
							</a>
						</li>
					</ul>
				</div>
				<div class="tab-content border-start border-bottom border-right border-top-0 p-4 br-dark">
					<div class="tab-pane active" id="succesResult">
						<div class="table-responsive">
							<table class="table text-md-nowrap" id="address">
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
										<th class="border-bottom-0">Invoice ID</th>
										<th class="border-bottom-0">Client Name</th>
										<!-- <th class="border-bottom-0">Client ID</th> -->
										<th class="border-bottom-0">Client Account ID | Ref</th>
										<th class="border-bottom-0">Amount</th>
										<th class="border-bottom-0">Paid Amount</th>
										<th class="border-bottom-0">Status</th>
										<!-- <th class="wd-10p border-bottom-0">Invoice Closed</th> -->
										<th class="border-bottom-0">Invoice Date</th>
										<!-- <th class="border-bottom-0">Action</th> -->
									</tr>
								</thead>
								<tbody>
								<?php 
											
											foreach ($batchPaidInvoices as $list) { 

												$clientName = $this->clients_model->get_client($list->clientID);
												$clientName = $clientName->client_name;

												$client_acc_id = $list->client_account_id;

												$clientRef = $this->clients_model->get_client_account($client_acc_id);
												$clientRef = $clientRef->account_reference;
												?>
											<!-- <tr>
												<td><?php echo $list->invoice_id ?></td>												
												<td><?php echo $clientName; ?></td>
												
												<td><?php echo $list->client_account_id." - ".$clientRef ?></td>												
												<td><?php echo "$".number_format((float)$list->amount, 2, '.', ''); 
												$totalamount=$list->amount+$totalamount; ?></td>
												<td><?php echo "$".number_format((float)$list->total_paid_amount, 2, '.', ''); 
												$totalpaidamount=$list->total_paid_amount+$totalpaidamount; ?></td>										
												<td><?php 
												if($list->status == 0 ){
												echo "Not Paid";
												}
												if($list->status == 1 ){
												echo "Paid";
												} ?>
												</td>

												<td><?php 

												$time=strtotime($list->invoice_date);
												$month=date("m",$time);
												$year=date("Y",$time);
												$date=date("d",$time);

												echo $month."/".$date."/".$year; 
												?></td>
												
												
												
											</tr> -->
											<?php } ?>
								</tbody>
							</table>

						</div>
					</div>
					<div class="tab-pane" id="failedResult">
						<div class="table-responsive">
							<table class="table text-md-nowrap" id="address">
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
										<th class="border-bottom-0">Invoice ID</th>
										<th class="border-bottom-0">Client Name</th>
										<!-- <th class="border-bottom-0">Client ID</th> -->
										<th class="border-bottom-0">Client Account ID | Ref</th>
										<th class="border-bottom-0">Amount</th>
										<th class="border-bottom-0">Paid Amount</th>
										<th class="border-bottom-0">Status</th>
										<!-- <th class="wd-10p border-bottom-0">Invoice Closed</th> -->
										<th class="border-bottom-0">Invoice Date</th>
										<th class="border-bottom-0">Action</th>
									</tr>
								</thead>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php

// Calling common adminend footer view file.
$this->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/footer'); ?>

    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>