import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const pdfPath = path.resolve('public/documents/quaf-brochure.pdf');
const outputDir = path.resolve('public/images/brochure');

if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

async function renderPdfPages() {
    console.log('Reading PDF file...');
    const pdfBuffer = fs.readFileSync(pdfPath);
    const pdfBase64 = pdfBuffer.toString('base64');

    console.log('Launching Chrome...');
    const browser = await puppeteer.launch({
        executablePath: chromePath,
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1400, height: 1400, deviceScaleFactor: 1 });

    const htmlContent = `
    <!DOCTYPE html>
    <html>
    <head>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    </head>
    <body>
        <canvas id="the-canvas"></canvas>
        <script>
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            let pdfDoc = null;

            async function loadPdf(base64Data) {
                const pdfData = atob(base64Data);
                const uint8Array = new Uint8Array(pdfData.length);
                for (let i = 0; i < pdfData.length; i++) {
                    uint8Array[i] = pdfData.charCodeAt(i);
                }
                pdfDoc = await pdfjsLib.getDocument({ 
                    data: uint8Array,
                    cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                    cMapPacked: true,
                    standardFontDataUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/standard_fonts/',
                }).promise;
                return pdfDoc.numPages;
            }

            async function renderPageToDataUrl(pageNum, targetSize = 1200) {
                const page = await pdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: 1 });
                const scale = targetSize / viewport.width;
                const scaledViewport = page.getViewport({ scale: scale });

                const canvas = document.getElementById('the-canvas');
                canvas.width = scaledViewport.width;
                canvas.height = scaledViewport.height;
                const context = canvas.getContext('2d');

                await document.fonts.ready;

                await page.render({
                    canvasContext: context,
                    viewport: scaledViewport
                }).promise;

                return canvas.toDataURL('image/jpeg', 0.92);
            }
        </script>
    </body>
    </html>
    `;

    await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

    console.log('Initializing PDF inside browser...');
    const totalPages = await page.evaluate((b64) => window.loadPdf(b64), pdfBase64);
    console.log(`PDF loaded. Total pages: ${totalPages}`);

    for (let i = 1; i <= totalPages; i++) {
        process.stdout.write(`Rendering page ${i}/${totalPages}... `);
        const dataUrl = await page.evaluate((num) => window.renderPageToDataUrl(num, 1200), i);
        const base64Data = dataUrl.replace(/^data:image\/jpeg;base64,/, '');
        const outputPath = path.join(outputDir, `page-${i}.jpg`);
        fs.writeFileSync(outputPath, Buffer.from(base64Data, 'base64'));
        console.log(`Saved -> page-${i}.jpg (${Math.round(fs.statSync(outputPath).size / 1024)} KB)`);
    }

    await browser.close();
    console.log('All pages successfully rendered to public/images/brochure/ !');
}

renderPdfPages().catch(err => {
    console.error('Fatal error:', err);
    process.exit(1);
});
