<?php

namespace App\Controller;

use App\Form\RestPasswordFormType;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\RestPasswordRequestFormType;
use App\Service\SendMailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/restpasword', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGenerator,
        EntityManagerInterface $entityManager,
        SendMailService $mail
    
    
    
    ): Response

    
    {


        $form = $this->createForm(RestPasswordRequestFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $user = $usersRepository->findOneByEmail($form->get('email')->getdata());

           if($user){

           
                // Generate a unique token
                $token = $tokenGenerator->generateToken();
              
    
                // Save the token to the user entity (assuming you have a property like 'resetToken' in your user entity)
                $user->setResetToken($token);
                 // Get the EntityManager and persist and flush the changes to the database
                $entityManager->persist($user);
                $entityManager->flush();


                // Generate forgetten password link 
                $url = $this->generateUrl('rest_pass',['token'=>$token],
                UrlGeneratorInterface::ABSOLUTE_URL);
                // creat mail info
                $context= compact('url', 'user');

                
                // Send email
                $mail->send(
                       'no-replay@monsite.net',
                        $user->getEmail(),
                        'Rest Your Password',
                        'password_rest',
                       $context
            
            );

            $this->addFlash('success','Email already sent ');
            return $this->redirectToRoute('app_login');
                


               


           }
           $this->addFlash('danger','this email dose not exist');
           return $this->redirectToRoute('forgotten_password');


        }
        return $this->render('security/rest_password_request.html.twig',[
                        'requestPassForm'=> $form ->createView()
        ]);
    }

    #[Route(path: '/ForgetPAss/{token}', name: 'rest_pass')]
    public function restPass(
        string $token,
        Request $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasher $passwordHasher
     
    
    ): Response
    {
            //token verificatio in db
            $user = $usersRepository->findOneByRestToken($token);
            

            if($user){

                $form = $this->createForm(RestPasswordFormType::class);
                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()){
                        //delete TOKEN
                        $user->setResetToken('');
                        $user->setPassword(
                            $passwordHasher->hashPassword(
                                                $user,
                                                $form->get('password')->getData())
                                                );
                        $entityManager->persist($user);
                        $entityManager->flush();
                        $this->addFlash('success','Password Changed successfully');
                        return $this->redirectToRoute('app_login');

                }
                
                return $this->render('security/rest_password.html.twig',[
                    'PassForm'=> $form ->createView()]);


            }
            $this->addFlash('danger', 'Token is INVALID');
            return $this->redirectToRoute('app_login');








    }
}