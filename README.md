## Symfony 7 EasyAdmin Api-Platform template

- rebase .env to .env.local
- change DATABASE_URL

then:

```
composer i

composer u

php bin/console d:d:c

php bin/console d:s:u -f

php bin/console lexik:jwt:generate-keypair --overwrite

symfony serve

```

#### Elasticsearch

```
docker network create elastic

docker run --name es01 -e "discovery.type=single-node" -e "xpack.security.enabled=false" -e "xpack.security.transport.ssl.enabled=false" -e "xpack.security.http.ssl.enabled=false" --net elastic -p 9200:9200 -it -m 1GB docker.elastic.co/elasticsearch/elasticsearch:8.14.1
```
