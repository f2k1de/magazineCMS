<?php
# Verwaltungsmodul
# (c) 2017 IK

error_reporting(E_ALL);

require("../assets/DBCore.php");

class verwaltung {
    function __construct() {
        session_start();
        $this->DB = new DBCore ('dbtest');
        $this->config = require('config.php');

        if(!isset($_SESSION['loggedin']) OR ($_SESSION['loggedin'] !== true)) {
             if(isset($_POST['user']) && isset($_POST['password'])) {
                if($this->dologin($_POST['user'], $_POST['password']) == "disabled") {
                    $this->GUIshowlogin("<div class='alert alert-danger'><b>Fehler beim Login:</b> Dieses Konto wurde deaktiviert.</div>");
                } else if($this->dologin($_POST['user'], $_POST['password']) == "nein") {
                    $this->GUIshowlogin("<div class='alert alert-danger'><b>Fehler beim Login:</b> Leider ist diese Kombination nicht bekannt.</div>");
                    // Zeige fehler beim Login
                } else {
                    // Zeige Dashboard
                    $this->GUIshowDashboard();
                }
                // Logge in
            } else {
                $this->GUIshowlogin();
            }

            //Show login
        } else {
            // Login erfolgreich
            if(!isset($_GET['page'])) {
                $this->GUIshowDashboard();
            } else if($_GET['page'] == 'logout') {
                $this->dologout();
            } else if ($_GET['page'] == 'newarticle') {
                $this->GUInewarticle();
            } else if ($_GET['page'] == 'mydrafts') {
                $this->GUImyDrafts();
            } else if ($_GET['page'] == 'menu') {
                $this->GUImenu();
            } else if ($_GET['page'] == 'staff') {
                $this->GUIstaff();
            } else if ($_GET['page'] == 'disableuser') {
                $this->GUIdisableUser();
            } else if ($_GET['page'] == 'recoverpw') {
                $this->GUIrecoverpw();
            } else if ($_GET['page'] == 'register') {
                $this->GUIregister();
            } else if ($_GET['page'] == 'viewdraft') {
                $this->GUIviewDraft();       
            } else if ($_GET['page'] == 'deldraft') {
                $this->GUIdelDraft();
            } else if ($_GET['page'] == 'editdraft') {
                $this->GUIeditDraft();
            } else if ($_GET['page'] == 'settings') {
                $this->GUIuserSettings();
            } else {
                $this->GUIshowDashboard();
            }
        }
    }

    public function dologin($username, $password) {
        if($username == "") {
            return(false);
        } else if ($password == "") {
            return(false);
        }
        
        $password = sha1($password . $this->config['hashsecret']);
                
        $username = $this->DB->real_escape_string($username);
        $sql = "SELECT * FROM " . $this->config['databaseprefix'] . "accounts WHERE user = '" . $username . "'";

        $result = $this->DB->query($sql);
        if($result === 0) {
	        $num = 0;
        } else {
	        $num = mysqli_num_rows($result);
        }
        $userdb = "";
        $passworddb = "";
        if($num > 0) {
	        $k = 0;
	        while ($row = mysqli_fetch_assoc($result)) {
		        $iddb[$k] = $row["id"];
                $userdb[$k] = $row["user"];
                $passworddb[$k] = $row["password"];
                $namedb[$k] = $row["vorname"];
                $disableddb[$k] = $row["disabled"];
		        $k++;
	        }
            $userdb = $userdb[0];
            $passworddb = $passworddb[0];
            $disableddb = $disableddb[0];
        }
        $login = true;
        $disabled = "nein";
        if($username != $userdb) {
            $login = false;
        }
        if($password != $passworddb) {
            $login = false;
        }
        if(@$disableddb == "1") {
            $disabled = "ja";
            $login = false;
        } else {
            $disabled = "nein";
        }

        if($disabled == "ja") {
            return("disabled");
        }
        if($login == false) {
            return("nein");
        } else if ($login == true) {
            $sql = "UPDATE " . $this->config['databaseprefix'] . "accounts SET lastseen = '" . time() . "' WHERE id = " . $iddb[0] . "   ;";
            $this->DB->modify($sql);
            $_SESSION['loggedin'] = true;
            $_SESSION['vorname'] = $namedb[0];
            $_SESSION['id'] = $iddb[0];
            return ("ja");
        }
        // Logge in
    }

    public function dologout() {
        unset($_SESSION['loggedin']);
        unset($_SESSION['vorname']);
        unset($_SESSION['id']);
        header('Location: index.php');
    }

