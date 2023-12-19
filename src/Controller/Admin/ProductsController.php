<?php

namespace App\Controller\Admin;
use App\Entity\Images;

use App\Entity\Products;
use App\Form\ProductsFormType;
use App\Service\PictureService;
use Doctrine\ORM\EntityManager;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/admin/products', name: 'admin_products_')]
class ProductsController extends AbstractController {
    
    #[Route('/', name: 'index')]
    public function index(ProductsRepository $productsRepository): Response
    {

          
        $products = $productsRepository->findAll();
        return $this->render('admin/products/index.html.twig', [
            'products' => $products,
           
        ]);
        
       
    }


    #[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $em, SluggerInterface $slugger , PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PRODUCT_ADMIN');
        $products = new Products();
        
        $productForm = $this->createForm(ProductsFormType::class, $products);

        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {

            $images = $productForm->get('images')->getData();

            foreach ($images as $image) {
                $folder = 'products';
                $fichier = $pictureService ->add($image ,$folder,300 ,300);

                $image = new Images();
                $image->setName($fichier);
                $products->addImage($image);

                
                
                $image->setProducts($products);
                $em->persist($image);
            }

            $slug=$slugger->slug($products->getName());

            $products->setSlug($slug);

            $em->persist($products);
            $em->flush();
            $this->addFlash('success', 'Product added with success');

            return $this->redirectToRoute('admin_products_index');
        }

        return $this->render('admin/products/add.html.twig',[
            'products'=> $products,

           'productForm' => $productForm->createView()
        ]);




    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Products $products,Request $request, EntityManagerInterface $em, SluggerInterface $slugger,PictureService $pictureService): Response
    {
        $this->denyAccessUnlessGranted('PRODUCT_EDIT',$products);
        
        $productForm = $this->createForm(ProductsFormType::class, $products);

        $productForm->handleRequest($request);

        if ($productForm->isSubmitted() && $productForm->isValid()) {

            $images = $productForm->get('images')->getData();

            foreach ($images as $image) 
            {
                $folder = 'products';
                $fichier = $pictureService ->add($image ,$folder,300 ,300);

                $image = new Images();
                $image->setName($fichier);
                $products->addImage($image);

                
                
                $image->setProducts($products);
                $em->persist($image);
            }
            
            $slug=$slugger->slug($products->getName());

            $products->setSlug($slug);

            $em->persist($products);
            $em->flush();
            $this->addFlash('success', 'Product edited with success');

            return $this->redirectToRoute('admin_products_index');
        }

        return $this->render('admin/products/edit.html.twig',[
            'product'=> $products,

           'productForm' => $productForm->createView()
        ]);




        return $this->render('admin/products/edit.html.twig');
    }

   
    #[Route('/delete/image/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteimg(Request $request, Images $image, EntityManagerInterface $em, PictureService $pictureService): JsonResponse
    {
   
    
        $data = json_decode($request->getContent(), true);
        if($this->isCsrfTokenValid('delete'. $image->getId(), $data['_token'])){

                $nom = $image->getName();
                if($pictureService->delete($nom, 'products', 300 , 300)){
                     // Remove the image
                    $em->remove($image);
                    $em->flush();

                    return new JsonResponse(['message' => 'Image deleted successfully'], 200);

                }
                return new JsonResponse(['error' => 'Image not found'], 404);

       
            }

        return new JsonResponse(['error' => 'Invalid request data'], 400);
    }
}