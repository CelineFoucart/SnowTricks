# SnowTricks

SnowTricks is a community site developed for the Openclassrooms Program PHP/Symfony. It is developed with the Symfony Framework.

## Getting Started

### Prerequisites

* PHP 8.0
* composer
* MariaDB / MySQL

### Installation

Install the project and the dependencies:

```sh
git clone https://github.com/CelineFoucart/SnowTricks.git
composer install
```

Install the starting data:

```sh
php bin/console d:m:m
php bin/console doctrine:fixtures:load
```
