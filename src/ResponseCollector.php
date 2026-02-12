<?php

namespace MilliPress\AcornMilliRules;

use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collects response modifications from MilliRules actions.
 *
 * Actions written here during rule execution; the middleware reads
 * the collected state and applies it to the outgoing HTTP response.
 */
class ResponseCollector
{
    /** @var array<string, string> */
    protected array $headers = [];

    protected ?string $redirectUrl = null;

    protected int $redirectStatus = 302;

    /**
     * Queue a header to be set on the response.
     */
    public function addHeader(string $name, string $value): void
    {
        $this->headers[$name] = $value;
    }

    /**
     * Queue a redirect response.
     */
    public function setRedirect(string $url, int $status = 302): void
    {
        $this->redirectUrl = $url;
        $this->redirectStatus = $status;
    }

    /**
     * Whether a redirect has been queued (replaces the original response).
     */
    public function hasReplacement(): bool
    {
        return $this->redirectUrl !== null;
    }

    /**
     * Get the replacement redirect response, or null.
     */
    public function getReplacement(): ?Response
    {
        if ($this->redirectUrl === null) {
            return null;
        }

        return new RedirectResponse($this->redirectUrl, $this->redirectStatus);
    }

    /**
     * Get all queued headers.
     *
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Reset state for the next request.
     */
    public function clear(): void
    {
        $this->headers = [];
        $this->redirectUrl = null;
        $this->redirectStatus = 302;
    }
}
