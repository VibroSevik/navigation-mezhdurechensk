## Навигация для города Междуреченск

> [!NOTE]
> English documentation can be found [here](https://github.com/VibroSevik/api.mezhdurechensk.itlabs.top/blob/master/README-EN.md "English documentation").

> [!IMPORTANT]  
> ext-imagick with ImageMagick must be installed on the system.

- перейти с .env на .env.local
- изменить DATABASE_URL

Затем выполнить:

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
> Для запуска Elasticsearch выполнить:
> ```sh
> docker network create elastic
> ```
> 
> ```sh
> docker run --name es01 -e "discovery.type=single-node" -e "xpack.security.enabled=false" -e "xpack.security.transport.ssl.enabled=false" -e "xpack.security.http.ssl.enabled=false" --net elastic -p 9200:9200 -it -m 1GB docker.elastic.co/elasticsearch/elasticsearch:8.14.1
> ```
