First off, thanks for taking the time to contribute!


# Table of contents

- [Getting started](#getting-started)
- [Internals](#internals))
    - [Structure of the repository](#structure-of-the-repository)
- [Deveopment](#development)
    - [Starting the environement](#starting-the-environment)
    - [Stopping the environment](#stopping-the-environment)
    - [Resetting the environment](#resetting-the-environment)


# Getting started

The development environment requires [Docker](https://docs.docker.com/) and [Docker Compose](https://docs.docker.com/compose/) to run. Please refer to the official documentation for step-by-step installation guide.

Clone the repository:

    $ git clone git@github.com:sendsmaily/smaily-prestashop-module.git

Next, change your working directory to the local repository:

    $ cd smaily-prestashop-module

And run the environment:

    $ docker-compose up

When running the enviroment for the first time, PrestaShop installation will automatically run, and it can take a while.

Once installation has finished and web server is ready to accept requests, you can access PrestaShop store from `http://localhost:8080` and administration interface from `http://localhost:8080/admin1` URL.

> Administrator user is `admin@smaily.sandbox` and password `smailydev1`.


# Internals

## Structure of the repository

The repository is split into multiple parts:

- `.github` - GitHub issue and pull request templates, and release workflow;
- `.sandbox` - files needed for running the development environment;
- `assets` - images for the user guide;
- `src` - module files;

Source files must follow standard PrestaShop module structure. Learn more about it from the [Modules Folder structure](https://devdocs.prestashop.com/1.7/modules/creation/module-file-structure/) chapter in the official documentation.


# Development

All written code must follow PrestaShop's [coding standards](https://devdocs.prestashop.com/1.7/development/coding-standards/) and [naming conventions](https://devdocs.prestashop.com/1.7/development/naming-conventions/).

## Starting the environment

You can run the environment by executing:

    $ docker-compose up

> **Note!** Make sure you do not have any other process(es) listening on ports 8080 and 8888.

## Stopping the environment

Environment can be stopped by executing:

    $ docker-compose down

## Resetting the environment

If you need to reset the Wordpress installation in the development environment, just simply delete environment's Docker volumes. Easiest way to achieve this is by running:

    $ docker-compose down -v
