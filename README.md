# shell-gpt-php

This is heavily based on [shell-gpt](https://github.com/TheR1D/shell_gpt)

## Installation

Download the latest release (v0.1.1):

    wget https://10kilobyte.com/shgpt/shgpt.phar
    sudo cp -r shgpt.phar /usr/local/bin/shgpt
    sudo chmod +x /usr/local/bin/shgpt

Or build it yourself:
    
You will need to install `phar-composer`. 
Instructions can be found on this link: 
[https://github.com/clue/phar-composer](https://github.com/clue/phar-composer).

    phar-composer build diversen/shell-gpt-php:v0.1.1 shgpt.phar
    sudo cp -f shgpt.phar /usr/local/bin/shgpt
    sudo chmod +x /usr/local/bin/shgpt

## Usage

You will need an API key

If you don't have one you will be prompted: 

    "No GPT-3 API key found. Please enter a valid API key:"
    # -> Enter your API key

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

### Create a dialog (ChatGPT)

Something more like ChatGPT (gpt-3.5-turbo model)

    shgpt dialog

    Type 'exit' to exit
    Message: 

## License

MIT © [Dennis Iversen](https://github.com/diversen)
