# shell-gpt-php

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

### Create a dialog

Something like ChatGPT (gpt-3.5-turbo model). Meaining that the context is loaded from the previous response.

    Type 'exit' to exit. 'save' to save
    You: Say "hello world"
    Assistant: Hello world!
    You: ...

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

### Questions

    shgpt chat "How do you say hello in spanish?"
    # -> "Hola"

### Set params

Save some model parameters that will override default params.
But not parameters that exists on the command line. 
    
    shgpt params -h

### Show token usage

    shgpt usage

## License

MIT © [Dennis Iversen](https://github.com/diversen)
