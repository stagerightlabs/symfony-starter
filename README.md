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
