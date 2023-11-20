<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class Edit_user_status extends MY_Controller
{
    /**
     * Edit user status.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewEditUserStatus')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');
            $this->load->model('modules/users/adminend/user_statuses/user_status_model');

            $data['userStatus'] = $this->user_status_model->userStatus(['statusName', 'statusSlug', 'statusDescription', 'state'], $this->uri->segment(5));

            if (!$data['userStatus']) {
                show_404();
                die;
            }

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('userStatusName', 'User status name', 'trim|htmlspecialchars|required|regex_match[/^[^,]+$/]', ['regex_match' => 'These characters are not allowed: ,']);

            if (strtolower($data['userStatus']->statusSlug) == strtolower($this->input->post('userStatusSlug', true))) {
                $this->form_validation->set_rules('userStatusSlug', 'User status slug', 'trim|htmlspecialchars|required');
            } else {
                $this->form_validation->set_rules('userStatusSlug', 'User status slug', 'trim|htmlspecialchars|required|is_unique[users_user_statuses.statusSlug]', ['is_unique' => 'This user status slug is already in use. please choose another one.']);
            }

            $this->form_validation->set_rules('userStatusDescription', 'User status description', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('state', 'State', 'trim|htmlspecialchars|required|in_list[active,inactive]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('in_list', '{field} must be one of: {param}.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Edit user status';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_statuses/edit_user_status/edit_user_status', $data);

            } else {

                if (!$this->user->hasPermission('users_editUserStatuses')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user_statuses/edit_user_status_model');

                    if (!$this->edit_user_status_model->editUserStatus($this->input->post(null, true))) {
                        $this->session->set_flashdata('editUserStatusSuccess', false);
                    } else {
                        $this->session->set_flashdata('editUserStatusSuccess', true);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
