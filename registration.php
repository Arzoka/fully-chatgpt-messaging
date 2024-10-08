<?php
session_start();
$env = parse_ini_file('.env');
$conn = new mysqli($env["HOST"], $env["USER"], $env["PASSWORD"], $env["DATABASE"]);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$username = $_POST['username'];
	$password = password_hash($_POST['password'], PASSWORD_BCRYPT);

	$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
	$stmt->bind_param("ss", $username, $password);

	if ($stmt->execute()) {
		echo "Registration successful!";
	} else {
		echo "Error: " . $stmt->error;
	}
}
?>

<head>
	<style>
		body {
			font-family: Arial, sans-serif;
		}

		form {
			margin: 20px 0;
		}

		input, textarea, select {
			display: block;
			margin: 10px 0;
			padding: 8px;
			width: 300px;
		}
	</style>
</head>

<form method="post">
	<input type="text" name="username" placeholder="Username" required>
	<input type="password" name="password" placeholder="Password" required>
	<button type="submit">Register</button>
</form>
