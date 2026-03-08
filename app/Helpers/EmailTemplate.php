<?php

namespace App\Helpers;

class EmailTemplate
{
    public static function render(string $title, string $intro, string $contentHtml, array $actions = []): string
    {
        $headerUrl = self::versionedAssetUrl('assets/images/brand/aorern-cabecalho-email-minimalista.png');
        $siteUrl = rtrim(BASE_URL, '/');
        $contactEmail = htmlspecialchars((string) (INSTITUTIONAL_EMAIL_PRIMARY ?? ''), ENT_QUOTES, 'UTF-8');
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $safeIntro = htmlspecialchars($intro, ENT_QUOTES, 'UTF-8');
        $actionsHtml = self::renderActions($actions);

        return <<<HTML
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$safeTitle}</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#18202b;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f6f8;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="padding:0;background:#ffffff;">
                            <img src="{$headerUrl}" alt="Cabeçalho institucional AORE/RN" style="display:block;width:100%;height:auto;border:0;">
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px 32px 20px;">
                            <h1 style="margin:0 0 12px;font-size:24px;line-height:1.25;color:#0b2a4a;">{$safeTitle}</h1>
                            <p style="margin:0 0 24px;font-size:15px;line-height:1.6;color:#4b5563;">{$safeIntro}</p>
                            <div style="font-size:15px;line-height:1.7;color:#18202b;">
                                {$contentHtml}
                            </div>
                            {$actionsHtml}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px 32px;border-top:1px solid #e5e7eb;background:#fafbfc;">
                            <p style="margin:0 0 6px;font-size:13px;color:#6b7280;">Portal institucional da AORE/RN</p>
                            <p style="margin:0 0 6px;font-size:13px;color:#6b7280;">Site: <a href="{$siteUrl}" style="color:#0b5cab;text-decoration:none;">{$siteUrl}</a></p>
                            <p style="margin:0;font-size:13px;color:#6b7280;">Contato: <a href="mailto:{$contactEmail}" style="color:#0b5cab;text-decoration:none;">{$contactEmail}</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }

    private static function versionedAssetUrl(string $relativePath): string
    {
        $publicPath = __DIR__ . '/../../public/' . ltrim($relativePath, '/');
        $version = is_file($publicPath) ? (string) filemtime($publicPath) : (string) time();

        return rtrim(BASE_URL, '/') . '/' . ltrim($relativePath, '/') . '?v=' . rawurlencode($version);
    }

    private static function renderActions(array $actions): string
    {
        if ($actions === []) {
            return '';
        }

        $buttons = '';

        foreach ($actions as $action) {
            $label = htmlspecialchars((string) ($action['label'] ?? ''), ENT_QUOTES, 'UTF-8');
            $url = htmlspecialchars((string) ($action['url'] ?? '#'), ENT_QUOTES, 'UTF-8');
            $background = htmlspecialchars((string) ($action['background'] ?? '#0b5cab'), ENT_QUOTES, 'UTF-8');
            $color = htmlspecialchars((string) ($action['color'] ?? '#ffffff'), ENT_QUOTES, 'UTF-8');

            if ($label === '') {
                continue;
            }

            $buttons .= <<<HTML
<a href="{$url}" style="display:inline-block;margin:0 12px 12px 0;padding:12px 18px;border-radius:8px;background:{$background};color:{$color};text-decoration:none;font-size:14px;font-weight:700;">{$label}</a>
HTML;
        }

        if ($buttons === '') {
            return '';
        }

        return <<<HTML
<div style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb;">
    <p style="margin:0 0 14px;font-size:13px;color:#6b7280;text-transform:uppercase;letter-spacing:0.08em;">Ações rápidas</p>
    {$buttons}
</div>
HTML;
    }
}
