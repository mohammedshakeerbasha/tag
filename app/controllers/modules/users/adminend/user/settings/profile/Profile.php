<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Profile extends MY_Controller
{
    /**
     * User profile settings.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewProfileUserSettings')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');
            $this->load->model('modules/users/adminend/users/user_model');

            $data['user'] = $this->user_model->user(['users_users.ID', 'firstName', 'surname'], $this->session->userID);

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('firstName', 'First name', 'trim|htmlspecialchars|required|max_length[50]');
            $this->form_validation->set_rules('surname', 'Surname', 'trim|htmlspecialchars|required|max_length[50]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'User settings';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/profile/profile', $data);

            } else {

                if (!$this->user->hasPermission('users_editProfileUserSettings')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user/settings/profile/user_settings_profile_model');

                    if ($this->user_settings_profile_model->updateProfile($this->input->post(null, true), $data['user']->ID)) {
                        $this->session->set_flashdata('editUserSettingsSuccess', true);
                    } else {
                        $this->session->set_flashdata('editUserSettingsSuccess', false);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
