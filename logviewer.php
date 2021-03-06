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
include "session.php";
include 'NavigationBar.class.php';

$page->prepTabBar();
$page->printHeader();

global $dbh;
$ps = $dbh->prepare("SELECT COUNT(*) from Log where userName=:userName");
$ps->execute(array(":userName"=>$user->getUserName()));
$row = $ps->fetch();

$navBar = new NavigationBar();
$navBar->setNumRows($row[0]);
$navBar->addGetParam("userName", $user->getUserName());
$pageCurrent = ( empty($_GET['page']) ? 1 : $_GET['page'] );
$navBar->setPageCurrent($pageCurrent);
$navBar->printNavBar();

?>
<table class="userlist" cellpadding="0" cellspacing="0">

<?php

$ps = $dbh->prepare("SELECT time, passPhrase, idClient, idNAS, status, message from Log where userName=:userName order by time DESC limit :rowsPerPage offset :rowsOffset");
$ps->bindValue(':userName', $user->getUserName());
$ps->bindValue(':rowsPerPage', $navBar->getRowsPerPage(), PDO::PARAM_INT);
$ps->bindValue(':rowsOffset', $navBar->getRowsOffset(), PDO::PARAM_INT);
$ps->execute();

while ($row = $ps->fetch()) {
?>
    <tr>
        <td><?php echo $row['time'] ?></td>
        <?php echo $currentUser->isAdmin() ? '<td>' . $row['passPhrase'] . '</td>' : '' ?>
        <td><?php echo $row['idClient'] ?></td>
        <td><?php echo $row['idNAS'] ?></td>
        <td><?php echo $row['status'] ?></td>
        <td><?php echo $row['message'] ?></td>
    </tr> 
<?php
}
?>
</table>

<?php
$navBar->printNavBar();

$page->printFooter();
?>