    public function showMyDrafts() {
        $id = $this->DB->real_escape_string($_SESSION['id']);
        $sql = "SELECT * FROM " . $this->config['databaseprefix'] . "drafts WHERE userid = '" . $id . "'  ORDER BY lastmod DESC;";
        $result = $this->DB->query($sql);
        if($result === 0) {
	        $num = 0;
        } else {
	        $num = mysqli_num_rows($result);
        }
        if($num > 0) {
	        $k = 0;
	        while ($row = mysqli_fetch_assoc($result)) {
		        $iddb[$k] = $row["id"];
                $titledb[$k] = $row["title"];
                $createdb[$k] = $row["created"];
                $lastmoddb[$k] = $row["lastmod"];
                $textdb[$k] = $row["text"];
		        $k++;
	        }

            $return = "";
            for($i=0; $i < $k; $i++) {
                $textteaser = substr($textdb[$i], 0, 50) . "…";
                $return .= "<tr><th>" . "$titledb[$i]</th><th>$createdb[$i]</th><th>" . date('d.m.Y',$lastmoddb[$i]) . "</th><th>$textteaser</th><th><a href='?page=viewdraft&id=" . $iddb[$i] . "'>Bearbeiten</a>" . "</tr>\n";
            }
            return($return);
            
        } else {
            return("Du hast keine Entwürfe angelegt.");
        }
    }

    public function showMenu() {
        $gespeicherttext = false;
        if(isset($_POST['save'])) {
            if($_POST['save'] == "1") {
                $this->writeLog("User " . $_SESSION['id'] . " changed menu", "changemenu");
                $gespeicherttext = true;
                for($i = 0; $i < 4; $i++) {
                    if($_POST["title" . $i] == "") {
                        $sql = "DELETE FROM " . $this->config['databaseprefix'] . "menu WHERE id = $i+1"; 
                        $this->DB->modify($sql);
                        // Leerer Titel
                    } else {
                        $sql = "DELETE FROM " . $this->config['databaseprefix'] . "menu WHERE id = $i+1"; 
                        $this->DB->modify($sql);
                        $temp = $_POST["title" . $i];
                        $templink = $_POST["link" . $i];
                        $tempnr = $i+1;
                        $tempnr = $this->DB->real_escape_string($tempnr);
                        $temp = $this->DB->real_escape_string($temp);
                        $templink = $this->DB->real_escape_string($templink);
                        $sql = "INSERT INTO " . $this->config['databaseprefix'] . "menu (id, title, link) VALUES ('$tempnr', '$temp', '$templink')"; 
                        $this->DB->modify($sql);
                        // Voller Titel
                    }
                }
            }
        }
        $sql = "SELECT * FROM " . $this->config['databaseprefix'] . "menu ORDER BY id ASC";
        $result = $this->DB->query($sql);
        if($result === 0) {
           $num = 0;
        } else {
	        $num = mysqli_num_rows($result);
        }
         $k = 0;
        if($num > 0) {
	        while ($row = mysqli_fetch_assoc($result)) {
		        $iddb[$k] = $row["id"];
                $titledb[$k] = $row["title"];
                $linkdb[$k] = $row["link"];
		        $k++;
	        }
        }
        $return = "";
        if($gespeicherttext) {
            $return .= "<div class='alert alert-success'>Gespeichert</div><br>";
        }
        $return .= "<form method='post'><ul><li>Titel:<input value='Startseite' disabled='disabled'> Link:<input value='/' disabled='disabled'></li>";
        for($i=0; $i < $k; $i++) {
            $return .= "<li>Titel:<input value='$titledb[$i]' name='title" . $i . "'> Link:<input value='$linkdb[$i]' name='link" . $i . "'></li>";
        }
        for($j=$i; $j < 4; $j++) {
            $return .= "<li>Titel:<input value='' name='title" . $j . "'> Link:<input value='' name='link" . $j . "'></li>";
        }
        $return .= "</ul><input type='hidden' name='save'  value='1'><input class='btn btn-primary' type='submit' value='Menü speichern'></form>";
        return($return);
    }

