<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class Edit_user_tag extends MY_Controller
{
    /**
     * Edit user tag.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewEditUserTag')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->library('form_validation');
            $this->load->model('modules/users/adminend/user_tags/user_tag_model');

            $data['userTag'] = $this->user_tag_model->userTag(['tagName', 'tagSlug', 'tagDescription', 'state'], $this->uri->segment(5));

            if (!$data['userTag']) {
                show_404();
                die;
            }

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('userTagName', 'User tag name', 'trim|htmlspecialchars|required|regex_match[/^[^,]+$/]', ['regex_match' => 'These characters are not allowed: ,']);

            if (strtolower($data['userTag']->tagSlug) == strtolower($this->input->post('userTagSlug', true))) {
                $this->form_validation->set_rules('userTagSlug', 'User tag slug', 'trim|htmlspecialchars|required');
            } else {
                $this->form_validation->set_rules('userTagSlug', 'User tag slug', 'trim|htmlspecialchars|required|is_unique[users_user_tags.tagSlug]', ['is_unique' => 'This user tag slug is already in use. please choose another one.']);
            }

            $this->form_validation->set_rules('userTagDescription', 'User tag description', 'trim|htmlspecialchars');
            $this->form_validation->set_rules('state', 'State', 'trim|htmlspecialchars|required|in_list[active,inactive]');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('in_list', '{field} must be one of: {param}.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Edit user tag';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_tags/edit_user_tag/edit_user_tag', $data);

            } else {

                if (!$this->user->hasPermission('users_editUserTags')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    $this->load->model('modules/users/adminend/user_tags/edit_user_tag_model');

                    if (!$this->edit_user_tag_model->editUserTag($this->input->post(null, true))) {
                        $this->session->set_flashdata('editUserTagSuccess', false);
                    } else {
                        $this->session->set_flashdata('editUserTagSuccess', true);
                    }

                    redirect(current_url());

                }

            }

        }
    }
} // Class end.
