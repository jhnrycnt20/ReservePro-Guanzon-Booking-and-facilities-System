# ReservePro Mobile App (Android)

This folder contains the **real Android app** for ReservePro, built with [Capacitor](https://capacitorjs.com/).

The app opens your **live HTTPS website** inside a native shell. No tunnel is needed after the website is deployed online.

## How it works

1. Deploy the Laravel website to the internet (HTTPS).
2. Point the Android app to that URL.
3. Build an APK/AAB and install it on phones (or publish to Google Play).

The website stays online. The mobile app is an extra way to open it — like a dedicated browser with an app icon.

---

## Step 1 — Deploy the website (one time)

### Option A: Render (recommended, free HTTPS)

1. Push this project to GitHub.
2. Go to [render.com](https://render.com) → **New** → **Blueprint**.
3. Connect your repo. Render reads `render.yaml`.
4. Wait for deploy to finish.
5. Copy your live URL, for example:  
   `https://reservepro-xxxx.onrender.com`

### Option B: Any host with HTTPS

Use shared hosting, VPS, Railway, etc. Requirements:

- PHP 8.2+
- MySQL or SQLite
- Public **HTTPS** URL
- Set `APP_URL` in `.env` to that URL

---

## Step 2 — Configure the mobile app

```bash
cd mobile
copy .env.example .env
```

Edit `mobile/.env`:

```env
CAPACITOR_SERVER_URL=https://reservepro-xxxx.onrender.com
```

Replace with your real deployed URL (no trailing slash).

Sync Capacitor:

```bash
npm run sync
```

---

## Step 3 — Build the Android APK

### Requirements

- [Android Studio](https://developer.android.com/studio) (includes Java + Android SDK)
- Node.js (already installed)

### Build steps

1. Open Android Studio.
2. In terminal:

```bash
cd mobile
npm run open:android
```

3. In Android Studio: **Build → Build Bundle(s) / APK(s) → Build APK(s)**.
4. APK path:

```
mobile/android/app/build/outputs/apk/debug/app-debug.apk
```

5. Copy the APK to your phone and install (enable “Install unknown apps” if asked).

### Release / Play Store

For Google Play, use **Build → Generate Signed Bundle / APK** and upload the `.aab` file to [Google Play Console](https://play.google.com/console).

---

## iPhone note

This Capacitor project is set up for **Android** first. iPhone needs:

- A Mac with Xcode
- `npx cap add ios`
- Apple Developer account for App Store

iPhone users can still use **Safari → Add to Home Screen** on the live HTTPS website (PWA).

---

## Useful commands

| Command | Purpose |
|---------|---------|
| `npm run config` | Apply `CAPACITOR_SERVER_URL` to Capacitor config |
| `npm run sync` | Sync web assets + config to Android project |
| `npm run open:android` | Open project in Android Studio |

---

## Troubleshooting

**White screen in app**  
- Check `CAPACITOR_SERVER_URL` is correct and uses `https://`
- Website must be online (not `localhost` or tunnel)

**Login/session issues**  
- Ensure production `APP_URL` matches your live domain
- Use HTTPS only

**Demo login accounts (seeded on Render)**  
All passwords: `password`

| Role | Email |
|------|-------|
| Guest | `guest@reservepro.test` |
| Admin | `admin@reservepro.test` |
| Front Desk | `frontdesk@reservepro.test` |
| Security | `security@reservepro.test` |

On the login page, tap any demo account to auto-fill the form.

**App still needs tunnel**  
- The app loads your **deployed** site, not your laptop. Deploy first, then rebuild the APK.

---

## Project structure

```
mobile/
  android/          # Native Android project (open in Android Studio)
  www/              # Local splash/loader shell
  capacitor.config.json
  scripts/set-server-url.js
```

After deployment, update `CAPACITOR_SERVER_URL` and run `npm run sync` before each new APK build if the URL changes.
