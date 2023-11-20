<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class User_tags extends MY_Controller
{
    /**
     * User tags CRUD index.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewUserTags')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            // Load stuff.
            $this->load->library('common/crud'); // Custom library.
            $this->load->library('common/paginator'); // Custom library.
            $this->load->model('modules/users/adminend/user_tags/user_tags_model');

            // Gather user tags.
            $data['userTags'] = $this->user_tags_model->userTags(
                // Selected fields.
                [
                    // 'user tags' table.
                    'ID',
                    'tagName',
                    'tagSlug',
                    'tagDescription',
                    'state',

                    // 'userTagsWithRelationsTable' aliased table.
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
                    // 'user tags' table.
                    'tagName',
                    'tagSlug',
                    'tagDescription',

                    // 'userTagsWithRelationsTable' aliased table.
                    'userCount'
                ],

                // Filter fields.
                [
                    // 'user tags' table.
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
                $data['userTags']->numRows,

                // URI segment number that captures the page number.
                5
            );

            // Generate pagination meta information.
            $data['paginationInfo'] = $this->paginator->paginationInfo($data['userTags']->numRows);

            // HTML document title.
            $data['title'] = 'User tags';

            // Load view.
            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_tags/user_tags/user_tags', $data);

        }
    }
} // Class end.
