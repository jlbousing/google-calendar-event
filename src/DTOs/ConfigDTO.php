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
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $redirectUri;

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

    /**
     * @return string
     */
    public function getClientId(): string
    {
        return $this->clientId;
    }

    /**
     * @param string $clientId
     * @return self
     */
    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    /**
     * @return string
     */
    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    /**
     * @param string $clientSecret
     * @return self
     */
    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    /**
     * @param string $redirectUri
     * @return self
     */
    public function setRedirectUri(string $redirectUri): self
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }
}
