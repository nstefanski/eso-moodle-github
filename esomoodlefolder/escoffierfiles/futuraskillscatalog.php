<?php

require_once('../config.php');

require_login();

global $USER;

print_R($USER);

//header("Location: http://my.escoffieronline.com/mod/scorm/view.php?id=1819"); /* Redirect browser */
//exit;