<?php

namespace App\Controller;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/usuarios', name: 'app_usuario_')]
class UsuarioController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UsuarioRepository $usuarioRepository
    ) {}

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $usuarios = $this->usuarioRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('usuarios/index.html.twig', [
            'usuarios' => $usuarios,
        ]);
    }

    #[Route('/nuevo', name: 'nuevo')]
    public function nuevo(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = new Usuario();

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $activo = $request->request->get('activo') ? true : false;
            $roles = $request->request->get('roles', []);

            // Validación básica
            if (empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
                $this->addFlash('error', 'Todos los campos son obligatorios.');
                return $this->render('usuarios/nuevo.html.twig', [
                    'usuario' => $usuario,
                    'errors' => ['Todos los campos son obligatorios']
                ]);
            }

            // Verificar que el email no existe
            $existeUsuario = $this->usuarioRepository->findOneBy(['email' => $email]);
            if ($existeUsuario) {
                $this->addFlash('error', 'Ya existe un usuario con este email.');
                return $this->render('usuarios/nuevo.html.twig', [
                    'usuario' => $usuario,
                    'errors' => ['Ya existe un usuario con este email']
                ]);
            }

            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setEmail($email);
            $usuario->setActivo($activo);
            $usuario->setRoles($roles);
            
            // Hashear la contraseña
            $hashedPassword = $passwordHasher->hashPassword($usuario, $password);
            $usuario->setPassword($hashedPassword);

            $this->entityManager->persist($usuario);
            $this->entityManager->flush();

            $this->addFlash('success', 'Usuario creado exitosamente.');
            return $this->redirectToRoute('app_usuario_index');
        }

        return $this->render('usuarios/nuevo.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    #[Route('/{id}/editar', name: 'editar')]
    public function editar(int $id, Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $usuario = $this->usuarioRepository->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $email = $request->request->get('email');
            $password = $request->request->get('password');
            $activo = $request->request->get('activo') ? true : false;
            $roles = $request->request->get('roles', []);

            // Validación básica
            if (empty($nombre) || empty($apellido) || empty($email)) {
                $this->addFlash('error', 'Los campos nombre, apellido y email son obligatorios.');
                return $this->render('usuarios/editar.html.twig', [
                    'usuario' => $usuario,
                    'errors' => ['Los campos nombre, apellido y email son obligatorios']
                ]);
            }

            // Verificar que el email no existe (excepto el actual)
            $existeUsuario = $this->usuarioRepository->createQueryBuilder('u')
                ->where('u.email = :email')
                ->andWhere('u.id != :id')
                ->setParameter('email', $email)
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();

            if ($existeUsuario) {
                $this->addFlash('error', 'Ya existe otro usuario con este email.');
                return $this->render('usuarios/editar.html.twig', [
                    'usuario' => $usuario,
                    'errors' => ['Ya existe otro usuario con este email']
                ]);
            }

            $usuario->setNombre($nombre);
            $usuario->setApellido($apellido);
            $usuario->setEmail($email);
            $usuario->setActivo($activo);
            $usuario->setRoles($roles);

            // Solo actualizar contraseña si se proporciona una nueva
            if (!empty($password)) {
                $hashedPassword = $passwordHasher->hashPassword($usuario, $password);
                $usuario->setPassword($hashedPassword);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Usuario actualizado exitosamente.');
            return $this->redirectToRoute('app_usuario_index');
        }

        return $this->render('usuarios/editar.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    #[Route('/{id}/ver', name: 'ver')]
    public function ver(int $id): Response
    {
        $usuario = $this->usuarioRepository->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }

        return $this->render('usuarios/ver.html.twig', [
            'usuario' => $usuario,
        ]);
    }

    #[Route('/{id}/eliminar', name: 'eliminar', methods: ['POST'])]
    public function eliminar(int $id): Response
    {
        $usuario = $this->usuarioRepository->find($id);

        if (!$usuario) {
            throw $this->createNotFoundException('Usuario no encontrado.');
        }

        $this->entityManager->remove($usuario);
        $this->entityManager->flush();

        $this->addFlash('success', 'Usuario eliminado exitosamente.');
        return $this->redirectToRoute('app_usuario_index');
    }
}
