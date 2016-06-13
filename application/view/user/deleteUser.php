<?php $this->renderFeedbackMessages(); ?>

<h4>Weet U zeker dat u uw acount wil verwijderen?</h4>
	<form method="post" action="<?php echo Config::get('URL'); ?>user/deleteUser_action">
		<input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
		<input class="btn waves-effect waves-light" type="submit" value="Ja" class="submit">
	</form>