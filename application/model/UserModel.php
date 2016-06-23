<?php

/**
 * UserModel
 * Handles all the PUBLIC profile stuff. This is not for getting data of the logged in user, it's more for handling
 * data of all the other users. Useful for display profile information, creating user lists etc.
 */
class UserModel
{
    /**
     * Gets an array that contains all the users in the database. The array's keys are the user ids.
     * Each array element is an object, containing a specific user's data.
     * The avatar line is built using Ternary Operators, have a look here for more:
     * @see http://davidwalsh.name/php-shorthand-if-else-ternary-operators
     *
     * @return array The profiles of all users
     */
    public static function getPublicProfilesOfAllUsers()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, user_email, user_active, user_deleted , upload_permission , delete_permission , edit_permission FROM users";
        $query = $database->prepare($sql);
        $query->execute();

        $all_users_profiles = array();

        foreach ($query->fetchAll() as $user) {

            // all elements of array passed to Filter::XSSFilter for XSS sanitation, have a look into
            // application/core/Filter.php for more info on how to use. Removes (possibly bad) JavaScript etc from
            // the user's values
            array_walk_recursive($user, 'Filter::XSSFilter');

            $all_users_profiles[$user->user_id] = new stdClass();
            $all_users_profiles[$user->user_id]->user_id = $user->user_id;
            $all_users_profiles[$user->user_id]->user_name = $user->user_name;
            $all_users_profiles[$user->user_id]->user_email = $user->user_email;
            $all_users_profiles[$user->user_id]->user_active = $user->user_active;
            $all_users_profiles[$user->user_id]->user_deleted = $user->user_deleted;
            $all_users_profiles[$user->user_id]->upload_permission = $user->upload_permission;
            $all_users_profiles[$user->user_id]->delete_permission = $user->delete_permission;
            $all_users_profiles[$user->user_id]->edit_permission = $user->edit_permission;
        }

