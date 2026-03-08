import fs from 'node:fs/promises';
import path from 'node:path';
import sharp from '/tmp/aorern-render/node_modules/sharp/lib/index.js';

const brandDir = '/var/www/aorern/public/assets/images/brand';
const sourcePng = '/var/www/aorern/public/assets/images/aore1.png';

const navy = '#03045e';
const navySoft = '#0f1f68';
const gold = '#d4af37';
const goldSoft = '#f3dc85';
const ink = '#18202b';
const paper = '#fbfaf6';
const stripeHeight = 14;
const headerStripes = `
  <rect x="0" y="0" width="1600" height="${stripeHeight}" fill="#cc2229"/>
  <rect x="0" y="${stripeHeight}" width="1600" height="${stripeHeight}" fill="#2a86c5"/>
  <rect x="0" y="${stripeHeight * 2}" width="1600" height="${stripeHeight}" fill="#e7d148"/>`;

const logoBase64 = await fs.readFile(sourcePng, 'base64');

const emailHeaderSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="420" viewBox="0 0 1600 420" role="img" aria-label="Cabecalho institucional da AORERN">
  <defs>
    <linearGradient id="headerBg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0%" stop-color="${navy}"/>
      <stop offset="58%" stop-color="${navySoft}"/>
      <stop offset="100%" stop-color="#1e325f"/>
    </linearGradient>
    <filter id="softShadow" x="-20%" y="-20%" width="140%" height="160%">
      <feDropShadow dx="0" dy="14" stdDeviation="18" flood-color="#000000" flood-opacity="0.18"/>
    </filter>
  </defs>

  <rect width="1600" height="420" rx="34" fill="url(#headerBg)"/>
${headerStripes}
  <circle cx="1450" cy="-40" r="240" fill="rgba(255,255,255,0.04)"/>
  <circle cx="1510" cy="20" r="150" fill="rgba(255,255,255,0.035)"/>
  <rect x="78" y="74" width="8" height="268" rx="4" fill="${gold}"/>
  <rect x="430" y="112" width="710" height="2" fill="rgba(243,220,133,0.28)"/>
  <rect x="430" y="322" width="710" height="2" fill="rgba(243,220,133,0.22)"/>

  <g filter="url(#softShadow)">
    <rect x="118" y="96" width="236" height="236" rx="30" fill="rgba(255,255,255,0.055)" stroke="rgba(255,255,255,0.14)" stroke-width="2"/>
    <image x="138" y="116" width="196" height="196" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  </g>

  <text x="430" y="152" fill="${goldSoft}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="23" font-weight="700" letter-spacing="4.8">ASSOCIAÇÃO DOS OFICIAIS DA RESERVA</text>
  <text x="430" y="220" fill="#ffffff" font-family="'DejaVu Sans','Arial',sans-serif" font-size="84" font-weight="800" letter-spacing="4">AORE/RN</text>
  <text x="430" y="270" fill="rgba(255,255,255,0.86)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="28" font-weight="600" letter-spacing="0.8">Rio Grande do Norte</text>
  <text x="430" y="308" fill="rgba(255,255,255,0.72)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24">Perpetuum Officium erga Patriam</text>

  <g transform="translate(1165 138)">
    <text x="0" y="0" fill="${goldSoft}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="18" font-weight="700" letter-spacing="2.8">COMUNICACAO</text>
    <text x="0" y="42" fill="#ffffff" font-family="'DejaVu Sans','Arial',sans-serif" font-size="18" font-weight="700">aorern.comunicacao@gmail.com</text>
    <text x="0" y="76" fill="rgba(255,255,255,0.68)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="16">cabecalho institucional para e-mails</text>
  </g>
