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
phar-composer build shell-gpt-php sgtpp.phar
mv sgtpp.phar shell-gpt-php/releases/sgtpp-$latestTag.phar
cd shell-gpt-php
chmod +x releases/sgtpp-$latestTag.phar
