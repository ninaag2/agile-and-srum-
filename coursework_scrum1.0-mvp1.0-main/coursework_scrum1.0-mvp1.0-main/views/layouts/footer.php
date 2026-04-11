<?php
// filepath: views/layouts/footer.php
?>


<footer class="site-footer">
    <div class="footer-container">
        <!-- Column 1 -->
        <div class="footer-col">
            <h4 class="footer-brand-title">Born Car</h4>
            <p>Drive your dreams with our premium car rental service. Comfort, safety, and reliability in every mile.</p>
            <p><strong>Company:</strong> Born Car JSC</p>
            <p><strong>Tax Code:</strong> 0123456789</p>
            <p><strong>Address:</strong> 20 Cong Hoa street, Tan Binh District, Ho Chi Minh City</p>
            <p><strong>Email:</strong> support@bonboncar.com</p>
            <!-- Ảnh Dummy Bộ Công Thương -->
            <img src="https://dummyimage.com/150x45/dc3545/ffffff.png&text=Bo+Cong+Thuong" alt="Da Thong Bao Bo Cong Thuong" class="bct-logo">
        </div>

        <!-- Column 2 -->
        <div class="footer-col">
            <h4>Policies</h4>
            <ul>
                <li><a href="#">Terms & Conditions</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Payment & Refund Policy</a></li>
                <li><a href="#">Insurance Information</a></li>
                <li><a href="#">Dispute Resolution</a></li>
            </ul>
        </div>

        <!-- Column 3 -->
        <div class="footer-col">
            <h4>Service Areas</h4>
            <ul>
                <li><a href="#">Hanoi</a></li>
                <li><a href="#">Da Nang</a></li>
                <li><a href="#">Ho Chi Minh City</a></li>
                <li><a href="#">Hai Phong</a></li>
                <li><a href="#">Can Tho</a></li>
            </ul>
        </div>

        <!-- Column 4 -->
        <div class="footer-col">
            <h4>Connect & Support</h4>
            <ul>
                <li><a href="https://www.facebook.com/GreenwichVietnam">📘 Facebook</a></li>
                <li><a href="#">💼 https://greenwich.edu.vn/</a></li>
                <li><a href="https://www.tiktok.com/@uniofgreenwich">🎵 TikTok</a></li>
                <li><a href="#">📸 Instagram</a></li>
            </ul>
            <div class="footer-hotline-box">
                <span class="footer-hotline-label">24/7 Hotline:</span>
                <span class="hotline">1900 8888</span>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        &copy; <?= date('Y') ?> Bon Bon Car. All rights reserved. Built with ❤️ for your journey.
    </div>
</footer>

