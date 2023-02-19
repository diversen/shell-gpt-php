# shell-gpt-php

This is heavily based on [shell-gpt](https://github.com/TheR1D/shell_gpt)

## Installation

Download a release, e.g.: 
    
    sgtpp-v0.0.1.phar

    sudo mv sgtpp-v0.0.1.phar /usr/local/bin/sgtpp
    sudo chmod +x /usr/local/bin/sgtpp

## Usage

Generate a shell command: 

    sgtpp shell "Command to search replace in files recursive in current directory"
    # -> find . -type f -exec sed -i 's/search/replace/g' {} \;

Option for executing the command (set -e flag): 

    sgtpp shell "Command to search replace in files recursive in current directory" -e
    # -> Execute command: find . -type f -exec sed -i 's/search/replace/g' {} \; ? Sure you want to continue? [Y/n]

Generating code:

    sgtpp code "Can you make a simple HTML template?" > index.html
    more index.html

Human questions with the GTP:

    sgtpp chat "How do you say hello in spanish?"
    # -> Hello, how are you?

## License

MIT
