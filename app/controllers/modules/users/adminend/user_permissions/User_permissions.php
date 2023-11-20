<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class User_permissions extends MY_Controller
{
    /**
     * User permissions CRUD index.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewUserPermissions')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            // Load stuff.
            $this->load->library('common/crud'); // Custom library.
            $this->load->model('modules/users/adminend/user_roles/user_roles_model');
            $this->load->model('modules/users/adminend/user_permissions/user_permissions_model');

            // Gather user roles.
            $data['userRoles'] = $this->user_roles_model->userRoles(
                // Selected fields.
                [
                    // 'user roles' table.
                    'ID',
                    'roleName',
                    'data',
                    'state',

                    // 'userRolesWithRelationsTable' aliased table.
                    'userCount'
                ],

                // Default order by field.
                'userCount',

                // Default order field.
                'DESC',

                // Number of rows per page.
                false,

                // Search fields.
                [],

                // Filter fields.
                [
                    // 'user roles' table.
                    'ID'
                ]
            );

            // Gather all user permissions.
            $data['userPermissions'] = $this->user_permissions_model->userPermissions(
                // Selected fields.
                [
                    // 'user permissions' table.
                    'permissionKey'
                ],

                // Default order by field.
                'ID',

                // Default order field.
                'ASC'
            );

            if (empty($this->input->post(null, true))) {

                // Gather filtered user permissions.
                $data['userPermissions'] = $this->user_permissions_model->userPermissions(
                    // Selected fields.
                    [
                        // 'user permissions' table.
                        'ID',
                        'permissionKey',
                        'moduleSlug',
                        'permissionName',
                        'permissionDescription',
                        'permissionType',
                        'state'
                    ],

                    // Default order by field.
                    'ID',

                    // Default order field.
                    'ASC',

                    // Search fields.
                    [
                        // 'user permissions' table.
                        'permissionKey',
                        'moduleSlug',
                        'permissionName',
                        'permissionDescription'
                    ],

                    // Filter fields.
                    [
                        // 'user permissions' table.
                        'state'
                    ]
                );

                $data['title'] = 'User permissions';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_permissions/user_permissions/user_permissions', $data);

            } else {

                if (!$this->user->hasPermission('users_assignUserPermissions')) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    // Generate $userPermissionKey array that contains all the permission keys.
                    foreach ($data['userPermissions']->result as $userPermission) {
                        $userPermissionKey[] = $userPermission->permissionKey;
                    }

                    // Process data for batch update.
                    foreach ($data['userRoles']->result as $userRole) {

                        $unserializedUserRoleData = unserialize($userRole->data);

                        // If unserialized user role data not an array (e.g., NULL), assign an associative array that contains permissions array key with empty array value.
                        if (is_array($unserializedUserRoleData)) {
                            $unserializedUserRoleData = $unserializedUserRoleData;
                        } else {
                            $unserializedUserRoleData['permissions'] = [];
                        }

                        // Remove user permissions that not exist in the user permissions database table.
                        foreach (array_keys($unserializedUserRoleData['permissions']) as $permissionKey) {
                            if (!in_array($permissionKey, $userPermissionKey)) {
                                unset($unserializedUserRoleData['permissions'][$permissionKey]);
                            }
                        }

                        // Process data for bulk user permissions update.
                        foreach ($this->input->input_stream('roles', true) as $userRoleID => $userRolePermissions) {
                            if ($userRole->ID == $userRoleID) {

                                foreach ($userRolePermissions as $permissionKey => $permissionState) {
                                    if (empty($permissionState)) {
                                        unset($unserializedUserRoleData['permissions'][$permissionKey]); // Remove all the submitted but unchecked user permissions.
                                    } else {
                                        $unserializedUserRoleData['permissions'][$permissionKey] = $permissionState; // Generate user permissions associative array.
                                    }
                                }

                            }
                        }

                        $serializedUserRoleData = serialize($unserializedUserRoleData);
                        $permissionCount = count($unserializedUserRoleData['permissions']);

                        $processedData[] = [
                            'ID' => $userRole->ID,
                            'data' => $serializedUserRoleData,
                            'permissionCount' => $permissionCount
                        ];

                    }

                    // Load model and database operations.
                    $this->load->model('modules/users/adminend/user_roles/edit_user_roles_model');

                    $currentUrl = current_url() . (!empty($this->input->server('QUERY_STRING')) ? '?' . $this->input->server('QUERY_STRING') : '');

                    if (!$this->edit_user_roles_model->editUserRolePermissions($processedData)) {
                        $this->session->set_flashdata('editUserRolePermissionsSuccess', false);
                    } else {
                        $this->session->set_flashdata('editUserRolePermissionsSuccess', true);
                    }

                    redirect($currentUrl);

                }

            }

        }
    }
} // Class end.
