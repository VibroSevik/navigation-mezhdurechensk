<?php

namespace App\Controller\Admin;

use App\Entity\EventData;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EventDataCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return EventData::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
                     ->onlyOnIndex();

        yield TextField::new('title', 'Заголовок')
                     ->setRequired(true);

        yield FormField::addRow();

        yield TextareaField::new('description', 'Описание')
                     ->setRequired(true);
    }
}