<?php

class Utilities {
    /**
     * Generate a security token.
     * */
    public static function getSecurityToken()
    {
        // Ensure the session exists before querying it.
        if (!Session::request_contains_session_id()) {
            Session::start();
        }

        return SecurityToken::inst()->getSecurityID();
    }
}
