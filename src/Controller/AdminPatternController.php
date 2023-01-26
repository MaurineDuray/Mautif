<?php

namespace App\Controller;

use App\Entity\Pattern;
use App\Repository\PatternRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminPatternController extends AbstractController
{
    #[Route('/admin/pattern', name: 'admin_pattern')]
    #[IsGranted("ROLE_ADMIN")]
    public function index(PatternRepository $repo): Response
    {
        $patterns = $repo ->findAll();

        return $this->render('admin/pattern/adminPattern.html.twig', [
            'patterns' => $patterns,
        ]);
    }

    #[Route('/admin/pattern/{slug}/delete', name:"admin_pattern_delete")]
    public function patternDelete(Pattern $pattern, EntityManagerInterface $manager)
    {
        $this->addFlash(
            "success",
            "Le motif {$pattern->getId()} a bien été supprimé"
        );

        $manager->remove($pattern);
        $manager->flush();

        return $this->redirectToRoute("admin/pattern/adminPattern.html.twig");
    }


}
