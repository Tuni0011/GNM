<?php

namespace App\Controller;

use App\Entity\Users;
use App\Service\JWTService;
use App\Service\SendMailService;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        UsersAuthenticator $authenticator,
        EntityManagerInterface $entityManager,
        SendMailService $mail,
        UsersRepository $usersRepository,
        SessionInterface $session,
        JWTService $jwt,
        #[Autowire('%image_directory%')  ] string $photoDir
    ): Response {
        $user = new Users();
        $fileName = null;
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($photo =   $form[ 'photo']->getData()){
                $fileName = uniqid().'.'.$photo->guessExtension();
                $photo->move($photoDir,$fileName);
            }
           
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setImageFileName($fileName);
              // Generate and set the referral code
              $referralCode = substr(md5(uniqid()), 0, 8); // Example: generate a random 8-character referral code
              $user->setReferralCode($referralCode);
  

            $entityManager->persist($user);
            $entityManager->flush();
             // Check if there is a referral code in the URL
             $referralCodeFromUrl = $request->query->get('referralCode');
             if ($referralCodeFromUrl) {
                 // Find the user with the given referral code
                 $referrer = $usersRepository->findOneBy(['referralCode' => $referralCodeFromUrl]);
 
                 if ($referrer) {
                     // Set the referrer for the current user
                     $user->setParent($referrer);
 
                     // Add the new user to the referrer's children collection
                     $referrer->addChild($user);
 
                     // Update the referrer in the database
                     $entityManager->flush();
 
                     // Store the referral information in the session for future use (optional)
                     $session->set('referrer_id', $referrer->getId());
                 }
             }

            // Generate JWT token for user activation
            $header = ['alg' => 'HS256', 'typ' => 'JWT'];
            $payload = ['id' => $user->getId()];
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            // send mail with activation token
            $mail->send(
                'no-replay@monsite.net',
                $user->getEmail(),
                'Activation of Your account',
                'register',
                compact('user', 'token')
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/{token}', name: 'verify_user')]
    public function verifyuser(
        string $token,
        JWTService $jwt,
        UsersRepository $usersRepository,
        EntityManagerInterface $em
    ): Response {
        // Check if token is valid, not expired, and signature matches
        if (
            $jwt->isValid($token) &&
            !$jwt->isExpired($token) &&
            $jwt->check($token, $this->getParameter('app.jwtsecret'))
        ) {
            $payload = $jwt->getPayload($token);

            // Find user by ID from payload
            $user = $usersRepository->find($payload['id']);

            if ($user && !$user->getIsVerified()) {
                // Set user as verified and update in the database
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'User Verified');
                return $this->redirectToRoute('app_main');
            }
        }

        // Redirect if token is expired or verification fails
        $this->addFlash('danger', 'Token is Expired or Verification Failed');
        return $this->redirectToRoute('app_login');
    }

    
   #[Route('resendverification', name: 'resend_verification')]
public function resendVerificationEmail(UsersRepository $usersRepository,
    Users $user,
    SendMailService $mail,
    JWTService $jwt,
    EntityManagerInterface $em
): Response {
 
    $user = $this->getUser();

    if(!$user){  
        $this->addFlash('danger', 'You have to login into your acount ');
        return $this->redirectToRoute('app_login');
    }
    // Check if the user is not verified
    if ($user->getIsVerified()) {
        $this->addFlash('danger', 'User is already verified.');
   
}



        // Generate a new JWT token for user activation
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = ['id' => $user->getId()];
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        // Send mail with the new activation token
        $mail->send(
            'no-replay@monsite.net',
            $user->getEmail(),
            'Activation of Your account',
            'register',
            compact('user', 'token')
        );

        $this->addFlash('success', 'Verification email resent successfully.');
    
    return  $this->redirectToRoute('app_main');
} 
    
}


