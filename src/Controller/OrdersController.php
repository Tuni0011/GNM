<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/orders', name: 'orders_')]
class OrdersController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(SessionInterface $session,Request $request, ProductsRepository $productsRepository, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $panier= $session->get('panier',[]);
        if($panier === []){
            
            $referer = $request->headers->get('referer') ?: $this->generateUrl('fallback_route');

            return $this->redirect($referer);

        }
        $order = new Orders();
        $order->setUsers($this->getUser());
        $order->setReference('ORDER_' . date('YmdHis') . '_' . uniqid());




        foreach($panier as $item => $quantity){
            $orderDetails = new OrdersDetails();
            $product = $productsRepository->find($item);
            $price= $product->getPrice();

            $orderDetails->setProducts($product);
            $orderDetails->setPrice($price);
            $orderDetails->setQuantity($quantity);

            $order->addOrdersDetail($orderDetails);
         

        };
        $em->persist($order);
        $em->flush();
        $session->remove('panier');

        return $this->render('orders/index.html.twig', [
            'controller_name' => 'OrdersController',
        ]);
    }
}
