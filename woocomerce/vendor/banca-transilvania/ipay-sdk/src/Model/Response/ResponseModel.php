<?php

namespace BTransilvania\Api\Model\Response;

class ResponseModel implements ResponseModelInterface
{
    protected \StdClass $response;
    protected ?string $errorCode = null;
    protected ?string $errorMessage = null;

    /**
     * Populates the response model from a \StdClass object.
     * Useful for direct conversion from API responses decoded from JSON.
     *
     * @param \StdClass $response The response object received from the API.
     */
    public function setResponse(\StdClass $response)
    {
        $this->response = $response;
        $this->errorCode = $response->errorCode ?? null;
        $this->errorMessage = $response->errorMessage ?? null;
    }

    /**
     * Populates the response model from an associative array.
     * This can be useful for testing or when working with data obtained in other formats.
     *
     * @param array $data The response data as an associative array.
     */
    public function fromArray(array $data)
    {
        $this->response = (object)$data;
        $this->errorCode = $data['errorCode'] ?? null;
        $this->errorMessage = $data['errorMessage'] ?? null;
    }

    /**
     * Converts the response model back into an associative array.
     * Useful for cases where we need to work with the model data in simplified data structures.
     *
     * @return array The model representation as an associative array.
     */
    public function toArray(): array
    {
        return (array)$this->response;
    }

    /**
     * A useful method for checking if the response indicates an error or a failure case.
     * This can be implemented based on the specific structure of the API responses.
     *
     * @return bool True if the model response indicates an error, False otherwise.
     */
    public function isError(): bool
    {
        return !is_null($this->errorCode);
    }

    /**
     * Retrieves the error message, if any, from the response.
     *
     * @return string|null The error message or null if not applicable.
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * Retrieves the error code, if any, from the response.
     *
     * @return string|null The error code or null if not applicable.
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * Checks if the response indicates a successful operation.
     *
     * @return bool True if the operation was successful, False otherwise.
     */
    public function isSuccess(): bool
    {
        return $this->errorCode == 0;
    }

    public function getLanguage()
    {
        return $this->response->language ?? 'en';
    }

    public function __get($name)
    {
        if (property_exists($this->response, $name)) {
            return $this->response->$name;
        }

        return null;
    }

    public function __call($name, $arguments)
    {
        if (preg_match('/^get([A-Z]\w*)$/', $name, $matches)) {
            $property = lcfirst($matches[1]);

            if (property_exists($this->response, $property)) {
                return $this->response->$property;
            }
            return null;
        }
        return null;
    }
}
