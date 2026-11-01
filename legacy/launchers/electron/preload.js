const { contextBridge, ipcRenderer } = require('electron');

contextBridge.exposeInMainWorld('launcher', {
  getMeta: () => ipcRenderer.invoke('launcher:get-meta'),
  validateAndOpen: (key) => ipcRenderer.invoke('launcher:validate-and-open', key),
});
