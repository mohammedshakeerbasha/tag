<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class Edit_user_role extends MY_Controller
{
    /**
     * Edit user role.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewEditUserRole')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');
            $this->load->model('modules/users/adminend/user_roles/user_role_model');

            $data['userRole'] = $this->user_role_model->userRole(['roleName', 'roleSlug', 'roleDescription', 'state'], $this->uri->segment(5));

            if (!$data['userRole']) {
                show_404();
                die;
            }

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('userRoleName', 'User role name', 'trim|htmlspecialchars|required|regex_match[/^[^,]+$/]', ['regex_match' => 'These characters are not allowed: ,']);

            if (strtolower($data['userRole']->roleSlug) == strtolower($this->input->post('userRoleSlug', true))) {
                $this->form_validation->set_rules('userRoleSlug', 'User role slug', 'trim|htmlspecialchars|required');
            } else {
                $this->form_validation->set_rules('userRoleSlug', 'User role slug', 'trim|htmlspecialchars|required|is_unique[users_user_roles.roleSlug]', ['is_unique' => 'This user role slug is already in use. please choose another one.']);
            }

            $this->form_validation->set_rules('userRoleDescription', 'User role description', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('state', 'State', 'trim|htmlspecialchars|required|in_list[active,inactive]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('in_list', '{field} must be one of: {param}.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Edit user role';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_roles/edit_user_role/edit_user_role', $data);

            } else {

                if (!$this->user->hasPermission('users_editUserRoles')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user_roles/edit_user_role_model');

                    if (!$this->edit_user_role_model->editUserRole($this->input->post(null, true))) {
                        $this->session->set_flashdata('editUserRoleSuccess', false);
                    } else {
                        $this->session->set_flashdata('editUserRoleSuccess', true);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
