<?php
defined('BASEPATH') OR exit('No direct script access allowed');






class Email extends MY_Controller
{
    /**
     * User email settings.
     */
    public function index()
    {
        if (!$this->user->isSignin()) {

            redirect('auth' . '?next=' . $this->url->currentUrl());

        } elseif (!$this->user->hasPermission('users_viewEmailUserSettings')) {

            $this->output->set_status_header(401);

            $data['title'] = 'Access denied';

            $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/errors/access_denied', $data);

        } else {

            // Unset these session values in order to avoid email verification steps bypass.
            $this->session->unset_userdata('emailUserSettings_stepTwo_email');

            $this->load->library('form_validation');
            $this->load->model('modules/users/adminend/users/user_model');

            $data['user'] = $this->user_model->user(['users_users.ID', 'firstName', 'email', 'emailVerification'], $this->session->userID);

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            if (strtolower($data['user']->email) == strtolower($this->input->post('email', true))) {
                $this->form_validation->set_rules('email', 'Email', 'trim|htmlspecialchars|required|valid_email|max_length[250]');
            } else {
                $this->form_validation->set_rules('email', 'Email', 'trim|htmlspecialchars|required|valid_email|max_length[250]|is_unique[users_users.email]', ['is_unique' => 'This email has already been registered. please choose another email.']);
            }

            // Custom error messages for specific validation rules.
            $this->form_validation->set_message('required', '{field} is required.');
            $this->form_validation->set_message('max_length', '{field} cannot exceed {param} characters in length.');
            $this->form_validation->set_message('valid_email', '{field} must be a valid email address.');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'User settings';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/email/email', $data);

            } else {

                if (!$this->user->hasPermission('users_editEmailUserSettings') && $data['user']->emailVerification == true) {

                    $this->output->set_status_header(401);

                    $data['title'] = 'Access denied';

                    $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/common/adminend/errors/access_denied', $data);

                } else {

                    // Generate random integer number.
                    $this->load->helper('string');
                    $data['randomIntegerNumber'] = random_string('nozero', 6); // 6 digit.

                    // Encrypt random integer number.
                    $this->load->library('encryption');

                    $encryptedRandomIntegerNumber = $this->encryption->encrypt($data['randomIntegerNumber']);

                    // Check whether user requested email verification before.
                    $this->load->model('modules/users/adminend/user/settings/email/user_settings_email_model');
                    $verificationRequested = $this->user_settings_email_model->verificationRequested($data['user']->ID, ['ID', 'token', 'datetimeUpdated']);

                    if ($verificationRequested) {

                        $this->config->load('modules/users/config');

                        // Check token expiration.
                        $tokenDateTime = new DateTime($verificationRequested->datetimeUpdated);
                        $nowDateTime = new DateTime();

                        $diff = $nowDateTime->diff($tokenDateTime);

                        $diffInMinutes = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;

                        $tokenExpirationInMinutes = (int) $this->preferences->type('system')->item('users_emailVerificationTokenExpirationInMinutes');

                        if ($diffInMinutes > $tokenExpirationInMinutes) {

                            // Token expired.
                            // Update both code and the token.
                            $this->user_settings_email_model->updateToken($verificationRequested->ID, ['token' => $encryptedRandomIntegerNumber]);

                        } else {

                            // Token not expired.
                            // Update the token but send the same verification code.
                            $decryptedRandomIntegerNumber = $this->encryption->decrypt($verificationRequested->token);
                            $encryptedRandomIntegerNumber = $this->encryption->encrypt($decryptedRandomIntegerNumber);

                            $data['randomIntegerNumber'] = $decryptedRandomIntegerNumber;

                            $this->user_settings_email_model->updateToken($verificationRequested->ID, ['token' => $encryptedRandomIntegerNumber]);

                        }

                    } else {

                        $this->user_settings_email_model->createToken(['userID' => $data['user']->ID, 'token' => $encryptedRandomIntegerNumber]);

                    }

                    // Generate $toEmail.
                    if (!$this->user->hasPermission('users_editEmailUserSettings')) {
                        $toEmail = $data['user']->email;
                    } else {
                        $toEmail = $this->input->post('email', true);
                    }

                    // Send email verification email.
                    $this->load->library('email');

                    $this->email->from($this->preferences->type('system')->item('app_email'), $this->preferences->type('system')->item('app_name'));
                    $this->email->to($toEmail);

                    $this->email->subject($data['randomIntegerNumber'] . ' is your ' . $this->preferences->type('system')->item('app_name') . ' email verification code');
                    $this->email->message($this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/email/email/verify_email', $data, true));

                    if ($this->email->send()) {

                        $this->session->emailUserSettings_stepOne_email = $toEmail;

                        if ($this->url->isNextUrl()) {
                            redirect('admin/user/settings/email/code' . '?next=' . $this->url->nextUrl());
                        } else {
                            redirect('admin/user/settings/email/code');
                        }

                    } else {

                        log_message('error', 'Email verification email sent was failed.');

                    }

                }

            }

        }
    }



