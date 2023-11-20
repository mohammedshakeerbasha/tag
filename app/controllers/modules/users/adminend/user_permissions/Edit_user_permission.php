<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class Edit_user_permission extends MY_Controller
{
    /**
     * Edit user permission.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewEditUserPermission')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');
            $this->load->model('modules/users/adminend/user_permissions/user_permission_model');

            $data['userPermission'] = $this->user_permission_model->userPermission(['permissionKey', 'moduleSlug', 'permissionName', 'permissionDescription', 'permissionType', 'state'], $this->uri->segment(5));

            if (!$data['userPermission']) {
                show_404();
                die;
            }

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            if (strtolower($data['userPermission']->permissionType) == 'local') {
                $this->form_validation->set_rules('userPermissionModuleSlug', 'User permission module slug', 'trim|htmlspecialchars|required|alpha_dash');
                $this->form_validation->set_rules('userPermissionName', 'User permission name', 'trim|htmlspecialchars|required');

                if (strtolower($data['userPermission']->permissionKey) == strtolower($this->input->post('userPermissionKey', true))) {
                    $this->form_validation->set_rules('userPermissionKey', 'User permission key', 'trim|htmlspecialchars|required|alpha_dash');
                } else {
                    $this->form_validation->set_rules('userPermissionKey', 'User permission key', 'trim|htmlspecialchars|required|alpha_dash|is_unique[users_permissions.permissionKey]', ['is_unique' => 'This user permission key is already in use. please choose another one.']);
                }

                $this->form_validation->set_rules('userPermissionDescription', 'User permission description', 'trim|htmlspecialchars');
            }

            $this->form_validation->set_rules('state', 'State', 'trim|htmlspecialchars|required|in_list[active,inactive]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('in_list', '{field} must be one of: {param}.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Edit user permission';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_permissions/edit_user_permission/edit_user_permission', $data);

            } else {

                if (!$this->user->hasPermission('users_editUserPermissions')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user_permissions/edit_user_permission_model');

                    if (!$this->edit_user_permission_model->editUserPermission($this->input->post(null, true), $data['userPermission']->permissionType)) {
                        $this->session->set_flashdata('editUserPermissionSuccess', false);
                    } else {
                        $this->session->set_flashdata('editUserPermissionSuccess', true);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
