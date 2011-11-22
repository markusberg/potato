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

include "config.php";
include "User.class.php";
include "Guest.class.php";
include "mschap.php";

$options = getopt("u:c:r:");

$userName = $options["u"];

$mschapChallengeHash = pack( 'H*', $options["c"] );
$mschapResponse = pack( 'H*', $options["r"] );

// $clientShortName = $options["s"];


if ( strtolower(substr( $userName, -6 )) == ".guest" ) {
    // Guest login
    $guestName = substr( $userName, 0, -6 );
    $guest = new Guest();
    
    try {
        $guest->fetch($guestName);
        $pwHash = NtPasswordHash($guest->getPassword());
        $calcResponse = ChallengeResponse($mschapChallengeHash, $pwHash);
        if ($calcResponse == $mschapResponse) {
            echo "NT_KEY: " . bin2hex(NtPasswordHashHash($guest->getPassword())) . "\n";
        } else {
            // force mschap auth to fail
            echo "NT_KEY: 42" . "\n";
        }
    } catch (NoGuestException $ignore) {
        // force mschap auth to fail
        echo "NT_KEY: 42" . "\n";
    }
    exit;
}

try {
    $user = new User();
    $user->fetch($userName);
    $user->verifySanity();

    if ( $user->checkOTPmschap($mschapChallengeHash, $mschapResponse) ) {
        if ( ! $user->isMemberOf($groupUser) ) {
            // User not member of access group
            $user->log("Valid login, but user is not a member of ${groupUser}.");
        } elseif ( $user->isLockedOut() ) {
            // Account locked out
            $user->invalidLogin();
            $user->log("Valid login, but account locked out.");
        } elseif ( $user->replayAttack()) {
            // Replay attack
            $user->invalidLogin();
            $user->log("Invalid login. OTP replay.");
        } else {
            $user->validLogin("mschap");
            echo "NT_KEY: " . bin2hex(NtPasswordHashHash($user->passPhrase)) . "\n";
            exit;
        }
    } else {
        $user->invalidLogin();
        $user->log("Invalid login [ mschap ]");
    }
} catch (NoSuchUserException $ignore) {
}

// Set a dummy password for the mschapv2 module to fail on
echo "NT_KEY: 42" . "\n";
exit;

?>
