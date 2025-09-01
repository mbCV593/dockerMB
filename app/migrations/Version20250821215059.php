<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250821215059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE movimientos_inventario DROP FOREIGN KEY movimientos_inventario_ibfk_1');
        $this->addSql('ALTER TABLE movimientos_inventario DROP FOREIGN KEY movimientos_inventario_ibfk_2');
        $this->addSql('DROP TABLE producto');
        $this->addSql('DROP TABLE movimientos_inventario');
        $this->addSql('ALTER TABLE categorias ADD updated_at DATETIME DEFAULT NULL, CHANGE descripcion descripcion LONGTEXT DEFAULT NULL, CHANGE activo activo TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE productos CHANGE descripcion descripcion LONGTEXT DEFAULT NULL, CHANGE stock stock INT NOT NULL, CHANGE activo activo TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE updated_at updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE stock_minimo stock_minimo INT NOT NULL');
        $this->addSql('ALTER TABLE productos RENAME INDEX categoria_id TO IDX_767490E63397707A');
        $this->addSql('ALTER TABLE productos RENAME INDEX proveedor_id TO IDX_767490E6CB305D73');
        $this->addSql('ALTER TABLE proveedores CHANGE direccion direccion LONGTEXT DEFAULT NULL, CHANGE activo activo TINYINT(1) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE usuarios CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE activo activo TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE usuarios RENAME INDEX uniq_usuarios_email TO UNIQ_EF687F2E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE producto (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, precio VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, descripcion VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE movimientos_inventario (id INT AUTO_INCREMENT NOT NULL, producto_id INT NOT NULL, usuario_id INT NOT NULL, tipo_movimiento VARCHAR(20) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, cantidad INT NOT NULL, stock_anterior INT NOT NULL, stock_nuevo INT NOT NULL, motivo TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, INDEX producto_id (producto_id), INDEX usuario_id (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE movimientos_inventario ADD CONSTRAINT movimientos_inventario_ibfk_1 FOREIGN KEY (producto_id) REFERENCES productos (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE movimientos_inventario ADD CONSTRAINT movimientos_inventario_ibfk_2 FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE productos CHANGE descripcion descripcion TEXT DEFAULT NULL, CHANGE stock stock INT DEFAULT 0 NOT NULL, CHANGE stock_minimo stock_minimo INT DEFAULT 5, CHANGE activo activo TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE updated_at updated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE productos RENAME INDEX idx_767490e63397707a TO categoria_id');
        $this->addSql('ALTER TABLE productos RENAME INDEX idx_767490e6cb305d73 TO proveedor_id');
        $this->addSql('ALTER TABLE categorias DROP updated_at, CHANGE descripcion descripcion TEXT DEFAULT NULL, CHANGE activo activo TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE proveedores CHANGE direccion direccion TEXT DEFAULT NULL, CHANGE activo activo TINYINT(1) DEFAULT 1 NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE usuarios CHANGE created_at created_at DATETIME NOT NULL, CHANGE activo activo TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE usuarios RENAME INDEX uniq_ef687f2e7927c74 TO UNIQ_usuarios_email');
    }
}
