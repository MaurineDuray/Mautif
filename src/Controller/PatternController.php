<?php

namespace App\Controller;

use App\Entity\Like;
use App\Entity\User;
use App\Entity\Pattern;
use App\Form\PatternType;
use App\Repository\UserRepository;
use App\Service\PaginationService;
use App\Repository\PatternRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PatternController extends AbstractController
{

    /**
     * Permet d'afficher la page reprenant tous les motifs 
     *
     * @param PatternRepository $repo
     * @return Response
     */
    #[Route('/patterns/{page<\d+>?1}', name: 'patterns_index')]
    public function index(PatternRepository $repo, PaginationService $pagination, $page, Request $request, EntityManagerInterface $manager): Response
    {
        $pagination -> setEntityClass(Pattern::class)
        ->setPage($page)
        ->setLimit(12);

        $user = $this->getUser();
        $likes = $manager->getRepository(Like::class)->findAll();

        return $this->render('pattern/index.html.twig', [
           'pagination'=> $pagination,
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

            foreach ($pattern->getImages() as $picture) {

                $picture->setIdPattern($pattern);
                $manager->persist($picture);
            }
            /*** */
            $pattern->setIdUser($this->getUser());
            $pattern->setCreationDate(new \DateTime());

            $manager->persist($pattern);
            $manager->flush();

            /**
             * Message flash pour alerter l'utilisateur de l'état de la tâche
             */
            $this->addFlash(
                'success',
                "L'annonce <strong>{$pattern->getTitle()} - {$pattern->getTheme()}</strong> a bien été enregistrée!"
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
    * Permet d'afficher un motif individuel à partir du slug
    */
    #[Route('/pattern/{slug}', name:'pattern_show')]
    public function show(string $slug, Pattern $pattern ):Response
    {
        return $this->render('pattern/show.html.twig', [
            'pattern' => $pattern,
        ]);
    }

    /**
     * Permet d'afficher le formulaire pour éditer un motif
     */
    #[Route('/pattern/{slug}/edit', name: 'pattern_edit')]
    #[Security("(is_granted('ROLE_USER') and user === pattern.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Cette annonce ne vous appartient pas, vous ne pouvez pas la modifier")]
    public function editPattern(Pattern $pattern, Request $request, EntityManagerInterface $manager):Response
    {
        $user = $this->getUser(); //récupération de l'utilisateur connecté

        $fileName = $pattern->getCover();
        if(!empty($fileName)){
            $pattern->setCover(new File($this->getParameter('uploads_directory').'/'.$pattern->getCover()));
        }

        $form = $this->createForm(PatternType::class, $pattern);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {        

            $pattern-> setCover($fileName);
            
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
     * Permet de supprimer un motif
     */
    #[Route('/pattern/{slug}/delete', name:"pattern_delete")]
    #[Security("(is_granted('ROLE_USER') and user === pattern.getIdUser()) or is_granted('ROLE_ADMIN')", message:"Cette annonce ne vous appartient pas, vous ne pouvez pas la supprimer")]
    public function patternDelete(Pattern $pattern, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le motif {$pattern->getId()} a bien été supprimé"
        );

        unlink($this->getParameter('uploads_directory').'/'.$pattern->getCover());
            
        $manager->remove($pattern);
        $manager->flush();

        return $this->redirectToRoute("patterns_index");
    }
}
