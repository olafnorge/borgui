#!/bin/sh

# If you would like to do some extra provisioning you may
# add any commands you wish to this file and they will
# be run after the Homestead machine is provisioned.
#
# If you have user-specific configurations you would like
# to apply, you may also create user-customizations.sh,
# which will be run after this script.

# install composer dependencies
echo "INSTALL COMPOSER DEPENDENCIES"
composer install \
    --no-autoloader \
    --no-scripts \
    --no-progress \
    --no-suggest \
    --no-ansi \
    --no-interaction \
    --no-plugins \
    --working-dir=/home/vagrant/code

# install borg
echo "INSTALL BORG"
sudo apt-get update -y --allow-releaseinfo-change
sudo apt-get install -y \
    -o Dpkg::Options::="--force-confdef" \
    -o Dpkg::Options::="--force-confold" \
    --no-install-recommends \
    libacl1-dev \
    libpython3.6-dev \
    python3-llfuse \
    python3-pip \
    python3-setuptools \
    python3-wheel
sudo -H pip3 install borgbackup[fuse]==$(php -r "echo (require('/home/vagrant/code/config/borg.php'))['version'];")
sudo apt-get autoremove --purge -y \
    -o Dpkg::Options::="--force-confdef" \
    -o Dpkg::Options::="--force-confold" \
    libacl1-dev \
    libpython3.6-dev \
    python3-pip \
    python3-setuptools \
    python3-wheel

# setup app
echo "SETUP HORIZON"
[ -f /home/vagrant/code/.env ] || cp /home/vagrant/code/.env.example /home/vagrant/code/.env
(cd /home/vagrant/code && /home/vagrant/code/artisan horizon:assets --no-ansi --no-interaction)
(cd /home/vagrant/code && /home/vagrant/code/artisan migrate --no-ansi --no-interaction)
composer dump-autoload --no-ansi --no-interaction --working-dir=/home/vagrant/code
echo "INSTALL YARN DEPENDENCIES"
(cd /home/vagrant/code && yarn install \
        --ignore-optional \
        --no-progress \
        --non-interactive)
echo "BUILD ASSETS"
(cd /home/vagrant/code && npm run development -- --color=false --display=minimal --no-progress --bail)

# setup and run horizon
echo "SETUP SUPERVISOR"
sudo tee /etc/supervisor/conf.d/horizon.conf << EOF
[program:horizon]
process_name=%(program_name)s
command=php /home/vagrant/code/artisan horizon
autostart=true
autorestart=true
user=vagrant
redirect_stderr=true
stdout_logfile=/home/vagrant/code/storage/logs/horizon.log
EOF
sudo supervisorctl reread
sudo supervisorctl add horizon
sudo supervisorctl status

# setup style guide
#if [ -f /home/vagrant/code/node_modules/@coreui/coreui-free-bootstrap-admin-template/package.json ]; then
#    echo "SETUP STYLEGUIDE 1"
#    (cd /home/vagrant/code/node_modules/@coreui/coreui-free-bootstrap-admin-template && npm install \
#            --ignore-scripts \
#            --no-audit \
#            --color=false \
#            --display=none \
#            --no-progress)
#    echo "SETUP STYLEGUIDE 2"
#    (cd /home/vagrant/code/node_modules/@coreui/coreui-free-bootstrap-admin-template && npm run build -- \
#            --color=false \
#            --display=none)
#
#    # link into public so that we have it available
#    [ ! -f /home/vagrant/code/public/styleguide ] || rm /home/vagrant/code/public/styleguide;
#    [ ! -L /home/vagrant/code/public/styleguide ] || rm /home/vagrant/code/public/styleguide;
#    (cd /home/vagrant/code/public && ln -sf ../node_modules/@coreui/coreui-free-bootstrap-admin-template/dist styleguide)
#fi
