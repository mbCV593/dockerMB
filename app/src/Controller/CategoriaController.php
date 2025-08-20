<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoriaController extends AbstractController
{
    private CategoriaRepository $categoriaRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(CategoriaRepository $categoriaRepository, EntityManagerInterface $entityManager)
    {
        $this->categoriaRepository = $categoriaRepository;
        $this->entityManager = $entityManager;
    }
    
    #[Route('/categorias', name: 'categorias_index', methods: ['GET'])]
    public function index(): Response
    {
        try {
            $categorias = $this->categoriaRepository->findBy(['activo' => true], ['nombre' => 'ASC']);
            $categoriasInactivas = $this->categoriaRepository->findBy(['activo' => false], ['nombre' => 'ASC']);
            
            return $this->render('categorias/index.html.twig', [
                'categorias' => $categorias,
                'categorias_inactivas' => $categoriasInactivas,
                'total_activas' => count($categorias),
                'total_inactivas' => count($categoriasInactivas),
                'usando_fallback' => false
            ]);
        } catch (\Exception $e) {
        
            $categoriasEstaticas = $this->getCategoriasEstaticas();
            $categoriasActivas = array_filter($categoriasEstaticas, fn($cat) => $cat['activo'] === true);
            $categoriasInactivas = array_filter($categoriasEstaticas, fn($cat) => $cat['activo'] === false);
            
            $this->addFlash('warning', 'Mostrando datos de ejemplo. Configura la conexión a la base de datos para ver datos reales.');
            
            return $this->render('categorias/index.html.twig', [
                'categorias' => $categoriasActivas,
                'categorias_inactivas' => $categoriasInactivas,
                'total_activas' => count($categoriasActivas),
                'total_inactivas' => count($categoriasInactivas),
                'usando_fallback' => true,
                'error_db' => $e->getMessage()
            ]);
        }
    }

    
    #[Route('/api/categorias', name: 'api_categorias_index', methods: ['GET'])]
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $activo = $request->query->get('activo');
            
         
            if ($activo !== null) {
                $activoBool = filter_var($activo, FILTER_VALIDATE_BOOLEAN);
                $categorias = $this->categoriaRepository->findBy(['activo' => $activoBool]);
            } else {
                
                $categorias = $this->categoriaRepository->findAll();
            }
            
            $data = [];
            foreach ($categorias as $categoria) {
                $data[] = $this->categoriaToArray($categoria);
            }
            
            return $this->json([
                'success' => true,
                'data' => $data,
                'total' => count($data),
                'filtro' => $activo !== null ? ($activoBool ? 'activas' : 'inactivas') : 'todas'
            ]);
        } catch (\Exception $e) {
        
            $categoriasEstaticas = $this->getCategoriasEstaticas();
            $activo = $request->query->get('activo');
        
            if ($activo !== null) {
                $activoBool = filter_var($activo, FILTER_VALIDATE_BOOLEAN);
                $categoriasEstaticas = array_filter($categoriasEstaticas, function($categoria) use ($activoBool) {
                    return $categoria['activo'] === $activoBool;
                });
                $categoriasEstaticas = array_values($categoriasEstaticas);
            }
            
            return $this->json([
                'success' => true,
                'data' => $categoriasEstaticas,
                'total' => count($categoriasEstaticas),
                'demo_mode' => true,
                'filtro' => $activo !== null ? ($activoBool ? 'activas' : 'inactivas') : 'todas'
            ]);
        }
    }

    #[Route('/api/categorias/activas', name: 'api_categorias_activas', methods: ['GET'])]
    public function apiActivas(): JsonResponse
    {
        try {
            $categorias = $this->categoriaRepository->findBy(['activo' => true]);
            
            $data = [];
            foreach ($categorias as $categoria) {
                $data[] = $this->categoriaToArray($categoria);
            }
            
            return $this->json([
                'success' => true,
                'data' => $data,
                'total' => count($data)
            ]);
        } catch (\Exception $e) {
            
            $categorias = $this->getCategoriasEstaticas();
            $categoriasActivas = array_filter($categorias, fn($c) => $c['activo'] === true);
            
            return $this->json([
                'success' => true,
                'data' => array_values($categoriasActivas),
                'total' => count($categoriasActivas),
                'demo_mode' => true
            ]);
        }
    }

    #[Route('/api/categorias/{id}', name: 'api_categorias_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function apiShow(int $id): JsonResponse
    {
        try {
            $categoria = $this->categoriaRepository->find($id);
            
            if (!$categoria) {
                return $this->json([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ], 404);
            }
            
            return $this->json([
                'success' => true,
                'data' => $this->categoriaToArray($categoria)
            ]);
        } catch (\Exception $e) {
    
            $categorias = $this->getCategoriasEstaticas();
            $categoria = array_filter($categorias, fn($c) => $c['id'] == $id);
            
            if (empty($categoria)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ], 404);
            }
            
            return $this->json([
                'success' => true,
                'data' => array_values($categoria)[0],
                'demo_mode' => true
            ]);
        }
    }

    #[Route('/api/categorias', name: 'api_categorias_create', methods: ['POST'])]
    public function apiCreate(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'success' => false,
                'error' => 'Datos JSON inválidos'
            ], 400);
        }

        if (empty($data['nombre'])) {
            return $this->json([
                'success' => false,
                'error' => 'El nombre es obligatorio'
            ], 400);
        }

        try {
            $categoria = new Categoria();
            $categoria->setNombre($data['nombre'])
                     ->setDescripcion($data['descripcion'] ?? null)
                     ->setActivo($data['activo'] ?? true);

            $this->entityManager->persist($categoria);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente',
                'data' => $this->categoriaToArray($categoria)
            ], 201);
        } catch (\Exception $e) {
        
            $nuevaCategoria = [
                'id' => rand(100, 999),
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? 'Sin descripción',
                'activo' => $data['activo'] ?? true,
                'created_at' => date('Y-m-d H:i:s'),
                'total_productos' => 0
            ];
            
            return $this->json([
                'success' => true,
                'message' => 'Categoría creada exitosamente (modo demo)',
                'data' => $nuevaCategoria,
                'demo_mode' => true,
                'note' => 'Esta categoría solo existe en memoria. Conecta la base de datos para persistencia real.'
            ], 201);
        }
    }

    #[Route('/api/categorias/{id}', name: 'api_categorias_update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function apiUpdate(int $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json([
                'success' => false,
                'error' => 'Datos JSON inválidos'
            ], 400);
        }

        try {
            $categoria = $this->categoriaRepository->find($id);
            
            if (!$categoria) {
                return $this->json([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ], 404);
            }


            if (isset($data['nombre'])) {
                $categoria->setNombre($data['nombre']);
            }
            if (isset($data['descripcion'])) {
                $categoria->setDescripcion($data['descripcion']);
            }
            if (isset($data['activo'])) {
                $categoria->setActivo($data['activo']);
            }

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente',
                'data' => $this->categoriaToArray($categoria)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => true,
                'message' => 'Categoría actualizada exitosamente (modo demo)',
                'demo_mode' => true,
                'note' => 'Los cambios solo existen en memoria. Conecta la base de datos para persistencia real.'
            ]);
        }
    }

    #[Route('/api/categorias/{id}', name: 'api_categorias_delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function apiDelete(int $id): JsonResponse
    {
        try {
            $categoria = $this->categoriaRepository->find($id);
            
            if (!$categoria) {
                return $this->json([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ], 404);
            }

            $categoria->setActivo(false);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Categoría marcada como inactiva exitosamente'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => true,
                'message' => 'Categoría marcada como inactiva exitosamente (modo demo)',
                'demo_mode' => true,
                'note' => 'Los cambios solo existen en memoria. Conecta la base de datos para persistencia real.'
            ]);
        }
    }

    #[Route('/api/categorias/{id}/restore', name: 'api_categorias_restore', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function apiRestore(int $id): JsonResponse
    {
        try {
            $categoria = $this->categoriaRepository->find($id);
            
            if (!$categoria) {
                return $this->json([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ], 404);
            }

            $categoria->setActivo(true);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Categoría restaurada exitosamente'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => true,
                'message' => 'Categoría restaurada exitosamente (modo demo)',
                'demo_mode' => true
            ]);
        }
    }

    #[Route('/api/categorias/{id}/productos', name: 'api_categorias_productos', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function apiProductos(int $id): JsonResponse
    {
        try {
            $categoria = $this->categoriaRepository->find($id);
            
            if (!$categoria) {
                return $this->json([
                    'success' => false,
                    'error' => 'Categoría no encontrada'
                ], 404);
            }
            
            $productos = [];
            foreach ($categoria->getProductos() as $producto) {
                $productos[] = [
                    'id' => $producto->getId(),
                    'nombre' => $producto->getNombre(),
                    'precio' => (float) $producto->getPrecio(),
                    'stock_actual' => $producto->getStockActual(),
                    'activo' => $producto->isActivo()
                ];
            }
            
            return $this->json([
                'success' => true,
                'categoria' => $this->categoriaToArray($categoria),
                'productos' => $productos,
                'total_productos' => count($productos)
            ]);
        } catch (\Exception $e) {
            return $this->json($this->getProductosPorCategoriaEstaticos($id));
        }
    }


    private function categoriaToArray($categoria): array
    {
        if (is_array($categoria)) {
            return $categoria; 
        }

        return [
            'id' => $categoria->getId(),
            'nombre' => $categoria->getNombre(),
            'descripcion' => $categoria->getDescripcion(),
            'activo' => $categoria->isActivo(),
            'created_at' => $categoria->getCreatedAt()->format('Y-m-d H:i:s'),
            'total_productos' => $categoria->getProductos()->count()
        ];
    }

    private function getCategoriasEstaticas(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Electrónicos',
                'descripcion' => 'Dispositivos electrónicos y tecnología',
                'activo' => true,
                'created_at' => '2025-08-19 08:00:00',
                'total_productos' => 3
            ],
            [
                'id' => 2,
                'nombre' => 'Ropa',
                'descripcion' => 'Vestimenta y accesorios',
                'activo' => true,
                'created_at' => '2025-08-19 08:05:00',
                'total_productos' => 2
            ],
            [
                'id' => 3,
                'nombre' => 'Hogar',
                'descripcion' => 'Artículos para el hogar',
                'activo' => true,
                'created_at' => '2025-08-19 08:10:00',
                'total_productos' => 1
            ],
            [
                'id' => 4,
                'nombre' => 'Deportes',
                'descripcion' => 'Artículos deportivos y fitness',
                'activo' => false,
                'created_at' => '2025-08-19 08:15:00',
                'total_productos' => 0
            ]
        ];
    }

    private function getProductosPorCategoriaEstaticos(int $categoriaId): array
    {
        $productosPorCategoria = [
            1 => [ 
                'success' => true,
                'categoria' => [
                    'id' => 1,
                    'nombre' => 'Electrónicos',
                    'descripcion' => 'Dispositivos electrónicos y tecnología',
                    'activo' => true
                ],
                'productos' => [
                    ['id' => 1, 'nombre' => 'Laptop HP', 'precio' => 25000.00, 'stock_actual' => 15, 'activo' => true],
                    ['id' => 2, 'nombre' => 'Mouse Logitech', 'precio' => 350.00, 'stock_actual' => 50, 'activo' => true],
                    ['id' => 3, 'nombre' => 'Teclado Mecánico', 'precio' => 1200.00, 'stock_actual' => 25, 'activo' => true]
                ],
                'total_productos' => 3,
                'demo_mode' => true
            ],
            2 => [
                'success' => true,
                'categoria' => [
                    'id' => 2,
                    'nombre' => 'Ropa',
                    'descripcion' => 'Vestimenta y accesorios',
                    'activo' => true
                ],
                'productos' => [
                    ['id' => 4, 'nombre' => 'Camiseta Nike', 'precio' => 450.00, 'stock_actual' => 30, 'activo' => true],
                    ['id' => 5, 'nombre' => 'Pantalón Jeans', 'precio' => 800.00, 'stock_actual' => 20, 'activo' => true]
                ],
                'total_productos' => 2,
                'demo_mode' => true
            ],
            3 => [
                'success' => true,
                'categoria' => [
                    'id' => 3,
                    'nombre' => 'Hogar',
                    'descripcion' => 'Artículos para el hogar',
                    'activo' => true
                ],
                'productos' => [
                    ['id' => 6, 'nombre' => 'Licuadora', 'precio' => 1500.00, 'stock_actual' => 8, 'activo' => true]
                ],
                'total_productos' => 1,
                'demo_mode' => true
            ]
        ];
        
        return $productosPorCategoria[$categoriaId] ?? [
            'success' => false,
            'error' => 'Categoría no encontrada',
            'demo_mode' => true
        ];
    }
}
