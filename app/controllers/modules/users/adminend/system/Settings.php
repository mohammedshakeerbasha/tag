<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class Settings extends MY_Controller
{
    /**
     * Users module settings.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('app_viewSystemSettings')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->config->load('modules/users/config');
            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('users_emailVerificationOption', 'Email verification option', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('users_emailVerificationTokenExpirationInMinutes', 'Email verification token expiration in minutes', 'trim|htmlspecialchars|required|is_natural_no_zero|max_length[250]');
            $this->form_validation->set_rules('users_minimumPasswordLength', 'Minimum password character length', 'trim|htmlspecialchars|required|is_natural_no_zero|max_length[250]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('is_natural_no_zero', '{field} must only contain digits & must be greater than zero.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'System settings';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/system/settings/settings', $data);

            } else {

                if (!$this->user->hasPermission('app_editSystemSettings')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $postData = $this->input->post(null, true);

                    // Remove unnecessary array keys.
                    unset($postData['submit']);
                    unset($postData[$this->config->item('csrf_token_name')]);

                    foreach ($postData as $settingKey => $settingValue) {
                        $processedData[] = [
                            'settingKey' => $settingKey,
                            'settingValue' => $settingValue,
                            'moduleSlug' => 'users' // All lowercase and words should concatenated with underscore.
                        ];
                    }

                    // Database operations.
                    if ($this->system_settings_model->replaceSettings($processedData)) {
                        $this->session->set_flashdata('editSystemSettingsSuccess', true);
                    } else {
                        $this->session->set_flashdata('editSystemSettingsSuccess', false);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
