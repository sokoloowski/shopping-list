<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductUnitEnum;
use App\Entity\ShoppingList;
use App\Repository\ShoppingListRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * @phpstan-ignore-next-line
 * because it is not happy with code from Symfony docs
 */
class ProductType extends AbstractType
{
    public function __construct(
        private readonly Security $security,
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('imageFile', VichImageType::class, [
                'label' => false,
                'required' => false,
                'empty_data' => null,
                'delete_label' => 'Usuń obraz',
                'download_label' => 'Pobierz obraz',
            ])
            ->add('quantity', NumberType::class)
            ->add('unit', EnumType::class, [
                'class' => ProductUnitEnum::class,
                'choice_label' => 'value',
            ])
            ->add('shoppingList', EntityType::class, [
                'class' => ShoppingList::class,
                'choice_label' => 'name',
                'query_builder' => fn(ShoppingListRepository $repository) => $repository->createQueryBuilder('l')
                    ->where('l.owner = :owner')
                    ->setParameter('owner', $this->security->getUser())
                    ->orderBy('l.name', 'ASC'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
