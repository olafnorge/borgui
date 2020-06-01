# BorgUI

BorgUI is a [laravel powered](https://laravel.com/) application that sits on top of
[BorgBackup](https://borgbackup.readthedocs.io/en/stable/#what-is-borgbackup). BorgBackup itself does not come with
an own UI but a powerful CLI. To fill this gap and to be able to view backups from anywhere I started writing a UI.

I am still missing a lot of features like initialize a repository, trigger a backup, or delete old backups. This is
because I set up BorgBackup as described in the official documentation and do the maintenance of my backups through
a systemd timer before I started implementing a UI for it.

In regard to BorgUI the above means that you can attach it to an existing BorgBackup Repository, browse the existing
backups, and download files or folders from any of them.

## Requirements

In my setup I use the following:

| What | Version | Why |
| --- | --- | --- |
| BorgBackup | \>= 1.1.10 |  |
| MySQL | \>= 5.7 | |
| Nginx | any | |
| npm | \>= 6.10 | Compilation of assets (CSS, JS, images, etc.) |
| PHP | \>= 7.3 | |
| Redis | \>= 4 | Caching, atomic locks, and HTTP Session management |
| Supervisord | any | running background jobs |
| yarn | \>= 1.20 | Compilation of assets (CSS, JS, images, etc.) |

Please have these available before you proceed with installing BorgUI.

## Preparation

### Installation of BorgBackup

Either use the package manager of your OS or execute the following command which will install it in the version
defined in the [`borg.php`](./config/borg.php) config file inside the `./config` folder.

```shell script
# cd into root folder of BorgUI
cd /path/where/you/extracted/the/archive

# install BorgBackup in the specified version 
pip install borgbackup==$(php -r "echo (require('./config/borg.php'))['version'];")
```

### Prepare MySQL user and database

BorgUI requires a valid MySQL database and user created before you can proceed with the installation. Please see the
following as a reference and make sure you replace the values with something that fits your needs.

```mysql
# please replace 'borg' and 'secret' with appropriate values
CREATE DATABASE IF NOT EXISTS `borg`;
CREATE USER IF NOT EXISTS 'borg'@'%' IDENTIFIED BY PASSWORD 'secret';
GRANT USAGE ON *.* TO 'borg'@'%';
GRANT ALL PRIVILEGES ON `borg`.* TO 'borg'@'%';
```

### Setup a supervisor program for background jobs

To execute tasks or jobs in the background BorgUI uses [Laravel Horizon](https://laravel.com/docs/horizon). All the
details are in the [deployment section of Horizon](https://laravel.com/docs/horizon#deploying-horizon).

An example supervisor program configuration may look like:

```
[program:horizon]
process_name=%(program_name)s
command=php <full path to the installation of borgui>/artisan horizon
autostart=true
autorestart=true
user=<web server user>
redirect_stderr=true
stdout_logfile=<full path to the installation of borgui>/storage/logs/horizon.log
stopwaitsecs=3600
``` 

## Installation

To install BorgUI please download a version from the [release page](https://github.com/olafnorge/borgui/releases),
extract the archive, and run composer to install the dependencies. It is advisable to unzip the archive and execute 
the commands as the user of your web server.

```shell script
# cd into root folder of BorgUI
cd /path/where/you/extracted/the/archive

# check if you match the platform requirements
composer check-platform-reqs --no-dev

# install dependencies
composer install --classmap-authoritative --no-dev

# publish horizon assets
artisan horizon:publish

# migrate database changes
artisan migrate --step

# cache configs, routes, and views for performance
artisan config:cache
artisan route:cache
artisan view:cache

# install dependencies for compiling the view assets
yarn install --ignore-optional

# compile view assets
npm run production
```

Please also consult the official Laravel documentation about
[deploying Laravel](https://laravel.com/docs/deployment) to a server.

## Configuration

All configuration settings can and should be done through environment variables. You may either put them into your
global environment file or, what is recommended, create a `.env` in the root of the installation. I'll only explain
the values that are required to run the application. Please refer to the [.env.example file](./.env.example) as a
reference for all the possible environment variables. If you need to set any other environment variable, which is not
explained in the list below, please refer to the [Laravel Documentation](https://laravel.com/docs/configuration)
for detailed explanation.

As a general advise you should only publish the environment variables which are necessary.

| ENV var | Mandatory | Default | Description |
| --- | :---: | --- | --- |
| APP_DEBUG | | `false` | Sets the application into debug mode. You should avoid using debug mode in production. |
| APP_ENV |  | `development` | Defines the environment of the application. In produtcion set this value to `production`. |
| APP_FORCE_SCHEME |  |  | If you are running the application behind a reverse proxies f.e. in plain HTTP Mode you can instruct the application to deliver only HTTPS URLs by setting this value to `https`. |
| APP_KEY | X |  | The app key is used for encryption and decryption. You can generate an initial value by executing `artisan key:generate --show` in the root of your installation. |
| APP_TIMEZONE |  | `UTC` | Depending on your needs you can set the time zone of the application. |
| APP_URL | X | `http://borg.local` | This URL is used by the console to properly generate URLs when using the Artisan command line tool. You should set this to the root of your application so that it is used when running Artisan tasks. |
| BORG_SCHEDULER_ENABLED |  | `false` | If you want to refresh the state of your repositories regulary set this value to `true`. A cronjob will run each hour and tries to fetch any changes. |
| BORG_WAIT_FOR_LOCK |  | `60` seconds | Set this to a higher value (in seconds w/o unit) if you are suffering from a slow connection. |
| DB_DATABASE | X | `borg` | Name of the database of the application. |
| DB_HOST | X | `localhost` | Host of the database of the application. |
| DB_PASSWORD | X |  | Password of the database of the application. |
| DB_USERNAME | X | `borg` | Username of the database of the application. |
| HORIZON_DASHBOARD_ENABLED |  | `false` | Dashboard of Horizon which is used as the queue scheduler. |
| HORIZON_MEMORY_LIMIT |  | `64` MB | Memory limit a queued job may consume while its being processed. Set this to a higher value if your jobs run out of memory. |
| HORIZON_NOTIFICATION_EMAIL |  |  | If you would like to be notified when one of your queues has a long wait time, you may configure an email address here. |
| HORIZON_PROCESSES |  | `10` | Configures how many worker processes will be spawned by Horizon. Set this to any value that fits your needs best. |
| HORIZON_TIMEOUT |  | `60` seconds | Defines how long a job may take to finish. It's advisable to set this value (in seconds w/o unit) pretty high if you have a slow connection and/or huge backup repositories. |
| HORIZON_TRIES |  | `3` | Number of tries before Horizon gives up and marks the job as failed. |
| LOG_LEVEL |  | `debug` | In production environments you should consider using a higher log level like f.e. `info` or `warning`. |
| MAIL_DRIVER |  | `log` | In order to send emails you need to set this value according to your setup. Please refer to [the official documentation](https://laravel.com/docs/mail#configuration) for details. You'll also need to set all the other `MAIL_*` variables. |
| OAUTH_CLIENT_ID | X |  | The only authentication method provided is oauth. Supported providers are `github`, `google`, and `linkedin`. Please refer to the documentation of these providers to generate proper oauth credentials. |
| OAUTH_CLIENT_SECRET | X |  | Please refer to the documentation of the oauth provider to generate proper oauth credentials. |
| OAUTH_DRIVER | X | `google` | Supported providers are `github`, `google`, and `linkedin`. |
| OAUTH_REDIRECT | X |  | In order to receive the authentication token you need to set this to `http(s)://your-domain.tld/auth/callback`. The path always needs to be `/auth/callback`. |
| REDIS_HOST | X | `localhost` | Host of the redis instance of the application. |
| REDIS_PASSWORD |  | `null` | Password of the redis instance of the application. |
| SESSION_DOMAIN |  | `null` | Here you may change the domain of the cookie used to identify a session in your application. This will determine which domains the cookie is available to your application. |
| SESSION_LIFETIME |  | `120` minutes | Here you may specify the number of minutes (w/o unit) that you wish the session to be allowed to remain idle before it expires. |
| SESSION_SECURE_COOKIE |  | `null` | By setting this option to true, session cookies will only be sent back to the server if the browser has a HTTPS connection. This will keep the cookie from being sent to you if it can not be done securely. |
| TRUSTED_PROXIES |  | `null` | Set this value if the application is behind a reverse proxy. Both IPv4 and IPv6 addresses are supported, along with CIDR notation. The "*" character is syntactic sugar within TrustedProxy to trust any proxy that connects directly to your server, a requirement when you cannot know the address of your proxy (e.g. if using ELB or similar). |

## Docker Image

With every release I also release a docker image on [hub.docker.com](https://hub.docker.com/r/olafnorge/borg/tags)
which I use in my production environment in a swarm cluster. The image includes all direct application requirements
like BorgBackup, Nginx, PHP, and supervisor. All assets have been compiled already and are included as well. 

Still, you need to provide a MySQL database and a Redis server and configure the application (see above).

If you are using a Docker Swarm Cluster, or alike, my
[Ansible role for BorgUI](https://github.com/olafnorge/ansible-borg-role) and my
[Ansible playbook](https://github.com/olafnorge/ansible-playbook-swarm), I make use of to roll out the Docker image
to production, are maybe interesting for you as well.

## Contributing and Development

Thank you for considering contributing to BorgUI! A PR is always very welcome. If you can't implement a bug fix or 
behavior change on your own you may write an issue explaining the current behavior and the expected change. Please also
describe what problem would be solved after implementing the change.

I added my Homestead configuration for your convenience. Please check [Homestead.yaml](./Homestead.yaml) before you
run `vagrant up` to prevent any IP conflicts in your existing network.
I also recommend reading the [official documentation of Homestead](https://laravel.com/docs/homestead).

## Security Vulnerabilities

If you discover a security vulnerability within BorgUI, please send an e-mail to Volker Machon via
[github@olafnorge.de](mailto:github@olafnorge.de). All security vulnerabilities will be promptly addressed.

## License

BorgUI is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
