#!/bin/bash

sudo chown -R $USER:www-data .

sudo find . \( -path ./vendor -o -path ./node_modules -o -path ./.git -o -path ./deploy \) -prune -o -type f -exec chmod 644 {} \;
sudo find . \( -path ./vendor -o -path ./node_modules -o -path ./.git -o -path ./deploy \) -prune -o -type d -exec chmod 755 {} \;

sudo find ./storage -type d -exec chmod 775 {} \;
sudo find ./storage -type f -exec chmod 664 {} \;

sudo chmod 775 ./bootstrap/cache
sudo find ./bootstrap/cache -type f -exec chmod 664 {} \;

sudo chmod 755 artisan
sudo find . \( -path ./vendor -o -path ./node_modules -o -path ./.git -o -path ./deploy \) -prune -o -type f -name "*.sh" -exec chmod 755 {} \;
