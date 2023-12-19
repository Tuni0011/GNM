<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Entity\SubCategories;
use App\Repository\SubCategoriesRepository;
use App\Repository\CategoriesRepository;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Route('/sub/categories', name: 'sub_categories_')]
class SubCategoriesController extends AbstractController
{
    
    public function index(): Response
    {
        return $this->render('sub_categories/index.html.twig', [
            'controller_name' => 'SubCategoriesController',
        ]);
    }
   
   
    #[Route('/{id}', name: 'child')]
    public function child(Categories $categories ): Response
    {
        // Get child categories
        $subcategories = $categories->getSubCategories();
        
        return $this->render('sub_categories/child.html.twig', [
            'subcategories' => $subcategories,
            
        ]);
    }
        
            
         
       
    }
    