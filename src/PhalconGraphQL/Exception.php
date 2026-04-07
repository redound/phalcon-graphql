<?php

namespace PhalconGraphQL;

use GraphQL\Error\ClientAware;

class Exception extends \Exception implements ClientAware
{
    protected $isClientSafe = true;
    protected $category = 'graphql';
    protected $errorCode;
    protected array $extra;

    public function __construct($code, $message = '', \Throwable $previous = null, array $extra = [])
    {
        $this->errorCode = $code;
        $this->extra = $extra;
        $numericCode = is_numeric($code) ? (int)$code : 0;

        parent::__construct($message, $numericCode, $previous);
    }

    public function isClientSafe(): bool
    {
        return $this->isClientSafe;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Get the original error code (can be string or int)
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * Get additional error information for GraphQL clients
     * @return array|null
     */
    public function getUserInfo(): ?array
    {
        return array_merge([
            'code' => $this->getErrorCode(),
            'category' => $this->getCategory()
        ], $this->extra);
    }

    /**
     * Get detailed error information for developers
     * @return array|null
     */
    public function getDeveloperInfo(): ?array
    {
        return [
            'code' => $this->getErrorCode(),
            'category' => $this->getCategory(),
            'message' => $this->getMessage(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'trace' => $this->getTraceAsString(),
            'previous' => $this->getPrevious() ? [
                'message' => $this->getPrevious()->getMessage(),
                'code' => $this->getPrevious()->getCode(),
                'file' => $this->getPrevious()->getFile(),
                'line' => $this->getPrevious()->getLine(),
                'trace' => $this->getPrevious()->getTraceAsString()
            ] : null
        ];
    }
}