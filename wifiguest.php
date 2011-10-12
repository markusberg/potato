<?php
/**
 * Mobile OTP self-service station and administration console
 * Version 1.0
 * 
 * PHP Version 5 with PDO, MySQL, and PAM support
 * 
 * Written by Markus Berg
 *   email: markus@kelvin.nu
 *   http://kelvin.nu/mossad/
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
include "session.php";
include "Guest.class.php";

$guest = new Guest();
$guest->userName = $currentUser->userName;

if ( isset($_POST['action']) ) {
    switch ($_POST['action']) {
        case "generate":
            $guest->generate();
            break;
        case "deactivate":
            $guest->deactivate();
            break;
    }
}

include 'header.php';
?>


<?php

?>
<h1>Wifi guest account</h1>
<p>In order to provide wifi access to Sectra guests, you can activate a
guest account which is only granted access to the external network.</p>

<form method="POST" action="wifiguest.php">
<p>
<?php

try {
    $guest->fetch($currentUser->userName);
    echo "The following guest account is active:";
    echo "<ul>\n";
    echo "<li>SSID: sectra-wifi</li>\n";
    echo "<li>Username: " . $currentUser->userName . ".guest</li>";
    echo "<li>Password: " . $guest->password . "</li>\n";
    echo "<li>Valid until: " . $guest->dateExpiration . "</li>\n";
    echo "</ul>\n";
    echo "</p>\n";
    echo "<p>\n";
    echo "<input type=\"hidden\" name=\"action\" value=\"deactivate\" />\n";
    echo "<input type=\"submit\" value=\"De-activate guest account\" />\n";
} catch (NoGuestException $e) {
?>
There's no guest account active for your account.
<p>
<input type="hidden" name="action" value="generate" />
<input type="submit" value="Activate guest account" />
<?php
}
?>

</p>
</form>
<?php
include 'footer.php';
?>