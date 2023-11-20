<?php
defined('BASEPATH') OR exit('No direct script access allowed');






class Signup extends MY_Controller
{
    /**
     * User signup.
     */
    public function index()
    {
        $this->config->load('modules/auth/config');

        $data['title'] = 'Sign up';

        if ($this->user->isSignin()) {

            redirect($this->preferences->type('system')->item('auth_signinRedirectRoute'));

        } elseif (!$this->preferences->type('system')->item('auth_signupOption')) {

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/signup/signup_disabled', $data);

        } else {

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('firstName', 'First name', 'trim|htmlspecialchars|required|max_length[50]');
            $this->form_validation->set_rules('surname', 'Surname', 'trim|htmlspecialchars|required|max_length[50]');
            $this->form_validation->set_rules('username', 'Username', 'trim|htmlspecialchars|required|alpha_numeric|min_length[5]|max_length[15]|is_unique[users_users.username]', ['is_unique' => 'This username has already been taken. please choose another username.']);
            $this->form_validation->set_rules('email', 'Email', 'trim|htmlspecialchars|strtolower|required|valid_email|max_length[250]|is_unique[users_users.email]', ['is_unique' => 'This email has already been registered. you can try <a href="' . site_url('auth') . '">sign in</a> or <a href="' . site_url(['auth', 'reset']) . '">recover</a> the account that associated with this email. if you need to sign up then please use another email.']);
            $this->form_validation->set_rules('password', 'New password', 'trim|htmlspecialchars|required|min_length[' . $this->preferences->type('system')->item('users_minimumPasswordLength') . ']');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('min_length', '{field} must be at least {param} characters in length.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');
            $this->form_validation->set_message('alpha_numeric', '{field} can only contain alpha-numeric characters (A-Z a-z 0-9).');
            $this->form_validation->set_message('valid_email', '{field} must be a valid email address.');

            if ($this->form_validation->run() == false) {

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/signup/signup', $data);

            } else {

                // Add new user.
                $this->load->model('modules/auth/userend/signup_model');
                $insertID = $this->signup_model->signup($this->input->post(null, true));

                if ($insertID) {

                    // Assign user meta data.
                    $assigns = [];

                    if (!empty($this->preferences->type('system')->item('auth_signupDefaultUserStatus'))) {
                        // Assign user status.
                        $this->load->model('modules/users/adminend/user_statuses/user_status_relations_model');
                        $assigns['status'] = $this->user_status_relations_model->assignUserStatus($this->preferences->type('system')->item('auth_signupDefaultUserStatus'), $insertID);
                    }

                    if (!empty($this->preferences->type('system')->item('auth_signupDefaultUserTags'))) {
                        // Assign user tags.
                        $this->load->model('modules/users/adminend/user_tags/user_tag_relations_model');
                        $assigns['tags'] = $this->user_tag_relations_model->assignUserTags(explode(',', $this->preferences->type('system')->item('auth_signupDefaultUserTags')), $insertID);
                    }

                    if (!empty($this->preferences->type('system')->item('auth_signupDefaultUserRoles'))) {
                        // Assign user roles.
                        $this->load->model('modules/users/adminend/user_roles/user_role_relations_model');
                        $assigns['roles'] = $this->user_role_relations_model->assignUserRoles(explode(',', $this->preferences->type('system')->item('auth_signupDefaultUserRoles')), $insertID);
                    }

                    if (!empty($this->preferences->type('system')->item('auth_signupDefaultUserGroups'))) {
                        // Assign user groups.
                        $this->load->model('modules/users/adminend/user_groups/user_group_relations_model');
                        $assigns['groups'] = $this->user_group_relations_model->assignUserGroups(explode(',', $this->preferences->type('system')->item('auth_signupDefaultUserGroups')), $insertID);
                    }

                }

                // Verify if $insertID & all the created $assigns array elements are return true.
                if ($insertID && (count(array_keys($assigns, true)) == count($assigns))) {

                    // Send welcome email.
                    if ($this->preferences->type('system')->item('auth_signupWelcomeEmail')) {
                        $this->load->library('email');

                        $this->email->from($this->preferences->type('system')->item('app_email'), $this->preferences->type('system')->item('app_name'));
                        $this->email->to($this->input->post('email', true));

                        $this->email->subject('Welcome to ' . $this->preferences->type('system')->item('app_name') . '!');
                        $this->email->message($this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/signup/email/welcome', $data, true));

                        $this->email->send();
                    }

                    $this->session->set_flashdata('signupSuccess', true);

                    if ($this->url->isNextUrl()) {
                        redirect('auth' . '?next=' . $this->url->nextUrl());
                    } else {
                        redirect('auth');
                    }

                } else {

                    $this->session->set_flashdata('signupSuccess', false);

                    if ($this->url->isNextUrl()) {
                        redirect('auth/signup' . '?next=' . $this->url->nextUrl());
                    } else {
                        redirect('auth/signup');
                    }

                }

            }

        }
    }
} // Class end.
