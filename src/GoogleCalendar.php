<?php

namespace Jlbousing\GoogleCalendar;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Exception;
use Jlbousing\GoogleCalendar\DTOs\ConfigDTO;
use Jlbousing\GoogleCalendar\DTOs\EventDTO;

class GoogleCalendar
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
     * @var ConfigDTO
     */
    protected ConfigDTO $configDTO;

    public function __construct(array $config)
    {
        $this->configDTO = new ConfigDTO($config);
        $this->client = new Client();
        $this->client->setApplicationName($this->configDTO->getAppName());
        $this->client->setClientId($this->configDTO->getClientId());
        $this->client->setClientSecret($this->configDTO->getClientSecret());
        $this->client->setRedirectUri($this->configDTO->getRedirectUri());
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        $this->client->setScopes([
            Calendar::CALENDAR,
            Calendar::CALENDAR_READONLY,
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar.events.readonly'
        ]);
    }

    public function auth()
    {
        $authUrl = $this->client->createAuthUrl();
        return $authUrl;
    }

    public function refreshToken($token)
    {
        try {
            $this->client->setAccessToken($token);
            if ($token && $this->client->isAccessTokenExpired() && $this->client->getRefreshToken()) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                return $newToken;
            }
        } catch (\Exception $e) {
            throw new \Exception('Error refreshing access token: ' . $e->getMessage());
        }
    }

    public function getToken(string $code)
    {
        try {
            $this->client->fetchAccessTokenWithAuthCode($code);
            $token = $this->client->getAccessToken();
            return $token;
        } catch (\Exception $e) {
            throw new \Exception('Error fetching access token: ' . $e->getMessage());
        }
    }

    public function listCalendars($token)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $calendarService = new Calendar($this->client);
            $calendarList = $calendarService->calendarList->listCalendarList();
            return $calendarList->getItems();
        } catch (\Exception $e) {
            throw new \Exception('Error listing calendars: ' . $e->getMessage());
        }
    }

    public function createEvent(EventDTO $eventDTO, $token)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $calendarService = new Calendar($this->client);
            $event = new Event($eventDTO->toArray());
            $calendarService->events->insert($eventDTO->getCalendarId(), $event);
            return $event;
        } catch (\Exception $e) {
            throw new \Exception('Error creating event: ' . $e->getMessage());
        }
    }

    public function getEvent(EventDTO $eventDTO, $eventId, $token)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $calendarService = new Calendar($this->client);
            $event = $calendarService->events->get($eventDTO->getCalendarId(), $eventId);
            return $event;
        } catch (\Exception $e) {
            throw new \Exception('Error getting event: ' . $e->getMessage());
        }
    }

    public function updateEvent(EventDTO $eventDTO, $eventId, $token)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $calendarService = new Calendar($this->client);
            $event = $calendarService->events->update($eventDTO->getCalendarId(), $eventId, new Event($eventDTO->toArray()));
            return $event;
        } catch (\Exception $e) {
            throw new \Exception('Error updating event: ' . $e->getMessage());
        }
    }

    public function deleteEvent(EventDTO $eventDTO, $eventId, $token)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $calendarService = new Calendar($this->client);
            $calendarService->events->delete($eventDTO->getCalendarId(), $eventId);
            return true;
        } catch (\Exception $e) {
            throw new \Exception('Error deleting event: ' . $e->getMessage());
        }
    }

    public function listEvents(EventDTO $eventDTO, $token)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $calendarService = new Calendar($this->client);
            $events = $calendarService->events->listEvents($eventDTO->getCalendarId());
            return $events;
        } catch (\Exception $e) {
            throw new \Exception('Error listing events: ' . $e->getMessage());
        }
    }

    public function listEventsByDate(EventDTO $eventDTO, $token)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $calendarService = new Calendar($this->client);
            $events = $calendarService->events->listEvents($eventDTO->getCalendarId());
            return $events;
        } catch (\Exception $e) {
            throw new \Exception('Error listing events by date: ' . $e->getMessage());
        }
    }
}
