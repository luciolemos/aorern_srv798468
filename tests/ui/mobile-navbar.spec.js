const { test, expect } = require('@playwright/test');

test('mobile institutional submenu opens from the parent item and can collapse', async ({ page }) => {
  const consoleIssues = [];
  page.on('console', (message) => {
    if (['error', 'warning'].includes(message.type())) {
      consoleIssues.push(`${message.type()}: ${message.text()}`);
    }
  });

  await page.goto('./');

  await expect(page).toHaveTitle(/AORE\/RN/);

  const menuPanel = page.locator('#navbarSite');
  const institutionalTrigger = page.locator('.mobile-subnav-trigger', { hasText: 'Institucional' });
  const overview = page.locator('#mobile-subnav-3 .dropdown-item', { hasText: 'Visão geral institucional' });
  const firstGroup = page.locator('#mobile-subnav-3 .mobile-subnav-group-title', { hasText: 'Essencial' });
  const secondGroup = page.locator('#mobile-subnav-3 .mobile-subnav-group-title', { hasText: 'Identidade' });

  await page.locator('.navbar-toggler').click();
  await expect(menuPanel).toHaveClass(/show/);

  await institutionalTrigger.click();
  await expect(institutionalTrigger).toHaveAttribute('aria-expanded', 'true');
  await expect(overview).toBeVisible();
  await expect(firstGroup).toBeVisible();
  await expect(secondGroup).toBeVisible();

  const positions = await page.evaluate(() => {
    const trigger = document.querySelector('[data-mobile-subnav-target="#mobile-subnav-3"]');
    const overviewLink = [...document.querySelectorAll('#mobile-subnav-3 .dropdown-item')]
      .find((item) => item.textContent.includes('Visão geral institucional'));
    const essential = [...document.querySelectorAll('#mobile-subnav-3 .mobile-subnav-group-title')]
      .find((item) => item.textContent.trim() === 'Essencial');

    return {
      panelScrollTop: document.querySelector('#navbarSite')?.scrollTop ?? null,
      triggerTop: trigger?.getBoundingClientRect().top ?? null,
      overviewTop: overviewLink?.getBoundingClientRect().top ?? null,
      essentialTop: essential?.getBoundingClientRect().top ?? null,
    };
  });

  expect(positions.panelScrollTop).toBe(0);
  expect(positions.triggerTop).toBeGreaterThanOrEqual(0);
  expect(positions.overviewTop).toBeGreaterThan(positions.triggerTop);
  expect(positions.essentialTop).toBeGreaterThan(positions.overviewTop);

  await institutionalTrigger.click();
  await expect(institutionalTrigger).toHaveAttribute('aria-expanded', 'false');
  await expect(page.locator('#mobile-subnav-3')).not.toHaveClass(/show/);

  expect(consoleIssues).toEqual([]);
});

test('mobile menu remains visible when opened after scrolling', async ({ page }) => {
  await page.goto('./');

  await page.evaluate(() => window.scrollTo(0, 520));
  await page.waitForTimeout(100);

  const navbar = page.locator('.navbar-site');
  const menuPanel = page.locator('#navbarSite');

  await page.locator('.navbar-toggler').click();
  await expect(menuPanel).toHaveClass(/show/);
  await expect(page.locator('.nav-link', { hasText: 'Início' })).toBeVisible();

  const geometry = await page.evaluate(() => {
    const navbarEl = document.querySelector('.navbar-site');
    const panelEl = document.querySelector('#navbarSite');

    return {
      scrollY: window.scrollY,
      navbarPosition: navbarEl ? getComputedStyle(navbarEl).position : null,
      navbarTop: navbarEl?.getBoundingClientRect().top ?? null,
      navbarBottom: navbarEl?.getBoundingClientRect().bottom ?? null,
      panelTop: panelEl?.getBoundingClientRect().top ?? null,
    };
  });

  expect(geometry.scrollY).toBeGreaterThan(0);
  expect(geometry.navbarPosition).toBe('fixed');
  expect(geometry.navbarTop).toBe(0);
  expect(geometry.navbarBottom).toBeGreaterThan(0);
  expect(geometry.panelTop).toBeGreaterThanOrEqual(geometry.navbarBottom);

  await navbar.locator('.navbar-toggler').click();
  await expect(menuPanel).not.toHaveClass(/show/);
});
