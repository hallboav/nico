# API

#### Subir tudo em modo de desenvolvimento

```sh
docker compose -f docker-compose.dev.yaml up -d
```

## Doctrine

#### Criar o banco de desenvolvimento local

```sh
# d:d:c
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:database:create
```

#### Criar o schema do banco de desenvolvimento local

```sh
# d:s:c
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:schema:create
```

#### Deletar o banco de desenvolvimento local

```sh
# d:d:d -f
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:database:drop --force
```

#### Criar o banco de desenvolvimento local

```sh
# d:d:c
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:database:create
```

#### Validar mapeamento de entidades

```sh
# d:m:in
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:schema:validate
```

#### Executar migrations existentes

```sh
# d:mi:m
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:migrations:migrate --no-interaction
```

#### Criar migration nova após criar uma entidade nova

Crie um banco vazio e depois adicione as migrations existentes:

```sh
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:database:delete --force
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:database:create
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:migrations:migrate --no-interaction
```

E finalmente faça o diff do seu banco com o seu mapeamento (commit somente o que for necessário):

```sh
# d:mi:di
docker compose -f docker-compose.dev.yaml exec php bin/console doctrine:migrations:diff
```

## Api Platform

#### Criar entidades que também são API resources

```sh
docker compose -f docker-compose.dev.yaml exec php bin/console maker:entity --api-resource
```

## Padrão de código

#### Executar PHPStan

```sh
docker compose -f docker-compose.dev.yaml exec php vendor/bin/phpstan analyse
```

#### Validar composer.json

```sh
docker compose -f docker-compose.dev.yaml exec php composer validate --strict
```


## Testes

Os testes executam no banco de dados `POSTGRES_DB=automacao_sti_test`.

Por isso é necessário executar:

```sh
docker compose -f docker-compose.dev.yaml rm db php --force --stop --volumes
docker volume rm automacao-sti-src_db_data --force
POSTGRES_DB=automacao_sti_test docker compose -f docker-compose.dev.yaml up -d db
APP_ENV=test docker compose -f docker-compose.dev.yaml up -d php
```

#### Executar testes da API (extends ApiTestCase)

Mais informações em https://api-platform.com/docs/distribution/testing/#testing-the-api

```sh
docker compose -f docker-compose.dev.yaml exec -e XDEBUG_MODE=coverage php bin/phpunit --coverage-text
```
