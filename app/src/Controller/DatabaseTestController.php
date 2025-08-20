<?php

namespace App\Controller;

use App\Entity\Categoria;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class DatabaseTestController extends AbstractController
{
    #[Route('/test-db', name: 'test_database')]
    public function testDatabase(Connection $connection): JsonResponse
    {
        try {
            $connection->connect();
            
            $stmt = $connection->executeQuery('SELECT COUNT(*) as total FROM categorias');
            $result = $stmt->fetchAssociative();
            
            return $this->json([
                'success' => true,
                'message' => 'Conexión exitosa a la base de datos',
                'total_categorias' => $result['total'],
                'database_info' => [
                    'driver' => $connection->getDriver()->getName(),
                    'platform' => $connection->getDatabasePlatform()->getName(),
                    'server_version' => $connection->getServerVersion()
                ]
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error de conexión: ' . $e->getMessage(),
                'error_details' => [
                    'code' => $e->getCode(),
                    'file' => basename($e->getFile()),
                    'line' => $e->getLine(),
                    'possible_solutions' => [
                        '1. Verificar que la IP del contenedor Docker esté permitida en el servidor MySQL remoto',
                        '2. Confirmar que las credenciales sean correctas',
                        '3. Verificar que el puerto 3310 esté abierto',
                        '4. Revisar la configuración del firewall de Oracle Cloud'
                    ]
                ],
                'database_url_used' => $_ENV['DATABASE_URL'] ?? 'No definido'
            ], 500);
        }
    }

    #[Route('/test-entities', name: 'test_entities')]
    public function testEntities(EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $categorias = $entityManager->getRepository(Categoria::class)->findAll();
            
            return $this->json([
                'success' => true,
                'message' => 'Entidades cargadas correctamente',
                'total_categorias' => count($categorias),
                'categorias' => array_map(function($cat) {
                    return [
                        'id' => $cat->getId(),
                        'nombre' => $cat->getNombre(),
                        'activo' => $cat->isActivo()
                    ];
                }, array_slice($categorias, 0, 5)) 
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error con las entidades: ' . $e->getMessage(),
                'error_details' => [
                    'code' => $e->getCode(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}
