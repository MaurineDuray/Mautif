<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\User;
use App\Entity\Galery;
use App\Entity\Pattern;
use App\Form\GaleryType;
use App\Form\SearchType;
use App\Form\PatternType;
use App\Form\PatternImgType;
use App\Form\PatternUpdateType;
use App\Entity\PatternImgModify;
use App\Form\CommentType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use App\Repository\PatternRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mailer\MailerInterface;

class PatternController extends AbstractController
{
    #[Route('/signaler/{id}', name: 'signaler')]
    public function signalement(Pattern $pattern, Request $request, MailerInterface $mailer):Response
    {
            // mail send
            $email = (new TemplatedEmail())
            ->from('design@maurine.be')
            ->to('design@maurine.be')
            ->subject('Signalement')
            ->htmlTemplate('mails/signal.html.twig')
            ->context([
                'pattern'=>$pattern
            ]);
            $mailer->send($email);

        $this->addFlash(
            "success",
            "Le motif a bien été signalé !"
        );

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer);
    }

    /**
     * Permet d'afficher la page reprenant tous les motifs 
     *
     * @param PatternRepository $repo
     * @return Response
     */
    #[Route('/patterns/{page<\d+>?1}', name: 'patterns_index')]
    public function home(PatternRepository $repo, PaginationService $pagination, $page, Request $request, EntityManagerInterface $manager): Response
    {
        $searchForm = $this->createForm(SearchType::class);

        if ($searchForm->handleRequest($request)->isSubmitted() && $searchForm->isValid()){
            $criteria = $searchForm['search']->getData();
            $patterns = $manager->getRepository(Pattern::class)->findPattern($criteria);
            $user = $this->getUser();
            $likes = $manager->getRepository(Like::class)->findAll();

            return $this->render('pattern/search.html.twig', [
                'search'=>$searchForm->createView(),
                'patterns'=>$patterns,
                'criteria'=>$criteria,
                'user'=> $user,
                'likes'=>$likes
            ]);
        }else{    
            $pagination -> setEntityClass(Pattern::class)
            ->setPage($page)
            ->setLimit(12);

            $user = $this->getUser();
            $likes = $manager->getRepository(Like::class)->findAll();

            return $this->render('pattern/index.html.twig', [
                'search'=>$searchForm->createView(),
                'pagination'=> $pagination,
                'user'=> $user,
                'likes'=>$likes
            ]);
        }
    }

     /**
     * Afficher les motifs triés
     */
    #[Route('/patterns/sort', name: 'patterns_sort')]
    public function index(PatternRepository $repo, Request $request, EntityManagerInterface $manager):Response
    {

        $theme = $request->query->get('theme');
        $color = $request->query->get('color');
        $license = $request->query->get('license');

        $repository = $manager->getRepository(Pattern::class);
        $queryBuilder = $repository->createQueryBuilder('p');

        $queryBuilder
            ->orderBy("p.creationDate", "ASC");

    if ($theme) {
        $queryBuilder
            ->andWhere('p.theme = :theme')
            ->setParameter('theme', $theme);
    }

    if ($color) {
        $queryBuilder
            ->andWhere('p.dominantColor = :color')
            ->setParameter('color', $color);
    }

    if ($license) {
        $queryBuilder
            ->andWhere('p.license = :license')
            ->setParameter('license', $license);
    }

    $patterns = $queryBuilder->getQuery()->getResult();

    $user = $this->getUser();
    $likes = $manager->getRepository(Like::class)->findAll();

    return $this->render('pattern/sort.html.twig', [
        'patterns' => $patterns,
        'user' => $user,
        'likes' => $likes
    ]);

        $user = $this->getUser();
        $likes = $manager->getRepository(Like::class)->findAll();
        return $this->render('pattern/sort.html.twig', [
            'patterns' => $patterns,
            'user'=> $user,
            'likes'=>$likes
        ]);
    }

    /**
     * Permet d'ajouter un motif au site
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('pattern/add', name:"pattern_add")]
    #[IsGranted("ROLE_USER")]
    public function addPattern(Request $request, EntityManagerInterface $manager):Response
    {
        $pattern = new Pattern();

        $form = $this->createForm(PatternType::class, $pattern);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*** */
            $pattern->setIdUser($this->getUser());
            $pattern->setCreationDate(new \DateTime());

            /**Gestion de l'image de couverture */
            $file = $form['cover']->getData();
            if (!empty($file)) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII;[^A-Za-z0-9_]remove;Lower()', $originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $e->getMessage();
                }
                $pattern->setCover($newFilename);
            }

            /**Gestion des images de la galerie */

           
            

            $manager->persist($pattern);
            $manager->flush();

            /**
             * Message flash pour alerter l'utilisateur de l'état de la tâche
             */
            $this->addFlash(
                'success',
                "Le motif <strong>{$pattern->getTitle()} - {$pattern->getTheme()}</strong> a bien été enregistrée!"
            );

            return $this->redirectToRoute('pattern_show', [
                'slug' => $pattern->getSlug()
            ]);
        }

        return $this->render("pattern/addPattern.html.twig", [
            'myform' => $form->createView()
        ]);
    }

    /**
    * Permet d'afficher un motif individuel à partir du slug + ajout de commentaire
    */
    #[Route('/pattern/{slug}', name:'pattern_show')]
    public function show(string $slug, Pattern $pattern, Request $request, EntityManagerInterface $manager ):Response
    {

        $comment = New Comment();

        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /*** */
            $comment->setIdUser($this->getUser());
            $comment->setDate(new \DateTime());
            $comment->setIdPattern($pattern);

            $manager->persist($comment);
            $manager->flush();

            /**
             * Message flash pour alerter l'utilisateur de l'état de la tâche
             */
            $this->addFlash(
                'success',
                "Votre commentaire a bien été enregistré!"
            );

            return $this->redirectToRoute('pattern_show',[
                'slug' => $pattern->getSlug(),
            ]);
        }

        return $this->render('pattern/show.html.twig', [
            'pattern' => $pattern,
            'myform' => $form->createView()
        ]);
    }

     /**
     * Permet d'ajouter une photo à la galerie de résultat
     */
    #[Route('/pattern/{slug}/galery', name:'add_galery')]
    #[IsGranted("ROLE_USER")]
    public function addGalery(string $slug, Pattern $pattern, Request $request,EntityManagerInterface $manager):Response
    {
        $galery = new Galery();
        $form = $this->createForm(GaleryType::class, $galery);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){

            $file = $form['picture']->getData();
            if (!empty($file)) {
                $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII;[^A-Za-z0-9_]remove;Lower()', $originalFilename);
                $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();
                try {
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    return $e->getMessage();
                }
                $galery->setPicture($newFilename);
            }

            $galery ->setPattern($pattern)
                ->setAuthor($this->getUser());
        
            $manager->persist($galery);
            $manager->flush();

            $this->addFlash(
                'success',
                "La photo de votre inspiration a bien été enregistrée!"
            );

            return $this->redirectToRoute('pattern_show', [
                'slug' => $pattern->getSlug(),
            ]);
        }

        return $this->render('pattern/addGalery.html.twig',[
            'pattern'=> $pattern,
            'myform' => $form->createView(),
            
        ]);
    
        
    }

    /**
     * PErmet de supprimer une image de galerie 
     */
    #[Route ('admin/galery/{id}/delete', name:"delete_galery")]
    #[Security("(is_granted('ROLE_USER') and user === galery.getAuthor()) or is_granted('ROLE_ADMIN')", message:"Ce résultat ne vous appartient pas, vous ne pouvez pas le supprimer")]
    public function deleteGalery(Galery $galery, EntityManagerInterface $manager):Response
    {
        $this->addFlash(
            'success',
            "Votre image a bien été supprimée"
        );
        
       
        unlink($this->getParameter('uploads_directory').'/'.$galery->getPicture());

        $manager->remove($galery);
        $manager->flush();

        $pattern = $galery->getPattern()->getSlug();

        return $this->redirectToRoute('pattern_show',[
            'slug'=>$pattern
        ]);

    }

    /**
     * Permet d'afficher le formulaire pour éditer un motif
     */
    #[Route('/pattern/{slug}/edit', name: 'pattern_edit')]
    #[Security("(is_granted('ROLE_USER') and user === pattern.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Ce motif ne vous appartient pas, vous ne pouvez pas la modifier")]
    public function editPattern(Pattern $pattern, Request $request, EntityManagerInterface $manager):Response
    {
        $user = $this->getUser(); //récupération de l'utilisateur connecté

        $fileName = $pattern->getCover();
        if(!empty($fileName)){
            $pattern->setCover(new File($this->getParameter('uploads_directory').'/'.$pattern->getCover()));
        }

        $form = $this->createForm(PatternUpdateType::class, $pattern);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {        

            $pattern-> setCover($fileName)
                    ->setIdUser($this->getUser());
            
            $manager->persist($pattern);
            $manager->flush();

            $this->addFlash(
                'success',
                "Le motif a bien été modifié"
            );

            return $this->redirectToRoute('pattern_show',['slug'=>$pattern->getSlug()]);
           
        }

        return $this->render("pattern/editPattern.html.twig",[
            "pattern"=>$pattern,
            "myform"=>$form->createView()
        ]);

    }

     /**
     * Permet de modifier l'image du motif
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route("/pattern/{slug}/imgmodify", name:"pattern_img")]
    #[Security("(is_granted('ROLE_USER') and user === pattern.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Ce motif ne vous appartient pas, vous ne pouvez pas la modifier")]
    public function imgModify(Pattern $pattern, Request $request, EntityManagerInterface $manager): Response
    {
        $imgModify = new PatternImgModify();
        $form = $this->createForm(PatternImgType::class, $imgModify);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            // supprimer l'image dans le dossier
            if(!empty($pattern->getCover()))
            {
                unlink($this->getParameter('uploads_directory').'/'.$pattern->getCover());
            }

            $file = $form['newCover']->getData();
            if(!empty($file))
            {
                $originalFilename = pathinfo($file->getClientOriginalName(),PATHINFO_FILENAME);
                $safeFilename = transliterator_transliterate('Any-Latin;Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                $newFilename = $safeFilename."-".uniqid().".".$file->guessExtension();
                try{
                    $file->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                }catch(FileException $e)
                {
                    return $e->getMessage();
                }
                $pattern->setCover($newFilename);
            }

            $manager->persist($pattern);
            $manager->flush();

            $this->addFlash(
                'success',
                'L\'image de couverture du motif a bien été modifiée'
            );

            return $this->redirectToRoute('pattern_show', [
                'slug' => $pattern->getSlug()
            ]);

        }

        return $this->render("pattern/imgModify.html.twig",[
            'myform' => $form->createView(),
            'pattern'=>$pattern
        ]);
    }

    /**
     * Permet de valider la suppression
     */
    #[Route('/pattern/{slug}/confirmdelete', name:"confirmdelete")]
    #[Security("(is_granted('ROLE_USER') and user === pattern.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Ce motif ne vous appartient pas, vous ne pouvez pas la supprimer")]
    public function confirmDelete(Pattern $pattern, EntityManagerInterface $manager)
    {
        return $this->render('pattern/confirmdelete.html.twig', [
            'pattern'=>$pattern,
            'slug'=>$pattern->getSlug()
         ]);
    }

    /**
     * Permet de supprimer un motif
     */
    #[Route('/pattern/{slug}/delete', name:"pattern_delete")]
    #[Security("(is_granted('ROLE_USER') and user === pattern.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Ce motif ne vous appartient pas, vous ne pouvez pas la supprimer")]
    public function patternDelete(Pattern $pattern, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le motif {$pattern->getId()} a bien été supprimé"
        );

        unlink($this->getParameter('uploads_directory').'/'.$pattern->getCover());

        $comments = $pattern->getComments();
        foreach($comments as $comment){
            $manager->remove($comment);
            $manager->flush();
        }

        $images = $pattern->getGaleries();
        if($images){
            foreach($images as $image){
                unlink($this->getParameter('uploads_directory').'/'.$image->getPicture());

                $manager->remove($image);
                $manager->flush();
            }
        }         
        $manager->remove($pattern);
        $manager->flush();

        return $this->redirectToRoute("patterns_index");
    }
}
