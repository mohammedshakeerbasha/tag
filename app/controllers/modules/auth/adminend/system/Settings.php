<?php
defined('BASEPATH') OR exit('No direct script access allowed');





class Settings extends MY_Controller
{
    /**
     * Auth module settings.
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

            $this->config->load('modules/auth/config');
            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('auth_signupOption', 'Sign up option', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('auth_signupDefaultUserState', 'Sign up default user state', 'trim|htmlspecialchars|required|in_list[active,inactive]');
            $this->form_validation->set_rules('auth_signupWelcomeEmail', 'Sign up welcome email', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('auth_signupDefaultUserStatus', 'Sign up default user status', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('auth_signupDefaultUserTags', 'Sign up default user tags', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('auth_signupDefaultUserRoles', 'Sign up default user roles', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('auth_signupDefaultUserGroups', 'Sign up default user groups', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('auth_passwordResetOption', 'Password reset option', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('auth_passwordResetTokenExpirationInMinutes', 'Password reset token expiration in minutes', 'trim|htmlspecialchars|required|is_natural_no_zero|max_length[250]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('is_natural_no_zero', '{field} must only contain digits & must be greater than zero.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');
            $this->form_validation->set_message('in_list', '{field} must be one of: {param}.');

            if ($this->form_validation->run() == false) {

                $this->load->library('common/crud'); // Custom library.
                $this->load->model('modules/users/adminend/user_statuses/user_statuses_model');
                $this->load->model('modules/users/adminend/user_tags/user_tags_model');
                $this->load->model('modules/users/adminend/user_roles/user_roles_model');
                $this->load->model('modules/users/adminend/user_groups/user_groups_model');

                $data['userStatuses'] = $this->user_statuses_model->userStatuses(
                    [
                        'ID',
                        'statusName',
                        'state',

                        'userCount'
                    ],
                    'userCount',
                    'DESC'
                );

                $data['userTags'] = $this->user_tags_model->userTags(
                    [
                        'ID',
                        'tagName',
                        'state',

                        'userCount'
                    ],
                    'userCount',
                    'DESC'
                );

                $data['userRoles'] = $this->user_roles_model->userRoles(
                    [
                        'ID',
                        'roleName',
                        'state',

                        'userCount'
                    ],
                    'userCount',
                    'DESC'
                );

                $data['userGroups'] = $this->user_groups_model->userGroups(
                    [
                        'ID',
                        'groupName',
                        'state',

                        'userCount'
                    ],
                    'userCount',
                    'DESC'
                );

                $data['title'] = 'System settings';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/adminend/system/settings/settings', $data);

            } else {

                if (!$this->user->hasPermission('app_editSystemSettings')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $postData = $this->input->input_stream(null, true);

                    // Remove unnecessary array keys.
                    unset($postData['submit']);
                    unset($postData[$this->config->item('csrf_token_name')]);

                    foreach ($postData as $settingKey => $settingValue) {
                        $processedData[] = [
                            'settingKey' => $settingKey,
                            'settingValue' => (is_array($settingValue) ? implode(',', $settingValue) : $settingValue),
                            'moduleSlug' => 'auth' // All lowercase and words should concatenated with underscore.
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
