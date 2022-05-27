<?php

namespace gewinum\marrysystem;

use gewinum\marrysystem\dataSchemes\MarryRequest;

class MarryRequests
{
    private static ?self $_instance = null;

    private array $requests = [];

    public function getByRequester(string $playerName): ?MarryRequest
    {
        foreach ($this->requests as $request) {
            if ($request->requester->getName() === $playerName) {
                return $request;
            }
        }

        return null;
    }

    public function getByRecipient(string $playerName): ?MarryRequest
    {
        foreach ($this->requests as $request) {
            if ($request->recipient->getName() === $playerName) {
                return $request;
            }
        }

        return null;
    }

    public function add(MarryRequest $request): void
    {
        $this->requests[$request->requester->getName() . "-" . $request->recipient->getName()] = $request;
    }

    public function remove(MarryRequest $request): void
    {
        unset($this->requests[$request->requester->getName() . "-" . $request->recipient->getName()]);
    }

    public static function getInstance(): self
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}