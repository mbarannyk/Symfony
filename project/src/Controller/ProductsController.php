<?php

namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductsController extends AbstractController
{
    private ProductsRepository $ProductsRepository;

    public function __construct(ProductsRepository $ProductsRepository)
    {
        $this->ProductsRepository = $ProductsRepository;
    }

    #[Route('/main', name: 'list_product', methods: ['GET'])]
    public function ProductPage(): Response
    {
        $products = $this->ProductsRepository->findAll();
        return $this->render('main.html.twig', ['products' => $products]);
    }

    #[Route('/main/add', name: 'add_Product', methods: ['GET', 'POST'])]
    public function addProduct(Request $request, ManagerRegistry $registry): Response
    {
        $product = new Products();
        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('price', TextType::class)
            ->add('season', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Products'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();
            $entityManager = $registry->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('list_product');
        }

        return $this->render('addProduct.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/main/edit/{id}', name: 'edit_product', methods: ['GET','POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Products::class)->find($id);
        $form = $this->createFormBuilder($product)
            ->add('name', TextType::class)
            ->add('price', TextType::class)
            ->add('season', TextType::class)
            ->add('save', SubmitType::class, ['label' => 'Create Products'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $product = $form->getData();

            $entityManager = $doctrine->getManager();
            $entityManager->persist($product);
            $entityManager->flush();
            return $this->redirectToRoute('list_product', ['id' => $product->getId()]);
        }
        return $this->render('editProduct.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/main/del/{id}', name: 'del_product', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function del(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Products::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'Id ' . $id . ' not found in the base Products'
            );
        }
        $entityManager->remove($product);
        $entityManager->flush();

        return $this->redirectToRoute('list_product');
    }

    #[Route('/main/display/{id}', name: 'display_product', methods: ['GET','POST'], requirements: ['id' => '\d+'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $product = $doctrine->getRepository(Products::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException(
                'Id '. $id . 'doesn`t exist in the base'
            );
        }
         return $this->render('displayProduct.html.twig', ['product' => $product]);
    }
}