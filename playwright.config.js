const { defineConfig, devices } = require('@playwright/test');

const baseURL = process.env.AORERN_BASE_URL || 'https://srv798468.hstgr.cloud/aorern/';

module.exports = defineConfig({
  testDir: './tests/ui',
  timeout: 30_000,
  expect: {
    timeout: 5_000,
  },
  use: {
    baseURL,
    ignoreHTTPSErrors: true,
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium-mobile',
      use: {
        ...devices['Pixel 5'],
      },
    },
  ],
  reporter: [['list'], ['html', { open: 'never' }]],
});
