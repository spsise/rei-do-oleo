
let deferredPrompt: any;

export const pwaInstaller = {
  canInstall: false,
  
  init() {
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      this.canInstall = true;
    });

    window.addEventListener('appinstalled', () => {
      this.canInstall = false;
      deferredPrompt = null;
    });
  },

  async install() {
    if (!deferredPrompt) {
      return false;
    }

    deferredPrompt.prompt();
    const { outcome } = await deferredPrompt.userChoice;
    
    if (outcome === 'accepted') {
      this.canInstall = false;
      deferredPrompt = null;
      return true;
    }
    
    return false;
  }
};
