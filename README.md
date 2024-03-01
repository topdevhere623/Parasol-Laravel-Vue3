# Dev

## Services:

**Applications  http://localhost:8100**

**Mailhog http://localhost:8111**

**Database available on localhost:8110**

## Init app

1. Install docker
2. Install make
3. Login to gitlab registry

```shell
make gitlab-reg-login
```

4. Run init command

**Linux**

```shell
make init
```

**Windows**

```shell
make init-win
```

5. Then run app

## Other commands

#### Reformat code

```shell
make csfix
```

#### Run app

```shell
make up
```

#### Stop app

```shell
make stop
```

#### Build app

```shell
make build
```

#### Artisan command

```shell
make art {command}
```

#### Container command

```shell
make exec {command}
```

#### Dev composer file

```shell
make docker-compose
```

#### Import DB

**WSL on Windows**
```shell
make db-import db={DB_name} file={path_to_sql}
```

**WSL or Linux**

```shell
make db-import-winwsl db={DB_name} file={path_to_sql}
```


#### Generate passport keys

```shell
make passport
```

#### Clear app cache

```shell
make cache
```

##### IDE helper files

```shell
make ide-helper
```
