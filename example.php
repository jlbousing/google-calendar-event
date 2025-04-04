<?php

require_once 'vendor/autoload.php';

use Jlbousing\GoogleCalendarEvent\GoogleCalendarEvent;
use Jlbousing\GoogleCalendarEvent\DTOs\ConfigDTO;
use Jlbousing\GoogleCalendarEvent\DTOs\EventDTO;
use Jlbousing\GoogleCalendarEvent\DTOs\EventListDTO;

// Configure credentials
$config = [
    'app_name' => 'My Application',
    'credentials_path' => __DIR__ . '/credentials.json', // Path to your credentials file
];

// Initialize the class
try {
    $calendarEvent = new GoogleCalendarEvent($config);
    echo "✅ Connection established successfully\n\n";
} catch (Exception $e) {
    die("❌ Connection error: " . $e->getMessage() . "\n");
}

// Function to display an interactive menu
function showMenu()
{
    echo "==== Google Calendar Event - Test Menu ====\n";
    echo "1. Create an event\n";
    echo "2. Get event details\n";
    echo "3. Update an event\n";
    echo "4. Delete an event\n";
    echo "5. List upcoming events\n";
    echo "6. Search events\n";
    echo "7. Get events between dates\n";
    echo "0. Exit\n";
    echo "Select an option: ";
    return trim(fgets(STDIN));
}

// Function to request event data
function requestEventData()
{
    echo "Event title: ";
    $title = trim(fgets(STDIN));

    echo "Description: ";
    $description = trim(fgets(STDIN));

    echo "Start date (YYYY-MM-DD): ";
    $startDate = trim(fgets(STDIN));

    echo "Start time (HH:MM): ";
    $startTime = trim(fgets(STDIN));

    echo "Duration in minutes: ";
    $duration = trim(fgets(STDIN));

    echo "Location (optional, press Enter to skip): ";
    $location = trim(fgets(STDIN));

    echo "Add attendees? (y/n): ";
    $addAttendees = strtolower(trim(fgets(STDIN))) === 'y';
    $attendees = [];

    if ($addAttendees) {
        $addingAttendees = true;
        while ($addingAttendees) {
            echo "Attendee email: ";
            $email = trim(fgets(STDIN));

            echo "Optional attendee? (y/n): ";
            $optional = strtolower(trim(fgets(STDIN))) === 'y';

            $attendees[] = [
                'email' => $email,
                'optional' => $optional
            ];

            echo "Add another attendee? (y/n): ";
            $addingAttendees = strtolower(trim(fgets(STDIN))) === 'y';
        }
    }

    echo "Send notifications? (y/n): ";
    $sendNotifications = strtolower(trim(fgets(STDIN))) === 'y';

    // Format dates for Google Calendar
    $timezone = new DateTimeZone('America/New_York'); // Change this to your timezone
    $startDateTime = new DateTime($startDate . ' ' . $startTime, $timezone);
    $endDateTime = clone $startDateTime;
    $endDateTime->add(new DateInterval('PT' . $duration . 'M'));

    // Create EventDTO
    $eventData = [
        'title' => $title,
        'description' => $description,
        'start' => $startDateTime->format('c'),
        'end' => $endDateTime->format('c'),
        'timezone' => $timezone->getName(),
        'send_notifications' => $sendNotifications
    ];

    if (!empty($location)) {
        $eventData['location'] = $location;
    }

    if (!empty($attendees)) {
        $eventData['attendees'] = $attendees;
    }

    return $eventData;
}

// Variable to store the last created event ID
$lastEventId = '';
$calendarId = "your-email-hereg@email.com";

