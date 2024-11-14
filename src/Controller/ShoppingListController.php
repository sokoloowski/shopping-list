<?php

namespace App\Controller;

use App\Entity\ShoppingList;
use App\Entity\User;
use App\Form\DeleteType;
use App\Form\ShoppingListType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/list', name: 'app_list_')]
class ShoppingListController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/new', name: 'create')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(ShoppingListType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $list = $form->getData();

            assert($list instanceof ShoppingList);
            assert($this->getUser() instanceof User);

            $list->setOwner($this->getUser());

            $this->entityManager->persist($list);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_list_read', [
                'list' => $list->getId(),
            ]);
        }

        return $this->render('shopping_list/update.html.twig', [
            'form' => $form,
            'action' => 'create'
        ]);
    }

    #[Route('/{list}', name: 'read')]
    public function read(ShoppingList $list): Response
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('shopping_list/details.html.twig', [
            'list' => $list,
        ]);
    }

    #[Route('/{list}/edit', name: 'update')]
    public function update(ShoppingList $list, Request $request): Response
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(ShoppingListType::class, $list);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('app_list_read', [
                'list' => $list->getId(),
            ]);
        }

        return $this->render('shopping_list/update.html.twig', [
            'form' => $form,
            'action' => 'update'
        ]);
    }

    #[Route('/{list}/delete', name: 'delete')]
    public function delete(ShoppingList $list, Request $request): Response
    {
        if ($list->getOwner() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->createForm(DeleteType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($list);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_overview');
        }

        return $this->render('shopping_list/delete.html.twig', [
            'list' => $list,
            'form' => $form,
        ]);
    }
}
