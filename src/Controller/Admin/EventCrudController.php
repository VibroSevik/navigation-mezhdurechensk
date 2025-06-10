<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichImageField;
use App\Entity\Event;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
                     ->setEntityPermission('ROLE_ADMIN')
                     ->setEntityLabelInPlural('Новости и события')
                     ->setEntityLabelInSingular('Событие')
                     ->setPageTitle(Crud::PAGE_NEW, 'Добавление события')
                     ->setPageTitle(Crud::PAGE_EDIT, 'Изменение события');
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
                     ->setColumns(4);

        yield FormField::addRow();

        yield TextField::new('date', 'Время проведения')
                     ->setColumns(4);

        yield FormField::addRow();

        yield TextareaField::new('shortDescription', 'Короткое описание')
                     ->onlyOnForms()
                     ->setColumns(4);

        yield TextEditorField::new('shortDescription', 'Короткое описание')
                     ->onlyOnIndex();

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

        yield FormField::addRow();

        yield $image;

        yield VichImageField::new('image', 'Изображение')
                     ->hideOnForm();

        yield FormField::addRow();

        yield CollectionField::new('eventData', 'Текстовые данные')
                     ->onlyOnForms()
                     ->useEntryCrudForm(EventDataCrudController::class);

        yield TextEditorField::new('eventDataToString', 'Текстовые данные')
                     ->onlyOnIndex();

        yield DateTimeField::new('createdAt', 'Создано')
                     ->hideOnForm();
    }
}