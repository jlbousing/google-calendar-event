<?php

namespace Jlbousing\GoogleCalendarEvent\DTOs;

class ConfigDTO
{
    /**
     * @var string
     */
    private $appName;

    /**
     * @var string
     */
    private $credentialsPath;

    /**
     * @var string
     */
    private $accessType = 'offline';

    /**
     * @var array
     */
    private $scopes = [];

    /**
     * @var string|null
     */
    private $tokenPath;

    /**
     * @param array $config
     * @return self
     */
    public static function fromArray(array $config): self
    {
        $dto = new self();

        if (!isset($config['app_name'])) {
            throw new \InvalidArgumentException('app_name is required');
        }

        if (!isset($config['credentials_path'])) {
            throw new \InvalidArgumentException('credentials_path is required');
        }

        $dto->setAppName($config['app_name']);
        $dto->setCredentialsPath($config['credentials_path']);

        if (isset($config['access_type'])) {
            $dto->setAccessType($config['access_type']);
        }

        if (isset($config['scopes'])) {
            $dto->setScopes($config['scopes']);
        }

        if (isset($config['token_path'])) {
            $dto->setTokenPath($config['token_path']);
        }

        return $dto;
    }

    /**
     * @return string
     */
    public function getAppName(): string
    {
        return $this->appName;
    }

    /**
     * @param string $appName
     * @return self
     */
    public function setAppName(string $appName): self
    {
        $this->appName = $appName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialsPath(): string
    {
        return $this->credentialsPath;
    }

    /**
     * @param string $credentialsPath
     * @return self
     */
    public function setCredentialsPath(string $credentialsPath): self
    {
        $this->credentialsPath = $credentialsPath;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccessType(): string
    {
        return $this->accessType;
    }

    /**
     * @param string $accessType
     * @return self
     */
    public function setAccessType(string $accessType): self
    {
        $this->accessType = $accessType;
        return $this;
    }

    /**
     * @return array
     */
    public function getScopes(): array
    {
        return $this->scopes;
    }

    /**
     * @param array $scopes
     * @return self
     */
    public function setScopes(array $scopes): self
    {
        $this->scopes = $scopes;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTokenPath(): ?string
    {
        return $this->tokenPath;
    }

    /**
     * @param string $tokenPath
     * @return self
     */
    public function setTokenPath(string $tokenPath): self
    {
        $this->tokenPath = $tokenPath;
        return $this;
    }
}
