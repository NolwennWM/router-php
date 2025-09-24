<?php

namespace NWM\Router\Services;

class Flash_Service
{
    /**
     * Method to add a flash message to the session
     *
     * @param string $type
     * @param string|array $message
     * @return void
     */
    public function addFlashMessage(string $type, string|array $message): void
    {
        $_SESSION["flashes"][$type][] = $message;
    }

    /**
     * Method to get flash messages from the session
     *
     * @param string $type
     * @return array
     */
    public function getFlashMessages(string $type = ""): array
    {
        if (!empty($type)) {
            $flashes = $_SESSION["flashes"][$type] ?? [];
            unset($_SESSION["flashes"][$type]);
            return $flashes;
        }
        $flashes = $_SESSION["flashes"] ?? [];
        unset($_SESSION["flashes"]);
        return $flashes;
    }
    /**
     * Method to start the session if not already started
     *
     * @return void
     */
    public function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}