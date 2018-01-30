<?php
error_reporting(E_ALL);
require("../assets/DBCore.php");
$DB = new DBCore ('dbtest');
$config = require('config.php');
if(isset($_GET['n']) && isset($_GET['p'])) {
	echo "<!doctype>\n\t<html>\n\t\t<head>\n\t\t\t<title>Verwaltung | Kolbe-Post</title>";
	echo "\n\t\t\t<link rel='stylesheet' href='bootstrap.min.css'>\n\t\t</head>\n\t\t<body>";
	echo "\n\t\t\t<div class='container'>\n\t\t\t\t<nav class='navbar navbar-light' style='background-color: rgb(211,28,26);'>";
	echo "\n\t\t\t\t<div class='navbar-brand' style='color:white'><big>Kolbe-Post - Verwaltung</big></div>\n\t\t\t</nav>\n";
	$n = $DB->real_escape_string("user" . $_GET['n']);
	$p = $DB->real_escape_string($_GET['p']);
	$sql = "SELECT * FROM kolbepost_accounts WHERE user LIKE '" . $n . "'; ";
	$result = $DB->query($sql);
	if($result === 0) {
		$num = 0;
	} else {
		$num = mysqli_num_rows($result);
	}
	if($num > 0) {
		$k = 0;
		while ($row = mysqli_fetch_assoc($result)) {
			$iddb[$k] = $row["id"];
			$vorname[$k] = $row["vorname"];
			$passwort[$k] = $row["password"];
			$disableddb[$k] = $row["disabled"];
			$k++;
		}
	}

	if($num !== 0) {
		if(substr($passwort[0], 0, 16) == substr($p, 0, 16)) {
			if(!isset($_POST['username']) && !isset($_POST['password'])) {
				$userid = $iddb[0];
				$vorname = $vorname[0];
				$disableddb = $disableddb[0];
				echo "<h2>Hallo $vorname!</h2>Schön, dass du zum Projekt <i>" . $config['name'] . "</i> beitragen willst. Lege einen Benutzernamen und ein Passwort fest, mit dem du dich hier einloggen wirst.";
				echo "<br/>\nBenutzername: <form action='register.php?n=" . $_GET['n'] . "&p=" . $_GET['p'] . "' method='POST'><input type='text' name='username' /><br />\nPasswort:<br /><input type='password' name='password' placeholder='Password'></input>
				<br /><input type='submit' value='weiter' class='btn btn-primary'></form>\n";
			} else {
				$errormsg = "";
				if($_POST['username'] == "") {
					$errormsg .= " Der Benutzername ist zu kurz";
				}  
				$username = $DB->real_escape_string($_POST['username']);
				$sql = "SELECT * FROM kolbepost_accounts WHERE user LIKE '" . $username . "'; ";
				$result = $DB->query($sql);
				if($result === 0) {
					$numname = 0;
				} else {
					$numname = mysqli_num_rows($result);
				}
				if($numname !== 0) {
					$errormsg .= " Dieser Benutzername ist bereits vergeben!";
				}
				if(strlen($_POST['password']) < 4) {
					$errormsg .= " Das Passwort ist zu kurz!";
				}

				if($errormsg == "") {
					$postusername = $DB->real_escape_string($_POST['username']);
					$postpasswort = $DB->real_escape_string(sha1($_POST['password'] . $config['hashsecret']));
					$userid = $iddb[0];
					$sql = "UPDATE kolbepost_accounts SET user = '" . $postusername . "', password = '" . $postpasswort . "' WHERE id = " . $userid . "; ";
					$DB->modify($sql);
					$time = time();
					$sql = "INSERT INTO kolbepost_logins (id, userid, timestamp, ip, device) VALUES (NULL, '" . $userid . "', '" . $time . "', '127.0.0.1', 'Anmeldung');";
					$DB->modify($sql);
					echo "<div class='alert alert-success'><b>Registrierung erfolgreich!</b> Du kannst dich nun auf der Verwaltungsseite einloggen! <a href='index.php'>→ Zum Login</a></div>";
					// Kein Fehler, registriere
				} else {
					echo "<div class='alert alert-danger'><b>Fehler:</b> Es hat leider nicht geklappt:" . $errormsg . "</div>";
					echo "\nBenutzername: <form action='register.php?n=" . $_GET['n'] . "&p=" . $_GET['p'] . "' method='POST'><input type='text' name='username' /><br />\nPasswort:<br /><input type='password' name='password' placeholder='Password'></input>
					<br /><input type='submit' value='weiter' class='btn btn-primary'></form>\n";
					// Gebe Fehler aus
				}
			}

		} else {
			echo "<div class='alert alert-danger'><b>Fehler:</b> Ein Teil des Links ist leider ungültig. Überprüfe ob du den ganzen Link aus der Mail kopiert hast.</div>";
			// Hash falsch
		}

	} else {
		echo "<div class='alert alert-danger'><b>Fehler:</b> Der Link ist leider ungültig. </div>";
	}
} else {
	header("HTTP/1.0 404 Not Found");
	echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
	<html><head>
	<title>404 Not Found</title>
	</head><body>
	<h1>Not Found</h1>
	<p>The requested URL was not found on this server.</p>
	</body></html>';
	die();
}
