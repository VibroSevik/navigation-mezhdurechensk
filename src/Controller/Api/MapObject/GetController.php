<?php

namespace App\Controller\Api\MapObject;

use App\Entity\Resource\MapObjectTypes;
use App\Repository\MapObjectRepository;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\Term;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

#[AsController]
class GetController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'fos_elastica.finder.map_object')]
        private readonly TransformedFinder $postFinder,

        private readonly MapObjectRepository $mapObjectRepository,
    )
    {}

    public function __invoke(
        #[MapQueryParameter] ?string $name,
        #[MapQueryParameter] ?bool $hotel,
        #[MapQueryParameter] ?bool $restaurant,
        #[MapQueryParameter] ?bool $sight,
        #[MapQueryParameter] ?bool $project): JsonResponse
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (!($name || $hotel || $restaurant || $sight || $project)) {
            return $this->json($this->mapObjectRepository->findAll(), Response::HTTP_OK, [], ['groups' => ['mapObject:read']]);
        }

        $boolQuery = new BoolQuery();

        if (!empty($name)) {
            $nameQuery = new Query\Wildcard('name', $name . '*');
            $boolQuery->addMust($nameQuery);
        }

        if ($hotel) {
            $hotelQuery = new Term();
            $hotelQuery->setTerm('objectType', MapObjectTypes::HOTEL->value);
            $boolQuery->addShould($hotelQuery);
        }

        if ($restaurant) {
            $restaurantQuery = new Term();
            $restaurantQuery->setTerm('objectType', MapObjectTypes::RESTAURANT->value);
            $boolQuery->addShould($restaurantQuery);
        }

        if ($sight) {
            $sightQuery = new Term();
            $sightQuery->setTerm('objectType', MapObjectTypes::SIGHT->value);
            $boolQuery->addShould($sightQuery);
        }

        if ($project) {
            $projectQuery = new Term();
            $projectQuery->setTerm('objectType', MapObjectTypes::PROJECT->value);
            $boolQuery->addShould($projectQuery);
        }

        $finalQuery = new Query($boolQuery);
        $results = $this->postFinder->find($finalQuery);
        return $this->json(['results' => $results], Response::HTTP_OK, [], ['groups' => 'mapObject:read']);
    }
}