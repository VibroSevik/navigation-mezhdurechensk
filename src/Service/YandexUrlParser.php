<?php

namespace App\Service;

/**
 * Class <strong>YandexUrlParser</strong> parses longitude and latitude from YandexMap url.
 * Can parse buildings and objects (restaurants, cafés, sights).
 */
class YandexUrlParser
{
    /**
     * Receives url and parse longitude and latitude. <br>
     * Parsing gor buildings and objects a little different.
     * @param string $url yandex map url.
     * @return array [longitude, latitude].
     */
    public function parseCoordinates(string $url): array
    {
        // @todo: delete after mapUrl field into MapObject entity will be non-nullable
        if (!$url) {
            return [null, null];
        }
        return $this->isMapBuilding($url) ?
            $this->parseMapBuildingCoordinates($url) : $this->parseMapObjectCoordinates($url);
    }

    private function isMapBuilding(string $url): bool
    {
        return !str_contains($url, 'point');
    }

    /**
     * Receives url and parse longitude and latitude as <strong>poi%5Bpoint%5D</strong> query parameter. <br>
     * Url {@link https://yandex.ru/maps/2/saint-petersburg/?ll=30.290424%2C59.944677&mode=poi&poi%5Bpoint%5D=30.289923%2C59.944856&poi%5Buri%5D=ymapsbm1%3A%2F%2Forg%3Foid%3D14216272245&z=19.24 example} <br>
     * Url structure: .../maps/2/<city>/?<other_params>&poi%5Bpoint%5D=30.289923%2C59.944856&<other_params>
     * @param string $url yandex map object.
     */
    private function parseMapObjectCoordinates(string $url): array
    {
        $longitudeAndLatitude = explode('&', explode('point%5D=', $url)[1])[0];
        return explode('%2C', $longitudeAndLatitude);
    }

    /**
     * Receives url and parse longitude and latitude as ll query parameter. <br>
     * Url {@link https://yandex.ru/maps/2/saint-petersburg/house/volkhovskiy_pereulok_6/Z0kYdA5gSEIBQFtjfXV1dXRmbQ==/?ll=30.290424%2C59.944677&z=19.24 example} <br>
     * Url structure: .../maps/2/<city>/house/<street>_<number>/<building_uuid>/?ll=30.290424%2C59.944677&z=19.24
     * @param string $url yandex map building.
     */
    private function parseMapBuildingCoordinates(string $url): array
    {
        $longitudeAndLatitude = explode('&', explode('ll=', $url)[1])[0];
        return explode('%2C', $longitudeAndLatitude);
    }
}