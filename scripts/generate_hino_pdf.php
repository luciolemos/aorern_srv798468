<?php

declare(strict_types=1);

$outputDir = __DIR__ . '/../public/assets/docs';
$outputFile = $outputDir . '/hino-aorern.pdf';

$lines = [
    ['text' => 'Cancao Oficial da AORE Natal - Reserva Atenta e Forte', 'size' => 16, 'gap' => 24],
    ['text' => 'Associacao dos Oficiais da Reserva do Exercito do Rio Grande do Norte', 'size' => 10, 'gap' => 20],

    ['text' => 'Introducao (falado + musical crescente)', 'size' => 12, 'gap' => 18],
    ['text' => 'Tropa, sentido!', 'size' => 11, 'gap' => 14],
    ['text' => 'Olhar a direita!!!!', 'size' => 11, 'gap' => 20],

    ['text' => 'Verso 1', 'size' => 12, 'gap' => 18],
    ['text' => 'AORE Natal, firmes na missao,', 'size' => 11, 'gap' => 14],
    ['text' => 'Servindo ao Brasil com fe e razao,', 'size' => 11, 'gap' => 14],
    ['text' => 'Civismo e cidadania em cada acao,', 'size' => 11, 'gap' => 14],
    ['text' => 'Moral e etica guiando a nacao.', 'size' => 11, 'gap' => 14],
    ['text' => '', 'size' => 11, 'gap' => 8],
    ['text' => 'Hierarquia e disciplina a conduzir,', 'size' => 11, 'gap' => 14],
    ['text' => 'Honestidade em todo o agir e servir,', 'size' => 11, 'gap' => 14],
    ['text' => 'Honra estampada em nosso olhar,', 'size' => 11, 'gap' => 14],
    ['text' => 'Compromisso eterno de sempre lutar.', 'size' => 11, 'gap' => 20],

    ['text' => 'Pre-Refrão', 'size' => 12, 'gap' => 18],
    ['text' => 'Somos reserva que nao recua,', 'size' => 11, 'gap' => 14],
    ['text' => 'Forca que nunca se apaga ou flutua,', 'size' => 11, 'gap' => 14],
    ['text' => 'Unidos no mesmo ideal,', 'size' => 11, 'gap' => 14],
    ['text' => 'Defendendo os valores do nosso Brasil.', 'size' => 11, 'gap' => 20],

    ['text' => 'Refrão', 'size' => 12, 'gap' => 18],
    ['text' => 'Reserva atenta e forte!', 'size' => 11, 'gap' => 14],
    ['text' => 'Nosso lema, nossa voz!', 'size' => 11, 'gap' => 14],
    ['text' => 'AORE Natal presente,', 'size' => 11, 'gap' => 14],
    ['text' => 'Sempre unidos, sempre nos!', 'size' => 11, 'gap' => 14],
    ['text' => '', 'size' => 11, 'gap' => 8],
    ['text' => 'Reserva atenta e forte!', 'size' => 11, 'gap' => 14],
    ['text' => 'Com honra, fe e coracao,', 'size' => 11, 'gap' => 14],
    ['text' => 'Marchamos firmes pelo Brasil,', 'size' => 11, 'gap' => 14],
    ['text' => 'Em defesa da nossa nacao!', 'size' => 11, 'gap' => 20],

    ['text' => 'Verso 2', 'size' => 12, 'gap' => 18],
    ['text' => 'Levamos adiante o legado imortal,', 'size' => 11, 'gap' => 14],
    ['text' => 'De Caxias, Correia Lima e Apollo Rezk, ideal,', 'size' => 11, 'gap' => 14],
    ['text' => 'Exemplo de bravura, honra e dever,', 'size' => 11, 'gap' => 14],
    ['text' => 'Luz que nos guia no servir e vencer.', 'size' => 11, 'gap' => 14],
    ['text' => '', 'size' => 11, 'gap' => 8],
    ['text' => 'Apoiamos os Oficiais R2 do Brasil,', 'size' => 11, 'gap' => 14],
    ['text' => 'Com lealdade, respeito e valor varonil,', 'size' => 11, 'gap' => 14],
    ['text' => 'Fortalecendo os lacos do Exercito,', 'size' => 11, 'gap' => 14],
    ['text' => 'Na sociedade, nossa missao e merito.', 'size' => 11, 'gap' => 20],

    ['text' => 'Pre-Refrão 2', 'size' => 12, 'gap' => 18],
    ['text' => 'Somos porta-vozes da farda honrada,', 'size' => 11, 'gap' => 14],
    ['text' => 'Na vida civil, voz respeitada,', 'size' => 11, 'gap' => 14],
    ['text' => 'Levamos valores por onde for,', 'size' => 11, 'gap' => 14],
    ['text' => 'Com disciplina, verdade e amor.', 'size' => 11, 'gap' => 20],

    ['text' => 'Refrão (repeticao com mais forca)', 'size' => 12, 'gap' => 18],
    ['text' => 'Reserva atenta e forte!', 'size' => 11, 'gap' => 14],
    ['text' => 'Nosso lema, nossa voz!', 'size' => 11, 'gap' => 14],
    ['text' => 'AORE Natal presente,', 'size' => 11, 'gap' => 14],
    ['text' => 'Sempre unidos, sempre nos!', 'size' => 11, 'gap' => 14],
    ['text' => '', 'size' => 11, 'gap' => 8],
    ['text' => 'Reserva atenta e forte!', 'size' => 11, 'gap' => 14],
    ['text' => 'Com honra, fe e coracao,', 'size' => 11, 'gap' => 14],
    ['text' => 'Marchamos firmes pelo Brasil,', 'size' => 11, 'gap' => 14],
    ['text' => 'Em defesa da nossa nacao!', 'size' => 11, 'gap' => 20],

    ['text' => 'Ponte (parte mais emocional)', 'size' => 12, 'gap' => 18],
    ['text' => 'De norte a sul ecoa a cancao,', 'size' => 11, 'gap' => 14],
    ['text' => 'E AORE Natal em cada missao,', 'size' => 11, 'gap' => 14],
    ['text' => 'Unidos na etica, na honra e no bem,', 'size' => 11, 'gap' => 14],
    ['text' => 'Servindo ao povo, servindo alem.', 'size' => 11, 'gap' => 20],

    ['text' => 'Final (grandioso e solene)', 'size' => 12, 'gap' => 18],
    ['text' => 'AORE Natal, orgulho de ser,', 'size' => 11, 'gap' => 14],
    ['text' => 'Nossa bandeira e servir e vencer,', 'size' => 11, 'gap' => 14],
    ['text' => 'Reserva que inspira, exemplo de acao,', 'size' => 11, 'gap' => 14],
    ['text' => 'Guardando os valores da nossa nacao!', 'size' => 11, 'gap' => 14],
    ['text' => '', 'size' => 11, 'gap' => 8],
    ['text' => 'Tropa, descansar!!!!', 'size' => 11, 'gap' => 14],
];

