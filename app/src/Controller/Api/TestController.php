<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class TestController extends AbstractController
{
    #[Route('/test', name: 'test', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'message' => 'API funcionando correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'data' => [
                'version' => '1.0',
                'environment' => 'development'
            ]
        ]);
    }

    #[Route('/categorias-simple', name: 'categorias_simple', methods: ['GET'])]
    public function categoriasSimple(): JsonResponse
    {
        return $this->json([
            [
                'id' => 1,
                'nombre' => 'Electrónicos',
                'totalProductos' => 25
            ],
            [
                'id' => 2,
                'nombre' => 'Oficina',
                'totalProductos' => 18
            ],
            [
                'id' => 3,
                'nombre' => 'Hogar',
                'totalProductos' => 12
            ]
        ]);
    }
}
