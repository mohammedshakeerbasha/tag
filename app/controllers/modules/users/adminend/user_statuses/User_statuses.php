<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class User_statuses extends MY_Controller
{
    /**
     * User statuses CRUD index.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewUserStatuses')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            // Load stuff.
            $this->load->library('common/crud'); // Custom library.
            $this->load->library('common/paginator'); // Custom library.
            $this->load->model('modules/users/adminend/user_statuses/user_statuses_model');

            // Gather user statuses.
            $data['userStatuses'] = $this->user_statuses_model->userStatuses(
                // Selected fields.
                [
                    // 'user statuses' table.
                    'ID',
                    'statusName',
                    'statusSlug',
                    'statusDescription',
                    'state',

                    // 'users' table.
                    'userCount'
                ],

                // Default order by field.
                'ID',

                // Default order field.
                'DESC',

                // Number of rows per page.
                $this->config->item('per_page'),

                // Search fields.
                [
                    // 'user statuses' table.
                    'statusName',
                    'statusDescription',
                    'statusSlug',

                    // 'users' table.
                    'userCount'
                ],

                // Filter fields.
                [
                    // 'user statuses' table.
                    'state'
                ]
            );

            // Generate pagination.
            $data['pagination'] = $this->paginator->pagination(
                // First URL.
                site_url([$this->uri->segment(1), $this->uri->segment(2), $this->uri->segment(3)]) . (empty($this->input->server('QUERY_STRING', true)) ? '' : '?' . $this->input->server('QUERY_STRING', true)),

                // Base URL.
                site_url() . '/' . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/' . $this->uri->segment(3) . '/page/',

                // Total number of rows.
                $data['userStatuses']->numRows,

                // URI segment number that captures the page number.
                5
            );

            // Generate pagination meta information.
            $data['paginationInfo'] = $this->paginator->paginationInfo($data['userStatuses']->numRows);

            // HTML document title.
            $data['title'] = 'User statuses';

            // Load view.
            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_statuses/user_statuses/user_statuses', $data);

        }
    }
} // Class end.
