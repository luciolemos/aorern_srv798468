<?php

namespace Tests\Services;

use App\Services\MembershipPresentationService;
use PHPUnit\Framework\TestCase;

class MembershipPresentationServiceTest extends TestCase
{
    public function testEnrichForListBuildsAvatarPreviewAndLabels(): void
    {
        $service = new MembershipPresentationService();

        $input = [
            'avatar' => 'uploads/users/avatar.png',
            'cidade' => 'Natal',
            'uf' => 'RN',
            'last_notification_type' => 'complementacao',
            'documentos_json' => json_encode([
                [
                    'name' => 'Foto',
                    'path' => 'uploads/filiacao/foto.png',
                    'mime_type' => 'image/png',
                ],
                [
                    'name' => 'PDF',
                    'path' => 'uploads/filiacao/doc.pdf',
                    'mime_type' => 'application/pdf',
                ],
            ], JSON_UNESCAPED_SLASHES),
        ];

        $out = $service->enrichForList($input);

        $this->assertSame('Natal/RN', $out['localizacao_label']);
        $this->assertSame('Complementação', $out['notification_type_label']);
        $this->assertStringContainsString('uploads/users/avatar.png', (string) $out['avatar_url']);
        $this->assertCount(2, $out['documentos']);
        $this->assertNotNull($out['preview_documento']);
        $this->assertTrue((bool) $out['preview_documento']['is_image']);
    }

    public function testEnrichForListHandlesInvalidDocumentJsonGracefully(): void
    {
        $service = new MembershipPresentationService();

        $out = $service->enrichForList([
            'avatar' => null,
            'cidade' => '',
            'uf' => '',
            'last_notification_type' => null,
            'documentos_json' => '{invalido}',
        ]);

        $this->assertSame('-', $out['localizacao_label']);
        $this->assertSame('Notificação', $out['notification_type_label']);
        $this->assertNull($out['avatar_url']);
        $this->assertSame([], $out['documentos']);
        $this->assertNull($out['preview_documento']);
    }
}

