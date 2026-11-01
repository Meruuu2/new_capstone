const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const fs = require('fs');

const VARIANTS = {
  admin: {
    role: 'admin',
    badge: 'Admin Portal',
    defaultSecret: 'UA-ADMIN-2025',
    secretEnv: 'FMS_ADMIN_SECRET',
  },
  college_staff: {
    role: 'college_staff',
    badge: 'College Staff Portal',
    defaultSecret: 'UA-COLLEGE-2025',
    secretEnv: 'FMS_COLLEGE_SECRET',
  },
  org_staff: {
    role: 'org_staff',
    badge: 'Organization Staff Portal',
    defaultSecret: 'UA-ORG-2025',
    secretEnv: 'FMS_ORG_SECRET',
  },
};

function getVariantRole() {
  // Dev: env wins so npm run start:college works even if variant.json exists.
  if (!app.isPackaged) {
    const fromEnv = process.env.FMS_PORTAL_ROLE;
    if (fromEnv && VARIANTS[fromEnv]) return fromEnv;
  }
  const variantPath = path.join(__dirname, 'variant.json');
  if (fs.existsSync(variantPath)) {
    try {
      const { role } = JSON.parse(fs.readFileSync(variantPath, 'utf8'));
      if (role && VARIANTS[role]) return role;
    } catch {
      /* fall through */
    }
  }
  const fromEnv = process.env.FMS_PORTAL_ROLE;
  if (fromEnv && VARIANTS[fromEnv]) return fromEnv;
  return 'admin';
}

function buildLoginUrl(role) {
  const base =
    process.env.FMS_LOGIN_URL || 'http://127.0.0.1:8000/fms-portal-entry';
  const token = process.env.FMS_ACCESS_TOKEN || 'UA-FMS-ACCESS-2025';
  const u = new URL(base.includes('://') ? base : `http://${base}`);
  u.searchParams.set('access_token', token);
  u.searchParams.set('role', role);
  return u.toString();
}

let mainWindow;

function createWindow() {
  const roleKey = getVariantRole();
  const v = VARIANTS[roleKey];
  const secret = process.env[v.secretEnv] || v.defaultSecret;
  const loginUrl = buildLoginUrl(v.role);
  const portalTitle = `UA Facility Management — ${v.badge}`;

  mainWindow = new BrowserWindow({
    width: 400,
    height: 520,
    resizable: false,
    autoHideMenuBar: true,
    webPreferences: {
      preload: path.join(__dirname, 'preload.js'),
      contextIsolation: true,
      nodeIntegration: false,
      sandbox: true,
    },
  });

  ipcMain.removeHandler('launcher:get-meta');
  ipcMain.removeHandler('launcher:validate-and-open');

  ipcMain.handle('launcher:get-meta', () => ({
    badge: v.badge,
  }));

  ipcMain.handle('launcher:validate-and-open', async (_event, key) => {
    const ok = typeof key === 'string' && key.trim() === secret;
    if (!ok) return { ok: false };
    try {
      await mainWindow.loadURL(loginUrl);
      mainWindow.setTitle(portalTitle);
      mainWindow.setMinimumSize(800, 600);
      mainWindow.setSize(1200, 800);
      mainWindow.center();
      mainWindow.setResizable(true);
    } catch (e) {
      console.error('[launcher] loadURL failed:', e);
      return { ok: false };
    }
    return { ok: true };
  });

  mainWindow.loadFile(path.join(__dirname, 'gate.html'));
}

app.whenReady().then(() => {
  createWindow();
  app.on('activate', () => {
    if (BrowserWindow.getAllWindows().length === 0) createWindow();
  });
});

app.on('window-all-closed', () => {
  if (process.platform !== 'darwin') app.quit();
});
