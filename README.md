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
    ->setSendNotifications(true);

$event = $googleCalendar->createEvent($eventDTO, $token);
echo "Event created with ID: " . $event->getId();
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
    'send_notifications' => true
]);
```

## Requirements

- PHP 7.2 or higher
- PHP cURL extension enabled
- Google account with Calendar API enabled

## License

MIT
