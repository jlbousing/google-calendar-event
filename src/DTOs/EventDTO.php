<?php

namespace Jlbousing\GoogleCalendar\DTOs;

class EventDTO
{
    /**
     * @var string
     */
    private $calendarId;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $start;

    /**
     * @var string
     */
    private $end;

    /**
     * @var string
     */
    private $timezone;

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var array
     */
    private $attendees = [];

    /**
     * @var bool
     */
    private $sendNotifications = false;

    /**
     * @var bool
     */
    private $createMeet = false;

    /**
     * @var bool
     */
    private $recordMeet = false;

    /**
     * @var bool
     */
    private $saveToDrive = false;

    /**
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $dto = new self();

        $dto->setTitle($data['title'] ?? 'Untitled');
        $dto->setDescription($data['description'] ?? '');
        $dto->setStart($data['start'] ?? null);
        $dto->setEnd($data['end'] ?? null);
        $dto->setTimezone($data['timezone'] ?? 'America/New_York');

        if (isset($data['location'])) {
            $dto->setLocation($data['location']);
        }

        if (isset($data['attendees'])) {
            $dto->setAttendees($data['attendees']);
        }

        if (isset($data['send_notifications'])) {
            $dto->setSendNotifications($data['send_notifications']);
        }

        if (isset($data['calendar_id'])) {
            $dto->setCalendarId($data['calendar_id']);
        }

        if (isset($data['create_meet'])) {
            $dto->setCreateMeet($data['create_meet']);
        }

        if (isset($data['record_meet'])) {
            $dto->setRecordMeet($data['record_meet']);
        }

        if (isset($data['save_to_drive'])) {
            $dto->setSaveToDrive($data['save_to_drive']);
        }

        return $dto;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = [
            'summary' => $this->title,
            'description' => $this->description,
            'start' => [
                'dateTime' => $this->start,
                'timeZone' => $this->timezone,
            ],
            'end' => [
                'dateTime' => $this->end,
                'timeZone' => $this->timezone,
            ],
        ];

        if ($this->location) {
            $data['location'] = $this->location;
        }

        if (!empty($this->attendees)) {
            $data['attendees'] = $this->attendees;
        }

        if ($this->createMeet) {
            $conferenceData = [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet'
                    ]
                ]
            ];

            // Configurar grabación si está habilitada
            if ($this->recordMeet) {
                $conferenceData['createRequest']['requestId'] = uniqid('recording-');
                // Nota: La grabación se configura automáticamente cuando se crea el evento
                // y el usuario tiene permisos de grabación en su cuenta de Google Workspace
            }

            $data['conferenceData'] = $conferenceData;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getCalendarId(): string
    {
        return $this->calendarId;
    }

    /**
     * @param string $calendarId
     * @return self
     */
    public function setCalendarId(string $calendarId): self
    {
        $this->calendarId = $calendarId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getStart(): string
    {
        return $this->start;
    }

    /**
     * @param string $start
     * @return self
     */
    public function setStart(string $start): self
    {
        $this->start = $start;
        return $this;
    }

    /**
     * @return string
     */
    public function getEnd(): string
    {
        return $this->end;
    }

    /**
     * @param string $end
     * @return self
     */
    public function setEnd(string $end): self
    {
        $this->end = $end;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     * @return self
     */
    public function setTimezone(string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLocation(): ?string
    {
        return $this->location;
    }

    /**
     * @param string $location
     * @return self
     */
    public function setLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttendees(): array
    {
        return $this->attendees;
    }

    /**
     * @param array $attendees
     * @return self
     */
    public function setAttendees(array $attendees): self
    {
        $this->attendees = $attendees;
        return $this;
    }

    /**
     * @param string $email
     * @param bool $optional
     * @return self
     */
    public function addAttendee(string $email, bool $optional = false): self
    {
        $this->attendees[] = [
            'email' => $email,
            'optional' => $optional
        ];
        return $this;
    }

    /**
     * @return bool
     */
    public function getSendNotifications(): bool
    {
        return $this->sendNotifications;
    }

    /**
     * @param bool $sendNotifications
     * @return self
     */
    public function setSendNotifications(bool $sendNotifications): self
    {
        $this->sendNotifications = $sendNotifications;
        return $this;
    }

    /**
     * @return bool
     */
    public function getCreateMeet(): bool
    {
        return $this->createMeet;
    }

    /**
     * @param bool $createMeet
     * @return self
     */
    public function setCreateMeet(bool $createMeet): self
    {
        $this->createMeet = $createMeet;
        return $this;
    }

    /**
     * @return bool
     */
    public function getRecordMeet(): bool
    {
        return $this->recordMeet;
    }

    /**
     * @param bool $recordMeet
     * @return self
     */
    public function setRecordMeet(bool $recordMeet): self
    {
        $this->recordMeet = $recordMeet;
        // Si se habilita la grabación, también se debe crear Meet
        if ($recordMeet) {
            $this->createMeet = true;
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function getSaveToDrive(): bool
    {
        return $this->saveToDrive;
    }

    /**
     * @param bool $saveToDrive
     * @return self
     */
    public function setSaveToDrive(bool $saveToDrive): self
    {
        $this->saveToDrive = $saveToDrive;
        // Si se guarda en Drive, también se debe grabar
        if ($saveToDrive) {
            $this->recordMeet = true;
            $this->createMeet = true;
        }
        return $this;
    }
}