if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
    fwrite(STDERR, "Nao foi possivel criar o diretorio de saida.\n");
    exit(1);
}

$escapePdfText = static function (string $text): string {
    return str_replace(
        ['\\', '(', ')'],
        ['\\\\', '\\(', '\\)'],
        $text
    );
};

$topY = 800;
$bottomY = 60;
$leftX = 50;

$pages = [];
$pageContent = "BT\n";
$currentY = $topY;
$currentSize = null;
$firstOnPage = true;

foreach ($lines as $line) {
    $size = $line['size'];
    $text = $escapePdfText($line['text']);
    $gap = $line['gap'];

    if (!$firstOnPage) {
        if (($currentY - $gap) < $bottomY) {
            $pageContent .= "ET";
            $pages[] = $pageContent;
            $pageContent = "BT\n";
            $currentY = $topY;
            $currentSize = null;
            $firstOnPage = true;
        } else {
            $pageContent .= sprintf("0 -%d Td\n", $gap);
            $currentY -= $gap;
        }
    }

    if ($firstOnPage) {
        $pageContent .= sprintf("%d %d Td\n", $leftX, $topY);
        $firstOnPage = false;
    }

    if ($size !== $currentSize) {
        $pageContent .= sprintf("/F1 %d Tf\n", $size);
        $currentSize = $size;
    }

    $pageContent .= sprintf("(%s) Tj\n", $text);
}

$pageContent .= "ET";
$pages[] = $pageContent;

$objects = [];
$objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";

$pageCount = count($pages);
$firstPageObj = 3;
$fontObj = $firstPageObj + $pageCount;
$firstContentObj = $fontObj + 1;

$kids = [];
for ($i = 0; $i < $pageCount; $i++) {
    $pageObj = $firstPageObj + $i;
    $contentObj = $firstContentObj + $i;
    $kids[] = $pageObj . ' 0 R';
    $objects[$pageObj] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 " . $fontObj . " 0 R >> >> /Contents " . $contentObj . " 0 R >>";
}

$objects[2] = "<< /Type /Pages /Kids [" . implode(' ', $kids) . "] /Count " . $pageCount . " >>";
$objects[$fontObj] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

for ($i = 0; $i < $pageCount; $i++) {
    $contentObj = $firstContentObj + $i;
    $content = $pages[$i];
    $objects[$contentObj] = "<< /Length " . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
}

$maxObj = max(array_keys($objects));

$pdf = "%PDF-1.4\n";
$offsets = [0];

for ($i = 1; $i <= $maxObj; $i++) {
    $offsets[$i] = strlen($pdf);
    $pdf .= $i . " 0 obj\n" . $objects[$i] . "\nendobj\n";
}

$xrefOffset = strlen($pdf);
$pdf .= "xref\n0 " . ($maxObj + 1) . "\n";
$pdf .= "0000000000 65535 f \n";

for ($i = 1; $i <= $maxObj; $i++) {
    $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
}

$pdf .= "trailer\n<< /Size " . ($maxObj + 1) . " /Root 1 0 R >>\n";
$pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

if (file_put_contents($outputFile, $pdf) === false) {
    fwrite(STDERR, "Nao foi possivel escrever o PDF.\n");
    exit(1);
}

fwrite(STDOUT, $outputFile . PHP_EOL);
