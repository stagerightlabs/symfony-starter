# Symfony 6 Starter

This repo provides a jumping off point for creating a web application using Symfony 6 and Docker.

## Instructions

To create a new application clone this repo locally and then wipe the git history. From the project root:

```
rm -fr .git
git init
```

Your new application now has its own separate git history and can be pushed to a new repository on GitHub.

## Local Development with Docker

### Up and Running

This project provides docker container definitions that can be used for local development and customized to fit your needs. You will need to have both [Docker](https://hub.docker.com/editions/community/docker-ce-desktop-windows/) and [Docker Compose](https://docs.docker.com/compose/install/) installed on your development machine. The free 'community edition' of Docker will suffice for our needs. If you are using Windows you may need to clone this repo into a WSL folder depending on which version of the Windows Docker client you are using.

Once you have Docker installed you can now build the images on your host machine. Use your CLI to navigate to the project root and then run this command:

```
docker-compose build
```

This will download all of the open source containers used in this project and also build the services that have been defined in the `docker-compose.yml` file.

To start the service containers, run this command:

```
docker-compose up -d
```

The `-d` flag will start the services as daemon process which will allow you to close your CLI without also terminating the services.

To stop all of the services run this command:

```
docker-compose down
```

You can check the status of the containers at any time by running this command:

```
docker-compose ps
```

You can also inspect service logs with the logs command:

```
docker-compose logs
```

You can use a `-f` flag to keep the log streams open and view new entries in real time. You can also specify a service name to see only the logs for that service.

### Installing Dependencies

You will need to install both PHP and Node dependencies locally before you can spin up the application.

To install the PHP dependencies:

```
docker-compose run --rm cli composer install
```

To install the node dependencies:

```
docker-compose run --rm node npm install
```

### Domain and Port Considerations

The service architecture in this project ships with Apache to handle web traffic requests. By default it is set up to serve content on the `symfony-starter.test` domain. In order to access this site on your host machine you will need to update your hosts file:

```
sudo bash -c "echo '127.0.0.1 symfony-starter.test' >> /etc/hosts"
```

To customize this domain for your project you will need to update the configuration files in the `docker/apache` folder. (Use grep to find all the locations where the domain is referenced.) Note: After adjusting the configuration files you will need to stop the running services, rebuild the containers, and then restart the services before you will see those changes go into effect.

Port 80 is the default used for web traffic. Your host machine can only have one program or service listening to port 80 at a time so attempting to spin up this development environment will fail if you have some other program on your machine using port 80. If you would like to spin up multiple applications on your host machine that will want to connect to each other you will need to assign each of them a different port number. This is done in the `docker-compose.yml` service definition. Under the 'ports' section you will see the definition '80:80'. The first is your host machine port and the second is the container port. To direct application web traffic to port 10000 you would specify it like this: '10000:80'. You can then access the running service at the same local domain as long as you add in the port number:

```
http://symfony-starter.test:10000
```

This project does not yet ship with SSL enabled locally but that may be added in the future. You are, however, free to set this up in your own project.

### Defined Services

This project comes with a handful of pre-defined services that can be used to do local development work on your Symfony application. However, the only limit here is your imagination. You can add and remove services as needed to support the growth of your application.

Most people who make heavy use of Docker prefer to assign a new container to each separate task that you might need for development. It is possible to combine multiple tasks within the same container but this is not considered "the docker way (tm)." A common practice is to use a single image definition as the basis for multiple similar services, such as we have done here with the node containers.

The beauty of Docker Compose is that it simplifies the orchestration of multiple services that are all used for the same project. When you use Docker Compose to spin up a set of services an internal network is automatically created. This allows each of the services defined in the `docker-compose.yml` file to talk to each other over a dedicated private network.

Here is a brief rundown of the services defined in this project:

- `apache`: A container running the Apache web server. All web traffic requests are handled by this container and then sent to PHP via a reverse proxy.

- `fpm`: A PHP container running FPM. Web requests received from Apache are processed by this container and the output is sent back to Apache before it appears in your browser window.

- `cli`: A PHP container for running Symfony console commands.

- `postgres`: A container running the Postgres relational database. The underlying data in the database is persisted to your host machine using a docker volume. This means that you will not lose any data when shutting down the docker services. To reset the database you will need to wipe the volume. Use `docker volume ls` to find the name of the volume, and then `docker volume rm [name]` to remove it.

### Local Ops Helper Script

Docker Compose provides a lot of great benefits but there are a few downsides. Running commands in containers requires a lot more typing and you have to remember what each service container is responsible for. This can sometimes be a damper on productive work. To simplify CLI interactions with this application a bash script has been provided that will automatically delegate commands to their appropriate service containers. The idea for this script comes from Chris Fidao and his [Shipping Docker](https://serversforhackers.com/shipping-docker) course.It provides a shorthand for interacting with the various docker services in use. For example:

To run a composer command:

```
./ops.sh composer update
```

To run a console command:

```
./ops.sh console debug:router
```

To access the postgres CLI (assuming the application database is called 'app'):

```
./ops.sh psql app
```

[Take a look](ops.sh) at the file to see what other delegation commands are available.  Note that in some cases we delegate using Docker 'exec' and sometimes we delegate using Docker 'run'. The difference is somewhat subtle: 'exec' is for performing operations within containers that are already running and 'run' is for spinning up new instances of containers to perform operations.

This script becomes even more powerful if you set it up with an alias in your host CLI.Here is an example using bash:

```bash
ss_ops() {
cd /absolute/path/to/project/root
./ops.sh ${*:-ps}
cd $OLDPWD
}
alias ss=ss_ops
```

Add this to your `.bashrc` file (or some other equivalent) and you now have a convenient shorthand for interacting with the application:

```
ss composer update
ss console debug:router
ss psql app
```

By using an absolute path in the alias definition we can now interact with this application from anywhere on our host machine not just the project root.

## Code Formatting

This project uses [`friendsofphp/php-cs-fixer`](https://github.com/FriendsOfPHP/PHP-CS-Fixer) to automate code formatting.  It is configured to use the [Symfony](https://symfony.com/doc/current/contributing/code/standards.html) formatting standard but many other standards are available.  To format the code in the `src` directory run this command:

```
composer format
```

You can also call php-cs-fixer directly:

```
./vendor/bin/php-cs-fixer fix
```

The formatting configuration can be adjusted in the `.php-cs-fixer.php` or `.php-cs-fixer.dist.php` files.

## Localization and Translation

This project makes use of Symfony's language string tools to easily translate rendered templates into different languages based on URL path, per recommended best practice. The first parameter in every route will be a `_locale` string that informs Symfony which language strings to use when performing translations.

The language strings themselves are PHP files that follow the unicode ICU message format. Symfony also supports YAML and XML string files, but only YAML and PHP allow for structured nesting.

See more here:

- [Translations](https://symfony.com/doc/current/translation.html)
- [How to Work with the User's Locale](https://symfony.com/doc/current/translation/locale.html#translation-locale-url)
- [The ICU Message Format](https://symfony.com/doc/current/translation/message_format.html)

## CSS and Design

This project is configured to use [Tailwind CSS](https://tailwindcss.com/) for page styling and design. Tailwind is a 'utility' framework; it does not offer much in the way of default styles. Instead it provides utility classes that you use to compose the look and feel of your design.  It is fully customizable as well; you can define the parameters of your site design in the tailwind config file and then use the generated CSS classes as a consistent design system across the entire site.

Using composition instead of traditional cascading inheritance makes it much easier to maintain CSS over the long term. When used in combination with embeddable twig components this pattern can be very powerful.

In this project Tailwind has been configured to analyze the template folder and automatically generate a css file that only includes the classes that are actually in use. There is a docker service running a file watcher so this compilation process happens entirely behind the scenes. You should be able to add a class in a template file, save it and then see the change in the browser after you refresh.

Tailwind uses [PostCSS](https://postcss.org/) under the hood, which opens up the possibility for including other cutting edge CSS features in this project as well. There are numerous PostCSS plugins available that bring a wide array of features to the table.

The compiled CSS file will not be tracked by git.  It will need to be regenerated on each deployment.

## Javascript

This project uses [esbuild](https://esbuild.github.io/) to compile javascript assets. It is smaller and faster than webpack or other similar tools. It also handles modern javascript features very well and it will let you control what level of ECMAScript compatibility you want in your final output file. A docker service has been set up to monitor the `assets/js/app.js` file and recompile it whenever changes have been made. This file is the main entrypoint for Javascript in this project; any additional Javascript added to the project will need to be loaded into that file. It is also possible to set up additional entry points that can be compiled into separate javascript files.

This project also comes with [Alpine.js](https://alpinejs.dev/) pre-installed. Alpine.js is an excellent modern replacement for JQuery that embraces standard browser APIs and modern ECMAScript features. There is a healthy plugin system available as well. By design Alpine components are defined directly in HTML, but it is also possible to create them with separate javascript files and then load them into the HTML when instantiating a component.

The compiled JS file will not be tracked by git.  It will need to be regenerated on each deployment.

Javascript code formatting is configured via [standard.js](standardjs.com).  You can format all of the javascript files in the project by running

```
docker-compose run --rm node npm run format
# or
./ops.sh npm run format
```

It would be possible to set up a docker service for this, but it is easier to [configure your IDE](https://standardjs.com/#are-there-text-editor-plugins) to perform the standard.js formatting automatically.


## Browser Cache Busting

Browser cache busting for generated assets has been implemented via an 'asset nonce' environment variable.  Running the `asset:nonce` command on deployment will generate a new nonce value and force browsers to download the latest version of the asset files.