// Main menu loop
while (true) {
    $option = showMenu();

    switch ($option) {
        case '1': // Create event
            echo "\n=== Create New Event ===\n";
            $eventData = requestEventData();

            try {
                $event = $calendarEvent->createEvent($eventData, "jbousing@gmail.com");
                $lastEventId = $event->getId();
                echo "\n✅ Event created successfully\n";
                echo "ID: " . $lastEventId . "\n";
                echo "Title: " . $event->getSummary() . "\n";
                echo "Start: " . $event->getStart()->getDateTime() . "\n";
                echo "End: " . $event->getEnd()->getDateTime() . "\n\n";
            } catch (Exception $e) {
                echo "\n❌ Error creating event: " . $e->getMessage() . "\n\n";
            }
            break;

        case '2': // Get event
            echo "\n=== Get Event Details ===\n";
            echo "Event ID" . ($lastEventId ? " (Press Enter to use $lastEventId)" : "") . ": ";
            $eventId = trim(fgets(STDIN));

            if (empty($eventId) && !empty($lastEventId)) {
                $eventId = $lastEventId;
            }

            if (empty($eventId)) {
                echo "\n❌ Invalid event ID\n\n";
                break;
            }

            try {
                $event = $calendarEvent->getEvent($eventId);
                echo "\n✅ Event found\n";
                echo "ID: " . $event->getId() . "\n";
                echo "Title: " . $event->getSummary() . "\n";
                echo "Description: " . $event->getDescription() . "\n";
                echo "Start: " . $event->getStart()->getDateTime() . "\n";
                echo "End: " . $event->getEnd()->getDateTime() . "\n";
                if ($event->getLocation()) {
                    echo "Location: " . $event->getLocation() . "\n";
                }

                $attendees = $event->getAttendees();
                if (!empty($attendees)) {
                    echo "Attendees:\n";
                    foreach ($attendees as $attendee) {
                        echo "  - " . $attendee->getEmail();
                        if ($attendee->getOptional()) {
                            echo " (optional)";
                        }
                        echo "\n";
                    }
                }
                echo "\n";
            } catch (Exception $e) {
                echo "\n❌ Error getting event: " . $e->getMessage() . "\n\n";
            }
            break;

        case '3': // Update event
            echo "\n=== Update Event ===\n";
            echo "Event ID" . ($lastEventId ? " (Press Enter to use $lastEventId)" : "") . ": ";
            $eventId = trim(fgets(STDIN));

            if (empty($eventId) && !empty($lastEventId)) {
                $eventId = $lastEventId;
            }

            if (empty($eventId)) {
                echo "\n❌ Invalid event ID\n\n";
                break;
            }

            $eventData = requestEventData();

            try {
                $event = $calendarEvent->updateEvent($eventId, $eventData);
                echo "\n✅ Event updated successfully\n";
                echo "ID: " . $event->getId() . "\n";
                echo "Title: " . $event->getSummary() . "\n";
                echo "Start: " . $event->getStart()->getDateTime() . "\n";
                echo "End: " . $event->getEnd()->getDateTime() . "\n\n";
            } catch (Exception $e) {
                echo "\n❌ Error updating event: " . $e->getMessage() . "\n\n";
            }
            break;

        case '4': // Delete event
            echo "\n=== Delete Event ===\n";
            echo "Event ID" . ($lastEventId ? " (Press Enter to use $lastEventId)" : "") . ": ";
            $eventId = trim(fgets(STDIN));

            if (empty($eventId) && !empty($lastEventId)) {
                $eventId = $lastEventId;
            }

            if (empty($eventId)) {
                echo "\n❌ Invalid event ID\n\n";
                break;
            }

            try {
                $result = $calendarEvent->deleteEvent($eventId);
                echo "\n✅ Event deleted successfully\n\n";
                if ($eventId === $lastEventId) {
                    $lastEventId = '';
                }
            } catch (Exception $e) {
                echo "\n❌ Error deleting event: " . $e->getMessage() . "\n\n";
            }
            break;

        case '5': // List events
            echo "\n=== List Upcoming Events ===\n";
            echo "Number of events to display (maximum): ";
            $max = trim(fgets(STDIN));
            $max = empty($max) ? 10 : (int)$max;

            echo "Time range (days from now): ";
            $days = trim(fgets(STDIN));
            $days = empty($days) ? 30 : (int)$days;

            try {
                $params = [
                    'time_min' => date('c'),
                    'time_max' => date('c', strtotime("+{$days} days")),
                    'order_by' => 'startTime',
                    'single_events' => true
                ];

                $events = $calendarEvent->listEvents('primary', $max, $params);

                if (empty($events)) {
                    echo "\nNo upcoming events scheduled.\n\n";
                    break;
                }

                echo "\n✅ Found " . count($events) . " events:\n\n";

                foreach ($events as $index => $event) {
                    $startDateTime = $event->getStart()->getDateTime();
                    $formattedDate = $startDateTime ? (new DateTime($startDateTime))->format('m/d/Y H:i') : 'All day';

                    echo ($index + 1) . ". " . $event->getSummary() . "\n";
                    echo "   ID: " . $event->getId() . "\n";
                    echo "   Date: " . $formattedDate . "\n";
                    if ($event->getDescription()) {
                        echo "   Description: " . $event->getDescription() . "\n";
                    }
                    if ($event->getLocation()) {
                        echo "   Location: " . $event->getLocation() . "\n";
                    }
                    echo "\n";
                }
            } catch (Exception $e) {
                echo "\n❌ Error listing events: " . $e->getMessage() . "\n\n";
            }
            break;

        case '6': // Search events
            echo "\n=== Search Events ===\n";
            echo "Search query: ";
            $query = trim(fgets(STDIN));

            if (empty($query)) {
                echo "\n❌ Search query cannot be empty\n\n";
                break;
            }

            echo "Number of results to display (maximum): ";
            $max = trim(fgets(STDIN));
            $max = empty($max) ? 10 : (int)$max;

            try {
                $events = $calendarEvent->searchEvents($query, 'primary', $max);

                if (empty($events)) {
                    echo "\nNo events found matching '$query'.\n\n";
                    break;
                }

                echo "\n✅ Found " . count($events) . " events matching '$query':\n\n";

                foreach ($events as $index => $event) {
                    $startDateTime = $event->getStart()->getDateTime();
                    $formattedDate = $startDateTime ? (new DateTime($startDateTime))->format('m/d/Y H:i') : 'All day';

                    echo ($index + 1) . ". " . $event->getSummary() . "\n";
                    echo "   ID: " . $event->getId() . "\n";
                    echo "   Date: " . $formattedDate . "\n";
                    if ($event->getDescription()) {
                        echo "   Description: " . $event->getDescription() . "\n";
                    }
                    echo "\n";
                }
            } catch (Exception $e) {
                echo "\n❌ Error searching events: " . $e->getMessage() . "\n\n";
            }
            break;

        case '7': // Get events between dates
            echo "\n=== Get Events Between Dates ===\n";
            echo "Start date (YYYY-MM-DD): ";
            $startDate = trim(fgets(STDIN));

            echo "End date (YYYY-MM-DD): ";
            $endDate = trim(fgets(STDIN));

            if (empty($startDate) || empty($endDate)) {
                echo "\n❌ Start and end dates are required\n\n";
                break;
            }

            echo "Number of results to display (maximum): ";
            $max = trim(fgets(STDIN));
            $max = empty($max) ? 10 : (int)$max;

            try {
                $timeMin = (new DateTime($startDate . ' 00:00:00'))->format('c');
                $timeMax = (new DateTime($endDate . ' 23:59:59'))->format('c');

                $events = $calendarEvent->getEventsBetweenDates($timeMin, $timeMax, 'primary', $max);

                if (empty($events)) {
                    echo "\nNo events found between $startDate and $endDate.\n\n";
                    break;
                }

                echo "\n✅ Found " . count($events) . " events between $startDate and $endDate:\n\n";

                foreach ($events as $index => $event) {
                    $startDateTime = $event->getStart()->getDateTime();
                    $formattedDate = $startDateTime ? (new DateTime($startDateTime))->format('m/d/Y H:i') : 'All day';

                    echo ($index + 1) . ". " . $event->getSummary() . "\n";
                    echo "   ID: " . $event->getId() . "\n";
                    echo "   Date: " . $formattedDate . "\n";
                    if ($event->getDescription()) {
                        echo "   Description: " . $event->getDescription() . "\n";
                    }
                    echo "\n";
                }
            } catch (Exception $e) {
                echo "\n❌ Error getting events between dates: " . $e->getMessage() . "\n\n";
            }
            break;

        case '0': // Exit
            echo "\nGoodbye!\n";
            exit(0);

        default:
            echo "\n❌ Invalid option. Please try again.\n\n";
    }
}
