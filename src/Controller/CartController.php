<?php
namespace App\Controller;

use App\Entity\Products;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index( SessionInterface $session,ProductsRepository $productsRepository)
    {

        $panier=$session->get('panier',[]);
        $data=[];
        $total=0;
        foreach($panier as $id => $quantity){
            $product = $productsRepository->find($id);
            $data[] =[
                'product' => $product,
                'quantity' => $quantity

            ];
            $total += $product ->getPrice() * $quantity ;

        }
           return $this->render('cart/index.html.twig',compact('data','total'));
    }


    #[Route('/add/{id}', name: 'add')]
    public function add(Products $product, SessionInterface $session,Request $request)
    {
        $id=$product->getId();
       $panier= $session->get('panier', []);
       if (empty($panier[$id])){
        $panier[$id]=0;
           $panier[$id]=1;
       } else{
           $panier[$id]++;
       }

        

        $session->set('panier',$panier);
        $referer = $request->headers->get('referer') ?: $this->generateUrl('fallback_route');

    return $this->redirect($referer);
        
    }

    #[Route('/remove/{id}', name: 'remove')]
    public function remove(Products $product, SessionInterface $session)
    {
            $id = $product->getId();
            $panier = $session->get('panier', []);

            if (!empty($panier[$id])) {
                if ($panier[$id] > 1) {
                    $panier[$id]--;
                } else {
                    unset($panier[$id]);
                }
            }

            $session->set('panier', $panier);
            return $this->redirectToRoute('cart_index');
    }
    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Products $product, SessionInterface $session)
    {
            $id = $product->getId();
            $panier = $session->get('panier', []);

            if (!empty($panier[$id])) 
                {
               
                    unset($panier[$id]);
                }
          

            $session->set('panier', $panier);
            return $this->redirectToRoute('cart_index');
    }
    #[Route('/empty', name: 'empty')]
    public function empty( SessionInterface $session)
    {
        $session->remove('panier');

        return $this->redirectToRoute('cart_index');
    }
    #[Route('/get-cart', name: 'get_cart')]
    public function getCartData(SessionInterface $session, ProductsRepository $productsRepository): JsonResponse
    {
        $panier = $session->get('panier', []);
        $data = [];
        foreach ($panier as $id => $quantity) {
            $product = $productsRepository->find($id);
            $data[] = [
                'id' => $id,
                'quantity' => $quantity,
            ];
        }

        return $this->json($data);
    }
}