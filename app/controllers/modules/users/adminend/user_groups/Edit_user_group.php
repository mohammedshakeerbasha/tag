<?php
defined('BASEPATH') OR exit('No direct script access allowed');






class Edit_user_group extends MY_Controller
{
    /**
     * Edit user group.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewEditUserGroup')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');
            $this->load->model('modules/users/adminend/user_groups/user_group_model');

            $data['userGroup'] = $this->user_group_model->userGroup(['groupName', 'groupSlug', 'groupDescription', 'state'], $this->uri->segment(5));

            if (!$data['userGroup']) {
                show_404();
                die;
            }

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('userGroupName', 'User group name', 'trim|htmlspecialchars|required|regex_match[/^[^,]+$/]', ['regex_match' => 'These characters are not allowed: ,']);

            if (strtolower($data['userGroup']->groupSlug) == strtolower($this->input->post('userGroupSlug', true))) {
                $this->form_validation->set_rules('userGroupSlug', 'User group slug', 'trim|htmlspecialchars|required');
            } else {
                $this->form_validation->set_rules('userGroupSlug', 'User group slug', 'trim|htmlspecialchars|required|is_unique[users_user_groups.groupSlug]', ['is_unique' => 'This user group slug is already in use. please choose another one.']);
            }

            $this->form_validation->set_rules('userGroupDescription', 'User group description', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('state', 'State', 'trim|htmlspecialchars|required|in_list[active,inactive]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('in_list', '{field} must be one of: {param}.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Edit user group';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_groups/edit_user_group/edit_user_group', $data);

            } else {

                if (!$this->user->hasPermission('users_editUserGroups')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user_groups/edit_user_group_model');

                    if (!$this->edit_user_group_model->editUserGroup($this->input->post(null, true))) {
                        $this->session->set_flashdata('editUserGroupSuccess', false);
                    } else {
                        $this->session->set_flashdata('editUserGroupSuccess', true);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
