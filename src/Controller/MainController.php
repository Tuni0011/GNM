<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use App\Repository\ShopRepository;
use App\Repository\SubCategoriesRepository;
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(CategoriesRepository $categoriesRepository, ShopRepository $shopRepository,ProductsRepository $productsRepository): Response
    {
        $shops = $shopRepository->findall();
        $categories = $categoriesRepository->findAll();
        $products = $productsRepository->findAll();

        return $this->render('main/index.html.twig', [
            'shops' => $shops,
            'categories' => $categories,
            'products' => $products,
        ]);
    }
    #[Route('/Shop/{id}', name: 'app_shop')]
    public function shop(
        CategoriesRepository $categoriesRepository,
        Shop $shop,
        ProductsRepository $productsRepository,
        SubCategoriesRepository $subCategoriesRepository
    ): Response {
        $categories = $categoriesRepository->findAll();
        $subCategories = $subCategoriesRepository->findAll();
        $products = $productsRepository->findBy(['shop' => $shop]);
    
        return $this->render('main/shop.html.twig', [
            'shop' => $shop,  // Use the key 'shop' instead of 'shops'
            'categories' => $categories,
            'products' => $products,
            'subCategories' => $subCategories,
        ]);
    }
}

