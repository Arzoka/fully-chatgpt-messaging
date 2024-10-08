<?php
session_start();
$env = parse_ini_file('.env');
$conn = new mysqli($env["HOST"], $env["USER"], $env["PASSWORD"], $env["DATABASE"]);

if (!isset($_SESSION['user_id'])) {
	header("Location: login.php");
	exit;
}

$user_id = $_SESSION['user_id'];

// Handle new message (direct or group)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$message = $_POST['message'];
	$receiver_id = $_POST['receiver_id'];
	$group_id = $_POST['group_id'] ?? null;
	$image = null;

	// File upload handling
	if (!empty($_FILES['image']['name'])) {
		$target_dir = "uploads/";
		$target_file = $target_dir . basename($_FILES["image"]["name"]);
		if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
			$image = $target_file;
		}
	}

	// If it's a group message
	if ($group_id) {
		$stmt = $conn->prepare("INSERT INTO group_messages (group_id, sender_id, message, image) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("iiss", $group_id, $user_id, $message, $image);
		$stmt->execute();
	} else {
		// Direct message
		$stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, image) VALUES (?, ?, ?, ?)");
		$stmt->bind_param("iiss", $user_id, $receiver_id, $message, $image);
		$stmt->execute();
	}
}

// Fetch direct messages
$messages_query = $conn->prepare("
    SELECT u.username, m.message, m.image, m.created_at 
    FROM messages m 
    JOIN users u ON m.sender_id = u.id 
    WHERE m.receiver_id = ? 
    ORDER BY m.created_at DESC");
$messages_query->bind_param("i", $user_id);
$messages_query->execute();
$messages_result = $messages_query->get_result();

// Fetch group messages
$group_messages_query = $conn->prepare("
    SELECT g.name as group_name, u.username, gm.message, gm.image, gm.created_at 
    FROM group_messages gm
    JOIN users u ON gm.sender_id = u.id
    JOIN groups g ON gm.group_id = g.id
    JOIN group_members gmemb ON gm.group_id = gmemb.group_id
    WHERE gmemb.user_id = ? 
    ORDER BY gm.created_at DESC");
$group_messages_query->bind_param("i", $user_id);
$group_messages_query->execute();
$group_messages_result = $group_messages_query->get_result();

// Fetch users and groups
$users_query = $conn->query("SELECT id, username FROM users WHERE id != $user_id");
$groups_query = $conn->query("
    SELECT g.id, g.name 
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = $user_id");

$user = $conn->query("SELECT id, username FROM users WHERE id = $user_id")->fetch_assoc();
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
		ul {
			list-style-type: none;
		}
		li {
			margin: 10px 0;
		}
		.message {
			margin-bottom: 20px;
		}
		.group-message {
			background-color: #f0f8ff;
		}
		.direct-message {
			background-color: #f8f8f8;
		}
	</style>
	<script src="buttons.js" defer></script>
</head>

<h2>Messages</h2>
<h3>Logged in as <?php echo $user['username']; ?></h3>

<form method="post" enctype="multipart/form-data">
	<!-- Receiver select -->
	<select name="receiver_id">
		<option value="">Select User</option>
		<?php while ($row = $users_query->fetch_assoc()): ?>
			<option value="<?php echo $row['id']; ?>"><?php echo $row['username']; ?></option>
		<?php endwhile; ?>
	</select>

	<!-- Group select -->
	<select name="group_id">
		<option value="">Select Group</option>
		<?php while ($row = $groups_query->fetch_assoc()): ?>
			<option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
		<?php endwhile; ?>
	</select>

	<textarea name="message" required></textarea>

	<!-- Image upload -->
	<input type="file" name="image">

	<button type="submit">Send</button>
</form>

<button id="hide-direct-messages">Hide</button>
<h3>Your Direct Messages:</h3>
<ul id="direct-messages">
	<?php while ($message = $messages_result->fetch_assoc()): ?>
		<li class="message direct-message">
			<strong><?php echo $message['username']; ?>:</strong> <?php echo $message['message']; ?>
			<?php if ($message['image']): ?>
				<br><img src="<?php echo $message['image']; ?>" alt="Message Image" style="width: 100px;">
			<?php endif; ?>
			<em><?php echo $message['created_at']; ?></em>
		</li>
	<?php endwhile; ?>
</ul>

<button id="hide-group-messages">Hide</button>
<h3>Your Group Messages:</h3>
<ul id="group-messages">
	<?php while ($group_message = $group_messages_result->fetch_assoc()): ?>
		<li class="message group-message">
			<strong><?php echo $group_message['group_name']; ?> (<?php echo $group_message['username']; ?>):</strong>
			<?php echo $group_message['message']; ?>
			<?php if ($group_message['image']): ?>
				<br><img src="<?php echo $group_message['image']; ?>" alt="Message Image" style="width: 100px;">
			<?php endif; ?>
			<em><?php echo $group_message['created_at']; ?></em>
		</li>
	<?php endwhile; ?>
</ul>
