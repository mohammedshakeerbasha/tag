<?php
defined('BASEPATH') OR exit('No direct script access allowed');






class Account extends MY_Controller
{
    /**
     * User account settings.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewAccountUserSettings')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');
            $this->load->model('modules/users/common/users_callables_model');
            $this->load->model('modules/users/adminend/users/user_model');

            $data['user'] = $this->user_model->user(['users_users.ID', 'username'], $this->session->userID);

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('currentPassword', 'Current password', [
                ['isCurrentPasswordCorrect', [$this->users_callables_model, 'isCurrentPasswordCorrect']]
            ]);

            if (strtolower($data['user']->username) == strtolower($this->input->post('username', true))) {
                $this->form_validation->set_rules('username', 'Username', 'trim|htmlspecialchars|required|alpha_numeric|min_length[5]|max_length[15]');
            } else {
                $this->form_validation->set_rules('username', 'Username', 'trim|htmlspecialchars|required|alpha_numeric|min_length[5]|max_length[15]|is_unique[users_users.username]', ['is_unique' => 'This username has already been taken. please choose another username.']);
            }

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('min_length', '{field} must be at least {param} characters in length.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');
            $this->form_validation->set_message('alpha_numeric', '{field} can only contain alpha-numeric characters (A-Z a-z 0-9).');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'User settings';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/account/account', $data);

            } else {

                if (!$this->user->hasPermission('users_editAccountUserSettings')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user/settings/account/user_settings_account_model');

                    if ($this->user_settings_account_model->changeUsername($this->input->post(null, true), $data['user']->ID)) {
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
