<?php

namespace Jlbousing\GoogleCalendarEvent;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;

class GoogleCalendarEvent
{
    protected Google_Client $client;
    protected Google_Service_Calendar $calendarService;

    public function __construct(array $config)
    {
        $this->client = new Google_Client();
        $this->client->setApplicationName($config['app_name']);
        $this->client->setAuthConfig($config['credentials_path']);
        $this->client->setScopes([Google_Service_Calendar::CALENDAR]);
        $this->client->setAccessType('offline');

        $this->calendarService = new Google_Service_Calendar($this->client);
    }

    public function createEvent(array $eventData, string $calendarId = 'primary'): Google_Service_Calendar_Event
    {
        $event = new Google_Service_Calendar_Event([
            'summary' => $eventData['title'] ?? 'Sin título',
            'description' => $eventData['description'] ?? '',
            'start' => [
                'dateTime' => $eventData['start'], // Ej: '2025-04-03T10:00:00-05:00'
                'timeZone' => $eventData['timezone'] ?? 'America/New_York',
            ],
            'end' => [
                'dateTime' => $eventData['end'],
                'timeZone' => $eventData['timezone'] ?? 'America/New_York',
            ],
        ]);

        return $this->calendarService->events->insert($calendarId, $event);
    }
}
