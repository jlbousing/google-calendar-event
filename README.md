# Google Calendar Event

PHP package to create Google Calendar events from any PHP project.

## Installation

```bash
composer require jlbousing/google-calendar-event
```

## Configuration

This package requires Google Calendar API credentials. Follow these steps to obtain them:

1. Go to the [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the Google Calendar API
4. Create OAuth 2.0 credentials and download the JSON file
5. Save the credentials file in your project

## Usage

### Initialize

```php
<?php

require_once 'vendor/autoload.php';

use Jlbousing\GoogleCalendarEvent\GoogleCalendarEvent;

// Configuration
$config = [
    'app_name' => 'Your Application Name',
    'credentials_path' => 'path/to/credentials.json',
];

// Initialize
$calendarEvent = new GoogleCalendarEvent($config);
```

### Create an event

```php
$eventData = [
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
    'send_notifications' => true
];

$event = $calendarEvent->createEvent($eventData);
echo "Event created with ID: " . $event->getId();
```

### Get an event

```php
$eventId = 'EVENT_ID';
$event = $calendarEvent->getEvent($eventId);
echo "Event title: " . $event->getSummary();
```

### Update an event

```php
$eventId = 'EVENT_ID';
$eventData = [
    'title' => 'Updated Meeting',
    'description' => 'New event description',
    'start' => '2023-04-15T10:00:00-05:00',
    'end' => '2023-04-15T11:00:00-05:00',
    'location' => 'New Location',
    'send_notifications' => true
];

$updatedEvent = $calendarEvent->updateEvent($eventId, $eventData);
echo "Event updated: " . $updatedEvent->getSummary();
```

### Delete an event

```php
$eventId = 'EVENT_ID';
$result = $calendarEvent->deleteEvent($eventId);
if ($result) {
    echo "Event successfully deleted";
}
```

### List events

```php
// Basic usage
$events = $calendarEvent->listEvents('primary', 10);

// With additional parameters
$params = [
    'time_min' => date('c', strtotime('tomorrow')),
    'time_max' => date('c', strtotime('+1 week')),
    'order_by' => 'startTime',
    'single_events' => true
];
$events = $calendarEvent->listEvents('primary', 20, $params);

foreach ($events as $event) {
    echo "Event: " . $event->getSummary() . " - Date: " . $event->getStart()->getDateTime() . "\n";
}
```

### Search events

```php
$events = $calendarEvent->searchEvents('Meeting', 'primary', 10);
foreach ($events as $event) {
    echo "Event: " . $event->getSummary() . " - Date: " . $event->getStart()->getDateTime() . "\n";
}
```

### Get events between dates

```php
$timeMin = date('c', strtotime('tomorrow'));
$timeMax = date('c', strtotime('+1 week'));
$events = $calendarEvent->getEventsBetweenDates($timeMin, $timeMax, 'primary', 10);
foreach ($events as $event) {
    echo "Event: " . $event->getSummary() . " - Date: " . $event->getStart()->getDateTime() . "\n";
}
```

## Data Transfer Objects (DTOs)

This package uses DTOs to handle data in a structured way:

### ConfigDTO

Handles configuration settings for the Google Calendar client:

```php
use Jlbousing\GoogleCalendarEvent\DTOs\ConfigDTO;

$configDTO = ConfigDTO::fromArray([
    'app_name' => 'Your Application Name',
    'credentials_path' => 'path/to/credentials.json',
    'access_type' => 'offline',
    'scopes' => [Google\Service\Calendar::CALENDAR],
    'token_path' => 'path/to/token.json'
]);
```

### EventDTO

Handles event data:

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;

$eventDTO = EventDTO::fromArray([
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
    'send_notifications' => true
]);

// Or create manually
$eventDTO = new EventDTO();
$eventDTO->setTitle('Important Meeting')
    ->setDescription('Discuss the new project')
    ->setStart('2023-04-15T09:00:00-05:00')
    ->setEnd('2023-04-15T10:00:00-05:00')
    ->setTimezone('America/New_York')
    ->setLocation('Conference Room A')
    ->addAttendee('colleague@example.com')
    ->addAttendee('manager@example.com', true)
    ->setSendNotifications(true);
```

### EventListDTO

Handles parameters for listing events:

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventListDTO;

$listDTO = EventListDTO::fromArray([
    'max_results' => 20,
    'order_by' => 'startTime',
    'single_events' => true,
    'time_min' => date('c', strtotime('tomorrow')),
    'time_max' => date('c', strtotime('+1 week')),
    'q' => 'Meeting',
    'calendar_id' => 'primary'
]);
```

## Requirements

- PHP 7.2 or higher
- PHP cURL extension enabled
- Google account with Calendar API enabled

## License

MIT