    public function getStaff() {
        $sql = "SELECT id, user, vorname, email, lastseen, disabled FROM " . $this->config['databaseprefix'] . "accounts";
        $result = $this->DB->query($sql);
        if($result === 0) {
           $num = 0;
        } else {
	        $num = mysqli_num_rows($result);
        }
        $k = 0;
        if($num > 0) {   
	        while ($row = mysqli_fetch_assoc($result)) {
		        $iddb[$k] = $row["id"];
                $titledb[$k] = $row["user"];
                $vornamedb[$k] = $row["vorname"];
                $emaildb[$k] = $row["email"];
                $lastseendb[$k] = $row["lastseen"];
                $disableddb[$k] = $row["disabled"];
		        $k++;
	        }
            $return = "";
            for($i = 0; $i < $k; $i++) {
                if($disableddb[$i] == "1") { 
                } else {
                    $return .= "<tr><th><b>$vornamedb[$i]</b></th><th>$emaildb[$i]</th><th>" . date("d. F Y, H:i", $lastseendb[$i]) . "</th><th><a href='?page=disableuser&uid=$iddb[$i]'>Deaktiveren</a>, <a href='?page=recoverpw&uid=$iddb[$i]'>PW vergessen</a ></th></td>";
                }
            }
            return($return);
        }
        return("Das Redaktionsmodul ist nicht aktiv");
    }

    public function disableUser($uid) {
        $uid = $this->DB->real_escape_string($uid);
        $sql = "UPDATE " . $this->config['databaseprefix'] . "accounts SET disabled = '1' WHERE id = " . $uid; 
        $this->DB->modify($sql);        
    }

    public function passwordRecovery($uid) {
        $uid = $this->DB->real_escape_string($uid);
        $sql = "SELECT id, email FROM " . $this->config['databaseprefix'] . "accounts WHERE id = " . $uid;
        $result = $this->DB->query($sql);
        if($result === 0) {
           $num = 0;
        } else {
	        $num = mysqli_num_rows($result);
        }
        $k = 0;
        if($num > 0) {   
	        while ($row = mysqli_fetch_assoc($result)) {
		        $iddb[$k] = $row["id"];
                $emaildb[$k] = $row["email"];
		        $k++;
	        }
        }
        mail($emaildb[0], "Passwort-Anfrage auf " . $this->config['host'], "Du hast ein neues Passwort auf " . $this->config['host'] . " angefragt. Klicke hier um ein neues Passwort zuzuweisen.");
    }

    public function createNewUser($name, $email) {
        $name = $this->DB->real_escape_string($name);
        $email = $this->DB->real_escape_string($email);
        // ToDo: Needs to be globalized and fixed
        $geheimerhash = $this->config['hashsecret'];
        $hash = sha1($name . microtime() . $geheimerhash);
        $username = "user" .  date('ydmhis');
        $sql = "INSERT INTO " . $this->config['databaseprefix'] . "accounts (id, user, password, vorname, email, lastseen, disabled) VALUES (NULL, '$username', '$hash', '$name', '$email', '0', '0');"; 
        $this->DB->modify($sql);
        $mailtext = "Hallo " . $name . "!\nEs wurde soeben ein Account auf " . $this->config['host'] . " für dich angelgt. Bitte verwende den folgenden Link um deinen Account einzurichten: https://" . $this->config['host'] . "/admin/register.php?n=" . substr($username, 4) . "&p=" . substr($hash, 0, 16) . " .\nWenn du nicht weißt, wovon diese Mail handelt, ignoriere sie einfach.";
        mail($email, "Dein Zugriff auf " . $this->config['host'], $mailtext);
    }

    public function checkIfMailExists($email) {
        $email = $this->DB->real_escape_string($email);
        $sql = "SELECT id, email, disabled FROM " . $this->config['databaseprefix'] . "accounts WHERE email = '" . $email . "';";
        $result = $this->DB->query($sql);
        if($result === 0) {
           $num = 0;
        } else {
	        $num = mysqli_num_rows($result);
        }
        if($num == 0) {
            return false;
        } else {
            return true;
        }
    }

    public function writeLog($message, $typeofaction) {
        $message = $this->DB->real_escape_string($message);
        $typeofaction = $this->DB->real_escape_string($typeofaction);
        $time = time();
        $sql = "INSERT INTO " . $this->config['databaseprefix'] . "log (id, timestamp, message, typeofaction) VALUES (NULL, '$time', '$message', '$typeofaction');"; 
        $this->DB->modify($sql);
    }

