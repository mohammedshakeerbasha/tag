<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class Add_user_permission extends MY_Controller
{
    /**
     * Add new user permission.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewCreateUserPermission')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('userPermissionModuleSlug', 'User permission module slug', 'trim|htmlspecialchars|required|alpha_dash');
            $this->form_validation->set_rules('userPermissionName', 'User permission name', 'trim|htmlspecialchars|required');
            $this->form_validation->set_rules('userPermissionKey', 'User permission key', 'trim|htmlspecialchars|required|alpha_dash|is_unique[users_permissions.permissionKey]', ['is_unique' => 'This user permission key is already in use. please choose another one.']);
            $this->form_validation->set_rules('userPermissionDescription', 'User permission description', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('state', 'State', 'trim|htmlspecialchars|required|in_list[active,inactive]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('in_list', '{field} must be one of: {param}.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Add user permission';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_permissions/add_user_permission/add_user_permission', $data);

            } else {

                if (!$this->user->hasPermission('users_createUserPermissions')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user_permissions/add_user_permission_model');
                    $insertID = $this->add_user_permission_model->addUserPermission($this->input->post(null, true));

                    if (!$insertID) {
                        $this->session->set_flashdata('addUserPermissionSuccess', false);
                    } else {
                        $this->session->set_flashdata('addUserPermissionSuccess', true);
                        $this->session->set_flashdata('insertID', $insertID);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
