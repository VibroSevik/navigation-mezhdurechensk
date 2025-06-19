<?php

namespace App\Controller\Admin;

use App\Controller\Admin\Field\VichFileField;
use App\Entity\MainWindow;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

class MainWindowCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MainWindow::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
                     ->setEntityPermission('ROLE_ADMIN')
                     ->setEntityLabelInPlural('Медиа главного экрана')
                     ->setEntityLabelInSingular('Медиа главного экрана')
                     ->setPageTitle(Crud::PAGE_NEW, 'Добавление медиа')
                     ->setPageTitle(Crud::PAGE_EDIT, 'Изменение медиа');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
                     ->onlyOnIndex();

        $media = VichFileField::new('mediaFile', 'Медиа')
                     ->setHelp('
                         <div class="mt-3">
                             <span class="badge badge-info">*.jpg</span>
                             <span class="badge badge-info">*.jpeg</span>
                             <span class="badge badge-info">*.png</span>
                             <span class="badge badge-info">*.webp</span>
                             <span class="badge badge-info">*.mp4</span>
                             <span class="badge badge-info">*.webm</span>
                         </div>
                     ')
                     ->onlyOnForms()
                     ->setFormTypeOption('allow_delete', true)
                     ->setRequired(true)
                     ->setColumns(2);

        if (Crud::PAGE_EDIT == $pageName) {
            $media->setRequired(false);
        }

        yield $media;
        yield VichFileField::new('media', 'Медиа')
                     ->hideOnForm();

        yield DateTimeField::new('createdAt', 'Создано')
                     ->hideOnForm();
    }
}