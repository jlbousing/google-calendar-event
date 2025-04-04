<?php

require_once 'vendor/autoload.php';

use Jlbousing\GoogleCalendarEvent\GoogleCalendarEvent;

// Enable all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configure credentials
$config = [
    'app_name' => 'My Application',
    'credentials_path' => __DIR__ . '/credentials.json',
];

echo "=== System Information ===\n";
echo "PHP version: " . phpversion() . "\n";
echo "cURL enabled: " . (function_exists('curl_version') ? 'Yes' : 'No') . "\n";
if (function_exists('curl_version')) {
    $curlInfo = curl_version();
    echo "cURL version: " . $curlInfo['version'] . "\n";
    echo "SSL version: " . $curlInfo['ssl_version'] . "\n";
}
echo "JSON enabled: " . (function_exists('json_encode') ? 'Yes' : 'No') . "\n";
echo "Timezone: " . date_default_timezone_get() . "\n\n";

echo "=== Checking Connectivity ===\n";
$host = 'oauth2.googleapis.com';
echo "Attempting to resolve host $host... ";
$ip = gethostbyname($host);
if ($ip != $host) {
    echo "OK ($ip)\n";
} else {
    echo "ERROR (Could not resolve DNS)\n";
}

echo "Checking connectivity with Google... ";
$connectionTest = @file_get_contents('https://www.google.com');
echo $connectionTest ? "OK\n" : "ERROR\n";

echo "\n=== Checking Credentials File ===\n";
$credentialsPath = __DIR__ . '/credentials.json';
echo "Credentials file: $credentialsPath\n";
echo "File exists: " . (file_exists($credentialsPath) ? 'Yes' : 'No') . "\n";

if (file_exists($credentialsPath)) {
    echo "File size: " . filesize($credentialsPath) . " bytes\n";
    $credentials = json_decode(file_get_contents($credentialsPath), true);
    echo "File is valid JSON: " . (json_last_error() === JSON_ERROR_NONE ? 'Yes' : 'No - ' . json_last_error_msg()) . "\n";

    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Contains client_id: " . (isset($credentials['client_id']) || isset($credentials['installed']['client_id']) ? 'Yes' : 'No') . "\n";
        echo "Contains client_secret: " . (isset($credentials['client_secret']) || isset($credentials['installed']['client_secret']) ? 'Yes' : 'No') . "\n";
    }
}

echo "\n=== Testing Event Creation with Fixed Data ===\n";
try {
    // Create an instance with detailed error handling
    $calendarEvent = new GoogleCalendarEvent($config);
    echo "✅ Connection established successfully\n\n";

    // Create an event with fixed data for testing
    $timezone = new DateTimeZone('America/New_York');
    $startDateTime = new DateTime('tomorrow 10:00', $timezone);
    $endDateTime = clone $startDateTime;
    $endDateTime->add(new DateInterval('PT60M'));

    $eventData = [
        'title' => 'Test Event',
        'description' => 'Test event description',
        'start' => $startDateTime->format('c'),
        'end' => $endDateTime->format('c'),
        'timezone' => $timezone->getName(),
    ];

    echo "Event data to create:\n";
    echo "Title: " . $eventData['title'] . "\n";
    echo "Description: " . $eventData['description'] . "\n";
    echo "Start: " . $eventData['start'] . "\n";
    echo "End: " . $eventData['end'] . "\n";
    echo "Timezone: " . $eventData['timezone'] . "\n\n";

    echo "Attempting to create the event...\n";
    $event = $calendarEvent->createEvent($eventData);

    echo "\n✅ Event created successfully\n";
    echo "ID: " . $event->getId() . "\n";
    echo "Title: " . $event->getSummary() . "\n";
    echo "Start: " . $event->getStart()->getDateTime() . "\n";
    echo "End: " . $event->getEnd()->getDateTime() . "\n";
} catch (Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Code: " . (method_exists($e, 'getCode') ? $e->getCode() : 'N/A') . "\n";
    echo "File: " . $e->getFile() . " (line " . $e->getLine() . ")\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";

    // Try to get more details about Google API errors if available
    if ($e instanceof Google\Service\Exception) {
        $error = json_decode($e->getMessage(), true);
        echo "\nAPI Error Details:\n";
        print_r($error);
    }
}
