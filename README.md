# shell-gpt-php

    cat README.md | shgpt q "Summerize the above README in the style of Shakespeare"

Greeting all, 'tis time to install mine shell-gpt-php. 'Tis a wondrous treasure that can assist with thy questions and commands, thine need only download the latest release, execute thine commands, and away we go! Set thy key for the dilaog, or 'tis time to pipe thy content in and generate shell command with or one line python program. Ask a question, or show token usage from thy last ninety days, 'tis all here! Tis a goodly thing all said, so depart thou now, and enjoy thine shell-gpt-php.

## Installation

Download the latest release:

```bash
wget https://www.10kilobyte.com/shgpt/shgpt.phar
sudo cp -f shgpt.phar /usr/local/bin/shgpt
sudo chmod +x /usr/local/bin/shgpt
rm shgpt.phar
```

* You may also [build it yourself](docs/BUILD.md) 
* Develop [commands](docs/DEVELOP.md)

## Set key

Set (or change) API key

    shgpt key

### Create a dialog

    shgpt dialog

Or:

    shgpt d

Something like ChatGPT (gpt-3.5-turbo model). Meaning that the answer is extrapolated from the previous questions and answers.

    You: Say "hello world"
    Assistant: Hello world!
    You: ...

Some sub commands are available in the dialog mode:

    Available commands: 

    save  - Save dialog to file
    exec  - Execute a command and feed the output to the dialog
    exit  - Exit the dialog
    comm  - Show all commands
    clear - Clear the dialog and start over

    Type a message to ChatGPT. Maybe 'hello world!' You may also use above commands.

All dialogs are auto-saved on exit to the directory `~/.config/shell-gpt-php/data/`

### Generate a shell command: 

Command optimized for shell commands.

    shgpt shell "Command to search replace in files recursive in current directory"
    # -> find . -type f -exec sed -i 's/search/replace/g' {} \;

    shgpt s "command to change origin of a git repo"
    # -> git remote set-url origin <new url>

Add the -e flag to execute the command:

    shgpt s "Command to search replace in files recursive in current directory" -e
    # -> Execute command: find . -type f -exec sed -i 's/search/replace/g' {} \; ? Sure you want to continue? [Y/n]

### Generating code:

Command to generate code snippets.

    shgpt code "Can you make a simple HTML template?" > index.html
    more index.html

    shgpt c "Write a one line hello world python program"
    # -> print("Hello World!")

## Ask any question

    shgpt question "Extract directories from this list of files: $(ls -l)"
    # -> bin, src, tests, vendor

    shgpt q "What is the distance to the sun? Just show me the number."
    # -> 149,597,870.7 kilometers 

### STDIN

The `shell, code, question` commands will accept input from STDIN. STDIN will be added before the prompt. E.g.: 

    cat composer.json | shgpt q "What type of input is this?"
    # -> This is a PHP package composer JSON file.

### Set params

Save some model parameters that will override default params.
But not parameters that exists on the command line. 

E.g. if you set the temperature to 2.0 you will get kind of crazy results
    
    shgpt params -h

### Show token usage

Last 24 hours, last 7 days, last 30 days and last 90 days. 

    shgpt usage

## License

MIT © [Dennis Iversen](https://github.com/diversen)
