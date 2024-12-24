<?php

namespace BalajiDharma\LaravelViewable;

use Illuminate\Http\Request;
use Jaybizzle\CrawlerDetect\CrawlerDetect;

class Visitor
{
    /**
     * PHP stores the DNT header under the "HTTP_DNT" key instead of "DNT".
     *
     * @var string
     */
    const DNT = 'HTTP_DNT';

    protected $request;

    protected CrawlerDetect $crawlerDetect;

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?? request();
        $this->crawlerDetect = new CrawlerDetect;
    }

    /**
     * Get the visitor's IP address
     */
    public function ip(): ?string
    {
        return $this->request->ip();
    }

    /**
     * Get the visitor's session ID
     */
    public function getSessionId(): ?string
    {
        return $this->request->session()->getId();
    }

    /**
     * Check if the visitor is authenticated
     */
    public function isAuthenticated(): bool
    {
        return auth()->check();
    }

    /**
     * Get the authenticated user's ID
     *
     * @return mixed|null
     */
    public function getId()
    {
        return $this->isAuthenticated() ? auth()->id() : null;
    }

    /**
     * Get the authenticated user's class
     */
    public function getType(): ?string
    {
        return $this->isAuthenticated() ? auth()->user()->getMorphClass() : null;
    }

    /**
     * Determine if the visitor has a "Do Not Track" header.
     */
    public function hasDoNotTrackHeader(): bool
    {
        return (int) $this->request->header(self::DNT) === 1;
    }

    public function isCrawler(): bool
    {
        return $this->crawlerDetect->isCrawler();
    }
}
