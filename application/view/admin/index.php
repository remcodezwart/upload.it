        <!-- echo out the system feedback (error and success messages) -->
        <?php $this->renderFeedbackMessages(); ?>
        <div>
            <table class="bordered centered responsive-table striped">
                <thead>
                <tr>
                    <td>Id</td>
                    <td>Username</td>
                    <td>User's email</td>
                    <td>Activated ?</td>
                    <td>suspension</td>
                    <td>Soft delete</td>
                    <td>permission upload</td>
                    <td>permission delete</td>
                    <td>permission edit</td>
                    <td>Submit</td>
                </tr>
                </thead>
                <?php foreach ($this->users as $user) { ?>
                    <tr>
                        <td><?= $user->user_id; ?></td>
                        <td><?= $user->user_name; ?></td>
                        <td><?= $user->user_email; ?></td>
                        <td><?= ($user->user_active == 0 ? 'No' : 'Yes'); ?></td>
                        <form action="<?= config::get("URL"); ?>admin/actionAccountSettings" method="post">
                            <td><input type="number" id="suspension"  /></td>

                            <td><input type="checkbox" id="softDelete<?=$user->user_id ?>" name="softDelete" <?php if ($user->user_deleted) { ?> checked <?php } ?> />      
                                <label for="softDelete<?=$user->user_id ?>"></label>                     
                            </td>
                            <td>
                                <input type="checkbox" id="<?=$user->user_id?>" name="uploadPermission" <?php if ($user->upload_permission) { ?> checked <?php } ?> />
                                <label for="<?=$user->user_id?>"></label>
                            </td>
                            <td>
                                <input type="checkbox" id="<?=$user->user_name ?>" name="deletePermission" <?php if ($user->delete_permission) { ?> checked <?php } ?> />
                                  <label for="<?=$user->user_name ?>"></label>
                            </td>
                            <td>
                                <input type="checkbox" id="<?=$user->user_email ?>" name="editPermission" <?php if ($user->edit_permission) { ?> checked <?php } ?> />  
                                <label for="<?=$user->user_email ?>"></label>
                            </td>
                            <td>
                                <input type="hidden" name="user_id" value="<?= $user->user_id; ?>" />
                                <input type="hidden" name="csrf_token" value="<?= Csrf::makeToken(); ?>" />
                                <input type="submit" value="verzenden" />
                            </td>
                        </tr>
                    </form>
                <?php } ?>
            </table>