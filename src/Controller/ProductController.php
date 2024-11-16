<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\ShoppingList;
use App\Form\DeleteType;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/list/{list}/product', name: 'app_product_')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/new', name: 'create')]
    public function create(ShoppingList $list, Request $request): Response
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProductType::class);
        $form->remove('shoppingList');

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Product $product */
            $product = $form->getData();

            $product->setShoppingList($list);

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_list_read', [
                'list' => $list->getId()
            ]);
        }

        return $this->render('product/update.html.twig', [
            'list' => $list,
            'form' => $form,
        ]);
    }

    #[Route('/{product}/edit', name: 'update')]
    public function update(ShoppingList $list, Product $product, Request $request): Response
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_list_read', [
                'list' => $list->getId()
            ]);
        }

        return $this->render('product/update.html.twig', [
            'list' => $list,
            'form' => $form,
        ]);
    }

    #[Route('/{product}/delete', name: 'delete')]
    public function delete(ShoppingList $list, Product $product, Request $request): Response
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(DeleteType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($product);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_list_read', [
                'list' => $list->getId()
            ]);
        }

        return $this->render('product/delete.html.twig', [
            'list' => $list,
            'form' => $form,
        ]);
    }

    #[Route('/{product}/toggle', name: 'toggle')]
    public function toggle(ShoppingList $list, Product $product): Response
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $product->toggleRealisation();
        $this->entityManager->flush();

        return $this->redirectToRoute('app_list_read', [
            'list' => $list->getId()
        ]);
    }
}
