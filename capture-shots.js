const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const urls = [
  { url: 'http://127.0.0.1:8086/', name: 'index' },
  { url: 'http://127.0.0.1:8086/unsere-lieder.html', name: 'unsere-lieder' },
  { url: 'http://127.0.0.1:8086/teilnahmebedingungen.html', name: 'teilnahmebedingungen' },
  { url: 'http://127.0.0.1:8086/mukivorteile.html', name: 'mukivorteile' },
  { url: 'http://127.0.0.1:8086/impressum.html', name: 'impressum' }
];

const viewports = [
  { name: 'mobile', width: 390, height: 844 },
  { name: 'desktop', width: 1280, height: 800 }
];

const outputDir = '/tmp/muki-shots';

(async () => {
  const browser = await chromium.launch();

  for (const { url, name } of urls) {
    for (const { name: vpName, width, height } of viewports) {
      const page = await browser.newPage({
        viewport: { width, height }
      });

      try {
        console.log(`Capturing ${name} (${vpName})...`);
        await page.goto(url, { waitUntil: 'networkidle' });

        // Wait a bit for any async rendering
        await page.waitForTimeout(500);

        const screenshotPath = path.join(outputDir, `${name}-${vpName}.png`);
        await page.screenshot({ path: screenshotPath, fullPage: true });
        console.log(`✓ Saved: ${screenshotPath}`);
      } catch (error) {
        console.error(`✗ Failed to capture ${name} (${vpName}): ${error.message}`);
      } finally {
        await page.close();
      }
    }
  }

  await browser.close();
  console.log('\nAll screenshots captured!');
})();