</svg>
`;

const letterheadSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="2480" height="3508" viewBox="0 0 2480 3508" role="img" aria-label="Timbre oficial da AORERN em A4">
  <defs>
    <linearGradient id="paperBg" x1="0" y1="0" x2="0" y2="1">
      <stop offset="0%" stop-color="#ffffff"/>
      <stop offset="100%" stop-color="${paper}"/>
    </linearGradient>
    <linearGradient id="topBand" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="${navy}"/>
      <stop offset="100%" stop-color="${navySoft}"/>
    </linearGradient>
    <linearGradient id="goldLine" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0%" stop-color="${goldSoft}"/>
      <stop offset="48%" stop-color="${gold}"/>
      <stop offset="100%" stop-color="${goldSoft}"/>
    </linearGradient>
  </defs>

  <rect width="2480" height="3508" fill="url(#paperBg)"/>
  <rect x="0" y="0" width="2480" height="256" fill="url(#topBand)"/>
  <rect x="0" y="256" width="2480" height="12" fill="url(#goldLine)"/>
  <rect x="180" y="328" width="2120" height="6" rx="3" fill="rgba(3,4,94,0.14)"/>

  <image x="188" y="52" width="172" height="172" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>

  <text x="404" y="96" fill="${goldSoft}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="26" font-weight="700" letter-spacing="4">ASSOCIAÇÃO DOS OFICIAIS DA RESERVA DO EXÉRCITO</text>
  <text x="404" y="160" fill="#ffffff" font-family="'DejaVu Sans','Arial',sans-serif" font-size="66" font-weight="800" letter-spacing="3">AORE/RN</text>
  <text x="404" y="206" fill="rgba(255,255,255,0.82)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="28" font-weight="600">Rio Grande do Norte</text>
  <text x="180" y="292" fill="${ink}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24" font-weight="600">Timbre institucional para oficios, comunicados e documentos oficiais</text>

  <g opacity="0.045">
    <image x="1540" y="2030" width="620" height="620" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  </g>

  <text x="180" y="3326" fill="rgba(24,32,43,0.7)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24">AORE/RN  |  16º Batalhão de Infantaria Motorizado  |  Bairro Tirol, Natal/RN</text>
  <text x="180" y="3362" fill="rgba(24,32,43,0.62)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="21">CNPJ: 70.145.065/0001-00  |  https://www.aorern.org  |  aorern.comunicacao@gmail.com</text>
  <text x="180" y="3394" fill="rgba(24,32,43,0.54)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20">WhatsApp institucional: +55 84 98806-1000</text>
  <rect x="180" y="3418" width="2120" height="6" rx="3" fill="url(#goldLine)"/>
  <rect x="180" y="3436" width="2120" height="2" rx="1" fill="rgba(3,4,94,0.26)"/>
</svg>
`;

const outputs = [
  {
    svgName: 'aorern-cabecalho-email.svg',
    pngName: 'aorern-cabecalho-email.png',
    svg: emailHeaderSvg,
    width: 1600,
    height: 420,
  },
  {
    svgName: 'aorern-timbre-oficial-a4.svg',
    pngName: 'aorern-timbre-oficial-a4.png',
    svg: letterheadSvg,
    width: 2480,
    height: 3508,
  },
];

const emailHeaderMinimalSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="320" viewBox="0 0 1600 320" role="img" aria-label="Cabecalho minimalista da AORERN">
  <rect width="1600" height="320" rx="24" fill="#ffffff"/>
${headerStripes}
  <image x="78" y="88" width="146" height="146" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  <text x="270" y="118" fill="${navy}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20" font-weight="700" letter-spacing="4.2">ASSOCIAÇÃO DOS OFICIAIS DA RESERVA DO EXÉRCITO</text>
  <text x="270" y="174" fill="${ink}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="64" font-weight="800" letter-spacing="2.2">AORE/RN</text>
  <text x="270" y="214" fill="rgba(24,32,43,0.78)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24" font-weight="600">Rio Grande do Norte</text>
  <text x="270" y="254" fill="rgba(24,32,43,0.68)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20">aorern.comunicacao@gmail.com</text>
  <rect x="270" y="272" width="1180" height="2" fill="rgba(3,4,94,0.16)"/>
