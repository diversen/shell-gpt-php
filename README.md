# shell-gpt-php

This is heavily based on [shell-gpt](https://github.com/TheR1D/shell_gpt)

## Installation

Download a release, e.g.: 
    
    shgpt-v0.0.3.phar

    sudo mv shgpt-v0.0.3.phar /usr/local/bin/shgpt
    sudo chmod +x /usr/local/bin/shgpt

Build it yourself:
    
You will need to install `phar-composer`
Instructions can be found on this link: [https://github.com/clue/phar-composer](https://github.com/clue/phar-composer).

    git clone git@github.com:diversen/shell-gpt-php.git
    cd shell-gpt-php
    composer install
    mkdir -p releases
    ./build.sh

Release will be in `releases/shgpt-v0.0.3.phar`


You will need an API key

If you don't have one you will be prompted: 

    "No GPT-3 API key found. Please enter a valid API key:"
    # -> Enter your API key


## Usage

### Generate a shell command: 

    shgpt shell "Command to search replace in files recursive in current directory"
    # -> find . -type f -exec sed -i 's/search/replace/g' {} \;

### Execute a shell command

Add the -e flag to execute the command:

    shgpt shell "Command to search replace in files recursive in current directory" -e
    # -> Execute command: find . -type f -exec sed -i 's/search/replace/g' {} \; ? Sure you want to continue? [Y/n]

### Generating code:

    shgpt code "Can you make a simple HTML template?" > index.html
    more index.html

### Human questions with the GPT:

    shgpt chat "How do you say hello in spanish?"
    # -> "Hola"

## License

MIT
