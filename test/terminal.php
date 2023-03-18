<?php

function get_terminal () {
    $shell = getenv('TERM');
    echo "The current shell is: $shell";
    echo getenv('SHELL');
}

echo get_terminal();