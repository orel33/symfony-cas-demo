#!/bin/bash

# 1) installation des dépendances PHP à partir de composer.json dans vendor/
[ -f composer.json ] && composer install

# 2) installation des dépendances JS à partir de package.json dans node_modules/
[ -f package.json ] &&  npm install

# EOF