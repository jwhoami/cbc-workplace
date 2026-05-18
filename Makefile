# Makefile — Generación de guías oficiales de CBC Workplace
#
# Uso:
#   make guides           # genera los 3 .docx
#   make guides GUIDE=admin
#   make guides-pdf       # genera los 3 .pdf (depende de guides)
#   make captures         # ejecuta pipeline Playwright completo
#   make captures-only SLUG=admin-org-suspend-modal
#   make annotate         # re-aplica overlay desde coords sidecar
#   make annotate-only SLUG=...
#   make reference-docx   # regenera la plantilla Pandoc
#   make lint             # lintea Markdown
#   make verify-captures
#   make clean
#
# Variables sobreescribibles:
#   GUIDE  = all | admin | impl | user            (default: all)
#   DATE   = string en formato libre              (default: año-mes actual)
#   TEMPLATE = ruta al reference.docx             (default: docs/guides/templates/cbc-reference.docx)
#   BUILD  = directorio de salida                 (default: docs/guides/build)

SHELL := /bin/bash

GUIDE    ?= all
LANG     ?= es
DATE     ?= $(shell date +"%Y-%m")
TEMPLATE ?= docs/guides/templates/cbc-reference.docx
BUILD    ?= docs/guides/build
SCRIPTS  := docs/guides/scripts
LUA      := $(SCRIPTS)/callout-filter.lua

PANDOC ?= pandoc
PYTHON ?= python3
NODE   ?= node
LO     ?= libreoffice

GUIDE_LIST_ALL := admin impl user
ifeq ($(GUIDE),all)
GUIDES := $(GUIDE_LIST_ALL)
else
GUIDES := $(GUIDE)
endif

OUT_FILES := $(foreach g,$(GUIDES),$(BUILD)/cbc-workplace-$(g).docx)
PDF_FILES := $(foreach g,$(GUIDES),$(BUILD)/cbc-workplace-$(g).pdf)

.PHONY: help guides guides-pdf reference-docx captures captures-only annotate annotate-only verify-captures lint clean clean-screenshots

help:
	@echo "Targets disponibles:"
	@echo "  make guides              Genera los .docx de las guías ($(GUIDE_LIST_ALL))"
	@echo "  make guides GUIDE=admin  Genera sólo la guía indicada"
	@echo "  make guides-pdf          Convierte los .docx a .pdf vía LibreOffice"
	@echo "  make reference-docx      Regenera docs/guides/templates/cbc-reference.docx"
	@echo "  make captures            Ejecuta el pipeline completo de capturas"
	@echo "  make captures-only SLUG=<slug>"
	@echo "  make annotate            Re-aplica overlay de anotaciones (no relanza browser)"
	@echo "  make annotate-only SLUG=<slug>"
	@echo "  make verify-captures     Valida que cada descriptor tenga su PNG"
	@echo "  make lint                Lintea Markdown bajo docs/guides/"
	@echo "  make clean               Borra build/ (mantiene screenshots versionadas)"
	@echo "  make clean-screenshots   Borra screenshots/ (requiere regenerar)"

guides: $(OUT_FILES)
	@echo "[ok] Guías generadas: $(OUT_FILES)"

$(BUILD)/cbc-workplace-%.docx: docs/guides/%/*.md $(TEMPLATE) | $(BUILD)
	@echo "[pandoc] Generando $@"
	@$(PANDOC) \
	  --from markdown+yaml_metadata_block+pipe_tables+raw_html+fenced_divs \
	  --to docx \
	  --reference-doc=$(TEMPLATE) \
	  --toc \
	  --toc-depth=3 \
	  --number-sections \
	  --metadata-file=docs/guides/$*/metadata.yaml \
	  --resource-path=.:docs/guides:docs/guides/$* \
	  --top-level-division=chapter \
	  $(if $(wildcard $(LUA)),--lua-filter=$(LUA),) \
	  --output=$@ \
	  $(sort $(filter-out docs/guides/$*/_%.md,$(wildcard docs/guides/$*/*.md)))

$(BUILD):
	@mkdir -p $@

reference-docx:
	@$(PYTHON) $(SCRIPTS)/build-reference-docx.py

guides-pdf: $(PDF_FILES)
	@echo "[ok] PDFs generados: $(PDF_FILES)"

$(BUILD)/%.pdf: $(BUILD)/%.docx
	@echo "[libreoffice] Convirtiendo $< -> $@"
	@$(LO) --headless --convert-to pdf --outdir $(BUILD) $<

captures:
	@cd docs/guides && $(NODE) scripts/captures.mjs --guide all

captures-only:
ifndef SLUG
	$(error SLUG no definido. Uso: make captures-only SLUG=admin-org-suspend-modal)
endif
	@cd docs/guides && $(NODE) scripts/captures.mjs --only $(SLUG)

annotate:
	@cd docs/guides && $(NODE) scripts/annotate.mjs

annotate-only:
ifndef SLUG
	$(error SLUG no definido. Uso: make annotate-only SLUG=admin-login-form)
endif
	@cd docs/guides && $(NODE) scripts/annotate.mjs --only $(SLUG)

verify-captures:
	@cd docs/guides && $(NODE) scripts/verify-captures.mjs

lint:
	@command -v markdownlint >/dev/null 2>&1 || { echo "markdownlint no instalado (npm i -g markdownlint-cli)"; exit 1; }
	@markdownlint docs/guides/**/*.md --ignore docs/guides/build --ignore docs/guides/screenshots

clean:
	@rm -rf $(BUILD) 2>/dev/null || true
	@echo "[ok] build/ borrado"
	@echo "[info] screenshots/ se mantiene (los PNG están versionados; usa 'make clean-screenshots' si quieres borrarlos)"

clean-screenshots:
	@rm -f docs/guides/screenshots/*.png docs/guides/screenshots/**/*.png 2>/dev/null || true
	@rm -f docs/guides/screenshots/**/*.coords.json 2>/dev/null || true
	@echo "[ok] screenshots/ borradas — recuerda regenerarlas con 'make captures' o 'git checkout docs/guides/screenshots/'"