<div id="authModal" class="auth-modal-overlay" aria-hidden="true">
    <div class="auth-modal-content" role="dialog" aria-modal="true" aria-labelledby="authModalTitle">
        <button type="button" class="auth-modal-close" id="authCloseBtn" aria-label="Close">&times;</button>

        <?php
        $loginError = $_SESSION['login_error'] ?? '';
        $loginSuccess = $_SESSION['login_success'] ?? '';
        $loginPrefillEmail = $_SESSION['login_prefill_email'] ?? '';
        $loginPrefillPassword = $_SESSION['login_prefill_password'] ?? '';
        $authSuccessQuery = trim($_GET['auth_success'] ?? '');
        if ($authSuccessQuery !== '') {
            $loginSuccess = $authSuccessQuery;
        }
        $registerErrors = $_SESSION['register_errors'] ?? [];
        $registerOld = $_SESSION['register_old_data'] ?? [];
        $otpError = $_SESSION['otp_error'] ?? '';
        $pendingRegistration = $_SESSION['pending_registration'] ?? [];
        $registerVerifyEmail = trim((string) ($_GET['verify_email'] ?? ($pendingRegistration['email'] ?? '')));
        $forgotErrors = $_SESSION['forgot_reset_errors'] ?? [];
        $forgotSuccess = $_SESSION['forgot_reset_success'] ?? '';
        $forgotOldEmail = $_SESSION['forgot_reset_old_email'] ?? '';
        $forgotVerifiedSession = $_SESSION['forgot_reset_verified'] ?? null;
        $isForgotOtpVerified = !empty($forgotVerifiedSession['email']) && !empty($forgotVerifiedSession['expires_at']) && ((int) $forgotVerifiedSession['expires_at'] >= time());
        $forgotStepFromSession = $_SESSION['forgot_reset_step'] ?? ($isForgotOtpVerified ? 'password' : 'send');

        $requestUri = $_SERVER['REQUEST_URI'] ?? 'index.php?action=browse_cars';
        $parsedRequestUri = parse_url($requestUri);
        $returnPath = $parsedRequestUri['path'] ?? 'index.php';
        parse_str($parsedRequestUri['query'] ?? '', $returnQuery);
        unset($returnQuery['auth_modal'], $returnQuery['forgot_step'], $returnQuery['auth_success']);
        $forgotReturnTo = $returnPath;
        if (!empty($returnQuery)) {
            $forgotReturnTo .= '?' . http_build_query($returnQuery);
        }
        $authReturnTo = $forgotReturnTo;

        if (empty($_SESSION['csrf_tokens']['send_reset_otp'])) {
            $_SESSION['csrf_tokens']['send_reset_otp'] = bin2hex(random_bytes(32));
        }
        if (empty($_SESSION['csrf_tokens']['register_submit'])) {
            $_SESSION['csrf_tokens']['register_submit'] = bin2hex(random_bytes(32));
        }
        if (empty($_SESSION['csrf_tokens']['verify_reset_otp_code'])) {
            $_SESSION['csrf_tokens']['verify_reset_otp_code'] = bin2hex(random_bytes(32));
        }
        if (empty($_SESSION['csrf_tokens']['reset_password_after_otp'])) {
            $_SESSION['csrf_tokens']['reset_password_after_otp'] = bin2hex(random_bytes(32));
        }

        $sendResetOtpToken = $_SESSION['csrf_tokens']['send_reset_otp'];
        $registerSubmitToken = $_SESSION['csrf_tokens']['register_submit'];
        $verifyResetOtpCodeToken = $_SESSION['csrf_tokens']['verify_reset_otp_code'];
        $resetPasswordAfterOtpToken = $_SESSION['csrf_tokens']['reset_password_after_otp'];

        unset(
            $_SESSION['login_error'],
            $_SESSION['login_success'],
            $_SESSION['login_prefill_email'],
            $_SESSION['login_prefill_password'],
            $_SESSION['register_errors'],
            $_SESSION['register_old_data'],
            $_SESSION['otp_error'],
            $_SESSION['forgot_reset_errors'],
            $_SESSION['forgot_reset_success'],
            $_SESSION['forgot_reset_old_email'],
            $_SESSION['forgot_reset_step']
        );
        ?>

        <div id="authLoginPanel" class="auth-panel">
            <h2 id="authModalTitle" class="auth-title">Login</h2>
            <?php if (!empty($loginError)): ?>
                <div class="auth-modal-alert auth-modal-alert-danger"><?= htmlspecialchars($loginError) ?></div>
            <?php endif; ?>
            <?php if (!empty($loginSuccess)): ?>
                <div class="auth-modal-alert auth-modal-alert-success"><?= htmlspecialchars($loginSuccess) ?></div>
            <?php endif; ?>
            <form action="index.php?action=login" method="POST" autocomplete="off">
                <input type="hidden" name="return_to" value="<?= htmlspecialchars($authReturnTo) ?>">
                <div class="auth-form-group">
                    <label for="modal_login_email">Email <span class="required-star">*</span></label>
                    <input id="modal_login_email" type="email" name="email" class="auth-input" value="<?= htmlspecialchars((string) $loginPrefillEmail) ?>" required>
                </div>

                <div class="auth-form-group">
                    <label for="modal_login_password">Password <span class="required-star">*</span></label>
                    <input id="modal_login_password" type="password" name="password" class="auth-input" value="<?= htmlspecialchars((string) $loginPrefillPassword) ?>" required>
                </div>

                <div class="auth-remember-wrap">
                    <input id="modal_login_remember" type="checkbox" name="remember" value="1">
                    <label for="modal_login_remember">Remember me</label>
                </div>

                <p class="auth-forgot-wrap">
                    <a href="#" class="auth-forgot-link" data-auth-switch="forgot" data-forgot-step="send">Forgot password?</a>
                </p>

                <button type="submit" class="auth-btn auth-btn-primary">Login</button>
            </form>

            <p class="auth-switch-text">
                Don't have an account?
                <button type="button" class="auth-switch-link" data-auth-switch="register">Register now</button>
            </p>
        </div>

        <div id="authRegisterPanel" class="auth-panel auth-hidden">
            <h2 class="auth-title">Register</h2>
            <?php if (!empty($registerErrors)): ?>
                <div class="auth-modal-alert auth-modal-alert-danger"><?php foreach ($registerErrors as $error): ?><?= htmlspecialchars($error) ?><br><?php endforeach; ?></div>
            <?php endif; ?>
            <form action="index.php?action=register_submit" method="POST" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($registerSubmitToken) ?>">
                <input type="hidden" name="register_submit_intent" value="1">
                <input type="hidden" name="return_to" value="<?= htmlspecialchars($authReturnTo) ?>">
                <div class="auth-form-group">
                    <label for="modal_register_fullname">Full name <span class="required-star">*</span></label>
                    <input id="modal_register_fullname" type="text" name="fullname" class="auth-input" value="<?= htmlspecialchars($registerOld['fullname'] ?? '') ?>" required>
                </div>

                <div class="auth-form-group">
                    <label for="modal_register_email">Email <span class="required-star">*</span></label>
                    <input id="modal_register_email" type="email" name="email" class="auth-input" value="<?= htmlspecialchars($registerOld['email'] ?? '') ?>" required>
                </div>

                <div class="auth-form-group">
                    <label for="modal_register_phone">Phone number <span class="required-star">*</span></label>
                    <input id="modal_register_phone" type="text" name="phone" class="auth-input" value="<?= htmlspecialchars($registerOld['phone'] ?? '') ?>" required>
                </div>

                <div class="auth-form-group">
                    <label for="modal_register_password">Password <span class="required-star">*</span></label>
                    <input id="modal_register_password" type="password" name="password" class="auth-input" required>
                </div>

                <button type="submit" class="auth-btn auth-btn-primary">Register</button>
                <button type="button" class="auth-btn auth-btn-cancel" id="authCancelBtn">Cancel</button>
            </form>

            <p class="auth-switch-text">
                Already have an account?
                <button type="button" class="auth-switch-link" data-auth-switch="login">Login</button>
            </p>
        </div>

        <div id="authRegisterOtpPanel" class="auth-panel auth-hidden">
            <h2 class="auth-title">Verify Registration OTP</h2>
            <?php if (!empty($otpError)): ?>
                <div class="auth-modal-alert auth-modal-alert-danger"><?= htmlspecialchars($otpError) ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['mock_email_otp'])): ?>
                <div class="auth-modal-alert auth-modal-alert-success"><?= htmlspecialchars((string) $_SESSION['mock_email_otp']) ?></div>
            <?php endif; ?>

            <p class="auth-forgot-note">Enter the 6-digit OTP sent to <strong><?= htmlspecialchars($registerVerifyEmail) ?></strong>.</p>
            <form action="index.php?action=verify_otp_submit" method="POST" autocomplete="off">
                <input type="hidden" name="email" value="<?= htmlspecialchars($registerVerifyEmail) ?>">
                <input type="hidden" name="return_to" value="<?= htmlspecialchars($authReturnTo) ?>">

                <div class="auth-form-group">
                    <label for="modal_register_otp">OTP Code <span class="required-star">*</span></label>
                    <input id="modal_register_otp" type="text" name="otp" class="auth-input" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit OTP" required>
                </div>

                <button type="submit" class="auth-btn auth-btn-primary">Verify Account</button>
            </form>

            <p class="auth-switch-text">
                Wrong email?
                <button type="button" class="auth-switch-link" data-auth-switch="register">Back to Register</button>
            </p>
        </div>

        <div id="authForgotPanel" class="auth-panel auth-hidden">
            <h2 class="auth-title">Forgot Password</h2>

            <?php if (!empty($forgotErrors)): ?>
                <div class="auth-modal-alert auth-modal-alert-danger">
                    <?php foreach ($forgotErrors as $error): ?>
                        <?= htmlspecialchars((string) $error) ?><br>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($forgotSuccess)): ?>
                <div class="auth-modal-alert auth-modal-alert-success"><?= htmlspecialchars($forgotSuccess) ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['mock_email_otp'])): ?>
                <div class="auth-modal-alert auth-modal-alert-success"><?= htmlspecialchars((string) $_SESSION['mock_email_otp']) ?></div>
            <?php endif; ?>

            <div id="forgotStepSend" class="auth-forgot-step">
                <p class="auth-forgot-note">Step 1: Enter your email to receive a 6-digit OTP code.</p>
                <form action="index.php?action=send_reset_otp" method="POST" autocomplete="off" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($sendResetOtpToken) ?>">
                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($forgotReturnTo) ?>">

                    <div class="auth-form-group">
                        <label for="forgot_email">Email <span class="required-star">*</span></label>
                        <input id="forgot_email" type="email" name="email" class="auth-input" value="<?= htmlspecialchars($forgotOldEmail) ?>" maxlength="150" required>
                    </div>

                    <button type="submit" class="auth-btn auth-btn-primary">Send OTP</button>
                </form>

                <p class="auth-switch-text">
                    Already have an OTP?
                    <button type="button" class="auth-switch-link" data-forgot-step-target="otp">Go to OTP confirmation</button>
                </p>
            </div>

            <div id="forgotStepOtp" class="auth-forgot-step auth-hidden">
                <p class="auth-forgot-note">Step 2: Enter your OTP code to confirm your identity.</p>
                <form action="index.php?action=verify_reset_otp_code" method="POST" autocomplete="off" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($verifyResetOtpCodeToken) ?>">
                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($forgotReturnTo) ?>">

                    <div class="auth-form-group">
                        <label for="forgot_verify_email">Email <span class="required-star">*</span></label>
                        <input id="forgot_verify_email" type="email" name="email" class="auth-input" value="<?= htmlspecialchars($forgotOldEmail) ?>" maxlength="150" required>
                    </div>

                    <div class="auth-form-group">
                        <label for="forgot_otp">OTP Code <span class="required-star">*</span></label>
                        <input id="forgot_otp" type="text" name="otp" class="auth-input" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" placeholder="Enter 6-digit OTP" autocomplete="one-time-code" required>
                    </div>

                    <button type="submit" class="auth-btn auth-btn-primary">Confirm OTP</button>
                </form>

                <p class="auth-switch-text">
                    Need a new OTP?
                    <button type="button" class="auth-switch-link" data-forgot-step-target="send">Back to send OTP</button>
                </p>
            </div>

            <div id="forgotStepPassword" class="auth-forgot-step auth-hidden">
                <p class="auth-forgot-note">Step 3: Set your new password.</p>
                <form action="index.php?action=verify_reset_otp" method="POST" autocomplete="off" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($resetPasswordAfterOtpToken) ?>">
                    <input type="hidden" name="return_to" value="<?= htmlspecialchars($forgotReturnTo) ?>">

                    <div class="auth-form-group">
                        <label for="forgot_password_email">Email <span class="required-star">*</span></label>
                        <input id="forgot_password_email" type="email" name="email" class="auth-input" value="<?= htmlspecialchars($forgotOldEmail) ?>" maxlength="150" required>
                    </div>

                    <div class="auth-form-group">
                        <label for="forgot_new_password">New Password <span class="required-star">*</span></label>
                        <input id="forgot_new_password" type="password" name="new_password" class="auth-input" minlength="8" required>
                    </div>

                    <div class="auth-form-group">
                        <label for="forgot_confirm_password">Confirm New Password <span class="required-star">*</span></label>
                        <input id="forgot_confirm_password" type="password" name="confirm_password" class="auth-input" minlength="8" required>
                    </div>

                    <button type="submit" class="auth-btn auth-btn-primary">Set New Password</button>
                </form>

                <p class="auth-switch-text">
                    Need to re-confirm OTP?
                    <button type="button" class="auth-switch-link" data-forgot-step-target="otp">Back to OTP confirmation</button>
                </p>
            </div>

            <p class="auth-switch-text">
                <button type="button" class="auth-switch-link" data-auth-switch="login">Back to Login</button>
            </p>
        </div>
    </div>
