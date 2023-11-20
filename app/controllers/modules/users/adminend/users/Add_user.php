<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class Add_user extends MY_Controller
{
    /**
     * Add new user.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewCreateUser')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('firstName', 'First name', 'trim|htmlspecialchars|required|max_length[50]');
            $this->form_validation->set_rules('surname', 'Surname', 'trim|htmlspecialchars|required|max_length[50]');
            $this->form_validation->set_rules('username', 'Username', 'trim|htmlspecialchars|required|alpha_numeric|min_length[5]|max_length[15]|is_unique[users_users.username]', ['is_unique' => 'This username has already been taken. please choose another username.']);
            $this->form_validation->set_rules('email', 'Email', 'trim|htmlspecialchars|strtolower|required|valid_email|max_length[250]|is_unique[users_users.email]', ['is_unique' => 'This email has already been registered. please choose another email.']);
            $this->form_validation->set_rules('emailVerification', 'Email verification', 'trim|htmlspecialchars|required|in_list[1,0]');
            $this->form_validation->set_rules('password', 'New password', 'trim|htmlspecialchars|min_length[' . $this->preferences->type('system')->item('users_minimumPasswordLength') . ']');
            $this->form_validation->set_rules('datetimeCreated', 'Date & time', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('notify', 'Notify', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('state', 'State', 'trim|htmlspecialchars|required|in_list[active,inactive]');

            $this->form_validation->set_rules('status', 'Status', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('tags', 'Tags', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('roles', 'Roles', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('groups', 'Groups', 'trim|htmlspecialchars');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('min_length', '{field} must be at least {param} characters in length.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');
            $this->form_validation->set_message('alpha_numeric', '{field} can only contain alpha-numeric characters (A-Z a-z 0-9).');
            $this->form_validation->set_message('valid_email', '{field} must be a valid email address.');
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

                $data['title'] = 'Add user';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/users/add_user/add_user', $data);

            } else {

                if (!$this->user->hasPermission('users_createUsers')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    // Add new user.
                    $this->load->model('modules/users/adminend/users/add_user_model');
                    $insertID = $this->add_user_model->addUser($this->input->post(null, true));

                    if ($insertID) {

                        // Assign user meta data.
                        $assigns = [];

                        if (!empty($this->input->post('status', true))) {
                            // Assign user status.
                            $this->load->model('modules/users/adminend/user_statuses/user_status_relations_model');
                            $assigns['status'] = $this->user_status_relations_model->assignUserStatus($this->input->post('status', true), $insertID);
                        }

                        if (!empty($this->input->input_stream('tags', true))) {
                            // Assign user tags.
                            $this->load->model('modules/users/adminend/user_tags/user_tag_relations_model');
                            $assigns['tags'] = $this->user_tag_relations_model->assignUserTags($this->input->input_stream('tags', true), $insertID);
                        }

                        if (!empty($this->input->input_stream('roles', true))) {
                            // Assign user roles.
                            $this->load->model('modules/users/adminend/user_roles/user_role_relations_model');
                            $assigns['roles'] = $this->user_role_relations_model->assignUserRoles($this->input->input_stream('roles', true), $insertID);
                        }

                        if (!empty($this->input->input_stream('groups', true))) {
                            // Assign user groups.
                            $this->load->model('modules/users/adminend/user_groups/user_group_relations_model');
                            $assigns['groups'] = $this->user_group_relations_model->assignUserGroups($this->input->input_stream('groups', true), $insertID);
                        }

                    }

                    // Verify if $insertID & all the created $assigns array elements are return true.
                    if ($insertID && (count(array_keys($assigns, true)) == count($assigns))) {

                        // Notify new user about their account feature.
                        if (!empty($this->input->input_stream('notify', true))) {

                            if (in_array('user.new_account', $this->input->input_stream('notify', true))) {

                                // Send notification email to user about their new account.
                                $this->load->library('email');

                                $this->email->from($this->preferences->type('system')->item('app_email'), $this->preferences->type('system')->item('app_name'));
                                $this->email->to($this->input->post('email', true));

                                $this->email->subject('Your ' . $this->preferences->type('system')->item('app_name') . ' account has been created');
                                $this->email->message($this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/users/add_user/email/new_account', $data = [], true));

                                if ($this->email->send()) {

                                    $this->session->set_flashdata('notifyUserNewAccount', true);

                                } else {

                                    log_message('error', 'Notify user about their new account email sent was failed.');
                                    $this->session->set_flashdata('notifyUserNewAccount', false);

                                }

                            }

                        }

                        $this->session->set_flashdata('addUserSuccess', true);
                        $this->session->set_flashdata('insertID', $insertID);

                    } else {

                        $this->session->set_flashdata('addUserSuccess', false);

                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
