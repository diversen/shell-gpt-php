#!/bin/sh

if [ -f shgpt.phar ]; then
    rm shgpt.phar
fi

# check if first argument is set and place it in a variable
if [ -z "$1" ]
then
    # Get latest tag
    TAG=$(git describe --tags `git rev-list --tags --max-count=1`)
else
    TAG=$1
fi

echo "Building version $TAG"

phar-composer build diversen/shell-gpt-php:$TAG shgpt.phar
