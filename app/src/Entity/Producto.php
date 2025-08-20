<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
#[ORM\Table(name: 'productos')]
class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $precio = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descripcion = null;

    #[ORM\Column]
    private int $stock = 0;

    #[ORM\Column]
    private int $stockMinimo = 5;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $codigoBarras = null;

    #[ORM\Column]
    private bool $activo = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: Categoria::class, inversedBy: 'productos')]
    #[ORM\JoinColumn(name: 'categoria_id', referencedColumnName: 'id', nullable: true)]
    private ?Categoria $categoria = null;

    #[ORM\ManyToOne(targetEntity: Proveedor::class, inversedBy: 'productos')]
    #[ORM\JoinColumn(name: 'proveedor_id', referencedColumnName: 'id', nullable: true)]
    private ?Proveedor $proveedor = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getPrecio(): ?string
    {
        return $this->precio;
    }

    public function setPrecio(string $precio): static
    {
        $this->precio = $precio;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): static
    {
        $this->stock = $stock;
        $this->updatedAt = new \DateTimeImmutable();
        return $this;
    }

    public function getStockMinimo(): int
    {
        return $this->stockMinimo;
    }

    public function setStockMinimo(int $stockMinimo): static
    {
        $this->stockMinimo = $stockMinimo;
        return $this;
    }

    public function getCodigoBarras(): ?string
    {
        return $this->codigoBarras;
    }

    public function setCodigoBarras(?string $codigoBarras): static
    {
        $this->codigoBarras = $codigoBarras;
        return $this;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getCategoria(): ?Categoria
    {
        return $this->categoria;
    }

    public function setCategoria(?Categoria $categoria): static
    {
        $this->categoria = $categoria;
        return $this;
    }

    public function getProveedor(): ?Proveedor
    {
        return $this->proveedor;
    }

    public function setProveedor(?Proveedor $proveedor): static
    {
        $this->proveedor = $proveedor;
        return $this;
    }

    public function getEstadoStock(): string
    {
        if ($this->stock <= 0) {
            return 'SIN_STOCK';
        } elseif ($this->stock <= $this->stockMinimo) {
            return 'STOCK_BAJO';
        }
        return 'STOCK_NORMAL';
    }

    public function getValorInventario(): float
    {
        return (float)$this->precio * $this->stock;
    }
}
