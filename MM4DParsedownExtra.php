<?php

class MM4DParsedownExtra
{
    private $markdown;

    function __construct()
    {
        include 'Parsedown.php';
        include 'ParsedownExtra.php';
        $this->markdown = new ParsedownExtra();
        $this->markdown->setUrlsLinked(false);
    }

    public function convert($md_content)
    {
        return $this->markdown->text($md_content);
    }
}