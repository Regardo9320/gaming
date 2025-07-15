<?php
// Backend deel, AJAX endpoint voor versie check
if (isset($_GET['action']) && $_GET['action'] === 'check_latest') {
    header('Content-Type: application/json');

    function checkUrlExists($url) {
        $headers = @get_headers($url);
        return $headers && strpos($headers[0], '200') !== false;
    }

    $BASE_URL = "https://install.exams.schoolyear.app/schoolyear-exams-win-";
    $EXT = ".msi";
    $LOGFILE = __DIR__ . '/log.txt';

    $MAJOR = 3;
    $MINOR = 5;
    $PATCH = 2;
    $MAX_PATCH = 10;
    $MAX_MINOR = 10000;
    $foundAny = false;
    $latestUrl = null;

    if (file_exists($LOGFILE)) {
        $lines = file($LOGFILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $lastLine = end($lines);
        if ($lastLine) {
            if (preg_match("/schoolyear-exams-win-(\d+)\.(\d+)\.(\d+)/", $lastLine, $matches)) {
                $MAJOR = (int)$matches[1];
                $MINOR = (int)$matches[2];
                $PATCH = (int)$matches[3] + 1;
            }
        }
    }

    $loggedUrls = file_exists($LOGFILE) ? file($LOGFILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    for ($p = $PATCH; $p <= $MAX_PATCH; $p++) {
        $url = $BASE_URL . "$MAJOR.$MINOR.$p" . $EXT;
        if (checkUrlExists($url)) {
            if (!in_array($url, $loggedUrls)) {
                file_put_contents($LOGFILE, $url . PHP_EOL, FILE_APPEND);
                $loggedUrls[] = $url;
                $foundAny = true;
                $latestUrl = $url;
            }
        }
    }

    for ($m = $MINOR + 1; $m <= $MAX_MINOR; $m++) {
        $foundInMinor = false;
        for ($p = 0; $p <= $MAX_PATCH; $p++) {
            $url = $BASE_URL . "$MAJOR.$m.$p" . $EXT;
            if (checkUrlExists($url)) {
                if (!in_array($url, $loggedUrls)) {
                    file_put_contents($LOGFILE, $url . PHP_EOL, FILE_APPEND);
                    $loggedUrls[] = $url;
                    $foundAny = true;
                    $foundInMinor = true;
                    $latestUrl = $url;
                }
            }
        }
        if (!$foundInMinor) {
            break;
        }
    }

    if (!$foundAny && !empty($loggedUrls)) {
        $latestUrl = end($loggedUrls);
    }

    if ($latestUrl) {
        echo json_encode(['success' => true, 'url' => $latestUrl, 'new_found' => $foundAny]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Geen versies gevonden en log is leeg.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
  <meta charset="UTF-8" />
  <title>Schoolyear Patcher Tool</title>
  <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
  <link rel="manifest" href="/images/site.webmanifest">
  <style>
    /* CSS variabelen light mode */
    :root {
      --bg-color: #f9fafb;
      --text-color: #333;
      --container-bg: #fff;
      --shadow-color: rgba(0, 0, 0, 0.08);
      --heading-color: #222;
      --subtext-color: #555;
      --input-border: #ccc;
      --input-hover-border: #3b82f6;
      --btn-bg: #3b82f6;
      --btn-bg-hover: #2563eb;
      --btn-shadow: rgba(59, 130, 246, 0.4);
      --btn-shadow-hover: rgba(37, 99, 235, 0.6);
      --download-btn-bg: #10b981;
      --download-btn-bg-hover: #059669;
      --download-btn-shadow: rgba(16, 185, 129, 0.45);
      --download-btn-shadow-hover: rgba(5, 150, 105, 0.65);
      --changelog-bg: #0284c7;
      --changelog-bg-hover: #0369a1;
      --changelog-shadow: rgba(2, 132, 199, 0.6);
    }

    /* CSS variabelen dark mode */
    body.dark {
      --bg-color: #121212;
      --text-color: #ddd;
      --container-bg: #1e1e1e;
      --shadow-color: rgba(0, 0, 0, 0.9);
      --heading-color: #eee;
      --subtext-color: #bbb;
      --input-border: #555;
      --input-hover-border: #3b82f6;
      --btn-bg: #2563eb;
      --btn-bg-hover: #3b82f6;
      --btn-shadow: rgba(37, 99, 235, 0.6);
      --btn-shadow-hover: rgba(59, 130, 246, 0.4);
      --download-btn-bg: #059669;
      --download-btn-bg-hover: #10b981;
      --download-btn-shadow: rgba(5, 150, 105, 0.65);
      --download-btn-shadow-hover: rgba(16, 185, 129, 0.45);
      --changelog-bg: #0369a1;
      --changelog-bg-hover: #0284c7;
      --changelog-shadow: rgba(3, 105, 161, 0.8);
    }

    /* Basis styles */
    body {
      margin: 0;
      padding: 0;
      height: 100vh;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
        Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
      background: var(--bg-color);
      color: var(--text-color);
      display: flex;
      justify-content: center;
      align-items: center;
      overflow-x: hidden;
      user-select: none;
      transition: background-color 0.3s, color 0.3s;
    }

    #mainContainer {
      width: 480px;
      background: var(--container-bg);
      padding: 28px 32px;
      box-shadow: 0 12px 32px var(--shadow-color);
      border-radius: 14px;
      text-align: center;
      z-index: 10;
      transition: background-color 0.3s, box-shadow 0.3s;
    }

    h2, h3 {
      margin: 0 0 16px 0;
      font-weight: 700;
      color: var(--heading-color);
      transition: color 0.3s;
    }

    p {
      margin-top: 0;
      margin-bottom: 20px;
      color: var(--subtext-color);
      font-weight: 500;
      font-size: 1rem;
      transition: color 0.3s;
    }

    input[type="file"] {
      width: 100%;
      padding: 8px;
      font-size: 1rem;
      border-radius: 8px;
      border: 1px solid var(--input-border);
      cursor: pointer;
      transition: border-color 0.3s;
      background: transparent;
      color: var(--text-color);
    }

    input[type="file"]:hover {
      border-color: var(--input-hover-border);
    }

    #options {
      display: none;
      margin-top: 24px;
      text-align: left;
      font-weight: 600;
      font-size: 1rem;
      color: var(--text-color);
      transition: color 0.3s;
    }

    #options label {
      display: block;
      margin-bottom: 12px;
      cursor: pointer;
      user-select: none;
    }

    #options input[type="checkbox"] {
      margin-right: 8px;
      transform: scale(1.2);
      vertical-align: middle;
      cursor: pointer;
    }

    button#patchBtn {
      margin-top: 16px;
      padding: 10px 24px;
      background: var(--btn-bg);
      border: none;
      border-radius: 12px;
      color: white;
      font-weight: 700;
      font-size: 1.1rem;
      cursor: pointer;
      box-shadow: 0 6px 15px var(--btn-shadow);
      transition: background 0.3s ease, box-shadow 0.3s ease;
      width: 100%;
      user-select: none;
    }

    button#patchBtn:hover {
      background: var(--btn-bg-hover);
      box-shadow: 0 8px 20px var(--btn-shadow-hover);
    }

    #status {
      margin-top: 22px;
      font-weight: 600;
      font-size: 1rem;
      min-height: 40px;
      color: var(--subtext-color);
      white-space: pre-line;
      user-select: text;
      transition: color 0.3s;
    }

    #downloadSection {
      margin-top: 48px;
      text-align: center;
      font-weight: 600;
      font-size: 1rem;
      color: var(--text-color);
      transition: color 0.3s;
    }

    #downloadSection button {
      margin-top: 12px;
      padding: 10px 28px;
      font-weight: 700;
      font-size: 1.05rem;
      background: var(--download-btn-bg);
      color: white;
      border: none;
      border-radius: 14px;
      cursor: pointer;
      box-shadow: 0 6px 15px var(--download-btn-shadow);
      transition: background 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
    }

    #downloadSection button:hover {
      background: var(--download-btn-bg-hover);
      box-shadow: 0 8px 20px var(--download-btn-shadow-hover);
    }

    /* Slideshow rechts */
    #slideshowSection {
      position: fixed;
      top: 50%;
      left: calc(50% + 240px + 80px);
      transform: translateY(-50%);
      width: 360px;
      height: 210px;
      background: var(--container-bg);
      box-shadow: 0 5px 20px var(--shadow-color);
      border-radius: 14px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 15px;
      z-index: 20;
      user-select: none;
      color: var(--heading-color);
      font-weight: 700;
      transition: background-color 0.3s, box-shadow 0.3s, color 0.3s;
    }

    #slideshowSection h3 {
      margin: 0 0 10px 0;
      font-weight: 700;
      color: var(--heading-color);
    }

    #imageSlideshow {
      flex: 1;
      width: 100%;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    #imageSlideshow img {
      max-width: 100%;
      max-height: 100%;
      opacity: 0;
      position: absolute;
      border-radius: 12px;
      transition: opacity 1s ease-in-out;
      pointer-events: none;
      user-select: none;
      object-fit: contain;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
    }

    #imageSlideshow img.active {
      opacity: 1;
      position: relative;
    }

    /* Status iframe links */
    #statusFrameSection {
      position: fixed;
      top: 50%;
      left: calc(50% - 240px - 80px - 360px);
      transform: translateY(-50%);
      width: 360px;
      height: 210px;
      background: var(--container-bg);
      box-shadow: 0 5px 20px var(--shadow-color);
      border-radius: 14px;
      overflow-x: hidden;
      overflow-y: hidden;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 15px;
      z-index: 20;
      user-select: none;
      color: var(--heading-color);
      font-weight: 700;
      box-sizing: border-box;
      transition: background-color 0.3s, box-shadow 0.3s, color 0.3s;
    }

    #statusFrameSection h3 {
      margin: 0 0 10px 0;
      font-weight: 700;
      color: var(--heading-color);
    }

    #statusFrameSection iframe {
      flex: 1;
      width: 100%;
      border-radius: 12px;
      border: none;
      overflow-x: hidden;
      overflow-y: hidden;
      background: transparent;
    }

    /* Changelog link box */
    #changelogLinkBox {
      margin-top: 10px;
      padding: 12px;
      background-color: var(--changelog-bg);
      border-radius: 8px;
      text-align: center;
      font-weight: 600;
      font-size: 1rem;
      color: white;
      cursor: pointer;
      user-select: none;
      width: 360px;
      box-sizing: border-box;
      box-shadow: 0 2px 8px var(--changelog-shadow);
      position: fixed;
      top: calc(50% + 110px + 20px);
      left: calc(50% - 240px - 80px - 360px);
      z-index: 30;
      transition: background-color 0.3s, box-shadow 0.3s;
    }

    #changelogLinkBox a {
      text-decoration: none;
      color: inherit;
      display: block;
      width: 100%;
      height: 100%;
    }

    #changelogLinkBox:hover {
      background-color: var(--changelog-bg-hover);
    }

    /* Dark/Light mode toggle button */
    #modeToggleBtn {
      position: fixed;
      top: 20px;
      right: 20px;
      background: var(--btn-bg);
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      box-shadow: 0 6px 15px var(--btn-shadow);
      transition: background 0.3s ease, box-shadow 0.3s ease;
      user-select: none;
      z-index: 100;
    }

    #modeToggleBtn:hover {
      background: var(--btn-bg-hover);
      box-shadow: 0 8px 20px var(--btn-shadow-hover);
    }
  </style>
