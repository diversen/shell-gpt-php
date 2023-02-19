#!/bin/sh
# Build a new version of the phar

# Get latest tag
git fetch --tags
latestTag=$(git describe --tags `git rev-list --tags --max-count=1`)

# if no tag use main
if [ -z "$latestTag" ]; then
    latestTag="main"
fi

composer install
cd ..
rm shell-gpt-php/releases/shgpt-$latestTag.phar
phar-composer build shell-gpt-php shell-gpt-php/releases/shgpt-$latestTag.phar
cd shell-gpt-php
chmod +x releases/shgpt-$latestTag.phar
