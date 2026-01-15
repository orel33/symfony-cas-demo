<?php

namespace App\Security;

class CasHelper
{
    public static function init(): void
    {
        if (\phpCAS::isInitialized()) {
            return;
        }

        \phpCAS::client(
            CAS_VERSION_3_0,
            $_ENV['CAS_SERVER_HOSTNAME'],
            (int) $_ENV['CAS_SERVER_PORT'],
            $_ENV['CAS_SERVER_URI'],
            $_ENV['CAS_SERVICE_URL']
        );

        \phpCAS::setNoCasServerValidation(); // accept self-signed certificates (local CAS only)
    }
}
