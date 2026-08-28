const fs = require('fs');
const path = require('path');

const envPath = path.join(__dirname, '..', '.env');
const configPath = path.join(__dirname, '..', 'capacitor.config.json');
const fallbackPath = path.join(__dirname, '..', '.env.example');

let serverUrl = process.env.CAPACITOR_SERVER_URL || '';

if (!serverUrl && fs.existsSync(envPath)) {
    const env = fs.readFileSync(envPath, 'utf8');
    const match = env.match(/^CAPACITOR_SERVER_URL=(.+)$/m);
    if (match) {
        serverUrl = match[1].trim();
    }
}

if (!serverUrl || serverUrl.includes('YOUR-PRODUCTION-URL')) {
    console.error('\nSet CAPACITOR_SERVER_URL in mobile/.env to your live HTTPS website first.');
    console.error('Example: CAPACITOR_SERVER_URL=https://reservepro.onrender.com\n');
    process.exit(1);
}

const config = JSON.parse(fs.readFileSync(configPath, 'utf8'));
config.server = {
    url: serverUrl,
    cleartext: false,
    androidScheme: 'https',
};

fs.writeFileSync(configPath, `${JSON.stringify(config, null, 2)}\n`);

const wwwIndex = path.join(__dirname, '..', 'www', 'index.html');
let html = fs.readFileSync(wwwIndex, 'utf8');
html = html.replace(
    /var productionUrl = '[^']*';/,
    `var productionUrl = '${serverUrl}';`
);
fs.writeFileSync(wwwIndex, html);

console.log(`Capacitor app will load: ${serverUrl}`);