</div>

<script>
    (function() {
        var modal = document.getElementById('authModal');
        var closeBtn = document.getElementById('authCloseBtn');
        var cancelBtn = document.getElementById('authCancelBtn');
        var loginPanel = document.getElementById('authLoginPanel');
        var registerPanel = document.getElementById('authRegisterPanel');
        var registerOtpPanel = document.getElementById('authRegisterOtpPanel');
        var forgotPanel = document.getElementById('authForgotPanel');
        var forgotStepSend = document.getElementById('forgotStepSend');
        var forgotStepOtp = document.getElementById('forgotStepOtp');
        var forgotStepPassword = document.getElementById('forgotStepPassword');

        if (!modal || !loginPanel || !registerPanel || !registerOtpPanel || !forgotPanel || !forgotStepSend || !forgotStepOtp || !forgotStepPassword) {
            return;
        }

        function setForgotStep(step) {
            forgotStepSend.classList.add('auth-hidden');
            forgotStepOtp.classList.add('auth-hidden');
            forgotStepPassword.classList.add('auth-hidden');

            if (step === 'password') {
                forgotStepPassword.classList.remove('auth-hidden');
            } else if (step === 'otp') {
                forgotStepOtp.classList.remove('auth-hidden');
            } else {
                forgotStepSend.classList.remove('auth-hidden');
            }
        }

        function openModal(mode, forgotStep) {
            loginPanel.classList.add('auth-hidden');
            registerPanel.classList.add('auth-hidden');
            registerOtpPanel.classList.add('auth-hidden');
            forgotPanel.classList.add('auth-hidden');

            if (mode === 'register') {
                registerPanel.classList.remove('auth-hidden');
            } else if (mode === 'register_otp') {
                registerOtpPanel.classList.remove('auth-hidden');
            } else if (mode === 'forgot') {
                forgotPanel.classList.remove('auth-hidden');
                setForgotStep(forgotStep === 'password' ? 'password' : (forgotStep === 'otp' ? 'otp' : 'send'));
            } else {
                loginPanel.classList.remove('auth-hidden');
            }

            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('auth-modal-lock');
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('auth-modal-lock');
        }

        document.addEventListener('click', function(event) {
            var loginTrigger = event.target.closest('a[href*="action=login_form"], a[href="#"][data-auth-open="login"], [data-auth-open="login"]');
            var registerTrigger = event.target.closest('a[href*="action=register_form"], a[href="#"][data-auth-open="register"], [data-auth-open="register"]');
            var switchBtn = event.target.closest('[data-auth-switch]');
            var forgotStepBtn = event.target.closest('[data-forgot-step-target]');

            if (loginTrigger) {
                event.preventDefault();
                openModal('login', 'send');
                return;
            }

            if (registerTrigger) {
                event.preventDefault();
                openModal('register', 'send');
                return;
            }

            if (forgotStepBtn) {
                event.preventDefault();
                var targetStep = forgotStepBtn.getAttribute('data-forgot-step-target');
                setForgotStep(targetStep === 'password' ? 'password' : (targetStep === 'otp' ? 'otp' : 'send'));
                return;
            }

            if (switchBtn) {
                var mode = switchBtn.getAttribute('data-auth-switch');
                var forgotStep = switchBtn.getAttribute('data-forgot-step') || 'send';

                if (mode === 'register') {
                    openModal('register', 'send');
                } else if (mode === 'register_otp') {
                    openModal('register_otp', 'send');
                } else if (mode === 'forgot') {
                    openModal('forgot', forgotStep === 'password' ? 'password' : (forgotStep === 'otp' ? 'otp' : 'send'));
                } else {
                    openModal('login', 'send');
                }
                return;
            }

        });

        if (closeBtn) {
            closeBtn.addEventListener('click', closeModal);
        }

        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                openModal('login', 'send');
            });
        }

        document.addEventListener('keydown', function(event) {
            if (
                event.key === 'Escape' &&
                modal.classList.contains('is-open') &&
                !loginPanel.classList.contains('auth-hidden')
            ) {
                closeModal();
            }
        });

        var query = new URLSearchParams(window.location.search);
        var queryAuth = query.get('auth');
        var queryAuthModal = query.get('auth_modal');
        var queryForgotStep = query.get('forgot_step');
        var hasLoginError = <?= !empty($loginError) ? 'true' : 'false' ?>;
        var hasLoginSuccess = <?= !empty($loginSuccess) ? 'true' : 'false' ?>;
        var hasRegisterErrors = <?= !empty($registerErrors) ? 'true' : 'false' ?>;
        var hasOtpError = <?= !empty($otpError) ? 'true' : 'false' ?>;
        var hasForgotErrors = <?= !empty($forgotErrors) ? 'true' : 'false' ?>;
        var hasForgotSuccess = <?= !empty($forgotSuccess) ? 'true' : 'false' ?>;
        var forgotStepFromSession = '<?= ($forgotStepFromSession === 'password') ? 'password' : (($forgotStepFromSession === 'otp') ? 'otp' : 'send') ?>';

        if (queryAuthModal === 'forgot' || hasForgotErrors || hasForgotSuccess) {
            openModal('forgot', queryForgotStep === 'password' ? 'password' : (queryForgotStep === 'otp' ? 'otp' : forgotStepFromSession));
        } else if (queryAuthModal === 'register_otp' || hasOtpError) {
            openModal('register_otp', 'send');
        } else if (queryAuthModal === 'login') {
            openModal('login', 'send');
        } else if (queryAuthModal === 'register') {
            openModal('register', 'send');
        } else

        if (queryAuth === 'register' || hasRegisterErrors) {
            openModal('register', 'send');
        } else if (queryAuth === 'login' || hasLoginError || hasLoginSuccess) {
            openModal('login', 'send');
        }
    })();
</script>