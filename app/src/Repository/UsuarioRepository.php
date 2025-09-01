<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }


    public function findUsuariosActivos(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.activo = :activo')
            ->setParameter('activo', true)
            ->orderBy('u.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByEmail(string $email): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }
}