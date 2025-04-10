# Google Calendar Event

PHP package to create Google Calendar events from any PHP project.

## Instalación

```bash
composer require jlbousing/google-calendar-event
```

## Configuración

Este paquete requiere credenciales de la API de Google Calendar. Sigue estos pasos para obtenerlas:

1. Ve a la [Consola de Google Cloud](https://console.cloud.google.com/)
2. Crea un nuevo proyecto o selecciona uno existente
3. Habilita la API de Google Calendar
4. Crea credenciales OAuth 2.0 y obtén tu Client ID, Client Secret y configura tu URI de redirección
5. Guarda estas credenciales para usarlas en tu proyecto

## Uso

### Inicialización

```php
<?php

require_once 'vendor/autoload.php';

use Jlbousing\GoogleCalendarEvent\GoogleCalendar;

// Configuración
$config = [
    'app_name' => 'Your Application Name',
    'client_id' => 'your-client-id-here',
    'client_secret' => 'your-client-secret-here',
    'redirect_uri' => 'https://your-redirect-uri.com',
];

// Inicializar
$googleCalendar = new GoogleCalendar($config);
```

### Autenticación

El paquete utiliza OAuth 2.0 para la autenticación. Primero necesitas generar una URL de autenticación y luego obtener un token de acceso:

```php
// Generar URL de autenticación
$authUrl = $googleCalendar->auth();
echo "Abre esta URL en tu navegador: " . $authUrl;

// Después de autorizar, obtendrás un código que debes usar para obtener el token
$code = 'código-de-autorización-recibido';
$token = $googleCalendar->getToken($code);

// Guarda este token para futuras solicitudes
```

### Refrescar un token expirado

```php
$newToken = $googleCalendar->refreshToken($token);
```

### Listar calendarios

```php
$calendars = $googleCalendar->listCalendars($token);
foreach ($calendars as $calendar) {
    echo "Calendario: " . $calendar->getSummary() . " - ID: " . $calendar->getId() . "\n";
}
```

### Crear un evento

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;

// Usando la clase EventDTO
$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary')
    ->setTitle('Reunión importante')
    ->setDescription('Discutir el nuevo proyecto')
    ->setStart('2023-04-15T09:00:00-05:00')
    ->setEnd('2023-04-15T10:00:00-05:00')
    ->setTimezone('America/New_York')
    ->setLocation('Sala de conferencias A')
    ->addAttendee('colega@example.com')
    ->addAttendee('gerente@example.com', true) // true = opcional
    ->setSendNotifications(true);

$event = $googleCalendar->createEvent($eventDTO, $token);
echo "Evento creado con ID: " . $event->getId();
```

### Obtener detalles de un evento

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$eventId = 'ID_DEL_EVENTO';
$event = $googleCalendar->getEvent($eventDTO, $eventId, $token);
echo "Título del evento: " . $event->getSummary();
```

### Actualizar un evento

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary')
    ->setTitle('Reunión actualizada')
    ->setDescription('Nueva descripción del evento')
    ->setStart('2023-04-15T10:00:00-05:00')
    ->setEnd('2023-04-15T11:00:00-05:00')
    ->setLocation('Nueva ubicación')
    ->setSendNotifications(true);

$eventId = 'ID_DEL_EVENTO';
$updatedEvent = $googleCalendar->updateEvent($eventDTO, $eventId, $token);
echo "Evento actualizado: " . $updatedEvent->getSummary();
```

### Eliminar un evento

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$eventId = 'ID_DEL_EVENTO';
$result = $googleCalendar->deleteEvent($eventDTO, $eventId, $token);
if ($result) {
    echo "Evento eliminado correctamente";
}
```

### Listar eventos

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$events = $googleCalendar->listEvents($eventDTO, $token);
$items = $events->getItems();

foreach ($items as $event) {
    echo "Evento: " . $event->getSummary() . " - Fecha: " . $event->getStart()->getDateTime() . "\n";
}
```

### Listar eventos por fecha

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;

$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary');

$events = $googleCalendar->listEventsByDate($eventDTO, $token);
$items = $events->getItems();

foreach ($items as $event) {
    echo "Evento: " . $event->getSummary() . " - Fecha: " . $event->getStart()->getDateTime() . "\n";
}
```

## Data Transfer Objects (DTOs)

Este paquete utiliza DTOs para manejar los datos de forma estructurada:

### ConfigDTO

Maneja la configuración para el cliente de Google Calendar:

```php
use Jlbousing\GoogleCalendarEvent\DTOs\ConfigDTO;

$configDTO = new ConfigDTO([
    'app_name' => 'Nombre de tu aplicación',
    'client_id' => 'tu-client-id',
    'client_secret' => 'tu-client-secret',
    'redirect_uri' => 'https://tu-uri-de-redireccion.com',
]);
```

### EventDTO

Maneja los datos de eventos:

```php
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;

// Crear manualmente
$eventDTO = new EventDTO();
$eventDTO->setCalendarId('primary')
    ->setTitle('Reunión importante')
    ->setDescription('Discutir el nuevo proyecto')
    ->setStart('2023-04-15T09:00:00-05:00')
    ->setEnd('2023-04-15T10:00:00-05:00')
    ->setTimezone('America/New_York')
    ->setLocation('Sala de conferencias A')
    ->addAttendee('colega@example.com')
    ->addAttendee('gerente@example.com', true)
    ->setSendNotifications(true);

// O crear desde un array
$eventDTO = EventDTO::fromArray([
    'calendar_id' => 'primary',
    'title' => 'Reunión importante',
    'description' => 'Discutir el nuevo proyecto',
    'start' => '2023-04-15T09:00:00-05:00',
    'end' => '2023-04-15T10:00:00-05:00',
    'timezone' => 'America/New_York',
    'location' => 'Sala de conferencias A',
    'attendees' => [
        ['email' => 'colega@example.com'],
        ['email' => 'gerente@example.com', 'optional' => true]
    ],
    'send_notifications' => true
]);
```

## Requisitos

- PHP 7.2 o superior
- Extensión cURL de PHP habilitada
- Cuenta de Google con la API de Calendar habilitada

## Licencia

MIT
