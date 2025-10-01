## Navigation for the Mezhdurechensk city

> [!NOTE]
> Russian documentation can be found [here](https://github.com/VibroSevik/api.mezhdurechensk.itlabs.top/blob/master/README.md "Russian documentation").

> [!IMPORTANT]  
> ext-imagick with ImageMagick must be installed on the system.

- rebase .env to .env.local
- change DATABASE_URL

then execute:

```sh
composer i
```

```sh
composer u
```

```sh
php bin/console d:d:c
```

```sh
php bin/console d:s:u -f
```

```sh
php bin/console lexik:jwt:generate-keypair
```

```sh
symfony serve
```

> [!TIP]
> For run Elasticsearch execute:
> ```sh
> docker network create elastic
> ```
>
> ```sh
> docker run --name es01 -e "discovery.type=single-node" -e "xpack.security.enabled=false" -e "xpack.security.transport.ssl.enabled=false" -e "xpack.security.http.ssl.enabled=false" --net elastic -p 9200:9200 -it -m 1GB docker.elastic.co/elasticsearch/elasticsearch:8.14.1
> ```
