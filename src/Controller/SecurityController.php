<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use App\Service\VerificationMailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository              $userRepository,
        private readonly VerificationMailerService   $verificationMailer,
    )
    {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $form = $this->createForm(RegisterType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            assert($user instanceof User);

            $hashedPassword = $this->passwordHasher->hashPassword($user, $user->getPassword() ?? '');
            $this->userRepository->upgradePassword($user, $hashedPassword);

            try {
                $this->verificationMailer->send(
                    $user->getEmail() ?? '',
                    $user->getVerificationCode()
                );
            } catch (TransportExceptionInterface) {
                // FIXME: mock is not mocking
                $this->entityManager->remove($user);
                $this->entityManager->flush();
                $this->addFlash('danger', 'Could not send verification e-mail');
                return $this->redirectToRoute('app_register');
            }

            $this->addFlash('success', 'Check Your inbox for verification link');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @codeCoverageIgnore
     */
    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/verify/{code}', name: 'app_verify')]
    public function verify(string $code, Request $request): Response
    {
        $user = $this->userRepository->findOneBy(['verificationCode' => $code]);

        if ($user === null or $this->getUser() !== $user) {
            $this->addFlash('danger', 'Invalid verification code');
            return $this->redirectToRoute('app_login');
        }

        $user->verify($code);
        $this->entityManager->flush();

        $this->addFlash('success', 'Your email has been verified');
        return $this->redirectToRoute('app_login');
    }
}
