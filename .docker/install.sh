#!/bin/bash

BLUE_BG='\n\033[2;44m\n\n'
GREEN_BG='\n\033[5;102m\n\n'
NC='\n\033[0m\n'

set -e

echo -e "${BLUE_BG}   Finishing compose containers... ${NC}"
make stop
make down

echo -e "${BLUE_BG}   Building containers... ${NC}"
make build

echo -e "${BLUE_BG}   Starting containers... ${NC}"
make up

echo -e "${BLUE_BG}   Composer Install and NPM... ${NC}"
make composer-install
make npm
make npm-build

echo -e "${BLUE_BG}   Execute migrations... ${NC}"
make migrate

echo -e "${GREEN_BG}   Ok, all installed! Have fun! ;-)... ${NC}"
