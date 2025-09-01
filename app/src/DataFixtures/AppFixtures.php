<?php

namespace App\DataFixtures;

use App\Entity\Usuario;
use App\Entity\Categoria;
use App\Entity\Producto;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $adminUser = new Usuario();
        $adminUser->setEmail('admin@pruebas.com')
                  ->setRoles(['ROLE_ADMIN'])
                  ->setNombre('Admin')
                  ->setApellido('Sistema')
                  ->setActivo(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($adminUser, 'admin123');
        $adminUser->setPassword($hashedPassword);
        
        $manager->persist($adminUser);

        $regularUser = new Usuario();
        $regularUser->setEmail('user@pruebas.com')
                   ->setRoles(['ROLE_USER'])
                   ->setNombre('Usuario')
                   ->setApellido('Prueba')
                   ->setActivo(true);
        
        $hashedPassword = $this->passwordHasher->hashPassword($regularUser, 'user123');
        $regularUser->setPassword($hashedPassword);
        
        $manager->persist($regularUser);

  
        $categoriaElectronicos = new Categoria();
        $categoriaElectronicos->setNombre('Electrónicos')
                             ->setDescripcion('Dispositivos electrónicos y accesorios')
                             ->setActivo(true);
        $manager->persist($categoriaElectronicos);

        $categoriaOficina = new Categoria();
        $categoriaOficina->setNombre('Oficina')
                        ->setDescripcion('Materiales y equipos de oficina')
                        ->setActivo(true);
        $manager->persist($categoriaOficina);

        $categoriaHogar = new Categoria();
        $categoriaHogar->setNombre('Hogar')
                      ->setDescripcion('Productos para el hogar')
                      ->setActivo(true);
        $manager->persist($categoriaHogar);

        $categoriaDeportes = new Categoria();
        $categoriaDeportes->setNombre('Deportes')
                         ->setDescripcion('Artículos deportivos y fitness')
                         ->setActivo(true);
        $manager->persist($categoriaDeportes);

    
        $manager->flush();

        $productos = [
            [
                'nombre' => 'Laptop Dell Inspiron',
                'precio' => '8500.00',
                'descripcion' => 'Laptop para oficina i5 8GB RAM',
                'stock' => 5,
                'stockMinimo' => 2,
                'categoria' => $categoriaElectronicos,
                'codigoBarras' => 'DELL001'
            ],
            [
                'nombre' => 'Mouse Inalámbrico',
                'precio' => '150.00',
                'descripcion' => 'Mouse óptico inalámbrico',
                'stock' => 25,
                'stockMinimo' => 5,
                'categoria' => $categoriaElectronicos,
                'codigoBarras' => 'MOUSE001'
            ],
            [
                'nombre' => 'Resma Papel Bond',
                'precio' => '45.00',
                'descripcion' => 'Papel bond carta 75gr',
                'stock' => 50,
                'stockMinimo' => 10,
                'categoria' => $categoriaOficina,
                'codigoBarras' => 'PAPEL001'
            ],
            [
                'nombre' => 'Teclado Mecánico',
                'precio' => '350.00',
                'descripcion' => 'Teclado mecánico retroiluminado',
                'stock' => 15,
                'stockMinimo' => 3,
                'categoria' => $categoriaElectronicos,
                'codigoBarras' => 'TEC001'
            ],
            [
                'nombre' => 'Silla Ergonómica',
                'precio' => '1200.00',
                'descripcion' => 'Silla ergonómica para oficina',
                'stock' => 8,
                'stockMinimo' => 2,
                'categoria' => $categoriaOficina,
                'codigoBarras' => 'SILLA001'
            ],
            [
                'nombre' => 'Cafetera Express',
                'precio' => '890.00',
                'descripcion' => 'Cafetera express 15 bares',
                'stock' => 12,
                'stockMinimo' => 3,
                'categoria' => $categoriaHogar,
                'codigoBarras' => 'CAFE001'
            ],
            [
                'nombre' => 'Pelota de Fútbol',
                'precio' => '85.00',
                'descripcion' => 'Pelota oficial de fútbol',
                'stock' => 30,
                'stockMinimo' => 8,
                'categoria' => $categoriaDeportes,
                'codigoBarras' => 'FUT001'
            ],
            [
                'nombre' => 'Monitor 24 pulgadas',
                'precio' => '1450.00',
                'descripcion' => 'Monitor LED Full HD 24"',
                'stock' => 10,
                'stockMinimo' => 2,
                'categoria' => $categoriaElectronicos,
                'codigoBarras' => 'MON001'
            ],
            [
                'nombre' => 'Escritorio de Madera',
                'precio' => '2200.00',
                'descripcion' => 'Escritorio ejecutivo de madera',
                'stock' => 5,
                'stockMinimo' => 1,
                'categoria' => $categoriaOficina,
                'codigoBarras' => 'ESC001'
            ],
            [
                'nombre' => 'Aspiradora',
                'precio' => '650.00',
                'descripcion' => 'Aspiradora compacta 1200W',
                'stock' => 7,
                'stockMinimo' => 2,
                'categoria' => $categoriaHogar,
                'codigoBarras' => 'ASP001'
            ]
        ];

        foreach ($productos as $productoData) {
            $producto = new Producto();
            $producto->setNombre($productoData['nombre'])
                    ->setPrecio($productoData['precio'])
                    ->setDescripcion($productoData['descripcion'])
                    ->setStock($productoData['stock'])
                    ->setStockMinimo($productoData['stockMinimo'])
                    ->setCategoria($productoData['categoria'])
                    ->setCodigoBarras($productoData['codigoBarras'])
                    ->setActivo(true);
            
            $manager->persist($producto);
        }


        $manager->flush();
    }
}
