<?php

namespace Mouf\Packanalyst\Widgets;

use Mouf\Html\Renderer\Renderable;
use Mouf\Html\HtmlElement\HtmlElementInterface;

/**
 * The search block.
 */
class SearchBlock implements HtmlElementInterface
{
    use Renderable;

    private $search;

    /**
     * The text displayed into the search block.
     *
     * @param string $search
     */
    public function __construct($search = null)
    {
        $this->search = $search;
    }

    /**
     * The text displayed into the search block.
     *
     * @param string $search
     */
    public function setSearch($search)
    {
        $this->search = $search;
    }
}