    # Related start #

    /**
     * Verify email verification code.
     */
    public function verifyEmailVerificationCode()
    {
        if (!isset($this->session->emailUserSettings_stepOne_email)) {

            redirect('admin/user/settings/email');

        } else {

            $this->load->library('form_validation');

            $this->form_validation->set_error_delimiters('<li class="mb-1">', '</li>');

            $this->form_validation->set_rules('emailVerificationCode', 'Email verification code', 'callback_validateEmailVerificationCode');

            if ($this->form_validation->run() == false) {

                $data['title'] = 'Enter email verification code';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/email/verify_email_verification_code', $data);

            } else {

                // Delete used email verification token database table row.
                $this->user_settings_email_model->deleteEmailVerificationToken($this->session->userID);

                if ($this->user_settings_email_model->changeVerifyEmail($this->session->userID, $this->session->emailUserSettings_stepOne_email)) {

                    $this->session->emailUserSettings_stepTwo_email = $this->session->emailUserSettings_stepOne_email;

                    // Unset these session values in order to avoid email verification steps bypass.
                    $this->session->unset_userdata('emailUserSettings_stepOne_email');

                    if ($this->url->isNextUrl()) {
                        redirect('admin/user/settings/email/succeeded' . '?next=' . $this->url->nextUrl());
                    } else {
                        redirect('admin/user/settings/email/succeeded');
                    }

                } else {

                    $this->session->set_flashdata('editUserSettingsSuccess', false);

                    // Unset these session values in order to avoid email verification steps bypass.
                    $this->session->unset_userdata('emailUserSettings_stepOne_email');

                    redirect('admin/user/settings/email');

                }

            }

        }
    }



    /**
     * Verify/Validate email verification code callback method.
     */
    public function validateEmailVerificationCode()
    {
        $this->load->model('modules/users/adminend/user/settings/email/user_settings_email_model');

        $emailVerificationCode = htmlspecialchars(trim($this->input->post('emailVerificationCode', true)));

        if (empty($emailVerificationCode)) {

            $this->form_validation->set_message('validateEmailVerificationCode', '{field} is required.');
            return false;

        } elseif ((bool) preg_match('/^[\-+]?[0-9]+$/', $emailVerificationCode) === false) { // Check if integer value.

            $this->form_validation->set_message('validateEmailVerificationCode', '{field} must be an integer.');
            return false;

        } elseif ($this->user_settings_email_model->emailVerificationCodeExpired($this->session->userID)) {

            $this->form_validation->set_message('validateEmailVerificationCode', 'The code that you\'ve entered is expired. please <a href="' . site_url(['admin', 'user', 'settings', 'email']) . '">request a new code</a>.');
            return false;

        } elseif (!$this->user_settings_email_model->validateEmailVerificationCode($this->session->userID, $emailVerificationCode)) {

            $this->form_validation->set_message('validateEmailVerificationCode', 'The code that you\'ve entered is incorrect. please try again.');
            return false;

        } else {

            return true;

        }
    }

    # Related end #



    /**
     * Finalize email change/verify operation.
     */
    public function operationSucceeded()
    {
        if (!isset($this->session->emailUserSettings_stepTwo_email)) {

            redirect('admin/user/settings/email');

        } else {

            if (empty($this->input->post(null, true))) {

                $data['title'] = 'Operation succeeded';

                $this->load->view($this->preferences->type('system')->item('app_themesDir') . '/' . $this->preferences->type('system')->item('app_themeDir') . '/modules/users/adminend/user/settings/email/change_verify_email_success', $data);

            } else {

                // Unset these session values in order to avoid email verification steps bypass.
                $this->session->unset_userdata('emailUserSettings_stepTwo_email');

                if ($this->url->isNextUrl()) {
                    redirect($this->url->nextUrl());
                } else {
                    redirect('admin/user/settings/email');
                }

            }

        }
    }
} // Class end.
