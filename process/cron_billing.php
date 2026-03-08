<?php
/**
 * Cron Script for Automated Billing Reminders
 * Path: /process/cron_billing.php
 * Run this via Cron Job at regular intervals (e.g., daily at 08:00)
 * Example: php /path/to/process/cron_billing.php
 */
require_once(__DIR__ . '/../database/database.php');
require_once(__DIR__ . '/../lib/fonnte.php');

// Output as text for logs
header('Content-Type: text/plain');

$db = Database::getConnection();
$today = (int)date('j'); // Day of the month (1-31)

echo "--- MIKHMON Billing Automation --- " . date('Y-m-d H:i:s') . "\n";
echo "Checking due date: Day $today\n";

// Find customers whose due_date is TODAY
$stmt = $db->prepare("SELECT * FROM billing WHERE due_date = ? AND (DATE(last_reminded_at) < CURDATE() OR last_reminded_at IS NULL)");
$stmt->execute([$today]);
$customers = $stmt->fetchAll();

if (empty($customers)) {
    echo "Result: No customers due for payment today.\n";
    exit;
}

$template = Database::getSetting('fonnte_billing_template', '');
if (empty($template)) {
    echo "Error: Billing template is empty. Please configure it in Mikhmon.\n";
    exit;
}

$fonnte = new Fonnte();
if (!$fonnte->isConfigured()) {
    echo "Error: Fonnte token is not configured.\n";
    exit;
}

$sent_count = 0;
$fail_count = 0;

foreach ($customers as $c) {
    echo "Processing: " . $c['username'] . " (" . $c['phone'] . ")... ";
    
    $message = str_replace(
        ['{username}', '{amount}', '{due_date}', '{description}'],
        [$c['username'], number_format($c['amount'], 0, ',', '.'), $c['due_date'], $c['description']],
        $template
    );
    
    $res = $fonnte->sendMessage($c['phone'], $message);
    
    if ($res['success']) {
        $db->prepare("UPDATE billing SET last_reminded_at = NOW() WHERE id = ?")->execute([$c['id']]);
        echo "SUCCESS.\n";
        $sent_count++;
    } else {
        echo "FAILED (" . $res['detail'] . ").\n";
        $fail_count++;
    }

    // Add 10 seconds delay between messages to prevent WhatsApp ban, except for the last message
    if ($sent_count + $fail_count < count($customers)) {
        echo "Waiting 10 seconds before next message...\n";
        sleep(10);
    }
}

echo "Summary: $sent_count sent, $fail_count failed.\n";
echo "--- JOB COMPLETED ---\n";
