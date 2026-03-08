import fs from 'node:fs';
import path from 'node:path';
import PDFDocument from 'pdfkit';
import SVGtoPDF from 'svg-to-pdfkit';

const root = path.resolve(path.dirname(new URL(import.meta.url).pathname), '..');
const coverPath = path.join(root, 'public/assets/images/brand/aorern-manual-identidade-visual.svg');
const outputPath = path.join(root, 'public/assets/images/brand/aorern-manual-identidade-visual.pdf');

const coverSvg = fs.readFileSync(coverPath, 'utf8');
const doc = new PDFDocument({
  size: 'A4',
  margin: 48,
  info: {
    Title: 'Manual de Identidade Visual da AORERN',
    Author: 'AORERN',
    Subject: 'Manual visual institucional multipagina',
    Keywords: 'AORERN, identidade visual, manual, timbre, cabecalho',
  },
});

const stream = fs.createWriteStream(outputPath);
doc.pipe(stream);

const colors = {
  navy: '#10253f',
  olive: '#556b2f',
  red: '#cc2229',
  gold: '#d9b54a',
  blue: '#2a86c5',
  text: '#314155',
  soft: '#eef2f5',
};

const pageW = doc.page.width;
const pageH = doc.page.height;

function pageTitle(title, subtitle) {
  doc.fillColor(colors.navy).font('Helvetica-Bold').fontSize(24).text(title, 48, 48);
  doc.fillColor(colors.text).font('Helvetica').fontSize(11).text(subtitle, 48, 80, { width: pageW - 96 });
  doc.moveTo(48, 110).lineTo(pageW - 48, 110).lineWidth(2).strokeColor(colors.gold).stroke();
}

function bulletList(items, startX, startY, width) {
  let y = startY;
  items.forEach((item) => {
    doc.fillColor(colors.olive).circle(startX + 5, y + 7, 3).fill();
    doc.fillColor(colors.text).font('Helvetica').fontSize(11.5).text(item, startX + 18, y, { width, lineGap: 3 });
    y = doc.y + 10;
  });
  return y;
}

function colorChip(x, y, color, label, code) {
  doc.roundedRect(x, y, 92, 92, 16).fill(color);
  doc.fillColor(color === colors.gold ? colors.navy : '#ffffff').font('Helvetica-Bold').fontSize(12).text(label, x + 12, y + 104, { width: 92, align: 'left' });
  doc.fillColor(colors.text).font('Helvetica').fontSize(10).text(code, x + 12, y + 122, { width: 110 });
}

function footer(pageNumber) {
  doc.moveTo(48, pageH - 42).lineTo(pageW - 48, pageH - 42).lineWidth(1).strokeColor('#d5dde6').stroke();
  doc.fillColor(colors.text).font('Helvetica').fontSize(9).text(`Manual de Identidade Visual da AORERN`, 48, pageH - 30);
  doc.text(`Pagina ${pageNumber}`, pageW - 110, pageH - 30, { width: 62, align: 'right' });
}

SVGtoPDF(doc, coverSvg, 36, 36, {
  width: pageW - 72,
  height: pageH - 72,
  preserveAspectRatio: 'xMidYMid meet',
});
footer(1);

doc.addPage({ size: 'A4', margin: 48 });
pageTitle('1. Uso da marca institucional', 'Orientacoes basicas para aplicacao da marca oficial da AORERN.');
doc.roundedRect(48, 136, 190, 190, 20).fill(colors.soft).stroke('#d5dde6');
doc.fillColor(colors.navy).font('Helvetica-Bold').fontSize(22).text('MARCA', 92, 214);
doc.fontSize(12).fillColor(colors.olive).text('VERSAO OFICIAL', 88, 246);
doc.fillColor(colors.navy).font('Helvetica-Bold').fontSize(15).text('Diretrizes principais', 276, 144);
bulletList([
  'Priorizar o arquivo PNG oficial em interfaces web, portais e materiais digitais de uso corrente.',
  'Usar a imagem base em alta qualidade sem recortes, deformacoes ou sobreposicoes indevidas.',
  'Preservar area minima de respiro ao redor da marca, evitando colagem direta em textos, bordas ou outros simbolos.',
  'Nao alterar proporcao, paleta-base nem a leitura principal da marca institucional.',
], 276, 176, 256);
doc.roundedRect(48, 360, pageW - 96, 120, 18).fill('#f8faf7').stroke('#d6decf');
doc.fillColor(colors.navy).font('Helvetica-Bold').fontSize(14).text('Aplicacoes recomendadas', 68, 382);
bulletList([
  'Home do portal, navbar, footer e sidebar administrativa.',
  'Assinaturas visuais, cards institucionais e capas de documentos.',
  'Materiais de cerimonial, memoria e comunicacao oficial.',
], 68, 410, pageW - 150);
footer(2);

