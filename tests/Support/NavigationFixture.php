<?php

namespace Tests\Support;

final class NavigationHeader
{
    public $title;
    public $url;
    public $button;
    public $active = false;

    /**
     * Preserve EE Header's title independently of its fluent object identity.
     *
     * @param string $title
     * @return void
     */
    public function __construct($title)
    {
        $this->title = $title;
    }

    /**
     * Record the URL supplied by the bridge without inventing route matching.
     *
     * @param mixed $url
     * @return self
     */
    public function withUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Match EE Header's single button payload, replacing any prior button.
     *
     * @param string $text
     * @param mixed $url
     * @return self
     */
    public function withButton($text, $url)
    {
        $this->button = array('text' => $text, 'url' => $url);
        return $this;
    }

    /**
     * Expose explicit bridge activation without rendering sidebar HTML.
     *
     * @return self
     */
    public function isActive()
    {
        $this->active = true;
        return $this;
    }
}

final class NavigationSidebar
{
    public $headers = array();

    /**
     * Match the sidebar factory's fluent make boundary.
     *
     * @return self
     */
    public function make()
    {
        return $this;
    }

    /**
     * Return a distinct header object, as EE's Sidebar service does.
     *
     * @param string $title
     * @return NavigationHeader
     */
    public function addHeader($title)
    {
        $header = new NavigationHeader($title);
        $this->headers[] = $header;
        return $header;
    }
}
