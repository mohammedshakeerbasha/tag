<?php
defined('BASEPATH') OR exit('No direct script access allowed');






class Add_user_role extends MY_Controller
{
    /**
     * Add new user role.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewCreateUserRole')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('userRoleName', 'User role name', 'trim|htmlspecialchars|required|regex_match[/^[^,]+$/]', ['regex_match' => 'These characters are not allowed: ,']);
            $this->form_validation->set_rules('userRoleSlug', 'User role slug', 'trim|htmlspecialchars|required|is_unique[users_user_roles.roleSlug]', ['is_unique' => 'This user role slug is already in use. please choose another one.']);
            $this->form_validation->set_rules('userRoleDescription', 'User role description', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('state', 'State', 'trim|htmlspecialchars|required|in_list[active,inactive]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('in_list', '{field} must be one of: {param}.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Add user role';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_roles/add_user_role/add_user_role', $data);

            } else {

                if (!$this->user->hasPermission('users_createUserRoles')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user_roles/add_user_role_model');
                    $insertID = $this->add_user_role_model->addUserRole($this->input->post(null, true));

                    if (!$insertID) {
                        $this->session->set_flashdata('addUserRoleSuccess', false);
                    } else {
                        $this->session->set_flashdata('addUserRoleSuccess', true);
                        $this->session->set_flashdata('insertID', $insertID);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
