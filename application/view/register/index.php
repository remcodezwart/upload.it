<!-- echo out the system feedback (error and success messages) -->
    <?php $this->renderFeedbackMessages(); ?>

    <!-- login box on left side -->
        <h2>Register a new account</h2>

        <!-- register form -->
        <form method="post" action="<?php echo Config::get('URL'); ?>register/register_action">
            <!-- the user name input field uses a HTML5 pattern check -->
            <input type="text" pattern="[a-zA-Z0-9]{2,64}" name="user_name" placeholder="Username (letters/numbers, 2-64 chars)" required="true" />
            <input type="text" name="user_email" placeholder="email address (a real address)" required="true" /><br />
            <input type="text" name="user_email_repeat" placeholder="repeat email address (to prevent typos)" required="true" />
            <input type="password" name="user_password_new" pattern=".{6,}" placeholder="Password (6+ characters)" required="true" autocomplete="off" />
            <input type="password" name="user_password_repeat" pattern=".{6,}" required placeholder="Repeat your password" autocomplete="off" /><br>

            <!-- show the captcha by calling the login/showCaptcha-method in the src attribute of the img tag -->
            <figure>
                <img id="captcha" class="captcha" src="<?php echo Config::get('URL'); ?>register/showCaptcha" />
            </figure>
            <input type="text" name="captcha" placeholder="Please enter above characters" required="true" />

            <!-- quick & dirty captcha reloader -->
            <a href="#"
               onclick="document.getElementById('captcha').src = '<?php echo Config::get('URL'); ?>register/showCaptcha?' + Math.random(); return false">Reload Captcha</a>
            
            <input type="submit" value="Register" />
        </form>
<!--
        Please note: This captcha will be generated when the img tag requests the captcha-generation
        (= a real image) from YOURURL/register/showcaptcha. As this is a client-side triggered request, a
        $_SESSION["captcha"] dump will not show the captcha characters. The captcha generation
        happens AFTER the request that generates THIS page has been finished.
  -->
