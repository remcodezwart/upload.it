<!DOCTYPE html>
<html>
<head>
    <title>uploadCloud</title>
    <meta charset="utf-8">
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- send empty favicon fallback to prevent user's browser hitting the server for lots of favicon requests resulting in 404s -->
    <link rel="icon" href="data:;base64,=">
    <link rel="stylesheet" href="<?php echo Config::get('URL'); ?>css/normalize.css" />
    <link rel="stylesheet" href="<?php echo Config::get('URL'); ?>css/style.css" />
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
    <script type="text/javascript" src="<?php echo Config::get('URL'); ?>javascript/materialize.min.js"></script>
    <script type="text/javascript" src="<?php echo Config::get('URL'); ?>javascript/dropzone.js"></script>
</head>
<body class="cyan darken-3">
    <div class="container">
        <div class="row">
            <?php if (!Session::userIsLoggedIn()) { ?>
                <nav class="deep-purple accent-4">
                    <ul>
                        <!-- for not logged in users -->
                        <div class="col s3">
                            <li<?php if (View::checkForActiveControllerAndAction($filename, "login/index")) { echo ' class="active" '; } ?> >
                                <a class="right-align" href="<?php echo Config::get('URL'); ?>login/index">Login</a>
                            </li>
                        </div>
                        <div class="col s6">
                        </div>
                        <div class="col s3">
                            <li<?php if (View::checkForActiveControllerAndAction($filename, "register/index")) { echo ' class="active" '; } ?> >
                                <a href="<?php echo Config::get('URL'); ?>register/index">Register</a>
                             </li>
                        </div>
                    </ul>
                </nav>
            </div>
        <?php };
         if (Session::userIsLoggedIn()) { ?>
            <nav class="deep-purple accent-4">
                <!-- my account -->
                <ul>
                    <div class="col s6">
                              <li><a class="dropdown-button" href="#!" data-activates="dropdown1">menu<i class="material-icons right">arrow_drop_down</i></a></li>
                    </div>
                </ul>
                <ul id="dropdown1" class="dropdown-content">
                        <li<?php if (View::checkForActiveController($filename, "user/index")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo Config::get('URL'); ?>user/index">My Account</a>
                        </li>
                        <li<?php if (View::checkForActiveController($filename, "user/editUsername")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo Config::get('URL'); ?>user/editusername">wijzig mijn gebruikersnaam</a>
                        </li>
                        <li<?php if (View::checkForActiveController($filename, "user/editUserEmail")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo Config::get('URL'); ?>user/edituseremail">wijzig mijn email</a>
                        </li>
                        <li<?php if (View::checkForActiveController($filename, "user/changePassword")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo Config::get('URL'); ?>user/changePassword">wijzig mijn paswoord</a>
                        </li>
                        <li<?php if (View::checkForActiveController($filename, "user/deleteUser")) { echo ' class="active" '; } ?> >
                            <a href="<?php echo Config::get('URL'); ?>user/deleteUser">acount verwijderen</a>
                        </li>
                        <li>
                            <a href="<?php echo Config::get('URL'); ?>login/logout">Logout</a>
                        </li>
                </ul>
                    <ul>
                        <div class="col s6">
                        <?php if (Session::get("user_account_type") == 7) { ?>
                            <li class="right-align"<?php if (View::checkForActiveController($filename, "admin")) {
                                echo ' class="active" ';
                            } ?> >
                                <a href="<?php echo Config::get('URL'); ?>admin/">Admin</a>
                            </li>
                        <?php } ?>
                        </div>    
                    </ul>
                </div>
            </nav>
        <?php } ?>
    <main class="z-depth-3 red darken-2">