    public function GUIshowlogin($error = "") {
        $this->LAYOUTtop();
        echo '<div class="container">
        
              <form method="post" action="index.php" class="form-signin" style="max-width: 330px;
              padding: 15px;
              margin: 0 auto;">
                <h2 class="form-signin-heading">Anmelden</h2>
                <div>Bitte gib deine Zugangsdaten an:</div>
                <label for="user" class="sr-only">Benutzername</label>
                <input type="text" name="user" "id="user" class="form-control" placeholder="Benutzername" required autofocus>
                <label for="password" class="sr-only">Passwort</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Passwort" required>
                <div class="checkbox">';
                echo "<span style='color:red;'>$error</span>";
                echo '</div>
                <button class="btn btn-lg btn-primary btn-block" type="submit">Einloggen</button>
              ';
        echo "<br/><small><a onClick=" . '"' . "document.getElementById('pwvergessen').style.display = 'block'" . '"' . ">Passwort vergessen?</a></small><div id='pwvergessen' style='display:none' class='alert alert-warning'>Es besteht keine Möglichkeit dir selbst das Passwort zurückzusetzen. Bitte frage jemanden aus dem Redaktionsteam, um das Passwort zurückzusetzten.</div></form> </div>";
        $this->LAYOUTfooter();
    } 

    public function GUIeditDraft() {
        $this->LAYOUTtop();
        $id = $_GET['id'];
        $id = $this->DB->real_escape_string($id);
        $sql = "SELECT * FROM " . $this->config['databaseprefix'] . "drafts WHERE id = '" . $id . "';";
        $result = $this->DB->query($sql);
        if($result === 0) {
           $num = 0;
        } else {
	        $num = mysqli_num_rows($result);
        }
        $k = 0;
        if($num > 0) {   
	        while ($row = mysqli_fetch_assoc($result)) {
		        $iddb[$k] = $row["id"];
                $titledb[$k] = $row["title"];
                $createddb[$k] = $row["created"];
                $urldb[$k] = $row["url"];
                $aktuelldb[$k] = $row["aktuell"];
                $textdb[$k] = $row["text"];
		        $k++;
            }
            $iddb = $iddb[0];
            $titledb = $titledb[0];
            $createddb = $createddb[0];
            $urldb = $urldb[0];
            $aktuelldb = $aktuelldb[0];
            $textdb = $textdb[0];
        }
        $meldung = "";
        if(isset($_POST['title']) && isset($_POST['date']) && isset($_POST['aktuelles']) && isset($_POST['text']) && isset($_POST['id'])) {
            if(isset($_POST['change'])) {
                // Entwurf
                $userid = $_SESSION['id'];
                $title = $_POST['title'];
                $created = $_POST['date'];
                $id = $_POST['id'];
                $lastmod = time();
                if($_POST['aktuelles'] == "yes")  {
                    $aktuell = 1;
                } else {
                    $aktuell = 0;
                }
                $text = $_POST['text'];
                $url = strtolower($title);
                $vokale = array("a", "o", "u", "-", "ss");
                $umlaute = array("ä", "ö", "ü", " ", "ß");
                $url = str_replace($umlaute, $vokale, $url);
                $url = $this->DB->real_escape_string($url);
                $userid = $this->DB->real_escape_string($userid);
                $title = $this->DB->real_escape_string($title);
                $id = $this->DB->real_escape_string($id);
                $created = $this->DB->real_escape_string($created);
                $lastmod = $this->DB->real_escape_string($lastmod);
                $aktuell = $this->DB->real_escape_string($aktuell);
                $text = $this->DB->real_escape_string($text);
                $sql = "UPDATE " . $this->config['databaseprefix'] . "drafts SET title = '" . $title . "', lastmod = '" . $lastmod . "', created = '" . $created . "', aktuell = '" . $aktuell . "', text = '" . $text . "' WHERE id = '" . $id . "';"; 
                $this->DB->modify($sql);
                header("Location: index.php?page=viewdraft&id=" . $id . "&msg=save");
                // ToDo in Datenbank
                echo "<div class='alert alert-success'>Entwurf geichert !</div>";
                echo "Meine Entwürfe:
                <table class='table'>
                <thead>
                <tr>
                <th>Name</th><th>Erstellt</th><th>Letzte Änderung</th><th>Teaser</th><th>Aktion</th>
                </tr>
                </thead>
                <tbody>
                " . $this->showMyDrafts() . "</tbody></table>";
            } 
        } else {
        $vorname = $_SESSION['vorname'];
        echo "Willkommen, $vorname. <br />
        <a href='index.php?page=mydrafts' class='btn btn-secondary'>← Zurück zur Übersicht</a><br />
        ";
        $meldung = "";
        echo "Einen Artikel bearbeiten: <br />$meldung
        <form method='post'>
        <input type='hidden' name='id' value='" . $iddb . "'>

        <div class='form-group'>
          <label for='title'>Titel des Artikels</label>
            <input type='text' class='form-control' id='title' name='title' value='" . $titledb . "' placeholder='Gebe hier einen aussagekräftigen Titel an'>
        </div>
        <div class='form-group'>
            <label for='date'>Datum</label> 
            <input class='form-control' value='" . $createddb . "' name='date'>
        </div>
        <div class='form-check'>
            <label for='aktuelles'>Erscheint unter Aktuelles:</label><br />
            <input type='radio' name='aktuelles' value='yes' id='yes' checked='checked'><label for='yes'> Ja</label> <input type='radio' name='aktuelles' id='no' value='no'><label for='no'> Nein</label><br />
        </div>
        <div class='form-group'>
        <label for='ausgabe'>Artikel gehört zu Ausgabe</label>
        <select class='form-control' id='ausgabe'>
          <option>-Bitte auswählen / Keine bestimmte Ausgabe-</option>

          <option>2</option>
          <option>3</option>
          <option>4</option>
          <option>5</option>
        </select>
      </div>

        <div class='form-group'>
            <label for='text'>Artikeltext</label>
            <textarea class='form-control' id='text' name='text' rows='5' placeholder='Verfasse hier deinen Artikel'>" . $textdb . "</textarea>
            <small>Formartierungshilfe: &lt;b&gt;<b>Fetter Text</b>&lt;/b&gt;, &lt;i&gt;<i>Kusiver Text</i>&lt;/i&gt;, &lt;u&gt;<u>Untersrichener Text</u>&lt;/u&gt;, &lt;big&gt;<big>Großer Text</big>&lt;/big&gt;</small>
        </div>
        <input type='submit' class='btn btn-primary' name='change' value='Entwurf ändern'>
        
        </form> 
        
        ";
        }
        $this->LAYOUTfooter();
    }


