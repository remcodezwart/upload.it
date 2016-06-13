<h1>Request a password reset</h1>
 

        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>

        <!-- request password reset form box -->
        <form method="post" action="<?php echo Config::get('URL'); ?>login/requestPasswordReset_action">
            <label for="user_name_or_email">
                Enter your username or email and you'll get a mail with instructions:
                <input type="text" name="user_name_or_email" required /><br />
            </label>

            <!-- show the captcha by calling the login/showCaptcha-method in the src attribute of the img tag -->
            <img id="captcha" class="captcha" src="<?php echo Config::get('URL'); ?>register/showCaptcha" /><br/>
            <input type="text" name="captcha" placeholder="Enter captcha above" required /><br />

            <!-- quick & dirty captcha reloader -->
            <a href="#"
               onclick="document.getElementById('captcha').src = '<?php echo Config::get('URL'); ?>register/showCaptcha?' + Math.random(); return false">Reload Captcha</a>

            <input type="submit" value="Send me a password-reset mail" />
        </form>
   <!--
        Please note: This captcha will be generated when the img tag requests the captcha-generation
        (= a real image) from YOURURL/register/showcaptcha. As this is a client-side triggered request, a
        $_SESSION["captcha"] dump will not show the captcha characters. The captcha generation
        happens AFTER the request that generates THIS page has been finished.
    -->