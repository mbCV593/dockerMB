<?php

namespace App\Repository;

use App\Entity\Proveedor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class ProveedorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Proveedor::class);
    }


    public function findActive(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.activo = :activo')
            ->setParameter('activo', true)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtiene todos los proveedores ordenados por ID ascendente
     */
    public function findAllOrderedByIdDirect(): array
    {
        $dql = 'SELECT p FROM App\Entity\Proveedor p ORDER BY p.id ASC';
        return $this->getEntityManager()
            ->createQuery($dql)
            ->getResult();
    }

    /**
     * Busca proveedores por nombre
     */
    public function findByName(string $nombre): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.nombre LIKE :nombre')
            ->setParameter('nombre', '%' . $nombre . '%')
            ->andWhere('p.activo = :activo')
            ->setParameter('activo', true)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtiene estadísticas básicas de proveedores
     */
    public function getStats(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = '
            SELECT 
                COUNT(*) as total_proveedores,
                SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos
            FROM proveedores
        ';
        
        $result = $conn->executeQuery($sql)->fetchAssociative();
        
        return [
            'total' => (int) $result['total_proveedores'],
            'activos' => (int) $result['activos'],
            'inactivos' => (int) $result['inactivos']
        ];
    }
}
