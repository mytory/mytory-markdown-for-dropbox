<?php

class MM4DMarkdownExtra
{
    function __construct()
    {
        include 'markdown.php';
    }

    public function convert($md_content)
    {
        return Markdown($md_content);
    }
}