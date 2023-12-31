
## About

This project  uses Asaas Payment Gateway integrated with Laravel on version v.10. The objective
id develop a payment processing system integrated with the Asaas approval environment, 
taking into account that the customer must access a page where they will select the 
payment option between Boleto, Credit Card or Pix.


## Technologies


- [Laravel Framework](https://laravel.com/)
- [Asaas Gateway Payment](https://www.asaas.com/)
- [Guzzle HTTP Client](https://github.com/guzzle/guzzle)
- [jQuery Mask Plugin](http://igorescobar.github.io/jQuery-Mask-Plugin/docs.html)
- [Docker](https://www.docker.com)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Redis](https://redis.io/)
- [Composer](https://getcomposer.org/)
- [NPM](https://www.npmjs.com/)
- [Validator Docs - Brasil](https://github.com/geekcom/validator-docs/)
- [Módulo de linguagem pt_BR (português brasileiro) para Laravel](https://github.com/lucascudo/laravel-pt-BR-localization/)


## Requirements

Requirements to run the project:

- [Docker](https://www.docker.com)
- [Docker Compose](https://docs.docker.com/compose/install/)
- [Windows Subsystem Linux - WSL (*Optional, but highly recommended*)](https://learn.microsoft.com/pt-br/windows/wsl/install)
- [Make (Optional)](https://www.gnu.org/software/make/)


## Instalation

##### <u>*PS: Windows user's it's highly recommended execute WSL Prompt*</u>

Clone project repository from `git@github.com:juniorari/laravel-asaas-integration.git`
```
git clone git@github.com:juniorari/laravel-asaas-integration.git
cd laravel-asaas-integration/
```

If you have installed `make`, just execute:
```
make install
```
OR
```
bash .docker/install.sh
```

And you see the magic!!

---


Otherwise (windows users or if you dont have `make`), follow these steps:


```
docker-compose up --build -d
```

Execute `composer` and `npm`, inside `app` container. Enter the container:
```
$ docker-compose exec app bash
```
Then, in this order:
```
composer install
npm install
npm npm run build
php artisan migrate
```

Create and Update your `ASAAS_TOKEN` on `.env` file:
```
ASAAS_TOKEN=XXXXXXXXXXXXXXXXX
```

Execute [http://localhost:810/](http://localhost:810/)

Enjoy it!

## Executing Tests

To execute tests, just run the command:

```
make tests
```
or 
```
docker-compose exec app env composer tests
```
