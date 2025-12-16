<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class GalleryResourcesTest extends TestCase
{
    private string $projectRoot;

    protected function setUp(): void
    {
        $root = realpath(__DIR__ . '/..');
        $this->projectRoot = $root !== false ? $root : __DIR__;
    }

    public function testGalleryCssFileExists(): void
    {
        $cssPath = $this->projectRoot . '/public/assets/css/gallery.css';
        $this->assertFileExists($cssPath, 'Arquivo CSS da galeria não encontrado.');
    }

    public function testGalleryJsFileExists(): void
    {
        $jsPath = $this->projectRoot . '/public/assets/js/gallery.js';
        $this->assertFileExists($jsPath, 'Arquivo JS da galeria não encontrado.');
    }

    public function testGalleryViewContainsLightboxHooks(): void
    {
        $viewPath = $this->projectRoot . '/app/Views/templates/site/pages/galeria.twig';
        $this->assertFileExists($viewPath, 'View pública da galeria não encontrada.');

        $contents = file_get_contents($viewPath) ?: '';
        $this->assertStringContainsString('gallery-lightbox', $contents, 'Lightbox não definido na view da galeria.');
        $this->assertStringContainsString('gallery-data', $contents, 'Payload JSON da galeria não renderizado.');
    }
}
