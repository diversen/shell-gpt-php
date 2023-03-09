#!/bin/sh

# Get latest tag
TAG=$(git describe --tags `git rev-list --tags --max-count=1`)
phar-composer build diversen/shell-gpt-php:$TAG shgpt.phar
