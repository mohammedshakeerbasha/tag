<?php
defined('BASEPATH') OR exit('No direct script access allowed');






class Signin extends MY_Controller
{
    /**
     * User signin.
     */
    public function index()
    {
        $this->config->load('modules/auth/config');

        if ($this->user->isSignin()) {

            redirect($this->preferences->type('system')->item('auth_signinRedirectRoute'));

        } else {

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('authIdentifier', 'Email or username', 'trim|htmlspecialchars|required');
            $this->form_validation->set_rules('password', 'Password', 'trim|htmlspecialchars|required');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Sign in';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/signin/signin', $data);

            } else {

                $this->load->model('modules/auth/userend/signin_model');

                $userID = $this->signin_model->signin($this->input->post(null, true));

                if (!$userID) {

                    $this->session->set_flashdata('signinSuccess', false);
                    $this->session->set_flashdata('authIdentifierValue', $this->input->post('authIdentifier', true));

                    if ($this->url->isNextUrl()) {
                        redirect('auth' . '?next=' . $this->url->nextUrl());
                    } else {
                        redirect('auth');
                    }

                } else {

                    // Make user remember after signed in.
                    if ($this->preferences->type('system')->item('auth_rememberMeOption')) {
                        if ($this->input->post('rememberMe', true)) {
                            $this->user->rememberMe($userID);
                        }
                    }

                    // Make users signed in.
                    $this->user->signin($userID);

                    // Redirect users to necessary places.
                    if (!$this->user->isEmailVerification()) {
                        if ($this->url->isNextUrl()) {
                            redirect('admin/user/settings/email' . '?next=' . $this->url->nextUrl());
                        } else {
                            redirect('admin/user/settings/email');
                        }
                    } else {
                        if ($this->url->isNextUrl()) {
                            redirect($this->url->nextUrl());
                        } else {
                            redirect($this->preferences->type('system')->item('auth_signinRedirectRoute'));
                        }
                    }

                }

            }

        }
    }
} // Class end.
