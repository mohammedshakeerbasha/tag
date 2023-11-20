<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Profile_picture extends MY_Controller
{

    
    /**
     * User profile settings.
     */
   public function index(){ 
      $this->load->model('modules/users/adminend/users/user_model');
        $id = $this->session->userID; 
         $config['upload_path'] = './upload/admin_profile_images_upload/';
        $config['allowed_types'] = 'gif|jpg|jpeg|JPG|png|PNG|JPEG';
        $config['max_size'] = 5120;
               if ($_FILES['uploadFile']['name']) {

                $secure_directory = 'upload/admin_profile_images_upload';
                $upload_file = $secure_directory;
                if (!is_dir($upload_file)) {
                    mkdir("./" . $upload_file, 0777);
                }
                $config['upload_path']          = $upload_file;
                $config['allowed_types']        = 'gif|jpg|png|pdf|doc|docx|jpeg';
                $config['max_size']             = 1000000;
                $this->load->library('upload', $config);
                if (!$this->upload->do_upload('uploadFile')) {
                } else {
                    $data = $this->upload->data();
                    $document_path          = $upload_file . '/' . $data['file_name'];
                    $this->user_model->update_profile_pic($document_path,$id);
                }
            }

      
         $data['title'] = 'User settings';
         $data['profile_data']=$this->user_model->get_user_by_id($this->session->userID);
         $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/profile/profile_picture', $data);
    }

    
} // Class end.
