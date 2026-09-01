/*
    Script de capturas para LockMaster.

    Levanta Chromium con viewport 1440x900, se loguea como admin/admin y saca
    las cinco capturas en capturas/out/.

    Uso:  node screenshots.js          (con la app levantada en localhost:8080)
          BASE_URL=... node screenshots.js
*/

const fs = require('fs');
const path = require('path');
const { chromium } = require('@playwright/test');

const BASE_URL = process.env.BASE_URL || 'http://localhost:8080';
const OUT_DIR = path.join(__dirname, 'out');
const VIEWPORT = { width: 1440, height: 900 };

// La toolbar del profiler solo estorba en las capturas. La ocultamos por CSS,
// sin tocar las plantillas.
const HIDE_TOOLBAR_CSS = '.sf-toolbar, .sf-minitoolbar { display: none !important; }';

async function capture(page, fileName) {
    // addStyleTag se pierde en cada navegación, así que lo reinyectamos siempre
    await page.addStyleTag({ content: HIDE_TOOLBAR_CSS });
    const filePath = path.join(OUT_DIR, fileName);
    await page.screenshot({ path: filePath });
    console.log(`  ✓ ${fileName}`);
}

async function logIn(page) {
    await page.goto(`${BASE_URL}/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('#username', 'admin');
    await page.fill('#password', 'admin');
    await Promise.all([
        page.waitForURL(url => !url.pathname.startsWith('/login')),
        page.click('form.login-form button[type="submit"]')
    ]);
    console.log(`  · sesión iniciada como admin (${page.url()})`);
}

/*
    No todos los puntos de acceso tienen reglas: findAllByAccessPoint() filtra
    por active = true, así que hay que buscar uno que sí las tenga.
    Recorremos el listado paginado, juntamos los enlaces /ap/auth/{id} y nos
    quedamos con el primero cuyo bloque de restricciones no esté vacío.
*/
async function findAccessPointWithRules(page) {
    const urls = [];

    for (let pageNumber = 1; ; pageNumber++) {
        await page.goto(`${BASE_URL}/access-point?page=${pageNumber}`, { waitUntil: 'domcontentloaded' });

        const pageUrls = await page.$$eval(
            'a[href*="/ap/auth/"]',
            links => links.map(link => link.getAttribute('href'))
        );

        // Página vacía o repetida: hemos llegado al final de la paginación
        if (pageUrls.length === 0 || pageUrls.every(url => urls.includes(url))) {
            break;
        }

        for (const url of pageUrls) {
            if (!urls.includes(url)) urls.push(url);
        }
    }

    for (const url of urls) {
        await page.goto(`${BASE_URL}${url}`, { waitUntil: 'domcontentloaded' });
        const ruleCount = await page.locator('.restrictions .restriction').count();

        if (ruleCount > 0) {
            console.log(`  · ${url} tiene ${ruleCount} día(s) con reglas`);
            return url;
        }
    }

    throw new Error(
        `Ningún punto de acceso tiene reglas activas (revisados ${urls.length}). ` +
        '¿Se han cargado las fixtures?'
    );
}

/*
    Despliega una operación del Swagger. Los bloques de swagger-ui son
    .opblock.opblock-{método} y se abren pulsando su cabecera .opblock-summary.

    El banner de API Platform es sticky, así que scrollIntoView() deja la
    cabecera de la operación tapada. Scrolleamos a mano dejando margen.
*/
async function openSwaggerOperation(page, method, pathFragment) {
    await page.goto(`${BASE_URL}/api`, { waitUntil: 'domcontentloaded' });
    await page.waitForSelector('.opblock', { timeout: 30000 });

    const block = page
        .locator(`.opblock.opblock-${method}`)
        .filter({ has: page.locator(`.opblock-summary-path[data-path*="${pathFragment}"]`) })
        .first();

    if (await block.count() === 0) {
        const available = await page.$$eval(
            '.opblock-summary-path',
            nodes => nodes.map(node => node.getAttribute('data-path'))
        );
        throw new Error(
            `No encuentro la operación ${method.toUpperCase()} ${pathFragment}. ` +
            `Operaciones en el documento: ${available.join(', ')}`
        );
    }

    const scrollBelowBanner = () => block.evaluate(node => {
        const top = node.getBoundingClientRect().top + window.scrollY;
        window.scrollTo(0, Math.max(0, top - 90));
    });

    await scrollBelowBanner();

    const isOpen = await block.evaluate(node => node.classList.contains('is-open'));
    if (!isOpen) {
        await block.locator('.opblock-summary').click();
        await block.locator('.opblock-body').waitFor({ state: 'visible', timeout: 10000 });
    }

    // Dejamos que termine la animación de despliegue antes de disparar
    await page.waitForTimeout(600);
    await scrollBelowBanner();
}

async function main() {
    fs.mkdirSync(OUT_DIR, { recursive: true });

    const browser = await chromium.launch();
    const context = await browser.newContext({ viewport: VIEWPORT });
    const page = await context.newPage();

    try {
        console.log(`Capturando ${BASE_URL} en ${OUT_DIR}`);

        await logIn(page);

        // 1. Listado de puntos de acceso
        await page.goto(`${BASE_URL}/access-point`, { waitUntil: 'domcontentloaded' });
        await capture(page, '01-access-points.png');

        // 2. Matriz de reglas por día de un punto de acceso que tenga reglas
        const authUrl = await findAccessPointWithRules(page);
        await page.goto(`${BASE_URL}${authUrl}`, { waitUntil: 'domcontentloaded' });
        await capture(page, '02-authorization-rules.png');

        // 3. Histórico de accesos
        await page.goto(`${BASE_URL}/access-log`, { waitUntil: 'domcontentloaded' });
        await capture(page, '03-access-log.png');

        // 4 y 5. Swagger con cada operación desplegada
        await openSwaggerOperation(page, 'get', 'access/check');
        await capture(page, '04-swagger-access-check.png');

        await openSwaggerOperation(page, 'post', '/token');
        await capture(page, '05-swagger-token.png');

        console.log('Listo.');
    } finally {
        await browser.close();
    }
}

main().catch(error => {
    console.error(`\nError: ${error.message}`);
    process.exit(1);
});
