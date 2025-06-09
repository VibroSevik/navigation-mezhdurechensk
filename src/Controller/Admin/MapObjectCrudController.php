<?php

namespace App\Controller\Admin;

use App\Entity\MapObject;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
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
    }
}