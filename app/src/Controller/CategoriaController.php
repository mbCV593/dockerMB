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

        $categorias = $categoriaRepository->findAllOrderedByIdDirect();
        
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
    
        if (empty($nombre)) {
            $this->addFlash('error', 'El nombre de la categoría es obligatorio.');
            return $this->redirectToRoute('categorias_nueva');
        }
        
        if (strlen($nombre) > 100) {
            $this->addFlash('error', 'El nombre no puede tener más de 100 caracteres.');
            return $this->redirectToRoute('categorias_nueva');
        }
        
 
        $categoria = new Categoria();
        $categoria->setNombre($nombre);
        $categoria->setDescripcion($descripcion ?: null);
        $categoria->setActivo(true);
        
        try {
            $entityManager->persist($categoria);
            $entityManager->flush();
            
            $entityManager->clear();
            
            $this->addFlash('success', sprintf('Categoría "%s" creada exitosamente.', $nombre));
            return $this->redirectToRoute('categorias_index');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al crear la categoría: ' . $e->getMessage());
            return $this->redirectToRoute('categorias_nueva');
        }
    }

    #[Route('/categorias/{id}/editar', name: 'categorias_editar', requirements: ['id' => '\d+'])]
    public function editar(int $id, CategoriaRepository $categoriaRepository): Response
    {
        $categoria = $categoriaRepository->find($id);
        
        if (!$categoria) {
            $this->addFlash('error', 'La categoría no existe.');
            return $this->redirectToRoute('categorias_index');
        }
        
        return $this->render('categorias/editar.html.twig', [
            'categoria' => $categoria
        ]);
    }

    #[Route('/categorias/{id}/actualizar', name: 'categorias_actualizar', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function actualizar(int $id, Request $request, EntityManagerInterface $entityManager, CategoriaRepository $categoriaRepository): Response
    {
        $categoria = $categoriaRepository->find($id);
        
        if (!$categoria) {
            $this->addFlash('error', 'La categoría no existe.');
            return $this->redirectToRoute('categorias_index');
        }
        
        $nombre = trim($request->request->get('nombre'));
        $descripcion = trim($request->request->get('descripcion'));
        $activo = (bool) $request->request->get('activo');
        
        // Validaciones básicas
        if (empty($nombre)) {
            $this->addFlash('error', 'El nombre de la categoría es obligatorio.');
            return $this->redirectToRoute('categorias_editar', ['id' => $id]);
        }
        
        if (strlen($nombre) > 100) {
            $this->addFlash('error', 'El nombre no puede tener más de 100 caracteres.');
            return $this->redirectToRoute('categorias_editar', ['id' => $id]);
        }
        
        try {
            // Actualizar los datos de la categoría
            $categoria->setNombre($nombre);
            $categoria->setDescripcion($descripcion ?: null);
            $categoria->setActivo($activo);
            
            // Establecer fecha de actualización con zona horaria de Guatemala
            $guatemala = new \DateTimeZone('America/Guatemala');
            $categoria->setFechaActualizacion(new \DateTime('now', $guatemala));
            
            $entityManager->flush();
            $entityManager->clear();
            
            $this->addFlash('success', sprintf('Categoría "%s" actualizada exitosamente.', $nombre));
            return $this->redirectToRoute('categorias_index');
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error al actualizar la categoría: ' . $e->getMessage());
            return $this->redirectToRoute('categorias_editar', ['id' => $id]);
        }
    }
}