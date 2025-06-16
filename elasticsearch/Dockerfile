# Используем официальный образ Elasticsearch 8.14.1 от Elastic
FROM docker.elastic.co/elasticsearch/elasticsearch:8.14.1

# Устанавливаем переменные окружения для Elasticsearch
ENV discovery.type=single-node
ENV xpack.security.enabled=false
ENV xpack.security.transport.ssl.enabled=false
ENV xpack.security.http.ssl.enabled=false

# Определяем рабочую директорию (необязательно, но может быть полезно)
WORKDIR /usr/share/elasticsearch

# Открываем порт 9200 для взаимодействия с Elasticsearch
EXPOSE 9200

# Команда по умолчанию при запуске контейнера
CMD ["elasticsearch"]

# Переменные окружения могут быть переопределены при запуске контейнера с помощью параметров командной строки Docker
