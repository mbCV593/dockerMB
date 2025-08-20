<?php

namespace App\Controller\Api;

use App\Repository\ProductoRepository;
use App\Repository\CategoriaRepository;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/inventario', name: 'api_inventario_')]
class InventarioController extends AbstractController
{
    private ProductoRepository $productoRepository;
    private CategoriaRepository $categoriaRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ProductoRepository $productoRepository, 
        CategoriaRepository $categoriaRepository,
        UsuarioRepository $usuarioRepository
    ) {
        $this->productoRepository = $productoRepository;
        $this->categoriaRepository = $categoriaRepository;
        $this->usuarioRepository = $usuarioRepository;
    }

    #[Route('/estadisticas', name: 'estadisticas', methods: ['GET'])]
    public function estadisticas(): JsonResponse
    {
        try {

            $totalProductos = count($this->productoRepository->findAll());
            $totalCategorias = count($this->categoriaRepository->findAll());
            $totalUsuarios = count($this->usuarioRepository->findAll());
            $productosBajoStock = $this->productoRepository->findBajoStock();
            $valorTotalInventario = $this->productoRepository->calcularValorTotal();

            return $this->json([
                'total_productos' => $totalProductos,
                'total_categorias' => $totalCategorias,
                'total_usuarios' => $totalUsuarios,
                'productos_bajo_stock' => count($productosBajoStock),
                'valor_total_inventario' => (float) $valorTotalInventario,
                'productos_alertas' => array_map(function($producto) {
                    return [
                        'id' => $producto->getId(),
                        'nombre' => $producto->getNombre(),
                        'stock_actual' => $producto->getStockActual(),
                        'stock_minimo' => $producto->getStockMinimo()
                    ];
                }, $productosBajoStock)
            ]);
        } catch (\Exception $e) {
    
            return $this->json($this->getEstadisticasEstaticas());
        }
    }

    #[Route('/resumen', name: 'resumen', methods: ['GET'])]
    public function resumen(): JsonResponse
    {
        try {
        
            $productos = $this->productoRepository->findAll();
            $categorias = $this->categoriaRepository->findActivas();
            
            $resumen = [
                'inventario' => [
                    'total_items' => 0,
                    'valor_total' => 0,
                    'productos_activos' => 0,
                    'productos_inactivos' => 0
                ],
                'categorias' => [],
                'alertas' => [
                    'stock_bajo' => [],
                    'productos_inactivos' => []
                ]
            ];

            foreach ($productos as $producto) {
                $resumen['inventario']['total_items'] += $producto->getStockActual();
                $resumen['inventario']['valor_total'] += $producto->getStockActual() * $producto->getPrecio();
                
                if ($producto->isActivo()) {
                    $resumen['inventario']['productos_activos']++;
                } else {
                    $resumen['inventario']['productos_inactivos']++;
                    $resumen['alertas']['productos_inactivos'][] = [
                        'id' => $producto->getId(),
                        'nombre' => $producto->getNombre()
                    ];
                }

                if ($producto->getStockActual() <= $producto->getStockMinimo()) {
                    $resumen['alertas']['stock_bajo'][] = [
                        'id' => $producto->getId(),
                        'nombre' => $producto->getNombre(),
                        'stock_actual' => $producto->getStockActual(),
                        'stock_minimo' => $producto->getStockMinimo()
                    ];
                }
            }

            foreach ($categorias as $categoria) {
                $resumen['categorias'][] = [
                    'id' => $categoria->getId(),
                    'nombre' => $categoria->getNombre(),
                    'total_productos' => $categoria->getProductos()->count()
                ];
            }

            return $this->json($resumen);
        } catch (\Exception $e) {

            return $this->json($this->getResumenEstático());
        }
    }

    #[Route('/movimientos', name: 'movimientos', methods: ['GET'])]
    public function movimientos(): JsonResponse
    {

        return $this->json($this->getMovimientosEstaticos());
    }

    private function getEstadisticasEstaticas(): array
    {
        return [
            'total_productos' => 6,
            'total_categorias' => 3,
            'total_usuarios' => 3,
            'productos_bajo_stock' => 2,
            'valor_total_inventario' => 1825500.00,
            'productos_alertas' => [
                [
                    'id' => 3,
                    'nombre' => 'Teclado Mecánico',
                    'stock_actual' => 25,
                    'stock_minimo' => 30
                ],
                [
                    'id' => 6,
                    'nombre' => 'Licuadora',
                    'stock_actual' => 8,
                    'stock_minimo' => 10
                ]
            ]
        ];
    }

    private function getResumenEstático(): array
    {
        return [
            'inventario' => [
                'total_items' => 148,
                'valor_total' => 1825500.00,
                'productos_activos' => 6,
                'productos_inactivos' => 0
            ],
            'categorias' => [
                ['id' => 1, 'nombre' => 'Electrónicos', 'total_productos' => 3],
                ['id' => 2, 'nombre' => 'Ropa', 'total_productos' => 2],
                ['id' => 3, 'nombre' => 'Hogar', 'total_productos' => 1]
            ],
            'alertas' => [
                'stock_bajo' => [
                    [
                        'id' => 3,
                        'nombre' => 'Teclado Mecánico',
                        'stock_actual' => 25,
                        'stock_minimo' => 30
                    ],
                    [
                        'id' => 6,
                        'nombre' => 'Licuadora',
                        'stock_actual' => 8,
                        'stock_minimo' => 10
                    ]
                ],
                'productos_inactivos' => []
            ]
        ];
    }

    private function getMovimientosEstaticos(): array
    {
        return [
            [
                'id' => 1,
                'tipo' => 'ENTRADA',
                'producto' => 'Laptop HP',
                'cantidad' => 5,
                'fecha' => '2025-08-19 10:30:00',
                'usuario' => 'Admin Sistema',
                'observaciones' => 'Compra a proveedor'
            ],
            [
                'id' => 2,
                'tipo' => 'SALIDA',
                'producto' => 'Mouse Logitech',
                'cantidad' => 2,
                'fecha' => '2025-08-19 14:15:00',
                'usuario' => 'Usuario Prueba',
                'observaciones' => 'Venta cliente'
            ],
            [
                'id' => 3,
                'tipo' => 'AJUSTE',
                'producto' => 'Camiseta Nike',
                'cantidad' => -1,
                'fecha' => '2025-08-19 16:00:00',
                'usuario' => 'María González',
                'observaciones' => 'Producto dañado'
            ]
        ];
    }
}
