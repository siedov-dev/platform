<?php

namespace App\Http\Responses;

use Illuminate\Http\Response;

class NoCookiesResponse extends Response
{
    /**
     * Sends HTTP headers.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendHeaders(): static
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        // headers
        foreach ($this->headers->allPreserveCaseWithoutCookies() as $name => $values) {
            $replace = 0 === strcasecmp($name, 'Content-Type');
            foreach ($values as $value) {
                header($name.': '.$value, $replace, $this->statusCode);
            }
        }

        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        return $this;
    }
}