doc.addPage({ size: 'A4', margin: 48 });
pageTitle('2. Paleta e linguagem visual', 'Cores aprovadas e funcao de cada grupo cromatico na identidade da associacao.');
colorChip(64, 150, colors.olive, 'Verde oliva', '#556b2f');
colorChip(190, 150, colors.blue, 'Azul apoio', '#2a86c5');
colorChip(316, 150, colors.red, 'Vermelho', '#cc2229');
colorChip(442, 150, colors.gold, 'Dourado', '#d9b54a');
doc.fillColor(colors.navy).font('Helvetica-Bold').fontSize(15).text('Uso recomendado da paleta', 48, 330);
bulletList([
  'O verde oliva deve permanecer como cor de dominancia institucional nas interfaces principais.',
  'O azul atua como apoio em gradientes, realces e composicoes complementares.',
  'O vermelho deve aparecer com controle, sobretudo em faixas, selos e acentos cerimoniais.',
  'O dourado comunica honraria, distincao e elementos heráldicos da marca institucional.',
], 48, 360, pageW - 110);
doc.roundedRect(48, 518, pageW - 96, 190, 18).fill('#ffffff').stroke('#d5dde6');
doc.fillColor(colors.navy).font('Helvetica-Bold').fontSize(14).text('Tipografia e tom visual', 68, 538);
bulletList([
  'Titulos: families serifa ou institucionais com presenca e sobriedade.',
  'Textos de apoio: sans-serif clara e legivel para conteudo digital e expedientes.',
  'Evitar paletas improvisadas, gradientes aleatorios ou contrastes desalinhados do sistema de marca.',
], 68, 568, pageW - 150);
footer(3);

doc.addPage({ size: 'A4', margin: 48 });
pageTitle('3. Cabecalhos e timbres oficiais', 'Assets aprovados para e-mail, documentos administrativos e expedientes formais.');
doc.roundedRect(48, 146, pageW - 96, 110, 16).fill('#ffffff').stroke('#d5dde6');
doc.rect(48, 146, pageW - 96, 10).fill(colors.red);
doc.rect(48, 156, pageW - 96, 10).fill(colors.blue);
doc.rect(48, 166, pageW - 96, 10).fill('#e7d148');
doc.circle(92, 204, 18).fill(colors.olive);
doc.fillColor(colors.navy).font('Helvetica-Bold').fontSize(16).text('Cabecalho institucional de e-mail', 122, 196);
doc.font('Helvetica').fontSize(11).fillColor(colors.text).text('Usar a versao minimalista em PNG para clientes de e-mail. Manter SVG apenas para edicao ou novas exportacoes.', 122, 220, { width: pageW - 200 });
doc.roundedRect(48, 292, 250, 370, 16).fill('#ffffff').stroke('#d5dde6');
doc.rect(48, 292, 250, 56).fill(colors.navy);
doc.circle(84, 320, 14).fill(colors.gold);
doc.fillColor('#ffffff').font('Helvetica-Bold').fontSize(13).text('Timbre oficial A4', 108, 314);
doc.moveTo(72, 386).lineTo(270, 386).strokeColor('#dce4eb').stroke();
doc.moveTo(72, 420).lineTo(270, 420).strokeColor('#dce4eb').stroke();
doc.moveTo(72, 454).lineTo(270, 454).strokeColor('#dce4eb').stroke();
doc.moveTo(72, 488).lineTo(270, 488).strokeColor('#dce4eb').stroke();
doc.fillColor(colors.navy).font('Helvetica-Bold').fontSize(15).text('Regras de uso', 336, 310);
bulletList([
  'Preferir o timbre PNG em documentos fechados e o SVG quando houver etapa de edicao.',
  'Preservar a faixa superior, o selo e o rodape institucional sem redimensionamentos arbitrarios.',
  'Incluir CNPJ, dominio e contato institucional conforme versoes oficiais ja aprovadas.',
  'Evitar reprocessar os arquivos em compressoes agressivas que comprometam nitidez e cor.',
], 336, 342, 206);
footer(4);

doc.end();

await new Promise((resolve, reject) => {
  stream.on('finish', resolve);
  stream.on('error', reject);
});

console.log(outputPath);
