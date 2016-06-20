<pre id="pre" contentEditable="true" onkeyup="save()">
<?= $this->file[0] ?>
</pre>
<form action="<?= Config::get('URL'); ?>user/saveFile" method="post">
	<input value="<?= $this->file[0] ?>" type="hidden" name="value" id="value">
	<input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
	<input type="hidden" name="id" value="<?php if (isset($_GET['id'])) {
		echo $_GET['id'];
	}?>" >
	<input type="submit" class="waves-effect waves-light btn" value="opslaan">
</form>	
<script type="text/javascript">
	function save()
	{
		document.getElementById("value").value = document.getElementById("pre").innerHTML;
	}
</script>