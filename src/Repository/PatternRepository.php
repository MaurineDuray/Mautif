<?php

namespace App\Repository;

use App\Entity\Pattern;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pattern>
 *
 * @method Pattern|null find($id, $lockMode = null, $lockVersion = null)
 * @method Pattern|null findOneBy(array $criteria, array $orderBy = null)
 * @method Pattern[]    findAll()
 * @method Pattern[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PatternRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pattern::class);
    }

    public function save(Pattern $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Pattern $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

   /**
    * Trouver les trois dernières créations par utilisateurs
    * @return Pattern[] Returns an array of Pattern objects
    */
   public function findLastByUser($value): array
   {
       return $this->createQueryBuilder('p')
           ->andWhere('p.id_user = :val')
           ->setParameter('val', $value)
           ->orderBy('p.id', 'ASC')
           ->setMaxResults(3)
           ->getQuery()
           ->getResult()
       ;
   }

   //Trouver un motif par la barre de recherche
   public function findPattern(string $criteria)
   {
       $qb = $this->createQueryBuilder('p');
       $qb
           ->where(
               $qb->expr()->andX(
                   $qb->expr()->orX(
                       $qb->expr()->like('p.title', ':criteria'),
                       $qb->expr()->like('p.theme', ':criteria'),
                       $qb->expr()->like('p.dominantColor', ':criteria'),
                       $qb->expr()->like('p.description', ':criteria')
                   ),
               )
           )
           ->setParameter('criteria', '%' . $criteria . '%');
       return $qb
           ->getQuery()
           ->getResult();
   }

    /**
     * Recherche des motifs par filtre
    * @return Pattern[] Returns an array of Pattern objects
    */
    public function findByCategory(string $theme, string $color, string $license): array
    {
       return $this->createQueryBuilder('p')
           ->select('p as pattern, p.slug, p.dominant_color, p.creation_date, p.cover, p.theme, p.title, p.theme, p.description, p.license')
           ->groupBy('p')
           ->orderBy('p.id', 'DESC')
           ->where('p.theme= :theme')
           ->andWhere('p.dominant_color: :color')
           ->andwhere('p.license= :license')
           ->setParameters([
            'theme'=> $theme,
            'color' => $color,
            'license' => $license
           ])
           ->getQuery()
           ->getResult()
       ;
    }

    public function findPatternsWithMostLikes($limit)
    {
        return $this->createQueryBuilder('p')
            ->select('p as pattern, p.slug, p.dominant_color, p.creation_date, p.cover, p.theme, p.title, p.description, p.license')
            ->leftJoin('p.likes', 'l') // Utilisation de l'association "likes" de l'entité "Pattern"
            ->select('p, COUNT(l.id) as likeCount')
            ->groupBy('p')
            ->orderBy('likeCount', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return Pattern[] Returns an array of Pattern objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Pattern
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