</svg>
`;

const letterheadCompactSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="2480" height="3508" viewBox="0 0 2480 3508" role="img" aria-label="Timbre compacto da AORERN em A4">
  <rect width="2480" height="3508" fill="#fcfbf8"/>
  <rect x="0" y="0" width="2480" height="188" fill="${navy}"/>
  <rect x="0" y="188" width="2480" height="10" fill="${gold}"/>
  <image x="176" y="36" width="118" height="118" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  <text x="338" y="74" fill="${goldSoft}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20" font-weight="700" letter-spacing="3.6">ASSOCIAÇÃO DOS OFICIAIS DA RESERVA DO EXÉRCITO</text>
  <text x="338" y="118" fill="#ffffff" font-family="'DejaVu Sans','Arial',sans-serif" font-size="50" font-weight="800" letter-spacing="2.2">AORE/RN</text>
  <text x="338" y="150" fill="rgba(255,255,255,0.8)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20" font-weight="600">Rio Grande do Norte</text>
  <text x="180" y="244" fill="${ink}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="21" font-weight="600">Timbre institucional compacto para oficios e memorandos</text>
  <rect x="180" y="268" width="2120" height="4" rx="2" fill="rgba(3,4,94,0.16)"/>
  <g opacity="0.035">
    <image x="1620" y="2120" width="480" height="480" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  </g>
  <text x="180" y="3326" fill="rgba(24,32,43,0.7)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24">AORE/RN  |  16º Batalhão de Infantaria Motorizado  |  Bairro Tirol, Natal/RN</text>
  <text x="180" y="3362" fill="rgba(24,32,43,0.62)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="21">CNPJ: 70.145.065/0001-00  |  https://www.aorern.org  |  aorern.comunicacao@gmail.com</text>
  <text x="180" y="3394" fill="rgba(24,32,43,0.54)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20">WhatsApp institucional: +55 84 98806-1000</text>
  <rect x="180" y="3418" width="2120" height="5" rx="2.5" fill="${gold}"/>
</svg>
`;

const letterheadMonoSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="2480" height="3508" viewBox="0 0 2480 3508" role="img" aria-label="Timbre monocromatico da AORERN em A4">
  <rect width="2480" height="3508" fill="#ffffff"/>
  <rect x="0" y="0" width="2480" height="184" fill="#1e1e1e"/>
  <rect x="0" y="184" width="2480" height="6" fill="#8f8f8f"/>
  <g opacity="0.98">
    <image x="182" y="34" width="122" height="122" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  </g>
  <text x="344" y="74" fill="#d9d9d9" font-family="'DejaVu Sans','Arial',sans-serif" font-size="19" font-weight="700" letter-spacing="3.2">ASSOCIAÇÃO DOS OFICIAIS DA RESERVA DO EXÉRCITO</text>
  <text x="344" y="120" fill="#ffffff" font-family="'DejaVu Sans','Arial',sans-serif" font-size="48" font-weight="800" letter-spacing="2">AORE/RN</text>
  <text x="344" y="152" fill="#d1d1d1" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20" font-weight="600">Rio Grande do Norte</text>
  <text x="180" y="236" fill="#262626" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20" font-weight="600">Timbre monocromatico para documentos formais</text>
  <rect x="180" y="258" width="2120" height="3" rx="1.5" fill="rgba(0,0,0,0.18)"/>
  <g opacity="0.028">
    <image x="1625" y="2110" width="500" height="500" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  </g>
  <text x="180" y="3326" fill="rgba(0,0,0,0.64)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24">AORE/RN  |  16º Batalhão de Infantaria Motorizado  |  Bairro Tirol, Natal/RN</text>
  <text x="180" y="3362" fill="rgba(0,0,0,0.54)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="21">CNPJ: 70.145.065/0001-00  |  https://www.aorern.org  |  aorern.comunicacao@gmail.com</text>
  <text x="180" y="3394" fill="rgba(0,0,0,0.46)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20">WhatsApp institucional: +55 84 98806-1000</text>
  <rect x="180" y="3418" width="2120" height="4" rx="2" fill="rgba(0,0,0,0.34)"/>
</svg>
`;

const emailHeaderClearSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="340" viewBox="0 0 1600 340" role="img" aria-label="Cabecalho claro da AORERN">
  <rect width="1600" height="340" rx="24" fill="#fcfbf8"/>
${headerStripes}
  <rect x="82" y="72" width="6" height="196" rx="3" fill="${gold}"/>
  <image x="118" y="74" width="168" height="168" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  <text x="342" y="108" fill="${navy}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20" font-weight="700" letter-spacing="3.6">ASSOCIAÇÃO DOS OFICIAIS DA RESERVA DO EXÉRCITO</text>
  <text x="342" y="166" fill="${ink}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="68" font-weight="800" letter-spacing="2.2">AORE/RN</text>
  <text x="342" y="210" fill="rgba(24,32,43,0.78)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24" font-weight="600">Rio Grande do Norte</text>
  <text x="342" y="246" fill="rgba(24,32,43,0.66)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="19">aorern.comunicacao@gmail.com</text>
  <rect x="342" y="274" width="1110" height="2" fill="rgba(3,4,94,0.16)"/>
</svg>
`;