</head>
<body>
  <button id="modeToggleBtn" aria-label="Toggle dark/light mode">Dark Mode</button>

  <div id="mainContainer">
    <h2>Upload een .msi-bestand</h2>
    <input type="file" id="fileInput" accept=".msi">

    <div id="options">
      <h3>Patch-opties:</h3>
      <label><input type="checkbox" id="vmPatch"> Bypass VM detection</label>
      <label><input type="checkbox" id="ramPatch"> Bypass minimum RAM requirement detection</label>
      <label><input type="checkbox" id="dotnetPatch"> Bypass .NET requirement detection</label>
      <button id="patchBtn">Voer patch uit</button>
    </div>

    <div id="status">Status berichten verschijnen hier...</div>

    <div id="downloadSection">
      <h3>Automatisch versies zoeken</h3>
      <p>Klik op de knop om de nieuwste versie URL te zoeken:</p>
      <button id="checkVersionBtn">Zoek nieuwste versie URL</button>
      <div id="versionResult" style="margin-top: 12px; user-select: text;"></div>
    </div>
  </div>

  <div id="slideshowSection" aria-label="Afbeeldingen slideshow">
    <h3>Sonic says:</h3>
    <div id="imageSlideshow"></div>
  </div>

  <div id="statusFrameSection" aria-label="Status Schoolyear">
    <h3>Status Schoolyear</h3>
    <iframe src="https://status.schoolyear.com/" scrolling="no" sandbox="allow-same-origin allow-scripts allow-popups allow-forms" loading="lazy"></iframe>
  </div>

  <div id="changelogLinkBox">
    <a href="https://help.schoolyear.com/hc/en-gb/articles/6080053570589-Changelog" target="_blank" rel="noopener noreferrer">
      Klik hier om de changelogs te bekijken.
    </a>
  </div>

  <audio id="backgroundAudio" loop>
    <source src="music/background.mp3" type="audio/mpeg" />
    Je browser ondersteunt geen audio.
  </audio>
  <script>
    const modeToggleBtn = document.getElementById('modeToggleBtn');
    const body = document.body;
    const audio = document.getElementById("backgroundAudio");
    const status = document.getElementById("status");
    const optionsDiv = document.getElementById("options");
    const fileInput = document.getElementById("fileInput");
    const patchBtn = document.getElementById("patchBtn");

    let loadedData = null;
    let loadedFilename = "";

    // DARK MODE + AUDIO INIT
    function applyMode(mode) {
      if (mode === 'dark') {
        body.classList.add('dark');
        modeToggleBtn.textContent = 'Light Mode';
      } else {
        body.classList.remove('dark');
        modeToggleBtn.textContent = 'Dark Mode';
      }
    }

    function getStoredPreference() {
      return localStorage.getItem('color-scheme');
    }

    function storePreference(mode) {
      if (mode) {
        localStorage.setItem('color-scheme', mode);
      } else {
        localStorage.removeItem('color-scheme');
      }
    }

    function getSystemPreference() {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function initMode() {
      const stored = getStoredPreference();
      try {
        if (stored === 'dark' || stored === 'light') {
          applyMode(stored);
          audio.play();
        } else {
          applyMode(getSystemPreference());
          audio.play();
        }
      } catch (e) {
        console.warn("Audio autoplay geblokkeerd:", e);
      }
    }

    // Event listeners
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
      if (!getStoredPreference()) {
        applyMode(e.matches ? 'dark' : 'light');
      }
    });

    modeToggleBtn.addEventListener('click', () => {
      const currentMode = body.classList.contains('dark') ? 'dark' : 'light';
      const newMode = currentMode === 'dark' ? 'light' : 'dark';
      applyMode(newMode);
      storePreference(newMode);
    });

    fileInput.addEventListener("click", () => {
      try {
        audio.play();
      } catch (e) {
        console.warn("Audio blokkeerde op input click:", e);
      }
    });

    fileInput.addEventListener("change", async function () {
      const file = this.files[0];
      if (!file) return;

      if (!file.name.toLowerCase().endsWith(".msi")) {
        status.textContent = "❌ Alleen .msi bestanden zijn toegestaan.";
        optionsDiv.style.display = "none";
        return;
      }

      status.textContent = "✅ Bestand geladen: " + file.name;
      optionsDiv.style.display = "block";
      const arrayBuffer = await file.arrayBuffer();
      loadedData = new Uint8Array(arrayBuffer);
      loadedFilename = file.name;
    });

    const PATCHES = {
      vmPatch: ["AI_DETECTED_VIRTUAL_MACHINE;"],
      ramPatch: ["PHYSICAL_MEMORY2048"],
      dotnetPatch: ["AI_DETECTED_DOTNET_VERSION", "AI_REQUIRED_DOTNET_VERSION4.7.2"]
    };

    patchBtn.addEventListener("click", () => {
      if (!loadedData) {
        status.textContent = "❌ Geen bestand geladen.";
        return;
      }

      const selectedStrings = [];
      for (const [checkboxId, strings] of Object.entries(PATCHES)) {
        if (document.getElementById(checkboxId).checked) {
          selectedStrings.push(...strings);
        }
      }

      if (selectedStrings.length === 0) {
        status.textContent = "⚠️ Geen patch-opties geselecteerd.";
        return;
      }

      const data = new Uint8Array(loadedData); // kopie
      let totalFound = 0;

      for (const targetString of selectedStrings) {
        const targetBytes = new TextEncoder().encode(targetString);
        for (let i = 0; i <= data.length - targetBytes.length; i++) {
          let match = true;
          for (let j = 0; j < targetBytes.length; j++) {
            if (data[i + j] !== targetBytes[j]) {
              match = false;
              break;
            }
          }
          if (match) {
            for (let j = 0; j < targetBytes.length; j++) {
              data[i + j] = 0x00;
            }
            totalFound++;
            break;
          }
        }
      }

      if (totalFound === 0) {
        status.textContent = "⚠️ Geen doelstrings gevonden in bestand.";
        return;
      }

      const blob = new Blob([data], { type: "application/octet-stream" });
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = "gepatcht_" + loadedFilename;
      a.click();
      status.textContent = `✅ ${totalFound} string(s) succesvol gepatcht. Bestand gedownload.`;
    });

    // -------- Versie check knop --------
    const checkBtn = document.getElementById('checkVersionBtn');
    const versionResult = document.getElementById('versionResult');

    checkBtn.addEventListener('click', () => {
      versionResult.textContent = "Bezig met zoeken...";
      fetch('?action=check_latest')
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            versionResult.innerHTML = (data.new_found ? "Nieuwe versie gevonden:" : "Laatste bekende versie:") +
              `<br><a href="${data.url}" target="_blank" rel="noopener noreferrer">${data.url}</a>`;
          } else {
            versionResult.textContent = data.message || "Geen resultaten gevonden.";
          }
        })
        .catch(err => {
          versionResult.textContent = "Fout bij zoeken: " + err.message;
        });
    });

    // ----------- Slideshow ----------------
    const images = [
      "images/voorbeeld1.png",
      "images/voorbeeld2.png",
      "images/voorbeeld3.png",
      "images/voorbeeld4.png"
    ];
    const slideshowContainer = document.getElementById("imageSlideshow");
    let currentIndex = 0;

    function createImgElement(src) {
      const img = document.createElement("img");
      img.src = src;
      img.alt = "Slideshow afbeelding";
      return img;
    }

    images.forEach((src, idx) => {
      const img = createImgElement(src);
      if (idx === 0) img.classList.add("active");
      slideshowContainer.appendChild(img);
    });

    const imgs = slideshowContainer.querySelectorAll("img");

    function getRandomNextIndex(current, max) {
      let next = current;
      while (next === current) {
        next = Math.floor(Math.random() * max);
      }
      return next;
    }

    function nextImage() {
      imgs[currentIndex].classList.remove("active");
      currentIndex = getRandomNextIndex(currentIndex, imgs.length);
      imgs[currentIndex].classList.add("active");
    }

    setInterval(nextImage, 3800);

    // Init bij laden
    initMode();
  </script>
</body>
</html>