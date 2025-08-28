<?php

namespace App\Controller;

use App\Entity\Proveedor;
use App\Repository\ProveedorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/proveedores')]
class ProveedorController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProveedorRepository $proveedorRepository;

    public function __construct(EntityManagerInterface $entityManager, ProveedorRepository $proveedorRepository)
    {
        $this->entityManager = $entityManager;
        $this->proveedorRepository = $proveedorRepository;
    }

    #[Route('/', name: 'proveedores_index')]
    public function index(): Response
    {
        $proveedores = $this->proveedorRepository->findAllOrderedByIdDirect();
        $total = count($proveedores);

        return $this->render('proveedores/index.html.twig', [
            'proveedores' => $proveedores,
            'total' => $total
        ]);
    }

    #[Route('/nuevo', name: 'proveedores_nuevo')]
    public function nuevo(): Response
    {
        return $this->render('proveedores/nuevo.html.twig');
    }

    #[Route('/crear', name: 'proveedores_crear', methods: ['POST'])]
    public function crear(Request $request): Response
    {
        $nombre = trim($request->request->get('nombre'));
        $contacto = trim($request->request->get('contacto'));
        $email = trim($request->request->get('email'));
        $telefono = trim($request->request->get('telefono'));
        $direccion = trim($request->request->get('direccion'));
        $activo = $request->request->get('activo') === '1';

        // Validaciones
        if (empty($nombre)) {
            $this->addFlash('error', 'El nombre del proveedor es obligatorio.');
            return $this->redirectToRoute('proveedores_nuevo');
        }

        if (strlen($nombre) > 200) {
            $this->addFlash('error', 'El nombre no puede superar los 200 caracteres.');
            return $this->redirectToRoute('proveedores_nuevo');
        }

        // Validar email si se proporciona
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'El formato del email no es válido.');
            return $this->redirectToRoute('proveedores_nuevo');
        }

        try {
            $proveedor = new Proveedor();
            $proveedor->setNombre($nombre);
            $proveedor->setContacto($contacto ?: null);
            $proveedor->setEmail($email ?: null);
            $proveedor->setTelefono($telefono ?: null);
            $proveedor->setDireccion($direccion ?: null);
            $proveedor->setActivo($activo);

            $this->entityManager->persist($proveedor);
            $this->entityManager->flush();

            $this->addFlash('success', "Proveedor '{$nombre}' creado exitosamente.");
            return $this->redirectToRoute('proveedores_index');

        } catch (\Exception $e) {
            error_log('Error al crear proveedor: ' . $e->getMessage());
            $this->addFlash('error', 'Error al crear el proveedor. Intente nuevamente.');
            return $this->redirectToRoute('proveedores_nuevo');
        }
    }

    #[Route('/{id}/editar', name: 'proveedores_editar')]
    public function editar(int $id): Response
    {
        $proveedor = $this->proveedorRepository->find($id);
        
        if (!$proveedor) {
            $this->addFlash('error', 'Proveedor no encontrado.');
            return $this->redirectToRoute('proveedores_index');
        }

        return $this->render('proveedores/editar.html.twig', [
            'proveedor' => $proveedor
        ]);
    }

    #[Route('/{id}/actualizar', name: 'proveedores_actualizar', methods: ['POST'])]
    public function actualizar(Request $request, int $id): Response
    {
        $proveedor = $this->proveedorRepository->find($id);
        
        if (!$proveedor) {
            $this->addFlash('error', 'Proveedor no encontrado.');
            return $this->redirectToRoute('proveedores_index');
        }

        $nombre = trim($request->request->get('nombre'));
        $contacto = trim($request->request->get('contacto'));
        $email = trim($request->request->get('email'));
        $telefono = trim($request->request->get('telefono'));
        $direccion = trim($request->request->get('direccion'));
        $activo = $request->request->get('activo') === '1';

        // Validaciones
        if (empty($nombre)) {
            $this->addFlash('error', 'El nombre del proveedor es obligatorio.');
            return $this->redirectToRoute('proveedores_editar', ['id' => $id]);
        }

        if (strlen($nombre) > 200) {
            $this->addFlash('error', 'El nombre no puede superar los 200 caracteres.');
            return $this->redirectToRoute('proveedores_editar', ['id' => $id]);
        }

        // Validar email si se proporciona
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'El formato del email no es válido.');
            return $this->redirectToRoute('proveedores_editar', ['id' => $id]);
        }

        try {
            $proveedor->setNombre($nombre);
            $proveedor->setContacto($contacto ?: null);
            $proveedor->setEmail($email ?: null);
            $proveedor->setTelefono($telefono ?: null);
            $proveedor->setDireccion($direccion ?: null);
            $proveedor->setActivo($activo);

            $this->entityManager->flush();

            $this->addFlash('success', "Proveedor '{$nombre}' actualizado exitosamente.");
            return $this->redirectToRoute('proveedores_index');

        } catch (\Exception $e) {
            error_log('Error al actualizar proveedor: ' . $e->getMessage());
            $this->addFlash('error', 'Error al actualizar el proveedor. Intente nuevamente.');
            return $this->redirectToRoute('proveedores_editar', ['id' => $id]);
        }
    }

    #[Route('/{id}/eliminar', name: 'proveedores_eliminar', methods: ['POST'])]
    public function eliminar(int $id): Response
    {
        $proveedor = $this->proveedorRepository->find($id);
        
        if (!$proveedor) {
            $this->addFlash('error', 'Proveedor no encontrado.');
            return $this->redirectToRoute('proveedores_index');
        }

        try {
            $nombre = $proveedor->getNombre();
            $this->entityManager->remove($proveedor);
            $this->entityManager->flush();

            $this->addFlash('success', "Proveedor '{$nombre}' eliminado exitosamente.");
        } catch (\Exception $e) {
            error_log('Error al eliminar proveedor: ' . $e->getMessage());
            $this->addFlash('error', 'Error al eliminar el proveedor. Es posible que tenga productos asociados.');
        }

        return $this->redirectToRoute('proveedores_index');
    }
}