    public function GUInewArticle() {
        $this->LAYOUTtop();
        echo "<a href='index.php' class='btn btn-secondary'>← Zurück zur Auswahl</a><br />
        ";
        $meldung = "";
        if(isset($_POST['title']) && isset($_POST['date']) && isset($_POST['aktuelles']) && isset($_POST['text'])) {
            if(isset($_POST['draft'])) {
                // Entwurf
                $userid = $_SESSION['id'];
                $title = $_POST['title'];
                $created = $_POST['date'];
                $lastmod = time();
                if($_POST['aktuelles'] == "yes")  {
                    $aktuell = 1;
                } else {
                    $aktuell = 0;
                }
                $text = $_POST['text'];
                $url = strtolower($title);
                $vokale = array("a", "o", "u", "-", "ss");
                $umlaute = array("ä", "ö", "ü", " ", "ß");
                $url = str_replace($umlaute, $vokale, $url);
                $url = $this->DB->real_escape_string($url);
                $userid = $this->DB->real_escape_string($userid);
                $title = $this->DB->real_escape_string($title);
                $created = $this->DB->real_escape_string($created);
                $lastmod = $this->DB->real_escape_string($lastmod);
                $aktuell = $this->DB->real_escape_string($aktuell);
                $text = $this->DB->real_escape_string($text);
                $sql = "INSERT INTO " . $this->config['databaseprefix'] . "drafts (id, userid, title, created, lastmod, url, aktuell, text) VALUES (NULL, '" . $userid . "', '" . $title . "', '" . $created . "', '" . $lastmod . "', '" . $url . "', '" . $aktuell . "', '" . $text . "');";
                $this->DB->modify($sql);
                // ToDo in Datenbank
                echo "<div class='alert alert-success'>Entwurf angelegt!</div>";
                echo "Meine Entwürfe:
                <table class='table'>
                <thead>
                <tr>
                <th>Name</th><th>Erstellt</th><th>Letzte Änderung</th><th>Teaser</th><th>Aktion</th>
                </tr>
                </thead>
                <tbody>
                " . $this->showMyDrafts() . "</tbody></table>";
            } 
        } else {
        echo "Einen neuen Artikel veröffentlichen: <br />$meldung
        <form method='post'>

        <div class='form-group'>
          <label for='title'>Titel des Artikels</label>
            <input type='text' class='form-control' id='title' name='title' placeholder='Gebe hier einen aussagekräftigen Titel an'>
        </div>
        <div class='form-group'>
            <label for='date'>Datum</label> 
            <input class='form-control' value='" . date('d.m.Y') . "' name='date'>
        </div>
        <div class='form-check'>
            <label for='aktuelles'>Erscheint unter Aktuelles:</label><br />
            <input type='radio' name='aktuelles' value='yes' id='yes' checked='checked'><label for='yes'> Ja</label> <input type='radio' name='aktuelles' id='no' value='no'><label for='no'> Nein</label><br />
        </div>
        <div class='form-group'>
        <label for='ausgabe'>Artikel gehört zu Ausgabe</label>
        <select class='form-control' id='ausgabe'>
          <option>-Bitte auswählen / Keine bestimmte Ausgabe-</option>

          <option>2</option>
          <option>3</option>
          <option>4</option>
          <option>5</option>
        </select>
      </div>

        <div class='form-group'>
            <label for='text'>Artikeltext</label>
            <textarea class='form-control' id='text' name='text' rows='5' placeholder='Verfasse hier deinen Artikel'></textarea>
            <small>Formartierungshilfe: &lt;b&gt;<b>Fetter Text</b>&lt;/b&gt;, &lt;i&gt;<i>Kusiver Text</i>&lt;/i&gt;, &lt;u&gt;<u>Untersrichener Text</u>&lt;/u&gt;, &lt;big&gt;<big>Großer Text</big>&lt;/big&gt;</small>
        </div>
        <input type='submit' class='btn btn-primary' name='draft' value='Entwurf anlegen'>
        
        </form> 
        
        ";
        }
        $this->LAYOUTfooter();
    }

