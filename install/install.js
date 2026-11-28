const generateButton = document.getElementById('generate-key');
if (generateButton) {
    generateButton.addEventListener('click', () => {
        const bytes = crypto.getRandomValues(new Uint8Array(32));
        document.getElementById('app-key').value = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('');
        document.getElementById('install-status').textContent = 'A secure secret key has been generated. It will be saved in .env after installation.';
    });
}

const installForm = document.getElementById('install-form');
if (installForm) {
    installForm.addEventListener('submit', () => {
        const button = installForm.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Building your kingdom...';
        document.getElementById('install-status').textContent = 'Importing the schema and game data. Keep this page open until setup completes.';
    });
}
