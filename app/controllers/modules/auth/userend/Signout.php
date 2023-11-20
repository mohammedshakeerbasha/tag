<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Signout extends MY_Controller
{
    /**
     * User signout.
     */
    public function index()
    {   
        if (!$this->user->isSignin()) {
            
            redirect('auth');

        } else {

            if (empty($this->input->post(null, true))) {

                $data['title'] = 'Sign out';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/signout/signout', $data);

            } else {

                $this->user->signout();
                $this->session->set_flashdata('signoutSuccess', true);
                redirect('auth');

            }
            
        }
    }
} // Class end.
