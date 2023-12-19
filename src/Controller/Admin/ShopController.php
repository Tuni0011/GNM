<?php

namespace App\Controller\Admin;


use App\Entity\Shop;
use App\Form\ShopFormType;
use App\Service\PictureService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/shop', name: 'admin_shop_')]
class ShopController extends AbstractController {
    
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('admin/shop/index.html.twig');
    }


    #[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, #[Autowire('%shop_directory%')] string $photoDir, SluggerInterface $slugger): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRODUCT_ADMIN');
        $shop = new Shop(); // Note: Use "new Shop()" to create a new Shop entity
    
        $shopForm = $this->createForm(ShopFormType::class, $shop);
    
        $shopForm->handleRequest($request);
    
        if ($shopForm->isSubmitted() && $shopForm->isValid()) {
            // Get the uploaded photo from the form
            $photo = $shopForm['photo']->getData();
    
            // Check if a new photo was uploaded
            if ($photo) {
                // Generate a unique filename and move the uploaded file
                $fileName = uniqid().'.'.$photo->guessExtension();
                $photo->move($photoDir, $fileName);
    
                // Set the new image filename on the Shop entity
                $shop->setImageFileNameShop($fileName);
    
                $this->addFlash('success', 'Shop picture updated successfully.');
            } else {
                $this->addFlash('danger', 'Shop picture is not set.');
            }
    
            // Persist and flush the entity
            $em->persist($shop);
            $em->flush();
    
            $this->addFlash('success', 'Product added with success');
    
            return $this->redirectToRoute('admin_shop_index');
        }
    
        return $this->render('admin/shop/add.html.twig', [
            'shop' => $shop,
            'shopForm' => $shopForm->createView(),
        ]);
    }
    




   

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Shop $shop, Request $request, EntityManagerInterface $em, #[Autowire('%image_directory%')] string $photoDir, SluggerInterface $slugger): Response
    {
        $shopForm = $this->createForm(ShopFormType::class, $shop);
    
        $shopForm->handleRequest($request);
    
        if ($shopForm->isSubmitted() && $shopForm->isValid()) {
            // Get the uploaded photo from the form
            $photo = $shopForm['photo']->getData();
            $fileName= null;
            // Check if a new photo was uploaded
            if ($photo) {
                // Generate a unique filename and move the uploaded file
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $photo->move($photoDir, $fileName);
    
                // Set the new image filename on the Shop entity
                $shop->setImageFileNameShop($fileName);
    
                $this->addFlash('success', 'Shop picture updated successfully.');
            } else {
                $this->addFlash('danger', 'Shop picture is not set.');
            }
    
            // Persist and flush the entity
            $em->persist($shop);
            $em->flush();
    
            $this->addFlash('success', 'Shop edited with success');
    
            return $this->redirectToRoute('admin_shop_index');
        }
    
        return $this->render('admin/shop/edit.html.twig', [
            'shop' => $shop,
            'shopForm' => $shopForm->createView(),
        ]);
    }
    
   
}