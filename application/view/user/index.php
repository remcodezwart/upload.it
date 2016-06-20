<?php $this->renderFeedbackMessages(); ?>

	<p class="center-align">opmerking . is niet toegestaan in de bestands naam</p>
	
	<table class="bordered centered striped">
	    <thead>
			<tr>
				<th>file</th>
				<th>editen</th>
				<th>verwijderen</th>
				<th>downloaden</th>
			</tr>
		</thead>
		<tbody>
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
		</tbody>
	</table>
		<?php
			foreach ($this->files as $fileData) {
		?>
	<div class="row">
		<div class="col s12 teal darken-1">
			<ul class="collapsible" data-collapsible="accordion">
    			<li>
     				<div class="collapsible-header"><i class="material-icons">message</i>beschijving van het bestand <?=$fileData->fake_name_of_file?></div>
     				<div class="collapsible-body"><p><?=$fileData->discription ?></p></div>
     				<?php 
     					if (UserModel::currentId($fileData->users_id)) { //makes it so only the user of thise file sees the comment box
     				?>
     				<form method="post" action="<?= config::get("URL"); ?>user/addComment">
							<input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
							<input type="hidden" name="id" value="<?=$fileData->id ?>">
							<textarea placeholder="voeg hier een comment toe" name="comment" required="true"><?=$fileData->discription ?></textarea>
							<input type="checkbox" name="reset" id="<?=$fileData->id?>" />
                           	<label for="<?=$fileData->id?>">een beschrijving resseten naar de standaard waarden?</label>
							<input type="submit" value="verzenden" />
					</form>
					<?php 
						}
					?>	
    			</li>
    		</ul>
		</div>
	</div>
		<?php
			}
		?>
	<form style="min-height:100px" class="dropzone needsclick dz-clickable blue darken-4" method="post" enctype="multipart/form-data" action="<?= config::get("URL"); ?>user/uploadFile_action" id="my-awesome-dropzone">
		<input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
		<input type="submit" value="verzenden">
	</form>