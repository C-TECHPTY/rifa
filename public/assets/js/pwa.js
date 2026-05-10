(function () {
  let deferredPrompt = null;
  const installButtons = [];

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
  }

  function isLocalhost() {
    return ['localhost', '127.0.0.1', '[::1]'].includes(window.location.hostname);
  }

  function isSecureInstallContext() {
    return window.isSecureContext || isLocalhost();
  }

  function setInstallState() {
    installButtons.forEach((button) => {
      if (isStandalone()) {
        button.textContent = 'App instalada';
        button.disabled = true;
      } else if (!isSecureInstallContext()) {
        button.textContent = 'Instalar app';
        button.hidden = false;
        button.disabled = false;
        button.title = 'Para instalar la app necesitas HTTPS activo en el dominio.';
      } else if (deferredPrompt) {
        button.hidden = false;
        button.disabled = false;
        button.title = 'Instalar app';
      } else {
        button.hidden = !button.dataset.alwaysVisible;
        button.disabled = false;
        button.title = 'Si el botón no abre instalación, usa el menú del navegador.';
      }
    });

    document.documentElement.classList.toggle('is-standalone', isStandalone());
  }

  function registerInstallButton(button) {
    installButtons.push(button);
    button.addEventListener('click', async () => {
      if (!isSecureInstallContext()) {
        alert('Para instalar la app necesitas activar HTTPS válido en el dominio. Por ahora puedes usarla desde el navegador con http://, pero Chrome no permite instalar PWA sin SSL.');
        return;
      }
      if (!deferredPrompt) {
        alert('Si tu navegador no muestra instalación automática, abre el menú de Chrome/Edge y elige "Instalar app" o "Agregar a pantalla principal". En iPhone usa Compartir > Agregar a inicio.');
        return;
      }
      deferredPrompt.prompt();
      await deferredPrompt.userChoice.catch(() => undefined);
      deferredPrompt = null;
      setInstallState();
    });
    setInstallState();
  }

  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredPrompt = event;
    setInstallState();
  });

  window.addEventListener('appinstalled', () => {
    deferredPrompt = null;
    setInstallState();
  });

  window.addEventListener('load', () => {
    document.querySelectorAll('[data-pwa-install]').forEach(registerInstallButton);
    document.querySelectorAll('[data-web-push-subscribe]').forEach(registerPushButton);
    setInstallState();

    if ('serviceWorker' in navigator) {
      const swUrl = new URL('service-worker.js', document.querySelector('link[rel="manifest"]')?.href || window.location.href);
      navigator.serviceWorker.register(swUrl).catch(() => {});
    }
  });

  function base64UrlToUint8Array(base64Url) {
    const padding = '='.repeat((4 - base64Url.length % 4) % 4);
    const base64 = (base64Url + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = window.atob(base64);
    return Uint8Array.from([...raw].map((char) => char.charCodeAt(0)));
  }

  function registerPushButton(button) {
    const publicKey = window.RIFAGRID_PUSH_PUBLIC_KEY || '';
    if (!('serviceWorker' in navigator) || !('PushManager' in window) || !publicKey) {
      button.disabled = true;
      button.title = publicKey ? 'Web Push no está disponible en este navegador.' : 'Configura WEB_PUSH_PUBLIC_KEY para activar Web Push.';
      return;
    }

    button.addEventListener('click', async () => {
      button.disabled = true;
      const original = button.textContent;
      button.textContent = 'Activando...';
      try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') throw new Error('Permiso de notificaciones denegado.');
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: base64UrlToUint8Array(publicKey),
        });

        const form = new FormData();
        form.set('_csrf', window.RIFAGRID_CSRF || '');
        form.set('subscription', JSON.stringify(subscription));
        const response = await fetch('../api/push_subscribe.php', { method: 'POST', body: form, headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!data.ok) throw new Error(data.message || 'No se pudo guardar la suscripción.');
        button.textContent = 'Push activo';
      } catch (error) {
        alert(error.message);
        button.textContent = original;
        button.disabled = false;
      }
    });
  }
})();
