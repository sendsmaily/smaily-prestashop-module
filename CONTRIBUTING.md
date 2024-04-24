First off, thanks for taking the time to contribute!

# Table of contents

- [Getting started](#getting-started)
- [Internals](#internals)
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

    $ docker compose up

You can access PrestaShop store from `http://localhost:8080` and administration interface from `http://localhost:8080/admin-dev` URL.

> Administrator user is `admin@prestashop.com` and password `prestashop`.

# Internals

## Structure of the repository

The repository is split into multiple parts:

- `.github` - GitHub issue and pull request templates, and release workflow;
- `assets` - images for the user guide;
- `src` - module files;

Source files must follow standard PrestaShop module structure. Learn more about it from the [Modules Folder structure](https://devdocs.prestashop-project.org/8/modules/creation/module-file-structure/) chapter in the official documentation.

# Development

All written code must follow PrestaShop's [coding standards](https://devdocs.prestashop-project.org/8/development/coding-standards/) and [naming conventions](https://devdocs.prestashop-project.org/8/development/naming-conventions/).

## Starting the environment

You can run the environment by executing:

    $ docker compose up -d

> **Note!** Make sure you do not have any other process(es) listening on ports 8080 and 8888.

### Developing in VsCode Remote Container

It is advised to develop the application inside VSCode remote container. This allows to get PHP intellisense on PrestaShop classes, includes, etc and provides an debugging option when using latest version of the `prestashop/prestashop-flashlight` image. Open `/var/www/html` directory of `prestashop` container as this provides context for intellisense.

## Stopping the environment

Environment can be stopped by executing:

    $ docker compose down --remove-orphans

## Resetting the environment

If you need to reset the installation, just simply delete environment's Docker volumes. Easiest way to achieve this is by running:

    $ docker compose down --remove-orphans -v

## Weird behavior in different versions

### php-cs-fixer not working

You may notice that `php-cs-fixer` might not work for some PrestaShop image versions. `php-cs-fixer` output provides a hint that the `/var/www/html/tests` directory does not exist. This is due to the `tests` folder being included in the [PrestaShop repo](https://github.com/PrestaShop/PrestaShop) but not in the docker image. Adding an empty `/var/www/heml/tests` folder enables the `php-cs-fixer`.
