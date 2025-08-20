<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoriaController extends AbstractController
{
    #[Route('/categorias', name: 'categorias_index')]
    public function index(): Response
    {
        $categorias = [
            ['id' => 1, 'nombre' => 'Electronicos', 'descripcion' => 'Dispositivos electronicos y accesorios', 'fecha' => '20/08/2025'],
            ['id' => 2, 'nombre' => 'Oficina', 'descripcion' => 'Materiales y equipos de oficina', 'fecha' => '20/08/2025'],
            ['id' => 3, 'nombre' => 'Hogar', 'descripcion' => 'Productos para el hogar', 'fecha' => '20/08/2025'],
            ['id' => 4, 'nombre' => 'Deportes', 'descripcion' => 'Articulos deportivos y fitness', 'fecha' => '20/08/2025']
        ];
        
        return $this->render('categorias/index.html.twig', [
            'categorias' => $categorias,
            'total' => count($categorias)
        ]);
    }
}