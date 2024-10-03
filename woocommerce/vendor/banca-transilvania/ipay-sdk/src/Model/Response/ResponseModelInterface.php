<?php

namespace BTransilvania\Api\Model\Response;

interface ResponseModelInterface
{
    /**
     * Populates the response model from a \StdClass object.
     * Useful for direct conversion from API responses decoded from JSON.
     *
     * @param \StdClass $response The response object received from the API.
     */
    public function setResponse(\StdClass $response);

    /**
     * Converts the response model back into an associative array.
     * Useful for cases where we need to work with the model data in simplified data structures.
     *
     * @return array The model representation as an associative array.
     */
    public function toArray(): array;

    /**
     * A useful method for checking if the response indicates an error or a failure case.
     * This can be implemented based on the specific structure of the API responses.
     *
     * @return bool True if the model response indicates an error, False otherwise.
     */
    public function isError(): bool;

    /**
     * Retrieves the error message, if any, from the response.
     *
     * @return string|null The error message or null if not applicable.
     */
    public function getErrorMessage(): ?string;

    /**
     * Retrieves the error code, if any, from the response.
     *
     * @return string|null The error code or null if not applicable.
     */
    public function getErrorCode(): ?string;

    /**
     * Checks if the response indicates a successful operation.
     *
     * @return bool True if the operation was successful, False otherwise.
     */
    public function isSuccess(): bool;
}
