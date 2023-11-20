<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class User_overview extends MY_Controller
{
    /**
     * User overview index.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->isEmailVerification()) {

            redirect('admin/user/settings/email' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewUserOverview')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

        } else {

            $this->load->helper('date');
            $this->load->model('modules/users/adminend/user_overview/user_overview_model');

            // Row 01.
            $data['numOnlineUsers'] = $this->user_overview_model->numOnlineUsers();
            $data['numTodayActiveUsers'] = $this->user_overview_model->numTodayActiveUsers();
            $data['numUsersByState'] = $this->user_overview_model->numUsersByState();
            $data['numUsersByEmailVerification'] = $this->user_overview_model->numUsersByEmailVerification();

            // Row 02.
            $data['numUsersByRegisteredMonth'] = $this->user_overview_model->numUsersByRegisteredMonth();
            $data['lastRegisteredUsers'] = $this->user_overview_model->lastRegisteredUsers(['firstName', 'surname', 'datetimeCreated']);
            $data['lastActiveUsers'] = $this->user_overview_model->lastActiveUsers(['firstName', 'surname', 'datetimeLastActivity']);

            // HTML document title.
            $data['title'] = 'User overview';

            // Load view.
            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user_overview/user_overview/user_overview', $data);

        }
    }
} // Class end.
