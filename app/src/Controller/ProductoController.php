<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Repository\ProductoRepository;
use App\Repository\CategoriaRepository;
use App\Repository\ProveedorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/productos')]
class ProductoController extends AbstractController
{
    #[Route('', name: 'producto_index')]
    public function index(ProductoRepository $productoRepository): Response
    {
        $productos = $productoRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('productos/index.html.twig', [
            'productos' => $productos,
        ]);
    }

    #[Route('/nuevo', name: 'producto_nuevo')]
    public function nuevo(
        Request $request, 
        EntityManagerInterface $em,
        CategoriaRepository $categoriaRepository,
        ProveedorRepository $proveedorRepository
    ): Response {
        $producto = new Producto();
        $categorias = $categoriaRepository->findBy(['activo' => true], ['nombre' => 'ASC']);
        $proveedores = $proveedorRepository->findBy(['activo' => true], ['nombre' => 'ASC']);

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $descripcion = $request->request->get('descripcion');
            $precio = $request->request->get('precio');
            $stock = (int) $request->request->get('stock', 0);
            $stockMinimo = (int) $request->request->get('stock_minimo', 5);
            $codigoBarras = $request->request->get('codigo_barras');
            $categoriaId = $request->request->get('categoria_id');
            $proveedorId = $request->request->get('proveedor_id');

            if (empty($nombre) || empty($precio)) {
                $this->addFlash('error', 'El nombre y el precio son obligatorios');
            } else {
                $producto->setNombre($nombre);
                $producto->setDescripcion($descripcion);
                $producto->setPrecio($precio);
                $producto->setStock($stock);
                $producto->setStockMinimo($stockMinimo);
                $producto->setCodigoBarras($codigoBarras);

                if ($categoriaId) {
                    $categoria = $categoriaRepository->find($categoriaId);
                    $producto->setCategoria($categoria);
                }

                if ($proveedorId) {
                    $proveedor = $proveedorRepository->find($proveedorId);
                    $producto->setProveedor($proveedor);
                }

                $em->persist($producto);
                $em->flush();

                $this->addFlash('success', 'Producto creado exitosamente');
                return $this->redirectToRoute('producto_index');
            }
        }

        return $this->render('productos/nuevo.html.twig', [
            'producto' => $producto,
            'categorias' => $categorias,
            'proveedores' => $proveedores,
        ]);
    }

    #[Route('/{id}', name: 'producto_ver', requirements: ['id' => '\d+'])]
    public function ver(Producto $producto): Response
    {
        return $this->render('productos/ver.html.twig', [
            'producto' => $producto,
        ]);
    }

    #[Route('/{id}/editar', name: 'producto_editar', requirements: ['id' => '\d+'])]
    public function editar(
        Producto $producto, 
        Request $request, 
        EntityManagerInterface $em,
        CategoriaRepository $categoriaRepository,
        ProveedorRepository $proveedorRepository
    ): Response {
        $categorias = $categoriaRepository->findBy(['activo' => true], ['nombre' => 'ASC']);
        $proveedores = $proveedorRepository->findBy(['activo' => true], ['nombre' => 'ASC']);

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $descripcion = $request->request->get('descripcion');
            $precio = $request->request->get('precio');
            $stock = (int) $request->request->get('stock', 0);
            $stockMinimo = (int) $request->request->get('stock_minimo', 5);
            $codigoBarras = $request->request->get('codigo_barras');
            $categoriaId = $request->request->get('categoria_id');
            $proveedorId = $request->request->get('proveedor_id');

            if (empty($nombre) || empty($precio)) {
                $this->addFlash('error', 'El nombre y el precio son obligatorios');
            } else {
                $producto->setNombre($nombre);
                $producto->setDescripcion($descripcion);
                $producto->setPrecio($precio);
                $producto->setStock($stock);
                $producto->setStockMinimo($stockMinimo);
                $producto->setCodigoBarras($codigoBarras);
                $producto->setUpdatedAt(new \DateTimeImmutable());

                // Actualizar categoría
                if ($categoriaId) {
                    $categoria = $categoriaRepository->find($categoriaId);
                    $producto->setCategoria($categoria);
                } else {
                    $producto->setCategoria(null);
                }

                // Actualizar proveedor
                if ($proveedorId) {
                    $proveedor = $proveedorRepository->find($proveedorId);
                    $producto->setProveedor($proveedor);
                } else {
                    $producto->setProveedor(null);
                }

                $em->flush();

                $this->addFlash('success', 'Producto actualizado exitosamente');
                return $this->redirectToRoute('producto_index');
            }
        }

        return $this->render('productos/editar.html.twig', [
            'producto' => $producto,
            'categorias' => $categorias,
            'proveedores' => $proveedores,
        ]);
    }

    #[Route('/{id}/eliminar', name: 'producto_eliminar', requirements: ['id' => '\d+'])]
    public function eliminar(Producto $producto, EntityManagerInterface $em): Response
    {
        $em->remove($producto);
        $em->flush();

        $this->addFlash('success', 'Producto eliminado exitosamente');
        return $this->redirectToRoute('producto_index');
    }

    #[Route('/{id}/toggle-activo', name: 'producto_toggle_activo', requirements: ['id' => '\d+'])]
    public function toggleActivo(Producto $producto, EntityManagerInterface $em): Response
    {
        $producto->setActivo(!$producto->isActivo());
        $producto->setUpdatedAt(new \DateTimeImmutable());
        $em->flush();

        $status = $producto->isActivo() ? 'activado' : 'desactivado';
        $this->addFlash('success', "Producto $status exitosamente");

        return $this->redirectToRoute('producto_index');
    }
}
