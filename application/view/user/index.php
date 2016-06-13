<?php $this->renderFeedbackMessages(); ?>

	<p class="center-align">opmerking . is niet toegestaan in de bestands naam</p>
	
	<table class="bordered centered responsive-table striped">
	    <thead>
			<tr>
				<th>file</th>
				<th>editen</th>
				<th>verwijderen</th>
				<th>downloaden</th>
			</tr>
		</thead>
		<?php
			foreach ($this->files as $fileData) {
		?>
		<tr>
			<td><?=$fileData->fake_name_of_file?></td>
			<td><a href="<?= config::get("URL"); ?>user/editRead?id=<?=$fileData->id ?>">editen/lezen</a></td>
			<td><a href="<?= config::get("URL"); ?>user/deleteFile?id=<?=$fileData->id ?>">verwijderen</a></td>
			<td><a href="<?= config::get("URL"); ?>user/download?token=<?= Csrf::makeToken(); ?>&file=<?=$fileData->real_name_of_file ?>" >downloaden</a></td>
		</tr>
		<?php
			}
		?>
	</table>
<form style="min-height:100px" class="dropzone needsclick dz-clickable blue darken-4" method="post" enctype="multipart/form-data" action="<?= config::get("URL"); ?>user/uploadFile_action">
	<input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
	<input type="submit" value="verzenden">
</form>


