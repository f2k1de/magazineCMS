<!doctype>
<html>
	<head>
		<title>Verwaltung | {NAME}</title>
		<link rel='stylesheet' href='bootstrap.min.css'>
		<style>
		/* Sticky footer styles
		-------------------------------------------------- */
		html {
			position: relative;
			min-height: 100%;
		}
		body {
			/* Margin bottom by footer height */
			margin-bottom: 60px;
		}
		.footer {
			position: absolute;
			bottom: 0;
			width: 100%;
			/* Set the fixed height of the footer here */
			height: 60px;
			background-color: #f5f5f5;
		}
		.container .text-muted {
			margin: 20px 0;
		}
		</style>

		<link rel='stylesheet' href='/scripts/lightbox/css/lightbox.css'>
	</head>
	<body>
	<nav class='navbar navbar-light navbar-static-top' style='background-color: {ACCENTCOLOR};'>
	<div class='container'>
	<div class='navbar-brand' style='color:white;'><big>{NAME} - Verwaltung</big></div>";
	<!-- IF VORNAME -->
		<span class='navbar-text navbar-right' style='color:white;'>{VORNAME}. <small><a href='?page=logout' class='navbar-link' style='color:white;'>Abmelden</a></small></span>";
	}
	<!-- ENDIF -->
	</nav>
		<div><div class='container'>