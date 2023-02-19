# shell-gpt-php

This is heavily based on [shell-gpt](https://github.com/TheR1D/shell_gpt)

## Installation

Download a release, e.g.: 
    
    shgpt-v0.0.2.phar

    sudo mv shgpt-v0.0.1.phar /usr/local/bin/shgpt
    sudo chmod +x /usr/local/bin/shgpt

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

### Human questions with the GTP:

    shgpt chat "How do you say hello in spanish?"
    # -> "Hola"

## License

MIT
