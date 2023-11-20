<?php
defined('BASEPATH') OR exit('No direct script access allowed');




class Security extends MY_Controller
{
    /**
     * User security settings.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewSecurityUserSettings')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');
            $this->load->model('modules/users/common/users_callables_model');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('currentPassword', 'Current password', [
                ['isCurrentPasswordCorrect', [$this->users_callables_model, 'isCurrentPasswordCorrect']]
            ]);
            $this->form_validation->set_rules('password', 'New password', 'trim|htmlspecialchars|required|min_length[' . $this->preferences->type('system')->item('users_minimumPasswordLength') . ']');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('min_length', '{field} must be at least {param} characters in length.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'User settings';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/security/security', $data);

            } else {

                if (!$this->user->hasPermission('users_editSecurityUserSettings')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/auth/userend/reset_model');

                    if (!$this->reset_model->resetPassword($this->session->userID, $this->input->post('password', true))) {

                        $this->session->set_flashdata('editUserSettingsSuccess', false);
                        redirect(current_url());

                    } else {

                        $this->user->signout(); // https://security.stackexchange.com/questions/105124/why-should-you-redirect-the-user-to-a-login-page-after-a-password-reset
                        $this->session->set_flashdata('resetPassword', true);
                        redirect('auth');

                    }

                }

            }

        }
    }
} // Class end.