    public function GUImyDrafts() {
        $this->LAYOUTtop();
        echo "<a href='index.php' class='btn btn-secondary'>← Zurück zur Auswahl</a><br />
        ";
        if(isset($_GET['msg'])) {
            if($_GET['msg'] == "del") {
                echo "<div class='alert alert-success'>Entwurf gelöscht!</div>";
            }
        }
        echo "Meine Entwürfe:
        <table class='table'>
        <thead>
        <tr>
        <th>Name</th><th>Erstellt</th><th>Letzte Änderung</th><th>Teaser</th><th>Aktion</th>
        </tr>
        </thead>
        <tbody>
        " . $this->showMyDrafts() . "</tbody></table>";
        $this->LAYOUTfooter();
    }

    public function GUIshowDashboard() {
        $this->LAYOUTtop();
        $vorname = $_SESSION['vorname'];
        echo "Willkommen, $vorname. <ul>
        <li><a href='?page=newarticle'>Neuen Artikel schreiben</a></li>
        <li><a href='?page=mydrafts'>Meine Entwürfe</a></li>
        <li><a href='?page=menu'>Menü bearbeiten</a></li>
        <li>Ausgaben bearbeiten</li>
        <li><a href='?page=staff'>Redaktion & Zugriff</a></li>
        <li><a href='?page=logout'>Logout</a></li>
        <ul>";
        $this->LAYOUTfooter();
    }

    public function GUImenu() {
        $this->LAYOUTtop();
        $vorname = $_SESSION['vorname'];
        echo "<a href='index.php' class='btn btn-secondary'>← Zurück zur Auswahl</a><br />" . $this->showMenu();
        $this->LAYOUTfooter();
    }

    public function GUIstaff() {
        $this->LAYOUTtop();
        $vorname = $_SESSION['vorname'];
        echo "<a href='index.php' class='btn btn-secondary'>← Zurück zur Auswahl</a><br />
        <a href='index.php?page=register' class='btn btn-primary'>Neues Mitglied anmelden.</a>
        <table class='table'>
            <thead>
            <tr>
            <th>Name</th><th>E-Mail</th><th>Letzter Login</th><th>Aktion</th>
            </tr>

            </thead>
            <tbody>" . $this->getStaff();
        echo "</tbody></table>";
        $this->LAYOUTfooter();
    }

    public function GUIregister() {
        $this->LAYOUTtop();
        $successmsg = false;
        $errormsg = false;
        if(isset($_POST['name']) AND isset($_POST['email'])) {
            // Register
            $exists = $this->checkIfMailExists($_POST['email']);
            if($exists == false) {
                $this->createNewUser($_POST['name'], $_POST['email']);
                $successmsg = true;
                $success = "Der Benutzer <b>" . $_POST['name'] . "</b> wurde gerade im System angelegt! Er/Sie erhält nun eine E-Mail mit allen weiteren Anweisungen um sich einzuloggen.";
            } else {
                $errormsg = true;
                $error = "Ein Benutzer mit der gleichen E-Mail Adresse existiert im System bereits";

            }
        }
        echo "<a href='index.php?page=staff' class='btn btn-secondary'>← Zurück zur Übersicht</a><br />
        Neues Mitglied.
        Bitte gebe hier die Daten für das Neumitglied an. <br />Weitere Informationen werden dann an die dort angegebene E-Mail Adresse gesendet. ";
        if($successmsg) {
            echo "<div class='alert alert-success'>" . $success . "</div>";
        } else if ($errormsg) {
            echo "<div class='alert alert-danger'>" . $error . "</div><form method='post'>
        Name: <input type='' name='name'>
        E-Mail Adresse (für Passwort benötigt):  <input type='email' name='email'>
        <input type='submit' class='btn btn-primary' value='Benutzer anlegen'>
        </form>";
        } else {
            echo "<form method='post'>
        Name: <input type='' name='name'>
        E-Mail Adresse (für Passwort benötigt):  <input type='email' name='email'>
        <input type='submit' class='btn btn-primary' value='Benutzer anlegen'>
        </form>";
        }
        $this->LAYOUTfooter();
    }

