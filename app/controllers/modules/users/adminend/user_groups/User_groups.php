<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class User_groups extends MY_Controller
{
    /**
     * User groups CRUD index.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewUserGroups')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            // Load stuff.
            $this->load->library('common/crud'); // Custom library.
            $this->load->library('common/paginator'); // Custom library.
            $this->load->model('modules/users/adminend/user_groups/user_groups_model');

            // Gather user groups.
            $data['userGroups'] = $this->user_groups_model->userGroups(
                // Selected fields.
                [
                    // 'user groups' table.
                    'ID',
                    'groupName',
                    'groupSlug',
                    'groupDescription',
                    'state',

                    // 'userGroupsWithRelationsTable' aliased table.
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
                    // 'user groups' table.
                    'groupName',
                    'groupSlug',
                    'groupDescription',

                    // 'userGroupsWithRelationsTable' aliased table.
                    'userCount'
                ],

                // Filter fields.
                [
                    // 'user groups' table.
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
                $data['userGroups']->numRows,

                // URI segment number that captures the page number.
                5
            );

            // Generate pagination meta information.
            $data['paginationInfo'] = $this->paginator->paginationInfo($data['userGroups']->numRows);

            // HTML document title.
            $data['title'] = 'User groups';

            // Load view.
            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_groups/user_groups/user_groups', $data);

        }
    }
} // Class end.
