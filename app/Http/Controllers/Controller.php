<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Return the redirect target only when it stays on this application's origin.
     *
     * Accepts a relative path ("/dashboard") or an absolute URL on the current
     * root. Protocol-relative ("//host"), backslash-prefixed and foreign URLs
     * are rejected so they cannot be used as open redirects.
     */
    protected function sameOriginRedirectTarget(mixed $target): ?string
    {
        if (! is_string($target) || $target === '' || $target === '/') {
            return null;
        }

        if (str_starts_with($target, '/')) {
            return preg_match('#^/[^/\\\\]#', $target) === 1 ? $target : null;
        }

        return str_starts_with($target, url('/').'/') ? $target : null;
    }
}
