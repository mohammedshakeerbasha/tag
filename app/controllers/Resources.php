<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Resources extends MY_Controller
{

	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('resources_model');
		$this->load->model('resources_model');
		$this->load->model('general_model');
		$this->load->model('dashboard_model');
		$this->load->model('employees_model');
		$this->load->model('email_model');
		$this->load->model('communication_model');
		$this->load->library('common/user');
		if (!$this->session->userID):
		redirect('auth');
		endif;
		
    }

	 
    public function client_links(){
    	$data['title'] = 'Client Links';
    	$data['client_links'] =  $this->resources_model->get_client_links();
    	$data['client_links_category'] =  $this->resources_model->get_client_links_category();
    	if($this->input->post()){    		
    	$data =  $this->resources_model->insert_client_links($this->input->post());
    	redirect('resources/client_links');
    	}
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/resources/client_link', $data);

    }

     public function update_links($id){   		
    	$data =  $this->resources_model->update_client_links($this->input->post(),$id);
    	redirect('resources/client_links');
        }

        public function insert_links_category(){   		
    	$data =  $this->resources_model->insert_links_category($this->input->post());
    	redirect('resources/client_links');
        }

     public function update_links_category($id){   		
    	$data =  $this->resources_model->update_links_category($this->input->post(),$id);
    	redirect('resources/client_links');
        }


    
  	public function client_documents(){
  		$data['title'] = 'Client Documents';
    	$data['client_documents'] =  $this->resources_model->get_client_documents();
    	$data['client_document_category'] =  $this->resources_model->get_client_document_category(); 
    	if ($this->input->post(null, true)) {
		   $doc_id=$this->resources_model->insert_client_documents($this->input->post());
         $config['upload_path'] = './upload/resource_document/';
        $config['allowed_types'] = 'gif|jpg|jpeg|JPG|png|PNG|JPEG|pdf|doc|docx|mp4|mpeg|ppt|pptx|xlsx|csv|xls';
               if ($_FILES['file_url']['name']) {

                $secure_directory = 'upload/resource_document';
                $upload_file = $secure_directory;
                if (!is_dir($upload_file)) {
                    mkdir("./" . $upload_file, 0777);
                }
                $config['upload_path']          = $upload_file;
       			 $config['allowed_types'] = 'gif|jpg|jpeg|JPG|png|PNG|JPEG|pdf|doc|docx|mp4|mpeg|ppt|pptx|xlsx|csv|xls';
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('file_url')) {
                } else {
                    $data = $this->upload->data();
                    $document_path          = $upload_file . '/' . $data['file_name'];
                    $this->resources_model->update_document_file($document_path,$doc_id);
                }
            }
 
		   $this->session->set_flashdata('msg','<div class="alert alert-success">Updated successfully</div>');	 
    	redirect('resources/client_documents');
    	}
		$this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/resources/client_document', $data);
    	
    }

    public function insert_document_category(){		
    	$data =  $this->resources_model->insert_document_category($this->input->post());
    	redirect('resources/client_documents');

    }

     public function update_documents($id){ 
	$data =  $this->resources_model->update_client_documents($this->input->post(),$id); 
    	
         $config['upload_path'] = './upload/resource_document/';
        $config['allowed_types'] = 'gif|jpg|jpeg|JPG|png|PNG|JPEG|pdf|doc|docx|mp4|mpeg|ppt|pptx|xlsx|csv|xls';
               if ($_FILES['file_url']['name']) {

                $secure_directory = 'upload/resource_document';
                $upload_file = $secure_directory;
                if (!is_dir($upload_file)) {
                    mkdir("./" . $upload_file, 0777);
                }
                $config['upload_path']          = $upload_file;
       			 $config['allowed_types'] = 'gif|jpg|jpeg|JPG|png|PNG|JPEG|pdf|doc|docx|mp4|mpeg|ppt|pptx|xlsx|csv|xls';
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('file_url')) {
                } else {
                    $data = $this->upload->data();
                    $document_path          = $upload_file . '/' . $data['file_name'];
                    $this->resources_model->update_document_file($document_path,$id);
                }
            }

    	redirect('resources/client_documents');
     }



          public function update_documents_category($id){   		
    	$data =  $this->resources_model->update_documents_category($this->input->post(),$id);
    	redirect('resources/client_documents');
        }

        public function delete_document($id){
        $this->db->where('id',$id);
        $res= $this->db->delete('tag_client_documents');
        redirect('resources/client_documents');

        }

  
} // Class end.
