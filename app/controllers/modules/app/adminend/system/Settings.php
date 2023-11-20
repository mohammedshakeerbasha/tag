<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Settings extends MY_Controller
{
    /**
     * App module settings.
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

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('app_name', 'App name', 'trim|htmlspecialchars|required|max_length[50]');
            $this->form_validation->set_rules('app_slogan', 'App slogan', 'trim|htmlspecialchars|max_length[150]');
            $this->form_validation->set_rules('app_email', 'App email', 'trim|htmlspecialchars|required|valid_email|max_length[250]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');
            $this->form_validation->set_message('valid_email', '{field} must be a valid email address.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'System settings';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/app/adminend/system/settings/settings', $data);

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
                            'moduleSlug' => 'app' // All lowercase and words should concatenated with underscore.
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
