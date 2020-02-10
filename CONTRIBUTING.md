# Setting up development environment

## Tools

This workflow uses [Tusk](https://rliebz.github.io/tusk/) - YAML-based task runner - to run tasks.
To see all available commands run:
```
tusk -h
```

## Building

To mirror local user to container you need to build the image first. Tusk file manages local user information. To mirror a different user you can modify default option values(`tusk build -h`).
```
tusk build
```

## Starting containers

Starting and stopping containers have a shortcut Tusk commands available so that you can start and stop containers from this folder.

To start the containers run:
```
tusk up
```
And to stop the containers run:
```
tusk down
```

## After installation script

To access admin section after install you need to delete the `install` directory and rename `admin` directory. This script will do it for you. You can access admin section from `localhost:8080\admin1`.
```
tusk post-install
```
