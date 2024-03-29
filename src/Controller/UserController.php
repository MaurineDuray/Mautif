<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ContactType;
use App\Repository\UserRepository;
use App\Repository\PatternRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * Route qui permet de notifier un auteur d'une demande de contact par l'utilisateur connecté
     */
    #[Route('/user/contact/{id}', name: 'contact')]
    #[Security("(is_granted('ROLE_USER')) or is_granted('ROLE_ADMIN')", message:"Vous devez être connecté pour pouvoir effectuer cette action")]
    public function contactUser(User $user, Request $request, MailerInterface $mailer, EntityManagerInterface $manager, PatternRepository $repo, UserInterface $utilisateur):Response
    {
            $id = $user->getId();
            $user = $manager->getRepository(User::class)->findUserById($id);
            $contactmail = $user->getEmail();

             // mail send
             $email = (new TemplatedEmail())
             ->from('design@maurine.be')
             ->to($contactmail)
             ->subject('Contact')
             ->htmlTemplate('mails/contact.html.twig')
             ->context([
                'utilisateur' => $utilisateur,
                'user'=>$user
             ]);
            $mailer->send($email);
            
            $this->addFlash(
                'success',
                'L\'utilisateur a bien été notifiée de votre demande de contact!'
            );

            $patterns=$repo->findLastByUser($user);

            return $this->render('user/index.html.twig', [
                'user' => $user,
                'patterns'=>$patterns
            ]);
    
    }

    /**
     * Permet d'afficher le profil d'un utilisateur et le compte de user
     */
    #[Route('/user/{slug}', name: 'user_profile')]
    public function index(User $user, PatternRepository $repo): Response
    {
        
        $patterns=$repo->findLastByUser($user);
       

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'patterns'=>$patterns
        ]);
    }

   
    
}
