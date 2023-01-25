<?php

namespace App\Controller;

use App\Entity\Pattern;
use App\Form\PatternType;
use App\Repository\PatternRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

    /**
     * Permet d'ajouter un motif au site
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('pattern/add', name:"pattern_add")]
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
}
