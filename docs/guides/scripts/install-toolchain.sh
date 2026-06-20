#!/usr/bin/env bash
# install-toolchain.sh — Instala el toolchain requerido por las guías:
#   pandoc, libreoffice, pngquant, markdownlint-cli, @mermaid-js/mermaid-cli
#
# Pre-requisitos:
#   - Ubuntu/Debian con apt (probado en WSL2 / Ubuntu 22.04)
#   - node 18+ y npm en el PATH del usuario actual
#   - sudo disponible (sólo para apt; npm usa el prefix del usuario)
#
# Tamaño aproximado: ~3 GB en disco (libreoffice es ~700 MB).
#
# Uso:
#   bash docs/guides/scripts/install-toolchain.sh           # todo
#   bash docs/guides/scripts/install-toolchain.sh --no-pdf  # sin libreoffice
#
# ¡NO corras este script con `sudo` desde fuera! sudo resetea PATH y se
# pierden herramientas instaladas en $HOME (p.ej. node via nvm). El script
# pedirá sudo internamente sólo cuando lo necesite (apt).
#
set -euo pipefail

if [[ ${EUID:-$(id -u)} -eq 0 ]] && [[ -n "${SUDO_USER:-}" ]]; then
  cat >&2 <<'MSG'
[error] No corras este script con `sudo` desde fuera.

Razón: sudo resetea PATH (secure_path) y pierde herramientas instaladas
en $HOME — típicamente node via nvm. El script ya invoca sudo
internamente sólo para los comandos que lo necesitan (apt).

Uso correcto:
  bash docs/guides/scripts/install-toolchain.sh
  # te pedirá contraseña cuando llegue al apt install
MSG
  exit 1
fi

WANT_PDF=1
for arg in "$@"; do
  case "$arg" in
    --no-pdf) WANT_PDF=0 ;;
    -h|--help)
      sed -n '2,22p' "$0"
      exit 0
      ;;
  esac
done

need() { command -v "$1" >/dev/null 2>&1; }

echo "[install] Verificando pre-requisitos…"
need node || { echo "[error] node no encontrado en PATH (\$PATH=$PATH)."; exit 1; }
need npm  || { echo "[error] npm no encontrado en PATH."; exit 1; }
need sudo || { echo "[error] sudo no disponible."; exit 1; }

NODE_VERSION="$(node --version)"
NPM_PREFIX="$(npm config get prefix 2>/dev/null || echo "")"
echo "[install]   node: $NODE_VERSION ($(command -v node))"
echo "[install]   npm prefix: $NPM_PREFIX"

# Determina si npm install -g requiere sudo. Si el prefix está en
# /usr o /opt, sí. Si está bajo $HOME (nvm/asdf/volta), no.
if [[ "$NPM_PREFIX" == /usr* || "$NPM_PREFIX" == /opt* ]]; then
  NPM_SUDO="sudo"
  echo "[install]   npm -g requerirá sudo (prefix bajo $NPM_PREFIX)"
else
  NPM_SUDO=""
  echo "[install]   npm -g sin sudo (prefix bajo \$HOME)"
fi

APT_PKGS=(pandoc pngquant)
if [[ $WANT_PDF -eq 1 ]]; then
  APT_PKGS+=(libreoffice)
fi

echo
echo "[install] sudo apt update"
sudo apt update

echo "[install] sudo apt install -y ${APT_PKGS[*]}"
sudo apt install -y "${APT_PKGS[@]}"

echo "[install] ${NPM_SUDO:-(sin sudo)} npm install -g markdownlint-cli @mermaid-js/mermaid-cli"
${NPM_SUDO} npm install -g markdownlint-cli @mermaid-js/mermaid-cli

echo
echo "[verify] Versiones instaladas:"
need pandoc       && pandoc       --version | head -1 || echo "  pandoc:       FALTA"
need pngquant     && pngquant     --version             || echo "  pngquant:     FALTA"
if [[ $WANT_PDF -eq 1 ]]; then
  need libreoffice && libreoffice --version | head -1 || echo "  libreoffice:  FALTA"
fi
need markdownlint && markdownlint --version             || echo "  markdownlint: FALTA"
need mmdc         && mmdc         --version             || echo "  mmdc:         FALTA"

echo
echo "[ok] Toolchain del sistema instalado. Próximos pasos:"
echo "    cd docs/guides && npm install && npx playwright install chromium"
echo "    pip3 install --user --break-system-packages python-docx==1.1.2"
echo "    make reference-docx"
