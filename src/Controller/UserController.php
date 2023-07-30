<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ContactType;
use App\Repository\PatternRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/user/contact/{id}', name: 'contact')]
    public function contactUser(User $user, Request $request, MailerInterface $mailer, EntityManagerInterface $manager, PatternRepository $repo):Response
    {
            $id = $user->getId();
            $user = $manager->getRepository(User::class)->findUserById($id);
            $contactmail = $user->getEmail();
            $utilisateur = $this->getUser();

             // mail send
             $email = (new TemplatedEmail())
             ->from('design@maurine.be')
             ->to($contactmail)
             ->subject('Contact')
             ->htmlTemplate('mails/contact.html.twig')
             ->context([
                 'utilisateur'=>$utilisateur,
                 'user'=>$user
             ]);
 
            $mailer->send($email);
 

            $this->addFlash(
                'success',
                '{user.pseudo} a bien été notifiée de votre demande de contact!'
            );

            $patterns=$repo->findLastByUser($user);

            return $this->render('user/index.html.twig', [
                'user' => $user,
                'patterns'=>$patterns
            ]);
    
    }

    /**
     * Permet d'afficher le profil d'un utilisateur et le compte de use
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