const letterheadClearSvg = `<?xml version="1.0" encoding="UTF-8"?>
<svg xmlns="http://www.w3.org/2000/svg" width="2480" height="3508" viewBox="0 0 2480 3508" role="img" aria-label="Timbre claro da AORERN em A4">
  <rect width="2480" height="3508" fill="#fcfbf8"/>
  <rect x="0" y="0" width="2480" height="12" fill="${gold}"/>
  <rect x="180" y="78" width="6" height="182" rx="3" fill="${gold}"/>
  <image x="222" y="82" width="162" height="162" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  <text x="438" y="116" fill="${navy}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24" font-weight="700" letter-spacing="3.8">ASSOCIAÇÃO DOS OFICIAIS DA RESERVA DO EXÉRCITO</text>
  <text x="438" y="176" fill="${ink}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="62" font-weight="800" letter-spacing="2.4">AORE/RN</text>
  <text x="438" y="220" fill="rgba(24,32,43,0.76)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="26" font-weight="600">Rio Grande do Norte</text>
  <text x="180" y="302" fill="${ink}" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24" font-weight="600">Timbre institucional em versão clara</text>
  <rect x="180" y="326" width="2120" height="4" rx="2" fill="rgba(3,4,94,0.16)"/>
  <g opacity="0.04">
    <image x="1540" y="2030" width="620" height="620" href="data:image/png;base64,${logoBase64}" preserveAspectRatio="xMidYMid meet"/>
  </g>
  <text x="180" y="3326" fill="rgba(24,32,43,0.7)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="24">AORE/RN  |  16º Batalhão de Infantaria Motorizado  |  Bairro Tirol, Natal/RN</text>
  <text x="180" y="3362" fill="rgba(24,32,43,0.62)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="21">CNPJ: 70.145.065/0001-00  |  https://www.aorern.org  |  aorern.comunicacao@gmail.com</text>
  <text x="180" y="3394" fill="rgba(24,32,43,0.54)" font-family="'DejaVu Sans','Arial',sans-serif" font-size="20">WhatsApp institucional: +55 84 98806-1000</text>
  <rect x="180" y="3418" width="2120" height="5" rx="2.5" fill="${gold}"/>
</svg>
`;

outputs.push(
  {
    svgName: 'aorern-cabecalho-email-claro.svg',
    pngName: 'aorern-cabecalho-email-claro.png',
    svg: emailHeaderClearSvg,
    width: 1600,
    height: 340,
  },
  {
    svgName: 'aorern-cabecalho-email-minimalista.svg',
    pngName: 'aorern-cabecalho-email-minimalista.png',
    svg: emailHeaderMinimalSvg,
    width: 1600,
    height: 320,
  },
  {
    svgName: 'aorern-timbre-oficial-a4-claro.svg',
    pngName: 'aorern-timbre-oficial-a4-claro.png',
    svg: letterheadClearSvg,
    width: 2480,
    height: 3508,
  },
  {
    svgName: 'aorern-timbre-oficial-a4-compacto.svg',
    pngName: 'aorern-timbre-oficial-a4-compacto.png',
    svg: letterheadCompactSvg,
    width: 2480,
    height: 3508,
  },
  {
    svgName: 'aorern-timbre-oficial-a4-monocromatico.svg',
    pngName: 'aorern-timbre-oficial-a4-monocromatico.png',
    svg: letterheadMonoSvg,
    width: 2480,
    height: 3508,
  }
);

for (const item of outputs) {
  const svgPath = path.join(brandDir, item.svgName);
  const pngPath = path.join(brandDir, item.pngName);

  await fs.writeFile(svgPath, item.svg, 'utf8');
  await sharp(Buffer.from(item.svg))
    .resize(item.width, item.height)
    .png({ compressionLevel: 9 })
    .toFile(pngPath);
}
