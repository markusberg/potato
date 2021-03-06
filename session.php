<?php
/**
 * Potato
 * One-time-password self-service and administration
 * Version 1.0
 * 
 * Written by Markus Berg
 *   email: markus@kelvin.nu
 *   http://kelvin.nu/software/potato/
 * 
 * Copyright 2011 Markus Berg
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 */

// Make sure we have a database handle
try {
    $dbh = new PDO("mysql:host=${dbServer};dbname=${dbName}", $dbUser, $dbPassword);
} catch (Exception $ignore) {
    echo "Database error.";
    exit();
}

session_start();
include "User.class.php";
include "Page.class.php";
$page = new Page();

if ( !isset( $_SESSION['currentUser'] ) ) {
    header("Location: login.php");
    exit;
}

$currentUser = new User();
$currentUser->setUserName( $_SESSION['currentUser'] );
$currentUser->setCSRFToken( $_SESSION['CSRFToken'] );

if ( isset($_SESSION['timeActivity']) ) {
    if ( ((int) (gmdate("U") - $_SESSION['timeActivity'])) > (30 * 60) ) {
        $_SESSION['msgInfo'] = "You have been logged out due to inactivity.";
        unset( $_SESSION['currentUser'] );
        unset( $_SESSION['timeActivity'] );
        unset( $_SESSION['CSRFToken'] );
        header("Location: login.php");
        exit;
    }
}

// Everything checks out. Update the session activity timer
$_SESSION['timeActivity'] = gmdate( "U" );

?>
