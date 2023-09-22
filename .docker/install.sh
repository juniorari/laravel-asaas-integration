#!/bin/bash

BLUE_BG='\n\033[2;44m\n\n'
GREEN_BG='\n\033[5;102m\n\n'
NC='\n\033[0m\n'


echo -e "${BLUE_BG}   Finishing compose containers... ${NC}"
make stop
make down

echo -e "${BLUE_BG}   Building containers... ${NC}"
make build

echo -e "${BLUE_BG}   Starting containers... ${NC}"
make up

echo -e "${BLUE_BG}   Composer Install... ${NC}"
make composer-install

echo -e "${BLUE_BG}   Execute migrations... ${NC}"
make migrate

echo -e "${GREEN_BG}   Ok, all installed! Have fun! ;-)... ${NC}"


#if [ ! -f "vendor/autoload.php" ]; then
#    echo "Instala as dependencias do projeto" && composer -vv install --no-scripts --optimize-autoloader --apcu-autoloader --no-interaction # instala as dependências do projeto
##    echo "Inicializa o Projeto" && php init --env=Development --overwrite=y # inicializa o projeto em modo de desenvolvimento
##    echo "Aplica migrations" && php yii migrate --interactive=0 # executa as migrations
#else
#    echo "Composer instalado!"
#fi
#
#exec chmod -Rf 777 /var/www/html/storage
##exec php artisan key:generate --ansi
##exec apache2-foreground # inicia o apache
