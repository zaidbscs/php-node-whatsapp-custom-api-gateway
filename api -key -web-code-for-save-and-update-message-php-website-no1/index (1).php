<?php
// ==========================================
// 1. DATABASE CONFIGURATION
// ==========================================
// Include the database connection
require_once 'db.php';

$success_msg = '';
$error_msg = '';

// ==========================================
// 2. HANDLE FORM SUBMISSION (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($phone) && !empty($message)) {
        $stmt = $conn->prepare("INSERT INTO whatsapp_queue (phone, message, status) VALUES (?, ?, 'pending')");
        $stmt->bind_param("ss", $phone, $message);

        if ($stmt->execute()) {
            $success_msg = 'Message saved to database successfully!';
        } else {
            $error_msg = 'Failed to save message.';
        }
        $stmt->close();
    } else {
        $error_msg = 'Phone and message are required.';
    }
}

// ==========================================
// 3. FETCH ALL MESSAGES FOR THE TABLE
// ==========================================
$result = $conn->query("SELECT id, phone, message, status, created_at FROM whatsapp_queue ORDER BY id DESC");
$messages = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Message Queue</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            width: 100%;
            max-width: 800px;
        }
        .card {
            background: #ffffff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        h2 {
            margin-top: 0;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }
        textarea {
            resize: vertical;
            height: 80px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #25D366;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #1ebe5d;
        }
        .alert-success {
            margin-top: 15px;
            text-align: center;
            color: green;
            font-weight: bold;
        }
        .alert-error {
            margin-top: 15px;
            text-align: center;
            color: red;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #f8f9fa;
            color: #333;
        }
        .badge-pending {
            background-color: #ffc107;
            color: #000;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-sent {
            background-color: #28a745;
            color: #fff;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Form Card -->
        <div class="card">
            <h2>Send WhatsApp Message</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="phone">Phone Number (with Country Code):</label>
                    <input type="text" id="phone" name="phone" placeholder="e.g., 923001234567" required>
                </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" placeholder="Type your message here..." required>Hello from other website</textarea>
                </div>
                <button type="submit">Save to Database</button>
            </form>

            <?php if (!empty($success_msg)): ?>
                <div class="alert-success">✅ <?php echo htmlspecialchars($success_msg); ?></div>
            <?php endif; ?>
            <?php if (!empty($error_msg)): ?>
                <div class="alert-error">❌ <?php echo htmlspecialchars($error_msg); ?></div>
            <?php endif; ?>
        </div>

        <!-- Messages Table Card -->
        <div class="card">
            <h2>Message Queue Status</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr>
                                <td><?php echo $msg['id']; ?></td>
                                <td><?php echo htmlspecialchars($msg['phone']); ?></td>
                                <td><?php echo htmlspecialchars($msg['message']); ?></td>
                                <td>
                                    <span class="<?php echo $msg['status'] === 'sent' ? 'badge-sent' : 'badge-pending'; ?>">
                                        <?php echo strtoupper($msg['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $msg['created_at']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align: center;">No messages found in queue.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>