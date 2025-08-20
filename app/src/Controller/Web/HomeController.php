<?php

namespace App\Controller\Web;

use App\Repository\ProductoRepository;
use App\Repository\UsuarioRepository;
use App\Repository\CategoriaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductoRepository $productoRepo, CategoriaRepository $categoriaRepo): Response
    {
        try {
            $productosRecientes = $productoRepo->findBy([], ['createdAt' => 'DESC'], 6);
            $productosBajoStock = $productoRepo->findBajoStock(5);
            $totalProductos = $productoRepo->count(['activo' => true]);
            $totalCategorias = $categoriaRepo->count(['activo' => true]);
        } catch (\Exception $e) {
            // Si hay error de conexión, usar datos por defecto
            $productosRecientes = $this->getProductosDemoData();
            $productosBajoStock = $this->getProductosBajoStockDemo();
            $totalProductos = count($productosRecientes);
            $totalCategorias = 4;
        }
        
        return $this->render('home/index.html.twig', [
            'productos_recientes' => array_slice($productosRecientes, 0, 6),
            'productos_bajo_stock' => $productosBajoStock,
            'total_productos' => $totalProductos,
            'total_categorias' => $totalCategorias
        ]);
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function dashboard(ProductoRepository $productoRepo): Response
    {
        try {
            $estadisticas = [
                'total_productos' => $productoRepo->count(['activo' => true]),
                'productos_bajo_stock' => count($productoRepo->findBajoStock(5)),
                'productos_sin_stock' => $productoRepo->count(['stock' => 0, 'activo' => true]),
                'valor_inventario' => $productoRepo->calcularValorTotal()
            ];
        } catch (\Exception $e) {
            // Si hay error de conexión, usar datos por defecto
            $productos = $this->getProductosDemoData();
            $productosBajoStock = $this->getProductosBajoStockDemo();
            
            $estadisticas = [
                'total_productos' => count($productos),
                'productos_bajo_stock' => count($productosBajoStock),
                'productos_sin_stock' => count(array_filter($productos, fn($p) => $p['stock'] == 0)),
                'valor_inventario' => array_sum(array_map(fn($p) => $p['precio'] * $p['stock'], $productos))
            ];
        }
        
        return $this->render('dashboard/index.html.twig', [
            'estadisticas' => $estadisticas
        ]);
    }

    #[Route('/productos', name: 'productos_web')]
    public function productos(ProductoRepository $productoRepo): Response
    {
        try {
            $productos = $productoRepo->findBy(['activo' => true], ['nombre' => 'ASC']);
        } catch (\Exception $e) {
            // Si hay error de conexión, usar datos estáticos
            $productos = $this->getProductosDemoData();
        }
        
        return $this->render('productos/index.html.twig', [
            'productos' => $productos
        ]);
    }

    private function getProductosDemoData(): array
    {
        return [
            [
                'id' => 1,
                'nombre' => 'Laptop Dell Inspiron',
                'precio' => 8500.00,
                'descripcion' => 'Laptop para oficina i5 8GB RAM, perfecta para trabajo y estudio',
                'stock' => 5,
                'stockMinimo' => 2,
                'activo' => true,
                'categoria' => ['id' => 1, 'nombre' => 'Electrónicos'],
                'createdAt' => new \DateTimeImmutable('2025-08-18 10:30:00')
            ],
            [
                'id' => 2,
                'nombre' => 'Mouse Inalámbrico',
                'precio' => 150.00,
                'descripcion' => 'Mouse óptico inalámbrico con batería de larga duración',
                'stock' => 25,
                'stockMinimo' => 5,
                'activo' => true,
                'categoria' => ['id' => 1, 'nombre' => 'Electrónicos'],
                'createdAt' => new \DateTimeImmutable('2025-08-18 11:15:00')
            ],
            [
                'id' => 3,
                'nombre' => 'Resma Papel Bond',
                'precio' => 45.00,
                'descripcion' => 'Papel bond carta 75gr, ideal para impresoras',
                'stock' => 50,
                'stockMinimo' => 10,
                'activo' => true,
                'categoria' => ['id' => 2, 'nombre' => 'Oficina'],
                'createdAt' => new \DateTimeImmutable('2025-08-18 12:00:00')
            ],
            [
                'id' => 4,
                'nombre' => 'Teclado Mecánico',
                'precio' => 350.00,
                'descripcion' => 'Teclado mecánico RGB para gaming y oficina',
                'stock' => 2,
                'stockMinimo' => 5,
                'activo' => true,
                'categoria' => ['id' => 1, 'nombre' => 'Electrónicos'],
                'createdAt' => new \DateTimeImmutable('2025-08-18 13:20:00')
            ],
            [
                'id' => 5,
                'nombre' => 'Monitor 24"',
                'precio' => 1250.00,
                'descripcion' => 'Monitor LED 24 pulgadas Full HD',
                'stock' => 0,
                'stockMinimo' => 3,
                'activo' => true,
                'categoria' => ['id' => 1, 'nombre' => 'Electrónicos'],
                'createdAt' => new \DateTimeImmutable('2025-08-18 14:45:00')
            ],
            [
                'id' => 6,
                'nombre' => 'Silla Ergonómica',
                'precio' => 950.00,
                'descripcion' => 'Silla de oficina ergonómica con soporte lumbar',
                'stock' => 8,
                'stockMinimo' => 3,
                'activo' => true,
                'categoria' => ['id' => 2, 'nombre' => 'Oficina'],
                'createdAt' => new \DateTimeImmutable('2025-08-18 15:10:00')
            ]
        ];
    }

    private function getProductosBajoStockDemo(): array
    {
        $productos = $this->getProductosDemoData();
        return array_filter($productos, function($producto) {
            return $producto['stock'] <= $producto['stockMinimo'] && $producto['stock'] > 0;
        });
    }
}
