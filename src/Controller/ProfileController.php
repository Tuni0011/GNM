<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\Orders;
use App\Repository\OrdersRepository;
use App\Repository\UsersRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/profile', name: 'profile_')]
class ProfileController extends AbstractController
{
    #[Route('/', name: 'user')]
    public function index(UsersRepository $userRepository, OrdersRepository $ordersRepository): Response
    {
        $user = $this->getUser();

        // Get all generations for the user
        $allGenerations = $userRepository->getAllGenerations($user);

        // Calculate the number of team members
        $teamMembersCount = array_sum(array_map('count', $allGenerations));
        $generationCounts = array_map('count', $allGenerations);

        // Fetch orders for the current user
        $orders = $user->getOrders();

        $totalConsumption = $this->calculateTotalConsumption($orders);

        $teamConsumption = $this->calculateTeamConsumption($allGenerations, $ordersRepository);

        $monthlyConsumption = $this->calculateMonthlyConsumption($ordersRepository, $user);

        $remuneration = $this->calculateUserRemuneration($user, $ordersRepository);

        // Calculate order commission using a private method
        $orderCommission = $this->calculateOrderCommission($orders);

        return $this->render('profile/index.html.twig', [
            'teamMembersCount' => $teamMembersCount,
            'generationCounts' => $generationCounts,
            'totalConsumption' => $totalConsumption,
            'teamConsumption' => $teamConsumption,
            'monthlyConsumption' => $monthlyConsumption,
            'orders' => $orders,
            'orderCommission' => $orderCommission,
            'remuneration' => $remuneration,
        ]);
    }

    private function calculateOrderCommission($orders): float
    {
        $orderCommission = 0;

        foreach ($orders as $order) {
            // Assuming there is a getTotalAmount() method in your Order entity
            $orderCommission += 0.2 * $order->getTotalAmount();
        }

        return $orderCommission;
    }

    private function calculateUserRemuneration(Users $user, OrdersRepository $ordersRepository): float
    {
        $ordersCommission = 0;

        // Get the parent
        $parent = $user->getParent();

        // If there is a parent, calculate remuneration recursively
        if ($parent) {
            // Debug statement
            dump($user ? ['id' => $user->getId(), 'email' => $user->getEmail()] : 'User is null');

            // Debug info for the parent
            dump($parent ? ['id' => $parent->getId(), 'email' => $parent->getEmail()] : 'Parent is null');

            // Calculate commission for the parent's orders
            $parentCommissionInfo = $this->calculateUserCommission($parent);
            $ordersCommission += $parentCommissionInfo['commission'];

            // Calculate commission for the current user's orders and add it to the parent
            $userCommissionInfo = $this->calculateUserCommission($user);
            $parentCommission = 0.06 * $userCommissionInfo['orderAmount']; // 0.6 times the order amount
            $ordersCommission += $parentCommission + $this->calculateUserRemuneration($parent, $ordersRepository);
        }

        return $ordersCommission;
    }

    private function calculateUserCommission(Users $user): array
    {
        // Get the user's orders
        $orders = $user->getOrders();

        $userCommission = 0;
        $totalOrderAmount = 0;

        // Calculate commission for each order
        foreach ($orders as $order) {
            // Assuming there is a getTotalAmount() method in your Order entity
            $orderAmount = $order->getTotalAmount();
            $totalOrderAmount += $orderAmount;
            $userCommission += $user->getCommission() * $orderAmount; // Use the user's commission property
        }

        return ['commission' => $userCommission, 'orderAmount' => $totalOrderAmount];
    }

    private function calculateTotalConsumption($orders): int
    {
        $totalConsumption = 0;

        foreach ($orders as $order) {
            foreach ($order->getOrdersDetails() as $orderDetail) {
                $totalConsumption += $orderDetail->getPrice();
            }
        }

        return $totalConsumption;
    }

    private function calculateTeamConsumption($allGenerations, $ordersRepository): int
    {
        $teamConsumption = 0;

        foreach ($allGenerations as $generation) {
            foreach ($generation as $teamMember) {
                $orders = $ordersRepository->findBy(['users' => $teamMember]);

                foreach ($orders as $order) {
                    foreach ($order->getOrdersDetails() as $orderDetail) {
                        $teamConsumption += $orderDetail->getPrice();
                    }
                }
            }
        }

        return $teamConsumption;
    }

    private function calculateMonthlyConsumption($ordersRepository, $user): int
    {
        // Calculate the date 30 days ago from today
        $startDate = new \DateTimeImmutable('-30 days');

        // Fetch orders for the last 30 days for the current user
        $monthlyOrders = $ordersRepository->findMonthlyOrders($user, $startDate);

        $monthlyConsumption = $this->calculateTotalConsumption($monthlyOrders);

        return $monthlyConsumption;
    }



    #[Route('/update-picture', name: 'update_picture')]
    public function updateProfilePicture(
        Request $request,
        EntityManagerInterface $entityManager,
        #[Autowire('%image_directory%')] string $photoDir
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfilePictureType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo = $form['photo']->getData();
            if ($photo) {
                $fileName = uniqid() . '.' . $photo->guessExtension();
                $photo->move($photoDir, $fileName);
                $user->setImageFileName($fileName);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Profile picture updated successfully.');
            } else {
                $this->addFlash('danger', 'Profile picture is not set.');
            }
        }

        return $this->render('profile/update_picture.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/update-profile', name: 'update_profile')]
    public function updateProfile(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(ProfileUpdateType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('profile/update_profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/link/{id}', name: 'link')]
    public function link(int $id, EntityManagerInterface $entityManager): Response
    {
        // Retrieve the user from the database based on the provided ID
        $user = $entityManager->getRepository(Users::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // You can access the user's referral code using $user->getReferralCode()
        $referralCode = $user->getReferralCode();

        $registrationLink = $this->generateUrl('app_register', ['referralCode' => $referralCode], true);

        return $this->render('profile/link.html.twig', [
            'user' => $user,
            'referralCode' => $referralCode,
            'registrationLink' => $registrationLink,
        ]);
    }
   
  


}
