<?php

namespace Jlbousing\GoogleCalendar;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
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
        $this->client->setApplicationName($this->configDTO->getAppName() ?? 'app');
        $this->client->setClientId($this->configDTO->getClientId());
        $this->client->setClientSecret($this->configDTO->getClientSecret());
        $this->client->setRedirectUri($this->configDTO->getRedirectUri());
        $this->client->setAccessType('offline');
        $this->client->setPrompt('select_account consent');
        $this->client->setScopes([
            Calendar::CALENDAR,
            Calendar::CALENDAR_READONLY,
            'https://www.googleapis.com/auth/calendar.events',
            'https://www.googleapis.com/auth/calendar.events.readonly',
            'https://www.googleapis.com/auth/drive.readonly',
            'https://www.googleapis.com/auth/drive.file'
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

            // Configurar parámetros adicionales para la creación del evento
            $params = [];
            if ($eventDTO->getSendNotifications()) {
                $params['sendUpdates'] = 'all';
            }

            // Si se solicita crear una sesión de Meet, agregar el parámetro conferenceDataVersion
            if ($eventDTO->getCreateMeet()) {
                $params['conferenceDataVersion'] = 1;
            }

            $createdEvent = $calendarService->events->insert($eventDTO->getCalendarId(), $event, $params);
            return $createdEvent;
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

    /**
     * Obtener grabaciones de Google Meet desde Drive
     * 
     * @param string|null $folderName Nombre de la carpeta donde buscar (por defecto "Meet Recordings")
     * @param int $maxResults Número máximo de resultados
     * @param string $token Token de acceso
     * @return array Lista de archivos de grabación
     * @throws Exception
     */
    public function getMeetRecordings($token, $folderName = 'Meet Recordings', $maxResults = 50)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $driveService = new Drive($this->client);

            // Buscar la carpeta "Meet Recordings"
            $query = "mimeType='application/vnd.google-apps.folder' and name='{$folderName}' and trashed=false";
            $folders = $driveService->files->listFiles([
                'q' => $query,
                'pageSize' => 1
            ]);

            $recordings = [];

            if (count($folders->getFiles()) > 0) {
                $folder = $folders->getFiles()[0];
                $folderId = $folder->getId();

                // Buscar archivos de video en la carpeta
                $videoQuery = "'{$folderId}' in parents and (mimeType='video/mp4' or mimeType='video/webm' or mimeType='video/x-msvideo') and trashed=false";
                $files = $driveService->files->listFiles([
                    'q' => $videoQuery,
                    'pageSize' => $maxResults,
                    'orderBy' => 'createdTime desc',
                    'fields' => 'files(id, name, createdTime, modifiedTime, size, webViewLink, webContentLink, mimeType)'
                ]);

                foreach ($files->getFiles() as $file) {
                    $recordings[] = [
                        'id' => $file->getId(),
                        'name' => $file->getName(),
                        'createdTime' => $file->getCreatedTime(),
                        'modifiedTime' => $file->getModifiedTime(),
                        'size' => $file->getSize(),
                        'webViewLink' => $file->getWebViewLink(),
                        'webContentLink' => $file->getWebContentLink(),
                        'mimeType' => $file->getMimeType()
                    ];
                }
            }

            return $recordings;
        } catch (\Exception $e) {
            throw new \Exception('Error getting Meet recordings: ' . $e->getMessage());
        }
    }

    /**
     * Obtener grabaciones de Meet asociadas a un evento específico
     * 
     * @param EventDTO $eventDTO
     * @param string $eventId ID del evento
     * @param string $token Token de acceso
     * @return array Lista de grabaciones asociadas al evento
     * @throws Exception
     */
    public function getEventRecordings(EventDTO $eventDTO, $eventId, $token)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $calendarService = new Calendar($this->client);
            $event = $calendarService->events->get($eventDTO->getCalendarId(), $eventId);

            // Obtener el título del evento para buscar grabaciones relacionadas
            $eventTitle = $event->getSummary();
            $eventStart = $event->getStart()->getDateTime();

            // Buscar grabaciones en Drive que coincidan con el título y fecha del evento
            $driveService = new Drive($this->client);

            // Formatear la fecha para la búsqueda
            $searchDate = date('Y-m-d', strtotime($eventStart));

            $query = "name contains '{$eventTitle}' and (mimeType='video/mp4' or mimeType='video/webm' or mimeType='video/x-msvideo') and trashed=false";
            $files = $driveService->files->listFiles([
                'q' => $query,
                'pageSize' => 10,
                'orderBy' => 'createdTime desc',
                'fields' => 'files(id, name, createdTime, modifiedTime, size, webViewLink, webContentLink, mimeType)'
            ]);

            $recordings = [];
            foreach ($files->getFiles() as $file) {
                $fileDate = date('Y-m-d', strtotime($file->getCreatedTime()));
                // Filtrar por fecha aproximada (mismo día)
                if ($fileDate === $searchDate) {
                    $recordings[] = [
                        'id' => $file->getId(),
                        'name' => $file->getName(),
                        'createdTime' => $file->getCreatedTime(),
                        'modifiedTime' => $file->getModifiedTime(),
                        'size' => $file->getSize(),
                        'webViewLink' => $file->getWebViewLink(),
                        'webContentLink' => $file->getWebContentLink(),
                        'mimeType' => $file->getMimeType()
                    ];
                }
            }

            return $recordings;
        } catch (\Exception $e) {
            throw new \Exception('Error getting event recordings: ' . $e->getMessage());
        }
    }

    /**
     * Descargar una grabación de Meet desde Drive
     * 
     * @param string $fileId ID del archivo en Drive
     * @param string $token Token de acceso
     * @param string|null $savePath Ruta donde guardar el archivo (opcional)
     * @return string|array Contenido del archivo o información del archivo
     * @throws Exception
     */
    public function downloadRecording($fileId, $token, $savePath = null)
    {
        $token = $this->refreshToken($token);
        try {
            $this->client->setAccessToken($token);
            $driveService = new Drive($this->client);

            // Obtener información del archivo
            $file = $driveService->files->get($fileId, ['fields' => 'id, name, mimeType, size']);

            // Descargar el contenido del archivo usando el método correcto
            $http = $this->client->authorize();
            $request = $driveService->files->get($fileId, ['alt' => 'media']);
            $response = $http->request('GET', (string)$request);
            $content = (string)$response->getBody();

            if ($savePath) {
                // Guardar en el sistema de archivos
                file_put_contents($savePath, $content);
                return [
                    'success' => true,
                    'path' => $savePath,
                    'file' => [
                        'id' => $file->getId(),
                        'name' => $file->getName(),
                        'size' => $file->getSize(),
                        'mimeType' => $file->getMimeType()
                    ]
                ];
            }

            // Devolver el contenido
            return $content;
        } catch (\Exception $e) {
            throw new \Exception('Error downloading recording: ' . $e->getMessage());
        }
    }
}
