<?php

namespace App\Controller\Admin;

use App\Entity\Recipe;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class RecipeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Recettes')
            ->setEntityLabelInSingular('Recette')
            ->setPageTitle('index', 'Nutridiet - administration des utilisateurs')
            ->setPaginatorPageSize(10);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')
                ->hideOnForm(),
            TextField::new('name'),
            MoneyField::new('price')->setCurrency('EUR'),
            IntegerField::new('nbPeople'),
            ChoiceField::new('difficulty')->renderExpanded()->setChoices([
                '1 très facile' => 1,
                '2 facile' => 2,
                '3 moyenne' => 3,
                '4 dure' => 4,
                '5 très dure' => 5,
            ]),
            TextareaField::new('description')
                ->setFormType(CKEditorType::class)
                ->hideOnIndex(),
            BooleanField::new('isFavorite')->renderAsSwitch(false),
            BooleanField::new('isPublic')->renderAsSwitch(false),
            DateTimeField::new('createdAt')
                ->hideOnForm()
        ];
    }
}
