<?php

namespace App\Controller\Api;

use App\Entity\Producto;
use App\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/productos', name: 'api_productos_')]
class ProductoController extends AbstractController
{
    private ProductoRepository $productoRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ProductoRepository $productoRepository, EntityManagerInterface $entityManager)
    {
        $this->productoRepository = $productoRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $productos = $this->productoRepository->findAll();
            
            $data = [];
            foreach ($productos as $producto) {
                $data[] = $this->productoToArray($producto);
            }
            
            return $this->json($data);
        } catch (\Exception $e) {
            return $this->json($this->getProductosEstaticos());
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $producto = $this->productoRepository->find($id);
            
            if (!$producto) {
                return $this->json(['error' => 'Producto no encontrado'], 404);
            }
            
            return $this->json($this->productoToArray($producto));
        } catch (\Exception $e) {
            $productos = $this->getProductosEstaticos();
            $producto = array_filter($productos, fn($p) => $p['id'] == $id);
            
            if (empty($producto)) {
                return $this->json(['error' => 'Producto no encontrado'], 404);
            }
            
            return $this->json(array_values($producto)[0]);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'Datos JSON inválidos'], 400);
        }

        try {
            $producto = new Producto();
            $producto->setNombre($data['nombre'] ?? '')
                     ->setPrecio($data['precio'] ?? 0)
                     ->setDescripcion($data['descripcion'] ?? null)
                     ->setStock($data['stock'] ?? 0)
                     ->setActivo($data['activo'] ?? true);

            $this->entityManager->persist($producto);
            $this->entityManager->flush();

            return $this->json($this->productoToArray($producto), 201);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'No se pudo crear el producto: ' . $e->getMessage(),
                'message' => 'Funcionalidad solo disponible con base de datos conectada'
            ], 500);
        }
    }

    #[Route('/bajo-stock', name: 'bajo_stock', methods: ['GET'])]
    public function bajoStock(): JsonResponse
    {
        try {
            $productos = $this->productoRepository->findBajoStock(5);
            
            $data = [];
            foreach ($productos as $producto) {
                $data[] = $this->productoToArray($producto);
            }
            
            return $this->json($data);
        } catch (\Exception $e) {
        
            $productos = $this->getProductosEstaticos();
            $productosBajoStock = array_filter($productos, fn($p) => $p['stock'] <= 5 && $p['stock'] > 0);
            
            return $this->json(array_values($productosBajoStock));
        }
    }

    private function productoToArray($producto): array
    {
        if (is_array($producto)) {
            return $producto; 
        }

        return [
            'id' => $producto->getId(),
            'nombre' => $producto->getNombre(),
            'precio' => $producto->getPrecio(),
            'descripcion' => $producto->getDescripcion(),
            'stock' => $producto->getStock(),
            'stock_minimo' => $producto->getStockMinimo(),
            'activo' => $producto->isActivo(),
            'created_at' => $producto->getCreatedAt()->format('Y-m-d H:i:s'),
            'estado_stock' => $producto->getEstadoStock(),
            'valor_inventario' => $producto->getValorInventario()
        ];
    }

    private function getProductosEstaticos(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Laptop Dell Inspiron',
                'precio' => 8500.00,
                'descripcion' => 'Laptop para oficina i5 8GB RAM',
                'stock' => 5,
                'stock_minimo' => 2,
                'activo' => true,
                'created_at' => '2025-08-19 10:00:00',
                'estado_stock' => 'STOCK_NORMAL',
                'valor_inventario' => 42500.00
            ],
            [
                'id' => 2,
                'nombre' => 'Mouse Inalámbrico',
                'precio' => 150.00,
                'descripcion' => 'Mouse óptico inalámbrico',
                'stock' => 25,
                'stock_minimo' => 5,
                'activo' => true,
                'created_at' => '2025-08-19 10:15:00',
                'estado_stock' => 'STOCK_NORMAL',
                'valor_inventario' => 3750.00
            ],
            [
                'id' => 3,
                'nombre' => 'Resma Papel Bond',
                'precio' => 45.00,
                'descripcion' => 'Papel bond carta 75gr',
                'stock' => 50,
                'stock_minimo' => 10,
                'activo' => true,
                'created_at' => '2025-08-19 10:30:00',
                'estado_stock' => 'STOCK_NORMAL',
                'valor_inventario' => 2250.00
            ],
            [
                'id' => 4,
                'nombre' => 'Teclado Mecánico',
                'precio' => 780.00,
                'descripcion' => 'Teclado mecánico RGB para gaming y oficina',
                'stock' => 3,
                'stock_minimo' => 5,
                'activo' => true,
                'created_at' => '2025-08-19 10:45:00',
                'estado_stock' => 'STOCK_BAJO',
                'valor_inventario' => 2340.00
            ],
            [
                'id' => 5,
                'nombre' => 'Monitor 24"',
                'precio' => 1580.00,
                'descripcion' => 'Monitor LED 24 pulgadas Full HD',
                'stock' => 2,
                'stock_minimo' => 3,
                'activo' => true,
                'created_at' => '2025-08-19 11:00:00',
                'estado_stock' => 'STOCK_BAJO',
                'valor_inventario' => 3160.00
            ]
        ];
    }
}