    public function GUIdisableUser() {
        $this->LAYOUTtop();
        echo "<a href='index.php?page=staff' class='btn btn-secondary'>← Zurück zur Übersicht</a><br />";
        if(!isset($_GET['uid'])) {
            echo "Es fehlt der Benutzer-Parameter";
        } else {
            if($_GET['uid'] != "") {
                $uid = $this->DB->real_escape_string($_GET['uid']);
                $sql = "SELECT id, vorname FROM " . $this->config['databaseprefix'] . "accounts WHERE id = " . $uid;
                $result = $this->DB->query($sql);
                if($result === 0) {
                $num = 0;
                } else {
                    $num = mysqli_num_rows($result);
                }
                $k = 0;
                if($num > 0) {   
                    while ($row = mysqli_fetch_assoc($result)) {
                        $iddb[$k] = $row["id"];
                        $vornamedb[$k] = $row["vorname"];
                        $k++;
                    }
                }
                if($iddb[0] == $_SESSION['id']) {
                    echo "<b>Du bist im Begriff dein eigenes Konto zu sperren.</b><br />";
                } else {
                    echo "Du sperrst " . @$vornamedb[0] . "!<br/>";
                }
                echo "Dies ist eine extrem harte Aktion die du durchführt. Der betreffene Benutzer kann dann NICHT mehr auf die Seite zugreifen.<br/>";
                 if(isset($_GET['confirm'])) {
                    if($_GET['confirm'] == 1) {
                        $this->disableUser($_GET['uid']);
                        echo "<b>Erfolgreich gesperrt</b>";
                    }
                } else {
                    echo "Möchtest du wirklich fortfahren? 
                   <a href='index.php'>Nein</a> <a href='index.php'>Nein</a> <a href='index.php'>Nein</a> <a href='index.php'>Nein</a> <a href='index.php?page=disableuser&uid=" . $_GET['uid'] . "&confirm=1'>Ja</a> <a href='index.php'>Nein</a> <a href='index.php'>Nein</a>";
                }
            }
        }
        $this->LAYOUTfooter();
    }

    public function GUIrecoverpw() {
        $this->LAYOUTtop();
        echo "<a href='index.php?page=staff' class='btn btn-secondary'>← Zurück zur Übersicht</a><br />";
        if(isset($_GET['confirm']) && $_GET['confirm'] == 1) {
            $this->passwordRecovery($_GET['uid']);
            echo "Mail versendet";
        } else {
            echo "Der betreffende Benutzer bekommt eine Mail mit einem Link, wo er sein Passwort zurücksetzten kann an die hier hinterlegte EMail Adresse gesendet. Fortfahren? <a href='index.php?page=recoverpw&uid=" . $_GET['uid'] . "&confirm=1'><input type='button' class='btn btn-primary' value='Weiter'></a>";
        }
        $this->LAYOUTfooter();
    }

    public function GUIviewDraft() {
        $this->LAYOUTtop();
        echo "<a href='index.php?page=mydrafts' class='btn btn-secondary'>← Zurück zur Übersicht</a>";
        if(isset($_GET['id'])) {
            $id = $this->DB->real_escape_string($_GET['id']);
            $sql = "SELECT * FROM " . $this->config['databaseprefix'] . "drafts WHERE id = " . $id . ";";
            $result = $this->DB->query($sql);
            if($result === 0) {
                $num = 0;
            } else {
                $num = mysqli_num_rows($result);
            }
            if($num > 0) {
                $k = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $iddb[$k] = $row["id"];
                    $useriddb[$k] = $row["userid"];
                    $titledb[$k] = $row["title"];
                    $createddb[$k] = $row["created"];
                    $lastmoddb[$k] = $row["lastmod"];
                    $urldb[$k] = $row["url"];
                    $aktuelldb[$k] = $row["aktuell"];
                    $textdb[$k] = $row["text"];
                    $k++;
                }
                $iddb = $iddb[0];
                $useriddb = $useriddb[0];
                $titledb = $titledb[0];
                $createddb = $createddb[0];
                $lastmoddb = $lastmoddb[0];
                $urldb = $urldb[0];
                $aktuelldb = $aktuelldb[0];
                $textdb = $textdb[0];
            }
            $sql = "SELECT * FROM " . $this->config['databaseprefix'] . "accounts WHERE id = " . $useriddb . ";";
            $result = $this->DB->query($sql);
            if($result === 0) {
                $num = 0;
            } else {
                $num = mysqli_num_rows($result);
            }
            if($num > 0) {
                $k = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $vornamedb[$k] = $row["vorname"];
                }
                $vornamedb = $vornamedb[0];
            }


