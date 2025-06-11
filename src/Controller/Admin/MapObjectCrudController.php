<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichImageField;
use App\Entity\MapObject;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MapObjectCrudController extends AbstractCrudController
{
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
                     ->setPageTitle(Crud::PAGE_EDIT, 'Изменение объекта');
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

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
                     ->onlyOnIndex();

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

        yield FormField::addRow();

        yield TextField::new('coordinateX', 'Координата X')
                     ->setColumns(3);

        yield TextField::new('coordinateY', 'Координата Y')
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