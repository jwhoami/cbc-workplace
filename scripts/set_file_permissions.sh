#!/bin/bash

sudo chown -R $USER:www-data .

sudo find . \( -path ./vendor -o -path ./node_modules -o -path ./.git -o -path ./deploy \) -prune -type f -o -exec chmod 644 {} \;
sudo find . \( -path ./vendor -o -path ./node_modules -o -path ./.git -o -path ./deploy \) -prune -type d -o -exec chmod 755 {} \;

sudo find ./storage -type f -exec chmod 644 {} \;
sudo find ./storage -type d -exec chmod 775 {} \;

sudo chmod 775 ./bootstrap/cache
sudo find ./bootstrap/cache -type f -exec chmod 644 {} \;

sudo chmod -R 755 artisan
sudo find . \( -path ./vendor -o -path ./node_modules -o -path ./.git -o -path ./deploy \) -prune -type f -name "*.sh" -o -exec chmod 755 {} \;
