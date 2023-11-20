<?php
defined('BASEPATH') OR exit('No direct script access allowed');







class Reset extends MY_Controller
{
	function __construct()
    {
        parent::__construct();
		$this->config->load('modules/auth/config');
		$this->load->model('email_model');
		
    }
    
    # Related start #

    /**
     * Initialize password reset process.
     */
    public function identifyAccount()
    {
        $this->config->load('modules/auth/config');

        $data['title'] = 'Reset password';

        if (!$this->preferences->type('system')->item('auth_passwordResetOption')) {

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/reset/reset_password_disabled', $data);

        } else {

            $this->load->model('modules/auth/userend/reset_model');

            $fields = ['users_users.ID', 'firstName', 'email'];

            if ($this->user->isSignin()) {

                $this->load->model('modules/users/adminend/users/user_model');
                $data['user'] = $this->user_model->user($fields, $this->session->userID);

                if (empty($this->input->post(null, true))) {

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/reset/identified_account', $data);

                } else {

                    if ($this->input->post('notyou', true) !== null) {
                        $this->user->signout();
                        redirect(current_url() . '?next=' . $this->url->nextUrl());
                    }

                    if ($this->input->post('continue', true) !== null) {
                        $data['accountInfo'] = $data['user'];
                    }

                }

            } else {

                $this->load->library('form_validation');

                $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

                $this->form_validation->set_rules('authIdentifier', 'Email or username', 'callback_accountExist');

                if ($this->form_validation->run() == false) {

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/reset/identify_account', $data);

                } else {

                    // Gather account information.
                    $data['accountInfo'] = $this->reset_model->accountInfo($this->input->post('authIdentifier', true), $fields);

                }

            }

            if ($data['accountInfo']) {

                // Generate random integer number.
                $this->load->helper('string');
                $data['randomIntegerNumber'] = random_string('nozero', 6); // 6 digit.

                // Encrypt random integer number.
                $this->load->library('encryption');

                $encryptedRandomIntegerNumber = $this->encryption->encrypt($data['randomIntegerNumber']);

                // Check whether user requested password reset before.
                $resetRequested = $this->reset_model->resetRequested($data['accountInfo']->ID, ['ID', 'token', 'datetimeUpdated']);

                if ($resetRequested) {

                    $this->config->load('modules/auth/config');

                    // Check token expiration.
                    $tokenDateTime = new DateTime($resetRequested->datetimeUpdated);
                    $nowDateTime = new DateTime();

                    $diff = $nowDateTime->diff($tokenDateTime);

                    $diffInMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

                    $tokenExpirationInMinutes = (int) $this->preferences->type('system')->item('auth_passwordResetTokenExpirationInMinutes');

                    if ($diffInMinutes > $tokenExpirationInMinutes) {

                        // Token expired.
                        // Update both code and the token.
                        $this->reset_model->updateToken($resetRequested->ID, ['token' => $encryptedRandomIntegerNumber]);

                    } else {

                        // Token not expired.
                        // Update the token but send the same reset code.
                        $decryptedRandomIntegerNumber = $this->encryption->decrypt($resetRequested->token);
                        $encryptedRandomIntegerNumber = $this->encryption->encrypt($decryptedRandomIntegerNumber);

                        $data['randomIntegerNumber'] = $decryptedRandomIntegerNumber;

                        $this->reset_model->updateToken($resetRequested->ID, ['token' => $encryptedRandomIntegerNumber]);

                    }

                } else {

                    $this->reset_model->createToken(['userID' => $data['accountInfo']->ID, 'token' => $encryptedRandomIntegerNumber]);

                }

                // Send password reset email.
              /*  $this->load->library('email');

                $this->email->from($this->preferences->type('system')->item('app_email'), $this->preferences->type('system')->item('app_name'));
                $this->email->to($data['accountInfo']->email);

                $this->email->subject($data['randomIntegerNumber'] . ' is your ' . $this->preferences->type('system')->item('app_name') . ' password reset code');
                $this->email->message($this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/reset/email/reset_password', $data, true));
				*/	
					// sending email
    	 			$edata = array(
                        'subject' => $data['randomIntegerNumber'] . ' is your ' . $this->preferences->type('system')->item('app_name') . ' password reset code',
                        'to' => $data['accountInfo']->email,
                        'name' => $data['accountInfo']->firstName,
                        'randomIntegerNumber'  => $data['randomIntegerNumber'],
                        'template_path' => $this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/reset/email/reset_password'
                    );
                    $res = $this->email_model->send_grid_email($edata);
					
				//$this->email->send()
                if ($res) {

                    $this->session->resetPassword_identifiedAccountInfo = $data['accountInfo'];

                    if ($this->url->isNextUrl()) {
                        redirect('auth/reset/code' . '?next=' . $this->url->nextUrl());
                    } else {
                        redirect('auth/reset/code');
                    }

                } else {

                    log_message('error', 'Password reset email sent was failed.');

                }

            }

        }
    }



