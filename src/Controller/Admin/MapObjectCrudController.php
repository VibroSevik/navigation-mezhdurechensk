<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichImageField;
use App\Entity\MapObject;
use App\Repository\MapObjectRepository;
use App\Service\YandexUrlParser;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\FormBuilderInterface;

class MapObjectCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly YandexUrlParser $yandexUrlParser,
        private readonly MapObjectRepository $mapObjectRepository,
    )
    {}

    public static function getEntityFqcn(): string
    {
        return MapObject::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
                     ->setEntityPermission('ROLE_ADMIN')
                     ->setEntityLabelInPlural('Объекты на карте')
                     ->setEntityLabelInSingular('Объект на карте')
                     ->setPageTitle(Crud::PAGE_NEW, 'Добавление объекта')
                     ->setPageTitle(Crud::PAGE_EDIT, 'Изменение объекта')
                     ->overrideTemplate('crud/new', 'admin/map_object/new_map_form.html.twig')
                     ->overrideTemplate('crud/edit', 'admin/map_object/edit_map_form.html.twig');
    }

    private function addPointsToRequest(): void
    {
        $allPoints = $this->mapObjectRepository
                     ->createQueryBuilder('p')
                     ->select('p.id, p.name, p.x, p.y, p.objectType')
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

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
                     ->add(Crud::PAGE_INDEX, Action::DETAIL)
                     ->setPermissions([
                         Action::NEW => 'ROLE_ADMIN',
                         Action::DELETE => 'ROLE_ADMIN',
                         Action::EDIT => 'ROLE_ADMIN',
                     ]);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var MapObject $entityInstance */
        $url = $entityInstance->getMapUrl();
        [$latitude, $longitude] = $this->yandexUrlParser->parseCoordinates($url);
        $entityInstance->setLatitude($latitude);
        $entityInstance->setLongitude($longitude);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var MapObject $entityInstance */
        $url = $entityInstance->getMapUrl();
        [$latitude, $longitude] = $this->yandexUrlParser->parseCoordinates($url);
        $entityInstance->setLatitude($latitude);
        $entityInstance->setLongitude($longitude);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
                     ->onlyOnIndex();

        yield FormField::addTab('Данные об объекте');

        yield TextField::new('name', 'Название')
                     ->setColumns(3);

        yield TextField::new('title', 'Заголовок')
                     ->setColumns(3);

        yield FormField::addRow();

        yield TextEditorField::new('description', 'Описание')
                     ->setColumns(6);

        yield FormField::addRow();

        yield TextField::new('phone', 'Телефон')
                     ->setColumns(2);

        yield TextField::new('address', 'Адрес')
                     ->setColumns(2);

        yield TextField::new('email', 'Почта')
                     ->setColumns(2);

        yield FormField::addRow();

        yield TextEditorField::new('openingHours', 'Режим работы')
                     ->setColumns(6);

        yield FormField::addTab('Отметка на карте');

        yield ChoiceField::new('objectType', 'Тип объекта')
                     ->setChoices(array_flip(MapObject::TYPES))
                     ->setColumns(6);

        yield FormField::addRow();

        yield TextField::new('mapUrl', 'Ссылка на объект/здание на яндекс карте')
                     ->onlyOnForms()
                     ->setColumns(6);

        yield TextField::new('latitude', 'Широта')
                     ->onlyOnIndex()
                     ->setColumns(3);

        yield TextField::new('longitude', 'Долгота')
                     ->onlyOnIndex()
                     ->setColumns(3);

        yield FormField::addRow();

        yield TextField::new('x', 'Координата X (только для чтения)')
                     ->setFormTypeOption('attr', ['readonly' => true])
                     ->onlyOnForms()
                     ->setColumns(3);

        yield TextField::new('y', 'Координата Y (только для чтения)')
                     ->setFormTypeOption('attr', ['readonly' => true])
                     ->onlyOnForms()
                     ->setColumns(3);

        yield FormField::addRow();

        $image = VichImageField::new('imageFile', 'Изображение')
                     ->setHelp('
                         <div class="mt-3">
                             <span class="badge badge-info">*.jpg</span>
                             <span class="badge badge-info">*.jpeg</span>
                             <span class="badge badge-info">*.png</span>
                             <span class="badge badge-info">*.webp</span>
                         </div>
                     ')
                     ->onlyOnForms()
                     ->setFormTypeOption('allow_delete', true)
                     ->setRequired(true)
                     ->setColumns(2);

        if (Crud::PAGE_EDIT == $pageName) {
            $image->setRequired(false);
        }

        yield $image;
        yield VichImageField::new('image', 'Изображение')
                     ->hideOnForm();

        yield DateTimeField::new('createdAt', 'Создано')
                     ->hideOnForm();
    }
}