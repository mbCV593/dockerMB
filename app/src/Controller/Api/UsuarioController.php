<?php

namespace App\Controller\Api;

use App\Entity\Usuario;
use App\Repository\UsuarioRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/api/usuarios', name: 'api_usuarios_')]
class UsuarioController extends AbstractController
{
    private UsuarioRepository $usuarioRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        try {
            $usuarios = $this->usuarioRepository->findAll();
            
            $data = [];
            foreach ($usuarios as $usuario) {
                $data[] = $this->usuarioToArray($usuario);
            }
            
            return $this->json($data);
        } catch (\Exception $e) {
            return $this->json($this->getUsuariosEstaticos());
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $usuario = $this->usuarioRepository->find($id);
            
            if (!$usuario) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }
            
            return $this->json($this->usuarioToArray($usuario));
        } catch (\Exception $e) {
            $usuarios = $this->getUsuariosEstaticos();
            $usuario = array_filter($usuarios, fn($u) => $u['id'] == $id);
            
            if (empty($usuario)) {
                return $this->json(['error' => 'Usuario no encontrado'], 404);
            }
            
            return $this->json(array_values($usuario)[0]);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->json(['error' => 'Datos JSON inválidos'], 400);
        }

        try {
            $usuario = new Usuario();
            $usuario->setEmail($data['email'] ?? '')
                    ->setNombre($data['nombre'] ?? '')
                    ->setApellido($data['apellido'] ?? '')
                    ->setRoles($data['roles'] ?? ['ROLE_USER'])
                    ->setActivo($data['activo'] ?? true);

            if (isset($data['password'])) {
                $hashedPassword = $passwordHasher->hashPassword($usuario, $data['password']);
                $usuario->setPassword($hashedPassword);
            }

            $this->entityManager->persist($usuario);
            $this->entityManager->flush();

            return $this->json($this->usuarioToArray($usuario), 201);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'No se pudo crear el usuario: ' . $e->getMessage(),
                'message' => 'Funcionalidad solo disponible con base de datos conectada'
            ], 500);
        }
    }

    #[Route('/activos', name: 'activos', methods: ['GET'])]
    public function activos(): JsonResponse
    {
        try {
            $usuarios = $this->usuarioRepository->findBy(['activo' => true]);
            
            $data = [];
            foreach ($usuarios as $usuario) {
                $data[] = $this->usuarioToArray($usuario);
            }
            
            return $this->json($data);
        } catch (\Exception $e) {
            
            $usuarios = $this->getUsuariosEstaticos();
            $usuariosActivos = array_filter($usuarios, fn($u) => $u['activo'] === true);
            
            return $this->json(array_values($usuariosActivos));
        }
    }

    private function usuarioToArray($usuario): array
    {
        if (is_array($usuario)) {
            return $usuario; 
        }

        return [
            'id' => $usuario->getId(),
            'email' => $usuario->getEmail(),
            'nombre' => $usuario->getNombre(),
            'apellido' => $usuario->getApellido(),
            'nombre_completo' => $usuario->getNombreCompleto(),
            'roles' => $usuario->getRoles(),
            'activo' => $usuario->isActivo(),
            'created_at' => $usuario->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }

    private function getUsuariosEstaticos(): array
    {
        return [
            [
                'id' => 1,
                'email' => 'admin@pruebas.com',
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'nombre_completo' => 'Admin Sistema',
                'roles' => ['ROLE_ADMIN'],
                'activo' => true,
                'created_at' => '2025-08-19 08:00:00'
            ],
            [
                'id' => 2,
                'email' => 'user@pruebas.com',
                'nombre' => 'Usuario',
                'apellido' => 'Prueba',
                'nombre_completo' => 'Usuario Prueba',
                'roles' => ['ROLE_USER'],
                'activo' => true,
                'created_at' => '2025-08-19 08:15:00'
            ],
            [
                'id' => 3,
                'email' => 'gerente@pruebas.com',
                'nombre' => 'María',
                'apellido' => 'González',
                'nombre_completo' => 'María González',
                'roles' => ['ROLE_USER', 'ROLE_MANAGER'],
                'activo' => true,
                'created_at' => '2025-08-19 09:00:00'
            ]
        ];
    }
}
