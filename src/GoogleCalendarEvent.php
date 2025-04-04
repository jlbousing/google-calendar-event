<?php

namespace Jlbousing\GoogleCalendarEvent;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Exception;
use Jlbousing\GoogleCalendarEvent\DTOs\ConfigDTO;
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;
use Jlbousing\GoogleCalendarEvent\DTOs\EventListDTO;

class GoogleCalendarEvent
{
    /**
     * @var Client
     */
    protected Client $client;

    /**
     * @var Calendar
     */
    protected Calendar $calendarService;

    /**
     * @param array $config
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $configDTO = ConfigDTO::fromArray($config);

        $this->client = new Client();
        $this->client->setApplicationName($configDTO->getAppName());
        $this->client->setAuthConfig($configDTO->getCredentialsPath());

        // If scopes are provided in config, use them, otherwise use default
        $scopes = $configDTO->getScopes();
        if (empty($scopes)) {
            $scopes = [Calendar::CALENDAR];
        }
        $this->client->setScopes($scopes);

        $this->client->setAccessType($configDTO->getAccessType());

        $this->calendarService = new Calendar($this->client);
    }

    /**
     * Create a new Google Calendar event
     * 
     * @param array $eventData
     * @param string $calendarId Default 'primary'
     * @return Event
     * @throws Exception
     */
    public function createEvent(array $eventData, string $calendarId = 'primary'): Event
    {
        $eventDTO = EventDTO::fromArray($eventData);

        if (empty($eventDTO->getStart()) || empty($eventDTO->getEnd())) {
            throw new Exception('Start and end dates are required');
        }

        $event = new Event($eventDTO->toArray());

        return $this->calendarService->events->insert($calendarId, $event, [
            'sendNotifications' => $eventDTO->getSendNotifications()
        ]);
    }

    /**
     * Update an existing Google Calendar event
     * 
     * @param string $eventId
     * @param array $eventData
     * @param string $calendarId Default 'primary'
     * @return Event
     * @throws Exception
     */
    public function updateEvent(string $eventId, array $eventData, string $calendarId = 'primary'): Event
    {
        // Get the existing event
        $event = $this->calendarService->events->get($calendarId, $eventId);
        $eventDTO = EventDTO::fromArray($eventData);

        // Update event fields
        if (!empty($eventDTO->getTitle())) {
            $event->setSummary($eventDTO->getTitle());
        }

        if (!empty($eventDTO->getDescription())) {
            $event->setDescription($eventDTO->getDescription());
        }

        if (!empty($eventDTO->getStart())) {
            $start = $event->getStart();
            $start->setDateTime($eventDTO->getStart());
            $start->setTimeZone($eventDTO->getTimezone());
            $event->setStart($start);
        }

        if (!empty($eventDTO->getEnd())) {
            $end = $event->getEnd();
            $end->setDateTime($eventDTO->getEnd());
            $end->setTimeZone($eventDTO->getTimezone());
            $event->setEnd($end);
        }

        if (!empty($eventDTO->getLocation())) {
            $event->setLocation($eventDTO->getLocation());
        }

        if (!empty($eventDTO->getAttendees())) {
            $event->setAttendees($eventDTO->getAttendees());
        }

        // Update the event in Google Calendar
        return $this->calendarService->events->update($calendarId, $eventId, $event, [
            'sendNotifications' => $eventDTO->getSendNotifications()
        ]);
    }

    /**
     * Delete a Google Calendar event
     * 
     * @param string $eventId
     * @param string $calendarId Default 'primary'
     * @return bool
     * @throws Exception
     */
    public function deleteEvent(string $eventId, string $calendarId = 'primary'): bool
    {
        $this->calendarService->events->delete($calendarId, $eventId);
        return true;
    }

    /**
     * Get a specific Google Calendar event
     * 
     * @param string $eventId
     * @param string $calendarId Default 'primary'
     * @return Event
     * @throws Exception
     */
    public function getEvent(string $eventId, string $calendarId = 'primary'): Event
    {
        return $this->calendarService->events->get($calendarId, $eventId);
    }

    /**
     * List Google Calendar events
     * 
     * @param string $calendarId Default 'primary'
     * @param int $maxResults Default 10
     * @param array $params Additional parameters
     * @return array
     * @throws Exception
     */
    public function listEvents(string $calendarId = 'primary', int $maxResults = 10, array $params = []): array
    {
        $params['max_results'] = $maxResults;
        $params['calendar_id'] = $calendarId;

        $listDTO = EventListDTO::fromArray($params);

        $results = $this->calendarService->events->listEvents(
            $listDTO->getCalendarId(),
            $listDTO->toArray()
        );

        return $results->getItems();
    }

    /**
     * Search for events matching a query
     * 
     * @param string $query
     * @param string $calendarId Default 'primary'
     * @param int $maxResults Default 10
     * @return array
     * @throws Exception
     */
    public function searchEvents(string $query, string $calendarId = 'primary', int $maxResults = 10): array
    {
        $listDTO = EventListDTO::fromArray([
            'max_results' => $maxResults,
            'calendar_id' => $calendarId,
            'q' => $query
        ]);

        $results = $this->calendarService->events->listEvents(
            $listDTO->getCalendarId(),
            $listDTO->toArray()
        );

        return $results->getItems();
    }

    /**
     * Get events between two dates
     * 
     * @param string $timeMin RFC3339 timestamp
     * @param string $timeMax RFC3339 timestamp
     * @param string $calendarId Default 'primary'
     * @param int $maxResults Default 10
     * @return array
     * @throws Exception
     */
    public function getEventsBetweenDates(string $timeMin, string $timeMax, string $calendarId = 'primary', int $maxResults = 10): array
    {
        $listDTO = EventListDTO::fromArray([
            'max_results' => $maxResults,
            'calendar_id' => $calendarId,
            'time_min' => $timeMin,
            'time_max' => $timeMax
        ]);

        $results = $this->calendarService->events->listEvents(
            $listDTO->getCalendarId(),
            $listDTO->toArray()
        );

        return $results->getItems();
    }
}
