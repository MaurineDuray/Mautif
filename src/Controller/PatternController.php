<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Pattern;
use App\Form\PatternType;
use App\Repository\PatternRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PatternController extends AbstractController
{
    #[Route('/patterns', name: 'patterns_index')]
    public function index(PatternRepository $repo): Response
    {
        $patterns=$repo->findAll();

        return $this->render('pattern/index.html.twig', [
            'patterns' => $patterns,
        ]);
    }

    #[Route('/mypattern', name:"mypattern")]
    #[IsGranted('ROLE_USER')]
    public function userPattern(PatternRepository $repo):Response
    {

        $patterns = $repo->findAll();

        return $this->render('pattern/mypattern.html.twig', [
            'user' => $this->getUser(),
            'patterns'=> $patterns
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
     * Permet d'afficher un motif 
     */
    #[Route('/pattern/{slug}', name:'pattern_show')]
    public function show(string $slug, Pattern $pattern ):Response
    {
        return $this->render('pattern/show.html.twig', [
            'pattern' => $pattern,
        ]);
    }

    #[Route('/pattern/{slug}/delete', name:"pattern_delete")]
    public function patternDelete(Pattern $pattern, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le motif {$pattern->getId()} a bien été supprimé"
        );

        $manager->remove($pattern);
        $manager->flush();

        return $this->redirectToRoute("mypattern");
    }
}
