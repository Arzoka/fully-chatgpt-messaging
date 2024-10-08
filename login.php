<?php
session_start();
$env = parse_ini_file('.env');
$conn = new mysqli($env["HOST"], $env["USER"], $env["PASSWORD"], $env["DATABASE"]);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$username = $_POST['username'];
	$password = $_POST['password'];

	$stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$stmt->store_result();

	if ($stmt->num_rows > 0) {
		$stmt->bind_result($id, $hashed_password);
		$stmt->fetch();

		if (password_verify($password, $hashed_password)) {
			$_SESSION['user_id'] = $id;
			header("Location: messages.php");
			exit;
		} else {
			echo "Invalid credentials.";
		}
	} else {
		echo "User not found.";
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
	<button type="submit">Login</button>
</form>
