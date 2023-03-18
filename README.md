# shell-gpt-php

This is heavily based on [shell-gpt](https://github.com/TheR1D/shell_gpt)

## Installation

Download the latest release (it is always here):

```bash
wget https://10kilobyte.com/shgpt/shgpt.phar
sudo cp -f shgpt.phar /usr/local/bin/shgpt
sudo chmod +x /usr/local/bin/shgpt
```

Or build it yourself:
    
You will need to install `phar-composer`. 

Clone this repo and run [build.sh](build.sh)

You can specify a tag:

    ./build.sh dev-main

Latest tag:

    ./build.sh

## Set key

Set (or change) API key

    shgpt key

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

    Type 'exit' to exit. 'save' to save
    You: Type something ...
    
### Show token usage

    shgpt usage

## License

MIT © [Dennis Iversen](https://github.com/diversen)