            if($aktuelldb == 1) {
                $aktuelltext = "ja";
            } else {
                $aktuelltext = "nein";
            }
            $message = "";
            if(isset($_GET['msg'])) {
                if($_GET['msg'] == "save") {
                    $message = "<div class='alert alert-success'>Änderungen gespeichert</div>";
                }
            }
            echo "$message<h2>" . $titledb . "</h2><h4>Verfasst am $createddb von $vornamedb<br /><small>URL: /$urldb | Erscheint unter aktuelles: $aktuelltext | Letzte Änderung: " . date('d.m.Y' , $lastmoddb) . "</small></h4><small><a href='index.php?page=editdraft&id=" . $iddb . "' class='btn btn-primary'>Bearbeiten</a> <a href='' class='btn btn-primary'>Veröffentlichen</a> <a href='?page=deldraft&id=" . $iddb . "'  class='btn btn-primary'>Löschen</a></small><div>" . $textdb . "</div>";
        } else {
            echo "Bitte ID des Entwurfes angeben";
        }
        $this->LAYOUTfooter(); 
    }

    public function GUIdelDraft() {
        if(isset($_GET['id'])) {
            if(isset($_GET['confirm'])) {
                $id = $this->DB->real_escape_string($_GET['id']);
                $sql = "INSERT INTO " . $this->config['databaseprefix'] . "draftsold (userid, title, created, lastmod, url, aktuell, text) SELECT userid, title, created, lastmod, url, aktuell, text FROM " . $this->config['databaseprefix'] . "drafts WHERE id LIKE " . $id . ";";
                $this->DB->modify($sql);
                $sql = "DELETE FROM " . $this->config['databaseprefix'] . "drafts WHERE id LIKE " .  $id . ";";
                $this->DB->modify($sql);
                header('Location: index.php?page=mydrafts&msg=del');
            } else {
            $this->LAYOUTtop();
                echo "<div class='alert alert-danger'><b>Achtung!</b> Dies wird den Entwurf unwiederruflich Löschen! Möchtest du Fortfahren?<br />
                <a href='?page=deldraft&id=" . $_GET['id'] . "&confirm' class='btn btn-success'>Ja</a> <a href='?page=viewdraft&id=" . $_GET['id'] . "' class='btn btn-danger'>Nein</a></div>";
            }
        } else {
            echo "Bitte die ID des Entwurfes angeben";
        }

        $this->LAYOUTfooter();
    }

    public function GUIuserSettings() {
        $this->LAYOUTtop();
        echo "Anzeigename: E-Mail Benachrichtigungen: Für neuen Artikel";
        $this->LAYOUTfooter();
    }

    public function LAYOUTtop() {
        // ToDo: HTML-Top
        $return = "<!doctype>
<html>
    <head>
        <title>Verwaltung | " . $this->config['name'] . "</title>
        <link rel='stylesheet' href='bootstrap.min.css'>
    </head>
    <body>
    <div class='container'>
    <nav class='navbar navbar-light' style='background-color: rgb(211,28,26);'>
    <div class='navbar-brand' style='color:white'><big>" . $this->config['name'] . " - Verwaltung</big></div>";
    if(isset($_SESSION['vorname'])) {
        $vorname = $_SESSION['vorname'];
        $return .= "<span class='navbar-text' style='float:right; color:white;'>$vorname. <a href='?page=logout'>Abmelden</a></span>";
    }
    $return .= "</nav>
        <div>";
        echo($return);
    }

    public function LAYOUTfooter() {
        $return = "</div>\n</body>\n</html>";
        echo $return;
        // ToDo: HTML-Footer
    }
    
}

$tool = new verwaltung();
?>
