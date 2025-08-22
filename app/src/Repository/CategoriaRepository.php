<?php

namespace App\Repository;

use App\Entity\Categoria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CategoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categoria::class);
    }

    public function findCategoriasActivas(): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.activo = :activo')
            ->setParameter('activo', true)
            ->orderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedById(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrderedByIdDirect(): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery('
            SELECT c FROM App\Entity\Categoria c
            ORDER BY c.id ASC
        ');
        
        return $query->getResult();
    }

    
    public function findByNombreLike(string $nombre): array
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.nombre LIKE :nombre')
            ->andWhere('c.activo = :activo')
            ->setParameter('nombre', '%' . $nombre . '%')
            ->setParameter('activo', true)
            ->orderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findCategoriasConProductos(): array
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'COUNT(p.id) as total_productos')
            ->leftJoin('c.productos', 'p', 'WITH', 'p.activo = :activo')
            ->andWhere('c.activo = :activo')
            ->setParameter('activo', true)
            ->groupBy('c.id')
            ->orderBy('c.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
