<?php

/**
 * UserController
 * Controls everything that is user-related
 */
class UserController extends Controller
{
    /**
     * Construct this object by extending the basic Controller class.
     */
    public function __construct()
    {
        parent::__construct();

        // VERY IMPORTANT: All controllers/areas that should only be usable by logged-in users
        // need this line! Otherwise not-logged in users could do actions.
        Auth::checkAuthentication();
    }

    /**
     * Show user's PRIVATE profile
     */
    public function index()
    {
        $this->View->render('user/index' , array(
            'files' => userModel::getFiles(),
            ));
    }

    /**
     * Show edit-my-username page
     */
    public function editUsername()
    {
        $this->View->render('user/editUsername');
    }

    /**
     * Edit user name (perform the real action after form has been submitted)
     */
    public function editUsername_action()
    {
        // check if csrf token is valid
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        UserModel::editUserName(Request::post('user_name'));
        Redirect::to('user/editUsername');
    }

    /**
     * Show edit-my-user-email page
     */
    public function editUserEmail()
    {
        $this->View->render('user/editUserEmail');
    }

    /**
     * Edit user email (perform the real action after form has been submitted)
     */
    // make this POST
    public function editUserEmail_action()
    {
        UserModel::editUserEmail(Request::post('user_email'));
        Redirect::to('user/editUserEmail');
    }
    /**
     * Password Change Page
     */
    public function changePassword()
    {
        $this->View->render('user/changePassword');
    }

    /**
     * Password Change Action
     * Submit form, if retured positive redirect to index, otherwise show the changePassword page again
     */
    public function changePassword_action()
    {
        $result = PasswordResetModel::changePassword(
            Session::get('user_name'), Request::post('user_password_current'),
            Request::post('user_password_new'), Request::post('user_password_repeat')
        );

        if($result) {
            Redirect::to('user/index');
        } else {
            Redirect::to('user/changePassword');
        }
    }
    public function deleteUser()
    {
        $this->View->render('user/deleteUser');
    }
    public function deleteUser_action()
    {
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        $result = UserModel::deleteUsers(
              Session::get('user_name')
        );

        if ($result) {
            Redirect::to('login/index');
        } else {
            Redirect::to('user/deleteUser');
        }
    }
    public function uploadFile_action()
    {
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }
        $result = UserModel::uploadFile();
        
        Redirect::to('login/index');
    }
    public function deleteFile()
    {
        if (!isset($_GET['id'])) {
           Redirect::to('user/index');
        }
        $this->View->render('user/deleteFile');
    }
    public function deleteFile_action()
    {
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }
        $result = UserModel::deleteFile();
        if ($result) {
            Redirect::to('user/index');
        } else {
            Redirect::to('user/deleteFile');
        }
    }
    public function editRead()
    {
        $this->View->render('user/fileEditor', array(
            'file' => userModel::getContentOfFile(),
        ));

    }
    public function saveFile()
    {
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }
        $result = UserModel::saveFile();
        Redirect::to('user/index');
    }
    public function download()
    {
        if (!isset($_GET['token'])) {
            return false;
            exit;
        }
        $token = $_GET['token'];

        if (!Csrf::isTokenValid($token)) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }
        UserModel::downloadFile();
    }
    public function addComment()
    {
        if (!Csrf::isTokenValid()) {
            LoginModel::logout();
            Redirect::home();
            exit();
        }

        UserModel::addComment();
        Redirect::to('user/index');
    }
}
