<?php

namespace App\Controller;

use App\Entity\Categoria;
use App\Repository\CategoriaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategoriaController extends AbstractController
{
    #[Route('/categorias', name: 'categorias_index')]
    public function index(CategoriaRepository $categoriaRepository): Response
    {
        // Obtener todas las categorías activas de la base de datos
        $categorias = $categoriaRepository->findBy(['activo' => true], ['fechaCreacion' => 'DESC']);
        
        return $this->render('categorias/index.html.twig', [
            'categorias' => $categorias,
            'total' => count($categorias)
        ]);
    }

    #[Route('/categorias/nueva', name: 'categorias_nueva')]
    public function nueva(): Response
    {
        return $this->render('categorias/nueva.html.twig');
    }

    #[Route('/categorias/crear', name: 'categorias_crear', methods: ['POST'])]
    public function crear(Request $request, EntityManagerInterface $entityManager): Response
    {
        $nombre = trim($request->request->get('nombre'));
        $descripcion = trim($request->request->get('descripcion'));
        
        // Validaciones básicas
        if (empty($nombre)) {
            $this->addFlash('error', 'El nombre de la categoría es obligatorio.');
            return $this->redirectToRoute('categorias_nueva');
        }
        
        if (strlen($nombre) > 100) {
            $this->addFlash('error', 'El nombre no puede tener más de 100 caracteres.');
            return $this->redirectToRoute('categorias_nueva');
        }
        
        // Crear nueva categoría
        $categoria = new Categoria();
        $categoria->setNombre($nombre);
        $categoria->setDescripcion($descripcion ?: null);
        $categoria->setActivo(true);
        $categoria->setFechaCreacion(new \DateTime());
        
        try {
            $entityManager->persist($categoria);
            $entityManager->flush();
            
            // Limpiar el cache de entidades para forzar recarga
            $entityManager->clear();
            
            $this->addFlash('success', sprintf('Categoría "%s" creada exitosamente.', $nombre));
            return $this->redirectToRoute('categorias_index');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al crear la categoría: ' . $e->getMessage());
            return $this->redirectToRoute('categorias_nueva');
        }
    }
}