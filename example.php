<?php

require_once 'vendor/autoload.php';

use Jlbousing\GoogleCalendarEvent\GoogleCalendar;
use Jlbousing\GoogleCalendarEvent\DTOs\ConfigDTO;
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;
use Jlbousing\GoogleCalendarEvent\DTOs\EventListDTO;

// Configure credentials
$config = [
    'app_name' => 'My Application',
    'client_id' => 'your-client-id-here',
    'client_secret' => 'your-client-secret-here',
    'redirect_uri' => 'https://your-redirect-uri.com',
];

// Initialize the class
try {
    $googleCalendar = new GoogleCalendar($config);
    echo "✅ Connection established successfully\n\n";
} catch (Exception $e) {
    die("❌ Connection error: " . $e->getMessage() . "\n");
}

// Function to display an interactive menu
function showMenu()
{
    echo "==== Google Calendar Event - Test Menu ====\n";
    echo "1. Generar URL de autenticación\n";
    echo "2. Obtener token de acceso con código de autorización\n";
    echo "3. Listar calendarios\n";
    echo "4. Crear un evento\n";
    echo "5. Obtener detalles de un evento\n";
    echo "6. Actualizar un evento\n";
    echo "7. Eliminar un evento\n";
    echo "8. Listar eventos\n";
    echo "0. Salir\n";
    echo "Select an option: ";
    return trim(fgets(STDIN));
}

// Function to request event data
function requestEventData()
{
    echo "ID del calendario (por defecto 'primary'): ";
    $calendarId = trim(fgets(STDIN));
    $calendarId = $calendarId ?: 'primary';

    echo "Título del evento: ";
    $title = trim(fgets(STDIN));

    echo "Descripción: ";
    $description = trim(fgets(STDIN));

    echo "Fecha de inicio (YYYY-MM-DD): ";
    $startDate = trim(fgets(STDIN));

    echo "Hora de inicio (HH:MM): ";
    $startTime = trim(fgets(STDIN));

    echo "Duración en minutos: ";
    $duration = trim(fgets(STDIN));

    echo "Ubicación (opcional, presiona Enter para omitir): ";
    $location = trim(fgets(STDIN));

    echo "¿Agregar asistentes? (s/n): ";
    $addAttendees = strtolower(trim(fgets(STDIN))) === 's';
    $attendees = [];

    if ($addAttendees) {
        $addingAttendees = true;
        while ($addingAttendees) {
            echo "Email del asistente: ";
            $email = trim(fgets(STDIN));

            echo "¿Asistente opcional? (s/n): ";
            $optional = strtolower(trim(fgets(STDIN))) === 's';

            $attendees[] = [
                'email' => $email,
                'optional' => $optional
            ];

            echo "¿Agregar otro asistente? (s/n): ";
            $addingAttendees = strtolower(trim(fgets(STDIN))) === 's';
        }
    }

    echo "¿Enviar notificaciones? (s/n): ";
    $sendNotifications = strtolower(trim(fgets(STDIN))) === 's';

    // Format dates for Google Calendar
    $timezone = new DateTimeZone('America/New_York'); // Change this to your timezone
    $startDateTime = new DateTime($startDate . ' ' . $startTime, $timezone);
    $endDateTime = clone $startDateTime;
    $endDateTime->add(new DateInterval('PT' . $duration . 'M'));

    // Create EventDTO
    $eventDTO = new EventDTO();
    $eventDTO->setCalendarId($calendarId);
    $eventDTO->setTitle($title);
    $eventDTO->setDescription($description);
    $eventDTO->setStart($startDateTime->format('c'));
    $eventDTO->setEnd($endDateTime->format('c'));
    $eventDTO->setTimezone($timezone->getName());
    $eventDTO->setSendNotifications($sendNotifications);

    if (!empty($location)) {
        $eventDTO->setLocation($location);
    }

    if (!empty($attendees)) {
        $eventDTO->setAttendees($attendees);
    }

    return $eventDTO;
}

// Almacenar token
$token = null;
$lastEventId = '';