    /**
     * Account existence checking callback method.
     */
    public function accountExist()
    {
        $authIdentifier = htmlspecialchars(trim($this->input->post('authIdentifier', true)));

        if (empty($authIdentifier)) {

            $this->form_validation->set_message('accountExist', '{field} is required.');
            return false;

        } elseif (!$this->reset_model->accountExist($authIdentifier)) {

            $this->form_validation->set_message('accountExist', 'Couldn\'t find an account with information that you\'ve provided.');
            return false;

        } else {

            return true;

        }
    }

    # Related end #



    # Related start #

    /**
     * Verify password reset code.
     */
    public function verifyPasswordResetCode()
    {
        if (!isset($this->session->resetPassword_identifiedAccountInfo)) {

            redirect('auth/reset');

        } else {

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('passwordResetCode', 'Password reset code', 'callback_validatePasswordResetCode');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Enter password reset code';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/reset/verify_password_reset_code', $data);

            } else {

                $this->session->resetPassword_passwordResetCodeVerified = true;

                if ($this->url->isNextUrl()) {
                    redirect('auth/reset/password' . '?next=' . $this->url->nextUrl());
                } else {
                    redirect('auth/reset/password');
                }

            }

        }
    }



    /**
     * Verify/Validate password reset code callback method.
     */
    public function validatePasswordResetCode()
    {
        $this->load->model('modules/auth/userend/reset_model');

        $passwordResetCode = htmlspecialchars(trim($this->input->post('passwordResetCode', true)));

        if (empty($passwordResetCode)) {

            $this->form_validation->set_message('validatePasswordResetCode', '{field} is required.');
            return false;

        } elseif ((bool) preg_match('/^[\-+]?[0-9]+$/', $passwordResetCode) === false) { // Check if integer value.

            $this->form_validation->set_message('validatePasswordResetCode', '{field} must be an integer.');
            return false;

        } elseif ($this->reset_model->passwordResetCodeExpired($this->session->resetPassword_identifiedAccountInfo->ID)) {

            $this->form_validation->set_message('validatePasswordResetCode', 'The code that you\'ve entered is expired. please <a href="' . (($this->url->isNextUrl()) ? site_url(['auth', 'reset']) . '?next=' . $this->url->nextUrl() : site_url(['auth', 'reset'])) . '">request a new code</a>.');
            return false;

        } elseif (!$this->reset_model->validatePasswordResetCode($this->session->resetPassword_identifiedAccountInfo->ID, $passwordResetCode)) {

            $this->form_validation->set_message('validatePasswordResetCode', 'The code that you\'ve entered is incorrect. please try again.');
            return false;

        } else {

            return true;

        }
    }

    # Related end #



    /**
     * Reset/Update old password with a new one.
     */
    public function resetPassword()
    {
        if (!isset($this->session->resetPassword_passwordResetCodeVerified)) {

            redirect('auth/reset');

        } else {

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('password', 'New password', 'trim|htmlspecialchars|required|min_length[' . $this->preferences->type('system')->item('users_minimumPasswordLength') . ']');

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('min_length', '{field} must be at least {param} characters in length.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Create new password';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/auth/userend/reset/reset_password', $data);

            } else {

                $this->load->model('modules/auth/userend/reset_model');

                $userID = $this->session->resetPassword_identifiedAccountInfo->ID;

                if (!$this->reset_model->resetPassword($userID, $this->input->post('password', true))) {

                    $this->session->set_flashdata('resetPassword', false);

                    if ($this->url->isNextUrl()) {
                        redirect(current_url() . '?next=' . $this->url->nextUrl());
                    } else {
                        redirect(current_url());
                    }

                } else {

                    // Unset these session values in order to avoid reset password steps bypass.
                    $this->session->unset_userdata(['resetPassword_identifiedAccountInfo', 'resetPassword_passwordResetCodeVerified']);

                    // Delete used reset password token database table row.
                    $this->reset_model->deleteResetPasswordToken($userID);

                    $this->user->signout(); // https://security.stackexchange.com/questions/105124/why-should-you-redirect-the-user-to-a-login-page-after-a-password-reset
                    $this->session->set_flashdata('resetPassword', true);

                    if ($this->url->isNextUrl()) {
                        redirect('auth' . '?next=' . $this->url->nextUrl());
                    } else {
                        redirect('auth');
                    }

                }

            }

        }
    }
} // Class end.
