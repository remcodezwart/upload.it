<h4> weet u zeker dat u deze file wilt verwijderen?</h4>
<form method="post" action="<?= config::get("URL"); ?>user/deleteFile_action">
	<input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
	<input type="hidden" name="id" value="<?=$_GET['id'] ?>">
	<input class="waves-effect waves-light btn" type="submit" value="Ja">
</form>