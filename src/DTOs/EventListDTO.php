<?php

namespace Jlbousing\GoogleCalendar\DTOs;

class EventListDTO
{
    /**
     * @var int
     */
    private $maxResults = 10;

    /**
     * @var string
     */
    private $orderBy = 'startTime';

    /**
     * @var bool
     */
    private $singleEvents = true;

    /**
     * @var string|null
     */
    private $timeMin;

    /**
     * @var string|null
     */
    private $timeMax;

    /**
     * @var string|null
     */
    private $q;

    /**
     * @var string
     */
    private $calendarId = 'primary';

    /**
     * @param array $params
     * @return self
     */
    public static function fromArray(array $params): self
    {
        $dto = new self();

        if (isset($params['max_results'])) {
            $dto->setMaxResults($params['max_results']);
        }

        if (isset($params['order_by'])) {
            $dto->setOrderBy($params['order_by']);
        }

        if (isset($params['single_events'])) {
            $dto->setSingleEvents($params['single_events']);
        }

        if (isset($params['time_min'])) {
            $dto->setTimeMin($params['time_min']);
        } else {
            $dto->setTimeMin(date('c'));
        }

        if (isset($params['time_max'])) {
            $dto->setTimeMax($params['time_max']);
        }

        if (isset($params['q'])) {
            $dto->setQ($params['q']);
        }

        if (isset($params['calendar_id'])) {
            $dto->setCalendarId($params['calendar_id']);
        }

        return $dto;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $params = [
            'maxResults' => $this->maxResults,
            'orderBy' => $this->orderBy,
            'singleEvents' => $this->singleEvents,
        ];

        if ($this->timeMin) {
            $params['timeMin'] = $this->timeMin;
        }

        if ($this->timeMax) {
            $params['timeMax'] = $this->timeMax;
        }

        if ($this->q) {
            $params['q'] = $this->q;
        }

        return $params;
    }

    /**
     * @return int
     */
    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    /**
     * @param int $maxResults
     * @return self
     */
    public function setMaxResults(int $maxResults): self
    {
        $this->maxResults = $maxResults;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * @param string $orderBy
     * @return self
     */
    public function setOrderBy(string $orderBy): self
    {
        $this->orderBy = $orderBy;
        return $this;
    }

    /**
     * @return bool
     */
    public function getSingleEvents(): bool
    {
        return $this->singleEvents;
    }

    /**
     * @param bool $singleEvents
     * @return self
     */
    public function setSingleEvents(bool $singleEvents): self
    {
        $this->singleEvents = $singleEvents;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimeMin(): ?string
    {
        return $this->timeMin;
    }

    /**
     * @param string $timeMin
     * @return self
     */
    public function setTimeMin(string $timeMin): self
    {
        $this->timeMin = $timeMin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimeMax(): ?string
    {
        return $this->timeMax;
    }

    /**
     * @param string $timeMax
     * @return self
     */
    public function setTimeMax(string $timeMax): self
    {
        $this->timeMax = $timeMax;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getQ(): ?string
    {
        return $this->q;
    }

    /**
     * @param string $q
     * @return self
     */
    public function setQ(string $q): self
    {
        $this->q = $q;
        return $this;
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
}
