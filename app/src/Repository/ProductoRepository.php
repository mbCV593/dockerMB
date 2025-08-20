<?php

namespace App\Repository;

use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producto::class);
    }

    public function findBajoStock(int $minimo = 5): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stock <= :minimo')
            ->andWhere('p.activo = :activo')
            ->setParameter('minimo', $minimo)
            ->setParameter('activo', true)
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Productos sin stock
     */
    public function findSinStock(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.stock = 0')
            ->andWhere('p.activo = :activo')
            ->setParameter('activo', true)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcula el valor total del inventario
     */
    public function calcularValorTotal(): float
    {
        $result = $this->createQueryBuilder('p')
            ->select('SUM(CAST(p.precio AS DECIMAL(10,2)) * p.stock) as valor_total')
            ->andWhere('p.activo = :activo')
            ->setParameter('activo', true)
            ->getQuery()
            ->getSingleScalarResult();

        return (float) ($result ?? 0);
    }

    /**
     * Busca productos por nombre
     */
    public function findByNombreLike(string $nombre): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nombre LIKE :nombre')
            ->andWhere('p.activo = :activo')
            ->setParameter('nombre', '%' . $nombre . '%')
            ->setParameter('activo', true)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Productos por categoría
     */
    public function findByCategoria(int $categoriaId): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.categoria = :categoria')
            ->andWhere('p.activo = :activo')
            ->setParameter('categoria', $categoriaId)
            ->setParameter('activo', true)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
