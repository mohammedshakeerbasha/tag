<?php
defined('BASEPATH') OR exit('No direct script access allowed');







/**
 * Homework.
 *
 * https://stackoverflow.com/questions/1030924/issues-with-pagination-and-sorting
 */

class Users extends MY_Controller
{
    /**
     * Users CRUD index.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewUsers')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            // Load stuff.
            $this->load->helper('date');
            $this->load->library('common/crud'); // Custom library.
            $this->load->library('common/paginator'); // Custom library.
            $this->load->model('modules/users/adminend/users/users_model');

            // Generate user filter info object.
            $filters = [
                ['filterby' => 'statusID', 'filterName' => 'status', 'table' => 'users_user_statuses', 'tableField' => 'statusName'],
                ['filterby' => 'tagID', 'filterName' => 'tag', 'table' => 'users_user_tags', 'tableField' => 'tagName'],
                ['filterby' => 'roleID', 'filterName' => 'role', 'table' => 'users_user_roles', 'tableField' => 'roleName'],
                ['filterby' => 'groupID', 'filterName' => 'group', 'table' => 'users_user_groups', 'tableField' => 'groupName']
            ];

            foreach ($filters as $filter) {
                $filterbyValues[] = $filter['filterby'];
            }

            if (
                !empty($this->input->get('filterby', true)) &&
                in_array(strtolower($this->input->get('filterby', true)), array_map('strtolower', $filterbyValues)) &&
                $this->input->get('filter', true) !== null
            ) {
                foreach ($filters as $filter) {
                    if (strtolower($this->input->get('filterby', true)) == strtolower($filter['filterby'])) {
                        $userFilterData = $this->users_model->userFilterData($filter['tableField'], $filter['table'], $this->input->get('filter', true));

                        $userFilter['filterName'] = $filter['filterName'];
                        $userFilter['filterValue'] = $userFilterData->{$filter['tableField']};
                        $userFilter['filterValueState'] = $userFilterData->state;

                        $data['userFilter'] = (object) $userFilter;
                    }
                }
            }

            // Generate user ID array to handle WHERE IN clause for 'custom' filterby fields.
            $filterbyFields = [
                'tagID' => 'users_user_tag_relations',
                'roleID' => 'users_user_role_relations',
                'groupID' => 'users_user_group_relations'
            ];

            if (
                !empty($this->input->get('filterby', true)) &&
                in_array(strtolower($this->input->get('filterby', true)), array_map('strtolower', array_keys($filterbyFields))) &&
                $this->input->get('filter', true) !== null
            ) {

                foreach ($filterbyFields as $filterbyField => $table) {
                    if (strtolower($this->input->get('filterby', true)) == strtolower($filterbyField)) {
                        $userIDsResult = $this->users_model->userIDs($filterbyField, $this->input->get('filter', true), $table);
                    }
                }

                if ($userIDsResult) {

                    foreach ($userIDsResult as $userID) {
                        $userIDs[] = $userID->userID;
                    }

                } else {
                    $userIDs = '';
                }

            } else {
                $userIDs = null;
            }

            // Gather users.
            $data['users'] = $this->users_model->users(
                // Selected fields.
                [
                    // 'users' table.
                    'ID',
                    'statusID',
                    'firstName',
                    'surname',
                    'username',
                    'email',
                    'emailVerification',
                    'state',
                    'datetimeCreated',
                    'datetimeLastActivity',

                    // 'statusesTable' aliased table.
                    'statusName',
                    'statusState',

                    // 'tagsTable' aliased table.
                    'tagCount',
                    'tagIDs',
                    'tagNames',
                    'tagStates',

                    // 'rolesTable' aliased table.
                    'roleCount',
                    'roleIDs',
                    'roleNames',
                    'roleStates',

                    // 'groupsTable' aliased table.
                    'groupCount',
                    'groupIDs',
                    'groupNames',
                    'groupStates'
                ],

                // Default order by field.
                'ID',

                // Default order field.
                'DESC',

                // Number of rows per page.
                $this->config->item('per_page'),

                // Search fields.
                [
                    // 'users' table.
                    'ID',
                    'firstName',
                    'surname',
                    'username',
                    'email',
                    'datetimeCreated',
                    'datetimeLastActivity'
                ],

                // Filter fields.
                [
                    // 'users' table.
                    'statusID',
                    'state'
                ],

                // Passing 'user IDs' to handle custom filters.
                $userIDs
            );

            // Generate pagination.
            $data['pagination'] = $this->paginator->pagination(
                // First URL.
                site_url([$this->uri->segment(1), $this->uri->segment(2)]) . (empty($this->input->server('QUERY_STRING', true)) ? '' : '?' . $this->input->server('QUERY_STRING', true)),

                // Base URL.
                site_url() . '/' . $this->uri->segment(1) . '/' . $this->uri->segment(2) . '/page/',

                // Total number of rows.
                $data['users']->numRows,

                // URI segment number that captures the page number.
                4
            );

            // Generate pagination meta information.
            $data['paginationInfo'] = $this->paginator->paginationInfo($data['users']->numRows);

            // HTML document title.
            $data['title'] = 'Users';

            // Load view.
            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/users/users/users', $data);

        }
    }
} // Class end.
