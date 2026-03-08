<?php

namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Helpers\Toast;
use App\Helpers\TextHelper;

class TwigEngine {
    private static $instance;
    private $twig;

    private function __construct() {
        $loader = new FilesystemLoader(__DIR__ . '/../Views/templates');
        // Disable Twig cache to avoid permission issues across users (CLI vs web server)
        $cacheConfig = false;

        $this->twig = new Environment($loader, [
            'cache' => $cacheConfig,
            'auto_reload' => true,
            'debug' => $_ENV['APP_ENV'] === 'dev'
        ]);

        // Adicionar funções globais
        $this->twig->addGlobal('BASE_URL', BASE_URL);
        $this->twig->addGlobal('APP_ENV', $_ENV['APP_ENV'] ?? 'prod');
        $this->twig->addGlobal('TINYMCE_API_KEY', TINYMCE_API_KEY ?? 'no-api-key');
        $this->twig->addGlobal('GOOGLE_MAPS_API_KEY', GOOGLE_MAPS_API_KEY ?? '');
        $this->twig->addGlobal('RECAPTCHA_SITE_KEY', RECAPTCHA_SITE_KEY ?? '');
        $this->twig->addGlobal('institutional_contact', $this->buildInstitutionalContactConfig());
        $this->twig->addGlobal('whatsapp', $this->buildWhatsappConfig());
        $this->twig->addGlobal('toast_html', Toast::render());
        
        // Adicionar dados da sessão para acesso global nos templates
        $this->twig->addGlobal('session', [
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? null,
            'user_email' => $_SESSION['user_email'] ?? null,
            'user_avatar' => $_SESSION['user_avatar'] ?? null,
            'user_role' => $_SESSION['user_role'] ?? null,
        ]);
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $parsedPath = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '/');
        $basePath = trim((string) (parse_url(BASE_URL, PHP_URL_PATH) ?? ''), '/');
        $currentPath = trim($parsedPath, '/');

        if ($basePath !== '' && ($currentPath === $basePath || str_starts_with($currentPath, $basePath . '/'))) {
            $currentPath = trim(substr($currentPath, strlen($basePath)), '/');
        }

        $this->twig->addGlobal('current_path', $currentPath);

        $this->twig->addFilter(new TwigFilter('excerpt', function (?string $content, int $limit = 160) {
            return TextHelper::excerpt($content, $limit);
        }));

        $this->twig->addFilter(new TwigFilter('strip_heading', function (?string $content) {
            return TextHelper::stripLeadingHeading($content);
        }));

        $this->twig->addFilter(new TwigFilter('first_paragraph', function (?string $content, int $limit = 160) {
            return TextHelper::firstParagraphExcerpt($content, $limit);
        }));

        $this->twig->addFunction(new TwigFunction('asset', function (string $path): string {
            return $this->buildVersionedAssetUrl($path);
        }));
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function render($template, $data = []) {
        return $this->twig->render($template . '.twig', $data);
    }

    public function getTwig() {
        return $this->twig;
    }

    private function buildWhatsappConfig(): array
    {
        $number = WHATSAPP_PHONE_E164 ?? '';
        $messages = WHATSAPP_MESSAGES ?? [];
        $links = [];

        foreach ($messages as $key => $message) {
            $links[$key] = $number !== ''
                ? sprintf('https://wa.me/%s?text=%s', $number, rawurlencode((string) $message))
                : '';
        }

        return [
            'number' => $number,
            'display' => WHATSAPP_PHONE_DISPLAY ?? '',
            'messages' => $messages,
            'links' => $links,
        ];
    }

    private function buildInstitutionalContactConfig(): array
    {
        $emails = array_values(array_filter([
            INSTITUTIONAL_EMAIL_PRIMARY ?? '',
            INSTITUTIONAL_EMAIL_SECONDARY ?? '',
        ]));

        return [
            'emergency' => [
                'dial' => EMERGENCY_PHONE_DIAL ?? '',
                'display' => EMERGENCY_PHONE_DISPLAY ?? '',
                'tel' => 'tel:' . (EMERGENCY_PHONE_DIAL ?? ''),
            ],
            'phone' => [
                'dial' => INSTITUTIONAL_PHONE_DIAL ?? '',
                'display' => INSTITUTIONAL_PHONE_DISPLAY ?? '',
                'tel' => 'tel:' . (INSTITUTIONAL_PHONE_DIAL ?? ''),
            ],
            'emails' => [
                'primary' => INSTITUTIONAL_EMAIL_PRIMARY ?? '',
                'secondary' => INSTITUTIONAL_EMAIL_SECONDARY ?? '',
                'all' => $emails,
                'mailto_primary' => 'mailto:' . (INSTITUTIONAL_EMAIL_PRIMARY ?? ''),
            ],
            'address' => [
                'line_1' => INSTITUTIONAL_ADDRESS_LINE_1 ?? '',
                'line_2' => INSTITUTIONAL_ADDRESS_LINE_2 ?? '',
            ],
        ];
    }

    private function buildVersionedAssetUrl(string $path): string
    {
        $relativePath = ltrim($path, '/');
        $absoluteUrl = BASE_URL . $relativePath;
        $publicFile = dirname(__DIR__, 2) . '/public/' . $relativePath;

        if (!is_file($publicFile)) {
            return $absoluteUrl;
        }

        $version = filemtime($publicFile);
        if ($version === false) {
            return $absoluteUrl;
        }

        return $absoluteUrl . '?v=' . $version;
    }
}