// Main menu loop
while (true) {
    $option = showMenu();

    switch ($option) {
        case '1': // Generar URL de autenticación
            echo "\n=== Generar URL de autenticación ===\n";
            try {
                $authUrl = $googleCalendar->auth();
                echo "\n✅ URL de autenticación generada:\n$authUrl\n\n";
                echo "Abre esta URL en tu navegador y autoriza la aplicación.\n";
                echo "Luego copia el código de autorización para obtener un token.\n\n";
            } catch (Exception $e) {
                echo "\n❌ Error al generar URL de autenticación: " . $e->getMessage() . "\n\n";
            }
            break;

        case '2': // Obtener token con código de autorización
            echo "\n=== Obtener token de acceso ===\n";
            echo "Ingresa el código de autorización: ";
            $code = trim(fgets(STDIN));

            try {
                $token = $googleCalendar->getToken($code);
                echo "\n✅ Token obtenido correctamente\n\n";
            } catch (Exception $e) {
                echo "\n❌ Error al obtener token: " . $e->getMessage() . "\n\n";
            }
            break;

        case '3': // Listar calendarios
            echo "\n=== Listar Calendarios ===\n";
            if (!$token) {
                echo "❌ Debes obtener un token primero (opción 2)\n\n";
                break;
            }

            try {
                $calendars = $googleCalendar->listCalendars($token);
                echo "\n✅ Calendarios encontrados: " . count($calendars) . "\n\n";

                foreach ($calendars as $index => $calendar) {
                    echo ($index + 1) . ". " . $calendar->getSummary() . "\n";
                    echo "   ID: " . $calendar->getId() . "\n";
                    echo "   Zona horaria: " . $calendar->getTimeZone() . "\n\n";
                }
            } catch (Exception $e) {
                echo "\n❌ Error al listar calendarios: " . $e->getMessage() . "\n\n";
            }
            break;

        case '4': // Crear evento
            echo "\n=== Crear Nuevo Evento ===\n";
            if (!$token) {
                echo "❌ Debes obtener un token primero (opción 2)\n\n";
                break;
            }

            $eventDTO = requestEventData();

            try {
                $event = $googleCalendar->createEvent($eventDTO, $token);
                $lastEventId = $event->getId();
                echo "\n✅ Evento creado correctamente\n";
                echo "ID: " . $lastEventId . "\n";
                echo "Título: " . $event->getSummary() . "\n";
                echo "Inicio: " . $event->getStart()->getDateTime() . "\n";
                echo "Fin: " . $event->getEnd()->getDateTime() . "\n\n";
            } catch (Exception $e) {
                echo "\n❌ Error al crear evento: " . $e->getMessage() . "\n\n";
            }
            break;

        case '5': // Obtener evento
            echo "\n=== Obtener Detalles de Evento ===\n";
            if (!$token) {
                echo "❌ Debes obtener un token primero (opción 2)\n\n";
                break;
            }

            echo "ID del calendario (por defecto 'primary'): ";
            $calendarId = trim(fgets(STDIN));
            $calendarId = $calendarId ?: 'primary';

            echo "ID del evento" . ($lastEventId ? " (Presiona Enter para usar $lastEventId)" : "") . ": ";
            $eventId = trim(fgets(STDIN));

            if (empty($eventId) && !empty($lastEventId)) {
                $eventId = $lastEventId;
            }

            if (empty($eventId)) {
                echo "\n❌ ID de evento inválido\n\n";
                break;
            }

            try {
                $eventDTO = new EventDTO();
                $eventDTO->setCalendarId($calendarId);

                $event = $googleCalendar->getEvent($eventDTO, $eventId, $token);
                echo "\n✅ Evento encontrado\n";
                echo "ID: " . $event->getId() . "\n";
                echo "Título: " . $event->getSummary() . "\n";
                echo "Descripción: " . $event->getDescription() . "\n";
                echo "Inicio: " . $event->getStart()->getDateTime() . "\n";
                echo "Fin: " . $event->getEnd()->getDateTime() . "\n";
                if ($event->getLocation()) {
                    echo "Ubicación: " . $event->getLocation() . "\n";
                }

                $attendees = $event->getAttendees();
                if (!empty($attendees)) {
                    echo "Asistentes:\n";
                    foreach ($attendees as $attendee) {
                        echo "  - " . $attendee->getEmail();
                        if ($attendee->getOptional()) {
                            echo " (opcional)";
                        }
                        echo "\n";
                    }
                }
                echo "\n";
            } catch (Exception $e) {
                echo "\n❌ Error al obtener evento: " . $e->getMessage() . "\n\n";
            }
            break;

        case '6': // Actualizar evento
            echo "\n=== Actualizar Evento ===\n";
            if (!$token) {
                echo "❌ Debes obtener un token primero (opción 2)\n\n";
                break;
            }

            echo "ID del evento" . ($lastEventId ? " (Presiona Enter para usar $lastEventId)" : "") . ": ";
            $eventId = trim(fgets(STDIN));

            if (empty($eventId) && !empty($lastEventId)) {
                $eventId = $lastEventId;
            }

            if (empty($eventId)) {
                echo "\n❌ ID de evento inválido\n\n";
                break;
            }

            $eventDTO = requestEventData();

            try {
                $event = $googleCalendar->updateEvent($eventDTO, $eventId, $token);
                echo "\n✅ Evento actualizado correctamente\n";
                echo "ID: " . $event->getId() . "\n";
                echo "Título: " . $event->getSummary() . "\n";
                echo "Inicio: " . $event->getStart()->getDateTime() . "\n";
                echo "Fin: " . $event->getEnd()->getDateTime() . "\n\n";
            } catch (Exception $e) {
                echo "\n❌ Error al actualizar evento: " . $e->getMessage() . "\n\n";
            }
            break;

        case '7': // Eliminar evento
            echo "\n=== Eliminar Evento ===\n";
            if (!$token) {
                echo "❌ Debes obtener un token primero (opción 2)\n\n";
                break;
            }

            echo "ID del calendario (por defecto 'primary'): ";
            $calendarId = trim(fgets(STDIN));
            $calendarId = $calendarId ?: 'primary';

            echo "ID del evento" . ($lastEventId ? " (Presiona Enter para usar $lastEventId)" : "") . ": ";
            $eventId = trim(fgets(STDIN));

            if (empty($eventId) && !empty($lastEventId)) {
                $eventId = $lastEventId;
            }

            if (empty($eventId)) {
                echo "\n❌ ID de evento inválido\n\n";
                break;
            }

            try {
                $eventDTO = new EventDTO();
                $eventDTO->setCalendarId($calendarId);

                $result = $googleCalendar->deleteEvent($eventDTO, $eventId, $token);
                echo "\n✅ Evento eliminado correctamente\n\n";
                if ($eventId === $lastEventId) {
                    $lastEventId = '';
                }
            } catch (Exception $e) {
                echo "\n❌ Error al eliminar evento: " . $e->getMessage() . "\n\n";
            }
            break;

        case '8': // Listar eventos
            echo "\n=== Listar Eventos ===\n";
            if (!$token) {
                echo "❌ Debes obtener un token primero (opción 2)\n\n";
                break;
            }

            echo "ID del calendario (por defecto 'primary'): ";
            $calendarId = trim(fgets(STDIN));
            $calendarId = $calendarId ?: 'primary';

            echo "Número de eventos a mostrar (máximo): ";
            $max = trim(fgets(STDIN));
            $max = empty($max) ? 10 : (int)$max;

            echo "Rango de tiempo (días desde hoy): ";
            $days = trim(fgets(STDIN));
            $days = empty($days) ? 30 : (int)$days;

            try {
                $eventDTO = new EventDTO();
                $eventDTO->setCalendarId($calendarId);

                $events = $googleCalendar->listEvents($eventDTO, $token);
                $items = $events->getItems();

                if (empty($items)) {
                    echo "\nNo hay eventos próximos programados.\n\n";
                    break;
                }

                echo "\n✅ Se encontraron " . count($items) . " eventos:\n\n";

                foreach ($items as $index => $event) {
                    $startDateTime = $event->getStart()->getDateTime();
                    $formattedDate = $startDateTime ? (new DateTime($startDateTime))->format('d/m/Y H:i') : 'Todo el día';

                    echo ($index + 1) . ". " . $event->getSummary() . "\n";
                    echo "   ID: " . $event->getId() . "\n";
                    echo "   Fecha: " . $formattedDate . "\n";
                    if ($event->getDescription()) {
                        echo "   Descripción: " . $event->getDescription() . "\n";
                    }
                    if ($event->getLocation()) {
                        echo "   Ubicación: " . $event->getLocation() . "\n";
                    }
                    echo "\n";
                }
            } catch (Exception $e) {
                echo "\n❌ Error al listar eventos: " . $e->getMessage() . "\n\n";
            }
            break;

        case '0': // Salir
            echo "\n¡Hasta luego!\n";
            exit(0);

        default:
            echo "\n❌ Opción inválida. Inténtalo de nuevo.\n\n";
    }
}
