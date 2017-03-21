<?php

class MM4DParsedown
{
    private $markdown;

    function __construct()
    {
        include 'Parsedown.php';
        $this->markdown = new Parsedown();
    }

    public function convert($md_content)
    {
        return $this->markdown->text($md_content);
    }
}