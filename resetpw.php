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
	$n = $DB->real_escape_string($_GET['n']);
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
			if(!isset($_POST['passwordconfirm']) && !isset($_POST['password'])) {
				$userid = $iddb[0];
				$vorname = $vorname[0];
				$disableddb = $disableddb[0];
				echo "<h2>Hallo $vorname!</h2>Hier kannst du dein Passwort für <i>" . $config['name'] . "</i> zurücksetzten. Lege dein neues Passwort fest.";
				echo "<br/>\nPasswort: <form action='resetpw.php?n=" . $_GET['n'] . "&p=" . $_GET['p'] . "' method='POST'><input type='password' name='password' /><br />\nPasswort wdh.:<br /><input type='password' name='passwordconfirm' placeholder='Password'></input>
				<br /><input type='submit' value='weiter' class='btn btn-primary'></form>\n";
			} else {
				$errormsg = "";
				if($_POST['passwordconfirm'] != $_POST['password']) {
					$errormsg .= " Die Passwörter stimmen nicht überein";
				}
				if(strlen($_POST['password']) < 4) {
					$errormsg .= " Das Passwort ist zu kurz!";
				}

				if($errormsg == "") {
					$postpasswort = $DB->real_escape_string(sha1($_POST['password'] . $config['hashsecret']));
					$userid = $iddb[0];
					$sql = "UPDATE kolbepost_accounts SET password = '" . $postpasswort . "' WHERE id = " . $userid . "; ";
					$DB->modify($sql);
					echo "<div class='alert alert-success'><b>Zurücksetzung erfolgreich!</b> Du kannst dich nun auf der Verwaltungsseite einloggen! <a href='index.php'>→ Zum Login</a></div>";
					// Kein Fehler, registriere
				} else {
					echo "<div class='alert alert-danger'><b>Fehler:</b> Es hat leider nicht geklappt:" . $errormsg . "</div>";
					echo "\nPasswort: <form action='resetpw.php?n=" . $_GET['n'] . "&p=" . $_GET['p'] . "' method='POST'><input type='password' name='password' /><br />\nPasswort wdh.:<br /><input type='password' name='passwordconfirm' placeholder='Password'></input>
					<br /><input type='submit' value='weiter' class='btn btn-primary'></form>\n";
					// Gebe fehler aus
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
