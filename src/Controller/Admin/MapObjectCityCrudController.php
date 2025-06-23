<?php

namespace App\Controller\Admin;

use App\Entity\MapObject;
use App\Entity\Resource\MapTypes;
use App\Repository\MapObjectRepository;
use App\Service\YandexUrlParser;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use Symfony\Component\Form\FormBuilderInterface;

class MapObjectCityCrudController extends AbstractMapObjectCrudController
{
    public function __construct(
        private readonly YandexUrlParser $yandexUrlParser,
        private readonly MapObjectRepository $mapObjectRepository,
    )
    {
        parent::__construct($yandexUrlParser);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
                     ->overrideTemplate('crud/new', 'admin/map_object/city_map/new_map_form.html.twig')
                     ->overrideTemplate('crud/edit', 'admin/map_object/city_map/edit_map_form.html.twig');
    }

    private function addPointsToRequest(): void
    {
        $allPoints = $this->mapObjectRepository
                     ->createQueryBuilder('p')
                     ->select('p.id, p.name, p.x, p.y, p.objectType')
                     ->where('p.mapType = :mapType')
                     ->setParameter('mapType', MapTypes::CITY)
                     ->getQuery()
                     ->getResult();
        $this->getContext()->getRequest()->attributes->set('all_points', $allPoints);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->addPointsToRequest();
        return $formBuilder;
    }

    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->addPointsToRequest();
        return $formBuilder;
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var MapObject $entityInstance */
        $entityInstance->setMapType(MapTypes::CITY->value);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder
    {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $queryBuilder
            ->andWhere('entity.mapType = :mapType')
            ->setParameter('mapType', MapTypes::CITY);
        return $queryBuilder;
    }
}