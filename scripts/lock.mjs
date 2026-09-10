import { readFile, writeFile } from "node:fs/promises";
import { resolve, dirname } from "node:path";
import { fileURLToPath } from "node:url";

const HERE = dirname(fileURLToPath(import.meta.url));
const DIST = resolve(HERE, "..", "dist", "index.html");

const USER = process.env.SITE_USER || "Splmn1testsite";
const PASS = process.env.SITE_PASS || "Boss649205739LLL!!!";
const ITER = 300000;

const b64 = (buf) => Buffer.from(buf).toString("base64");

async function deriveKey(pass, salt) {
  const base = await crypto.subtle.importKey("raw", new TextEncoder().encode(pass), "PBKDF2", false, ["deriveKey"]);
  return crypto.subtle.deriveKey(
    { name: "PBKDF2", salt, iterations: ITER, hash: "SHA-256" },
    base,
    { name: "AES-GCM", length: 256 },
    false,
    ["encrypt"]
  );
}

const html = await readFile(DIST, "utf8");

const tags = [];
const grab = (re) => {
  let m;
  while ((m = re.exec(html))) tags.push(m[0]);
};
grab(/<script type="module"[^>]*><\/script>/g);
grab(/<link rel="stylesheet"[^>]*>/g);

if (!tags.length) {
  console.error("не нашёл теги сборки в index.html");
  process.exit(1);
}

const payload = tags.join("\n");
const salt = crypto.getRandomValues(new Uint8Array(16));
const iv = crypto.getRandomValues(new Uint8Array(12));
const key = await deriveKey(USER + ":" + PASS, salt);
const cipher = new Uint8Array(
  await crypto.subtle.encrypt({ name: "AES-GCM", iv }, key, new TextEncoder().encode(payload))
);

let out = html;
for (const tag of tags) out = out.replace(tag, "");

const gate = `
    <style>
      html, body { margin: 0; height: 100%; background: #0b0714; }
      #gate {
        position: fixed; inset: 0; z-index: 99999; display: flex;
        align-items: center; justify-content: center;
        background: radial-gradient(80% 50% at 50% 18%, #341257 0%, #170a29 55%, #0b0714 100%);
        font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
      }
      #gate form {
        width: 300px; padding: 26px 22px; border-radius: 18px;
        background: linear-gradient(180deg, #2a1044 0%, #1a0a2e 100%);
        box-shadow: 0 0 0 2px #c8912e, 0 0 0 3px #5b3d0d, 0 18px 44px rgba(0,0,0,.6);
        text-align: center;
      }
      #gate h1 { margin: 0 0 18px; font-size: 19px; font-weight: 700; color: #f0cd78; letter-spacing: .3px; }
      #gate input {
        width: 100%; box-sizing: border-box; margin-bottom: 10px; padding: 11px 13px;
        border-radius: 10px; border: 1.5px solid #6672f0; background: rgba(10,5,38,.92);
        color: #fff; font-size: 15px; outline: none;
      }
      #gate input:focus { border-color: #c163f5; }
      #gate button {
        width: 100%; margin-top: 6px; padding: 12px; border: 0; border-radius: 999px;
        background: linear-gradient(180deg, #c363e8 0%, #8e21bb 46%, #5f0c84 100%);
        box-shadow: 0 0 0 2px #e0b352, 0 0 0 3px #5b3d0d;
        color: #fff; font-size: 16px; font-weight: 700; cursor: pointer;
      }
      #gate button:disabled { filter: grayscale(.7) brightness(.75); cursor: default; }
      #gate p { min-height: 18px; margin: 12px 0 0; font-size: 13px; color: #ff8a8a; }
    </style>
    <div id="gate">
      <form autocomplete="on">
        <h1>Bonus Park</h1>
        <input id="gu" name="username" type="text" placeholder="Login" autocomplete="username" autocapitalize="off" spellcheck="false" />
        <input id="gp" name="password" type="password" placeholder="Password" autocomplete="current-password" />
        <button type="submit">Enter</button>
        <p id="ge"></p>
      </form>
    </div>
    <script>
      (function () {
        var S = "${b64(salt)}", V = "${b64(iv)}", C = "${b64(cipher)}", IT = ${ITER};
        var raw = function (s) { var b = atob(s), a = new Uint8Array(b.length); for (var i = 0; i < b.length; i++) a[i] = b.charCodeAt(i); return a; };
        var gate = document.getElementById("gate");
        var form = gate.querySelector("form");
        var err = document.getElementById("ge");
        var btn = form.querySelector("button");
        function boot(html) {
          gate.remove();
          var box = document.createElement("div");
          box.innerHTML = html;
          var nodes = Array.prototype.slice.call(box.childNodes);
          nodes.forEach(function (n) {
            if (n.tagName === "SCRIPT") {
              var s = document.createElement("script");
              for (var i = 0; i < n.attributes.length; i++) s.setAttribute(n.attributes[i].name, n.attributes[i].value);
              document.head.appendChild(s);
            } else if (n.nodeType === 1) {
              document.head.appendChild(n);
            }
          });
        }
        form.addEventListener("submit", function (e) {
          e.preventDefault();
          err.textContent = "";
          btn.disabled = true;
          var pass = document.getElementById("gu").value + ":" + document.getElementById("gp").value;
          crypto.subtle
            .importKey("raw", new TextEncoder().encode(pass), "PBKDF2", false, ["deriveKey"])
            .then(function (base) {
              return crypto.subtle.deriveKey(
                { name: "PBKDF2", salt: raw(S), iterations: IT, hash: "SHA-256" },
                base,
                { name: "AES-GCM", length: 256 },
                false,
                ["decrypt"]
              );
            })
            .then(function (key) {
              return crypto.subtle.decrypt({ name: "AES-GCM", iv: raw(V) }, key, raw(C));
            })
            .then(function (buf) {
              try { sessionStorage.setItem("bp.k", pass); } catch (x) {}
              boot(new TextDecoder().decode(buf));
            })
            .catch(function () {
              btn.disabled = false;
              err.textContent = "Wrong login or password";
            });
        });
        try {
          var saved = sessionStorage.getItem("bp.k");
          if (saved) {
            var parts = saved.split(":");
            document.getElementById("gu").value = parts.shift();
            document.getElementById("gp").value = parts.join(":");
            form.dispatchEvent(new Event("submit"));
          }
        } catch (x) {}
      })();
    </script>
`;

out = out.replace("<div id=\"root\"></div>", '<div id="root"></div>' + gate);

await writeFile(DIST, out, "utf8");
console.log("index.html закрыт паролем: зашифровано тегов", tags.length, "| итераций PBKDF2", ITER);
