## Automação STI

#### Subindo a aplicação em desenvolvimento

Suba todos os containers:

```sh
docker compose -f docker-compose.dev.yaml up -d
```

Espere alguns segundos até o as dependências do PHP de desenvolvimento serem instaladas:

```sh
docker compose -f docker-compose.dev.yaml logs -f php
```

E finalmente execute:

```sh
docker compose -f docker-compose.dev.yaml exec php bin/console hautelook:fixtures:load -n
```

#### OpenAPI (Swagger UI)

http://localhost:8880/docs


#### Como obter o token de autenticação

```sh
curl localhost:8880/login -H "content-type:application/json" -d '{"username":"admin","password":"password"}'
```