        return $all_users_profiles;
    }

    /**
     * Gets a user's profile data, according to the given $user_id
     * @param int $user_id The user's id
     * @return mixed The selected user's profile
     */
    public static function getPublicProfileOfUser($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, user_email, user_active, user_has_avatar, user_deleted
                FROM users WHERE user_id = :user_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $user_id));

        $user = $query->fetch();

        if ($query->rowCount() == 1) {
            if (Config::get('USE_GRAVATAR')) {
                $user->user_avatar_link = AvatarModel::getGravatarLinkByEmail($user->user_email);
            } else {
                $user->user_avatar_link = AvatarModel::getPublicAvatarFilePathOfUser($user->user_has_avatar, $user->user_id);
            }
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_USER_DOES_NOT_EXIST'));
        }

        // all elements of array passed to Filter::XSSFilter for XSS sanitation, have a look into
        // application/core/Filter.php for more info on how to use. Removes (possibly bad) JavaScript etc from
        // the user's values
        array_walk_recursive($user, 'Filter::XSSFilter');

        return $user;
    }

    /**
     * @param $user_name_or_email
     *
     * @return mixed
     */
    public static function getUserDataByUserNameOrEmail($user_name_or_email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_id, user_name, user_email FROM users
                                     WHERE (user_name = :user_name_or_email OR user_email = :user_name_or_email)
                                           AND user_provider_type = :provider_type LIMIT 1");
        $query->execute(array(':user_name_or_email' => $user_name_or_email, ':provider_type' => 'DEFAULT'));

        return $query->fetch();
    }

    /**
     * Checks if a username is already taken
     *
     * @param $user_name string username
     *
     * @return bool
     */
    public static function doesUsernameAlreadyExist($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_id FROM users WHERE user_name = :user_name LIMIT 1");
        $query->execute(array(':user_name' => $user_name));
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * Checks if a email is already used
     *
     * @param $user_email string email
     *
     * @return bool
     */
    public static function doesEmailAlreadyExist($user_email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("SELECT user_id FROM users WHERE user_email = :user_email LIMIT 1");
        $query->execute(array(':user_email' => $user_email));
        if ($query->rowCount() == 0) {
            return false;
        }
        return true;
    }

    /**
     * Writes new username to database
     *
     * @param $user_id int user id
     * @param $new_user_name string new username
     *
     * @return bool
     */
    public static function saveNewUserName($user_id, $new_user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users SET user_name = :user_name WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(':user_name' => $new_user_name, ':user_id' => $user_id));
        if ($query->rowCount() == 1) {
            return true;
        }
        return false;
    }

    /**
     * Writes new email address to database
     *
     * @param $user_id int user id
     * @param $new_user_email string new email address
     *
     * @return bool
     */
    public static function saveNewEmailAddress($user_id, $new_user_email)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $query = $database->prepare("UPDATE users SET user_email = :user_email WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(':user_email' => $new_user_email, ':user_id' => $user_id));
        $count = $query->rowCount();
        if ($count == 1) {
            return true;
        }
        return false;
    }

    /**
     * Edit the user's name, provided in the editing form
     *
     * @param $new_user_name string The new username
     *
     * @return bool success status
     */
    public static function editUserName($new_user_name)
    {
        // new username same as old one ?
        if ($new_user_name == Session::get('user_name')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_SAME_AS_OLD_ONE'));
            return false;
        }

        // username cannot be empty and must be azAZ09 and 2-64 characters
        if (!preg_match("/^[a-zA-Z0-9]{2,64}$/", $new_user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        // clean the input, strip usernames longer than 64 chars (maybe fix this ?)
        $new_user_name = substr(strip_tags($new_user_name), 0, 64);

        // check if new username already exists
        if (self::doesUsernameAlreadyExist($new_user_name)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USERNAME_ALREADY_TAKEN'));
            return false;
        }

        $status_of_action = self::saveNewUserName(Session::get('user_id'), $new_user_name);
        if ($status_of_action) {
            Session::set('user_name', $new_user_name);
            Session::add('feedback_positive', Text::get('FEEDBACK_USERNAME_CHANGE_SUCCESSFUL'));
            return true;
        } else {
            Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            return false;
        }
    }

    /**
     * Edit the user's email
     *
     * @param $new_user_email
     *
     * @return bool success status
     */
    public static function editUserEmail($new_user_email)
    {
        // email provided ?
        if (empty($new_user_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_FIELD_EMPTY'));
            return false;
        }

        // check if new email is same like the old one
        if ($new_user_email == Session::get('user_email')) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_SAME_AS_OLD_ONE'));
            return false;
        }

        // user's email must be in valid email format, also checks the length
        // @see http://stackoverflow.com/questions/21631366/php-filter-validate-email-max-length
        // @see http://stackoverflow.com/questions/386294/what-is-the-maximum-length-of-a-valid-email-address
        if (!filter_var($new_user_email, FILTER_VALIDATE_EMAIL)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_EMAIL_DOES_NOT_FIT_PATTERN'));
            return false;
        }

        // strip tags, just to be sure
        $new_user_email = substr(strip_tags($new_user_email), 0, 254);

        // check if user's email already exists
        if (self::doesEmailAlreadyExist($new_user_email)) {
            Session::add('feedback_negative', Text::get('FEEDBACK_USER_EMAIL_ALREADY_TAKEN'));
            return false;
        }

        // write to database, if successful ...
        // ... then write new email to session, Gravatar too (as this relies to the user's email address)
        if (self::saveNewEmailAddress(Session::get('user_id'), $new_user_email)) {
            Session::set('user_email', $new_user_email);
            Session::set('user_gravatar_image_url', AvatarModel::getGravatarLinkByEmail($new_user_email));
            Session::add('feedback_positive', Text::get('FEEDBACK_EMAIL_CHANGE_SUCCESSFUL'));
            return true;
        }

        Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
        return false;
    }

    /**
     * Gets the user's id
     *
     * @param $user_name
     *
     * @return mixed
     */
    public static function getUserIdByUsername($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id FROM users WHERE user_name = :user_name AND user_provider_type = :provider_type LIMIT 1";
        $query = $database->prepare($sql);

        // DEFAULT is the marker for "normal" accounts (that have a password etc.)
        // There are other types of accounts that don't have passwords etc. (FACEBOOK)
        $query->execute(array(':user_name' => $user_name, ':provider_type' => 'DEFAULT'));

        // return one row (we only have one result or nothing)
        return $query->fetch()->user_id;
    }

    /**
     * Gets the user's data
     *
     * @param $user_name string User's name
     *
     * @return mixed Returns false if user does not exist, returns object with user's data when user exists
     */
    public static function getUserDataByUsername($user_name)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT user_id, user_name, user_email, user_password_hash, user_active,user_deleted, user_suspension_timestamp, user_account_type,
                       user_failed_logins, user_last_failed_login
                  FROM users
                 WHERE (user_name = :user_name OR user_email = :user_name)
                       AND user_provider_type = :provider_type
                 LIMIT 1";
        $query = $database->prepare($sql);

        // DEFAULT is the marker for "normal" accounts (that have a password etc.)
        // There are other types of accounts that don't have passwords etc. (FACEBOOK)
        $query->execute(array(':user_name' => $user_name, ':provider_type' => 'DEFAULT'));

        // return one row (we only have one result or nothing)
        return $query->fetch();
    }
    /**
     * Gets the user's data by user's id and a token (used by login-via-cookie process)
     *
     * @param $user_id
     * @param $token
     *
     * @return mixed Returns false if user does not exist, returns object with user's data when user exists
     */
    public static function uploadFile()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $userId = Usermodel::getUserIdByUsername(Session::get('user_name'));

        $query = $database->prepare("SELECT users.upload_permission FROM users WHERE user_id=:userId LIMIT 1");
        $query->execute(array(':userId' => $userId));
        $result = $query->fetch();

        if (!$result->upload_permission) {
            return false;
            exit();
        }
 
        if (!isset($_FILES['file'])) {
            return false;
            exit();
        }
        $fakeFileName = $_FILES['file']['name'];

        $query = $database->prepare("SELECT users.upload_permission FROM file WHERE fake_name_of_file=:fakeName LIMIT 1");
        $query->execute(array(':fakeName' => $fakeFileName));
        $result = $query->fetch();

        if (empty($_FILES['file']) || $_FILES['file'] === null) {
            return false;
            exit();
        }
        if (!is_file($_FILES['file']['tmp_name'])) {
            return false;
            exit();
        }
        clearstatcache();//clears the cache of the functions
        if ($_FILES["file"]["size"] > 500000) {
            return false;
            exit();
        } 
        clearstatcache();

        $forbiddenCharachters = array("&", "!", "@", "}", "{", "[", "]", "?", "(", "|", ")", "*", " ", ">", "<", "/", "\\", ":", "=", "-", "+", "'","”",'"', "*", "^", "#", "%","¦","¦","‹","´","›","“","“","´","~","`","´","ˆ","¯","”","”","ˆ","");
        $realFileName = str_replace($forbiddenCharachters,"",$_FILES['file']['name']);
        preg_match("/(?:\W.+)\w/",$realFileName,$extension);

        $hash = self::writeFileToDatabase($extension,$fakeFileName);
        if ($hash === false || $hash === null || $hash == "") {
            Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            return false;
            exit();
        }

        $tmp_name = $_FILES["file"]["tmp_name"];
 
        move_uploaded_file($tmp_name, "../uploads/$hash");
        return true;
    }
    public static function getFiles()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT * FROM file WHERE active=:active");
        $query->execute(array(':active' => "1"));

        $result = $query->fetchAll();

        array_walk_recursive($result, 'Filter::XSSFilter');
        $databse = null;
        return $result;
    }
    public static function deleteFile()
    {
        if (!isset($_POST['id'])) {
            return false;
            exit();
        } else {
            $id = $_POST['id'];
        }
        $database = DatabaseFactory::getFactory()->getConnection();
        $userId = Usermodel::getUserIdByUsername(Session::get('user_name'));

        $query = $database->prepare("SELECT users.delete_permission FROM users  WHERE user_id = :user_id LIMIT 1");
        $query->execute(array(':user_id' => $userId));
        $result = $query->fetch();

        if (!$result->delete_permission) {
            return false;
            exit();
        }
        $query = $database->prepare("SELECT file.users_id FROM file WHERE id=:id LIMIT 1 ");
        $query->execute(array(':id' => $id));
        $result = $query->fetch();

        foreach ($result as $chek) {
            if ($chek != $userId) {
                Session::add('feedback_negative', Text::get('NOT_OWNER_OF_FILE'));
                return false;
                exit();
            } else {
                $query = $database->prepare("UPDATE file SET active=:active WHERE id=:id");
                $query->execute(array('active' => "0", ':id' => $id));
                return true;
                exit();
            }
        }
    }
    public static function getContentOfFile()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        if (!isset($_GET['id'])) {
            return false;
            exit();
        }
        $id = $_GET['id'];

        $query = $database->prepare("SELECT file.real_name_of_file FROM file WHERE id=:id LIMIT 1 ");
        $query->execute(array(':id' => $id));
        $result = $query->fetch();
        if ($result === null) {
            return false;
            exit();
        }
        if (!file_exists('../uploads/'.$result->real_name_of_file)) {
            clearstatcache();
            return false;
            exit();
        }
        clearstatcache();
        $fileContent = file_get_contents('../uploads/'.$result->real_name_of_file);
        clearstatcache();
        $displayContent = array($fileContent);
        array_walk_recursive($displayContent, 'Filter::XSSFilter');
        $database = null;

        if ($displayContent[0] == null|| $displayContent[0] == "") {
            $displayContent[0] = "not editable file";
        }

        return $displayContent;
    }
    public static function downloadFile()
    {
        if (isset($_GET['file'])) {
            $file = $_GET['file'];
        }
        $file = str_replace("\\","",$file);
        $file = str_replace("/","",$file);//makes it so users can not get logic files from the server by going up maps
        clearstatcache();
        if (file_exists('../uploads/'.$file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($file).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize("../uploads/" . $file));
            readfile("../uploads/".$file);
            return $file;
        }
    }
    
    public static function saveFile()
    {

        $file = $_POST['id'];
        $file = str_replace("\\","",$file);
        $file = str_replace("/","",$file);

        $value = filter_var($_POST['value'], FILTER_SANITIZE_STRING);

        $database = DatabaseFactory::getFactory()->getConnection();
        $userId = Usermodel::getUserIdByUsername(Session::get('user_name'));

        $query = $database->prepare("SELECT users.edit_permission FROM users WHERE user_id=:id LIMIT 1 ");
        $query->execute(array(':id' => $userId));
        $result = $query->fetch();

        if (!$result) {
            Session::add('feedback_negative', Text::get('NO PERMISSION TO EDIT'));
            return false;
            exit();
        }
        $query = $database->prepare("SELECT * FROM file WHERE id=:id LIMIT 1 ");
        $query->execute(array(':id' => $file));
        $result = $query->fetch();

        if ($result->users_id != $userId) {
            Session::add('feedback_negative', Text::get('NOT_OWNER'));
            return false;
            exit();
        }
        if (!$result->active) {
            Session::add('feedback_negative', Text::get('FILE_DOES_NOT_EXSIST'));
            return false;
            exit();
        }

        clearstatcache();
        if (!file_exists('../uploads/'.$result->real_name_of_file)) {
            Session::add('feedback_negative', Text::get('FILE_DOES_NOT_EXSIST'));
            return false;
            exit();
        }
        if ($value === null||$value == "") {
            Session::add('feedback_negative', Text::get('EMPTY_STRINGS'));
            return false;
            exit();
        }
        preg_match("/\W.*/",$result->real_name_of_file,$extension);//gets the extension of the file

        $hash = self::writeFileToDatabase($extension,$result->fake_name_of_file);

        $myfile = fopen("../uploads/".$hash, "w");

        $fileContents = array();
        $fileContents = explode("/n",$value);

        foreach ($fileContents as $singleLine) {
            file_put_contents("../uploads/".$hash, $singleLine."\n", FILE_APPEND | LOCK_EX);      
        }
        
        $query = $database->prepare("UPDATE file SET active=:active WHERE id=:id");
        $query->execute(array(':id' => $_POST['id'],':active' => "0"));

        Session::add('feedback_positive', Text::get('FILE_EDITED_SUCCES'));

        return true;
    }
    public static function addComment()
    {
        if (!isset($_POST['comment'])) {
            Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            return false;
            exit();
        }
        if (!isset($_POST['id'])) {
            Session::add('feedback_negative', Text::get('FEEDBACK_UNKNOWN_ERROR'));
            return false;
            exit();
        }

        $userId = Usermodel::getUserIdByUsername(Session::get('user_name'));
        $database = DatabaseFactory::getFactory()->getConnection();
        $query = $database->prepare("SELECT file.users_id FROM file WHERE users_id=:user_id AND id=:id");
        $query->execute(array(':user_id' => $userId,':id' => $_POST['id']));
        $result = $query->fetch();

        if ($userId !== $result->users_id) {
            Session::add('feedback_negative', Text::get('NOT_OWNER'));
            return false;
            exit();
        }
        $comment = filter_var($_POST['comment'], FILTER_SANITIZE_STRING);
        if ($_POST['reset'] == "on") {
            $comment = "deze gebruiker heeft nog geen beschrijving toegevoegd aan zijn/haar bestand";
        }
        if ($comment == "") {
            Session::add('feedback_negative', Text::get('EMPTY_COMMENT'));
            return false;
            exit();
        }

        $query = $database->prepare("UPDATE file SET discription=:discription WHERE users_id=:user_id AND id=:id");
        $query->execute(array(':discription' => $comment,':user_id' => $userId,':id' => $_POST['id']));
        $database = null;

        return true;

    }
    public static function currentId($id)
    {
        $userId = Usermodel::getUserIdByUsername(Session::get('user_name'));
        if ($id === $userId) {
            return true;
        } else {
            return false;
        }
    }
    protected static function writeFileToDatabase($extension,$fakeFileName)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $userId = Usermodel::getUserIdByUsername(Session::get('user_name'));

        if ($extension[0] == ".txt"||$extension[0] == ".html"||$extension[0] == ".htm"||$extension[0] == ".php"|| $extension[0] == ".zip"||$extension[0] == ".docb"||$extension[0] == ".dotx"||$extension[0] == ".docx" || $extension[0] == ".doc"|| $extension[0] == ".dot"|| $extension[0] == ".ppt"||$extension[0] == ".pot"|| $extension[0] == ".pps"||$extension[0]  == ".pptx" ||$extension[0] == ".pptm" || $extension[0] == ".ppsx" ||$extension[0]  == ".ppsm" ||$extension[0] == ".sldx"||$extension[0] == ".sldm"||$extension[0]  == ".potx" || $extension[0] == ".potm"||$extension[0] == ".ppam") {
        
        while (true) {  
            $randomNumbers = "";
            $numbers = "qwertyuioplkjhgfdsazxcvbnm"; //the charachters a to z for a random hash
            $number = mt_rand(20,30);

            for ($amout=0; $amout < $number; $amout++) { 
                $letter = mt_rand(0,25);
                $randomNumbers .= $numbers[$letter];
            }

            $hash = $randomNumbers.$number.$extension[0];
            clearstatcache();
            if (!file_exists('../uploads/'.$hash)) {
                break;               
            } 
        }
        $query = $database->prepare("INSERT INTO file (users_id,fake_name_of_file,real_name_of_file) VALUES (:user_id,:fakeName,:realName)");
        $query->execute(array(':user_id' => $userId, ':fakeName' => $fakeFileName, ':realName' => $hash));
        $database = null;

        return $hash;
        } else {
            return false;
        }
    }
}