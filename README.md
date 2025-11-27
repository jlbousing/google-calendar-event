# Google Calendar Event

PHP package to create Google Calendar events from any PHP project.

## Installation

```bash
composer require jlbousing/google-calendar-event
```

## Example of use with a Laravel API

https://github.com/jlbousing/laravel-google-calendar-example

## Configuration

This package requires Google Calendar API credentials. Follow these steps to obtain them:

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google Calendar API
4. Create OAuth 2.0 credentials and obtain your Client ID, Client Secret, and configure your redirect URI
5. Save these credentials to use in your project

## Usage

### Initialization

```php
<?php

require_once 'vendor/autoload.php';

use Jlbousing\GoogleCalendar\GoogleCalendar;

// Configuration
$config = [
    'app_name' => 'Your Application Name',
    'client_id' => 'your-client-id-here',
    'client_secret' => 'your-client-secret-here',
    'redirect_uri' => 'https://your-redirect-uri.com',
];

// Initialize
$googleCalendar = new GoogleCalendar($config);
```

### Authentication

The package uses OAuth 2.0 for authentication. First, you need to generate an authentication URL and then obtain an access token:

```php
// Generate authentication URL
$authUrl = $googleCalendar->auth();
echo "Open this URL in your browser: " . $authUrl;

// After authorizing, you'll receive a code that you must use to obtain the token
$code = 'authorization-code-received';
$token = $googleCalendar->getToken($code);

// Save this token for future requests
```

### Refresh an expired token

```php
$newToken = $googleCalendar->refreshToken($token);
```

### List calendars

```php
$calendars = $googleCalendar->listCalendars($token);
foreach ($calendars as $calendar) {
    echo "Calendar: " . $calendar->getSummary() . " - ID: " . $calendar->getId() . "\n";
}
```

### Create an event

```php
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

// Using the EventDTO class
$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary')
    ->setTitle('Important Meeting')
    ->setDescription('Discuss the new project')
    ->setStart('2023-04-15T09:00:00-05:00')
    ->setEnd('2023-04-15T10:00:00-05:00')
    ->setTimezone('America/New_York')
    ->setLocation('Conference Room A')
    ->addAttendee('colleague@example.com')
    ->addAttendee('manager@example.com', true) // true = optional
    ->setSendNotifications(true)
    ->setCreateMeet(true); // Create a Google Meet session

$event = $googleCalendar->createEvent($eventDTO, $token);
echo "Event created with ID: " . $event->getId();
echo "Meet link: " . $event->getHangoutLink(); // Get the Google Meet link
```

### Create an event with Google Meet

To create an event with a Google Meet session, simply set the `createMeet` property to `true` in your EventDTO:

```php
$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary')
    ->setTitle('Virtual Meeting')
    ->setDescription('Online team meeting')
    ->setStart('2023-04-15T09:00:00-05:00')
    ->setEnd('2023-04-15T10:00:00-05:00')
    ->setTimezone('America/New_York')
    ->setCreateMeet(true); // This will create a Google Meet session

$event = $googleCalendar->createEvent($eventDTO, $token);

// Get the Meet link
$meetLink = $event->getHangoutLink();
echo "Google Meet link: " . $meetLink;
```

The Google Meet link will be automatically included in the event details and sent to all attendees if notifications are enabled.

### Create an event with Google Meet recording

To create an event with Google Meet recording that saves to Drive, use the `setRecordMeet()` and `setSaveToDrive()` methods:

```php
$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary')
    ->setTitle('Recorded Virtual Meeting')
    ->setDescription('This meeting will be recorded')
    ->setStart('2023-04-15T09:00:00-05:00')
    ->setEnd('2023-04-15T10:00:00-05:00')
    ->setTimezone('America/New_York')
    ->setCreateMeet(true)
    ->setRecordMeet(true)      // Enable recording
    ->setSaveToDrive(true);     // Save recording to Google Drive

$event = $googleCalendar->createEvent($eventDTO, $token);

// Get the Meet link
$meetLink = $event->getHangoutLink();
echo "Google Meet link: " . $meetLink;
```

**Note:** Recording functionality requires a Google Workspace account (Business Standard, Business Plus, Enterprise, or Education). The recording must be manually started during the meeting by an authorized user.

### Get Meet recordings from Drive

Retrieve all Meet recordings stored in Google Drive:

```php
// Get all recordings from the default "Meet Recordings" folder
$recordings = $googleCalendar->getMeetRecordings($token);

// Or specify a custom folder name and max results
$recordings = $googleCalendar->getMeetRecordings($token, 'My Recordings', 100);

foreach ($recordings as $recording) {
    echo "Recording: " . $recording['name'] . "\n";
    echo "Created: " . $recording['createdTime'] . "\n";
    echo "Size: " . ($recording['size'] ? number_format($recording['size'] / 1024 / 1024, 2) . " MB" : "N/A") . "\n";
    echo "View Link: " . $recording['webViewLink'] . "\n\n";
}
```

### Get recordings for a specific event

Retrieve recordings associated with a specific calendar event:

```php
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$eventId = 'EVENT_ID';
$recordings = $googleCalendar->getEventRecordings($eventDTO, $eventId, $token);

if (!empty($recordings)) {
    foreach ($recordings as $recording) {
        echo "Recording: " . $recording['name'] . "\n";
        echo "Link: " . $recording['webViewLink'] . "\n";
    }
} else {
    echo "No recordings found for this event.";
}
```

### Download a Meet recording

Download a recording file from Google Drive:

```php
$fileId = 'DRIVE_FILE_ID';

// Download and save to a file
$result = $googleCalendar->downloadRecording($fileId, $token, '/path/to/save/recording.mp4');
if ($result['success']) {
    echo "Recording saved to: " . $result['path'] . "\n";
    echo "File: " . $result['file']['name'] . "\n";
}

// Or get the content directly
$content = $googleCalendar->downloadRecording($fileId, $token);
// Process the content as needed
```

### Get event details

```php
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$eventId = 'EVENT_ID';
$event = $googleCalendar->getEvent($eventDTO, $eventId, $token);
echo "Event title: " . $event->getSummary();
```

### Update an event

```php
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary')
    ->setTitle('Updated Meeting')
    ->setDescription('New event description')
    ->setStart('2023-04-15T10:00:00-05:00')
    ->setEnd('2023-04-15T11:00:00-05:00')
    ->setLocation('New Location')
    ->setSendNotifications(true);

$eventId = 'EVENT_ID';
$updatedEvent = $googleCalendar->updateEvent($eventDTO, $eventId, $token);
echo "Event updated: " . $updatedEvent->getSummary();
```

### Delete an event

```php
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$eventId = 'EVENT_ID';
$result = $googleCalendar->deleteEvent($eventDTO, $eventId, $token);
if ($result) {
    echo "Event successfully deleted";
}
```

### List events

```php
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$events = $googleCalendar->listEvents($eventDTO, $token);
$items = $events->getItems();

foreach ($items as $event) {
    echo "Event: " . $event->getSummary() . " - Date: " . $event->getStart()->getDateTime() . "\n";
}
```

### List events by date

```php
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$events = $googleCalendar->listEventsByDate($eventDTO, $token);
$items = $events->getItems();

foreach ($items as $event) {
    echo "Event: " . $event->getSummary() . " - Date: " . $event->getStart()->getDateTime() . "\n";
}
```

## Data Transfer Objects (DTOs)

This package uses DTOs to handle data in a structured way:

### ConfigDTO

Handles the configuration for the Google Calendar client:

```php
use Jlbousing\GoogleCalendar\DTOs\ConfigDTO;

$configDTO = new ConfigDTO([
    'app_name' => 'Your Application Name',
    'client_id' => 'your-client-id',
    'client_secret' => 'your-client-secret',
    'redirect_uri' => 'https://your-redirect-uri.com',
]);
```

### EventDTO

Handles event data:

```php
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

// Create manually
$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary')
    ->setTitle('Important Meeting')
    ->setDescription('Discuss the new project')
    ->setStart('2023-04-15T09:00:00-05:00')
    ->setEnd('2023-04-15T10:00:00-05:00')
    ->setTimezone('America/New_York')
    ->setLocation('Conference Room A')
    ->addAttendee('colleague@example.com')
    ->addAttendee('manager@example.com', true)
    ->setSendNotifications(true);

// Or create from an array
$eventDTO = EventDTO::fromArray([
    'calendar_id' => 'primary',
    'title' => 'Important Meeting',
    'description' => 'Discuss the new project',
    'start' => '2023-04-15T09:00:00-05:00',
    'end' => '2023-04-15T10:00:00-05:00',
    'timezone' => 'America/New_York',
    'location' => 'Conference Room A',
    'attendees' => [
        ['email' => 'colleague@example.com'],
        ['email' => 'manager@example.com', 'optional' => true]
    ],
    'send_notifications' => true,
    'create_meet' => true,
    'record_meet' => true,
    'save_to_drive' => true
]);
```

## Google Meet Recording Features

### Requirements for Recording

- **Google Workspace Account**: Recording is only available for Google Workspace accounts (Business Standard, Business Plus, Enterprise, or Education editions)
- **Permissions**: The user must have recording permissions enabled in their Google Workspace account
- **Drive Access**: The package requires Drive API scopes to access recordings stored in Google Drive

### How Recording Works

1. When you create an event with `setRecordMeet(true)`, the event is configured to support recording
2. During the meeting, an authorized participant must manually start the recording from the Meet interface
3. Once the recording is stopped, it is automatically saved to Google Drive in the "Meet Recordings" folder
4. You can then retrieve the recordings using the methods provided in this package

### Drive API Scopes

The package automatically includes the following Drive API scopes:

- `https://www.googleapis.com/auth/drive.readonly` - Read access to Drive files
- `https://www.googleapis.com/auth/drive.file` - Access to files created by the app

Make sure to re-authenticate your application after adding these scopes to get the necessary permissions.

## Requirements

- PHP 7.4 or higher (PHP 8.0+ recommended for latest features)
- PHP cURL extension enabled
- Google account with Calendar API enabled
- Google Drive API enabled (for recording features)
- Google Workspace account (for recording functionality)

## License

MIT
