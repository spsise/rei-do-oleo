#!/bin/bash
# scripts/analyze-template.sh
# Analisa arquivos do TailAdmin e gera relatório de classes customizadas e problemas de integração

TEMPLATE_DIR=${1:-"../tailadmin-template"}
REPORT_FILE="analyze-template-report.txt"

# Limpa relatório anterior
> "$REPORT_FILE"

echo "🔎 Analisando arquivos em: $TEMPLATE_DIR" | tee -a "$REPORT_FILE"

echo -e "\n--- CLASSES NÃO RECONHECIDAS PELO TAILWIND ---" | tee -a "$REPORT_FILE"
grep -rhoE 'class(Name)?=\"([^\"]+)\"' "$TEMPLATE_DIR" | \
  sed -E 's/class(Name)?=\"//g; s/\"//g' | \
  tr ' ' '\n' | sort | uniq | \
  grep -vE '^(bg|text|border|shadow|rounded|p|m|w|h|flex|grid|items|justify|gap|z|font|transition|hover|focus|ring|dark|block|inline|hidden|overflow|absolute|relative|fixed|sticky|top|bottom|left|right|container|col|row|divide|space|cursor|select|resize|order|object|opacity|pointer|visible|invisible|align|place|content|self|auto|min|max|truncate|whitespace|break|leading|tracking|list|decoration|underline|line|no-underline|italic|not-italic|uppercase|lowercase|capitalize|normal-case|antialiased|subpixel-antialiased|sr-only|not-sr-only|prose|from|via|to|animate|duration|delay|ease|origin|scale|rotate|translate|skew|transform|transition|will-change|group|peer|first|last|odd|even|visited|checked|focus-within|focus-visible|active|disabled|enabled|required|optional|read-only|read-write|placeholder|file|open|closed|motion-safe|motion-reduce|aria-|data-)' | \
  tee -a "$REPORT_FILE"

echo -e "\n--- USOS DE @apply COM !important ---" | tee -a "$REPORT_FILE"
grep -r --include='*.css' '@apply [^;]*!' "$TEMPLATE_DIR" | tee -a "$REPORT_FILE"

echo -e "\n--- DEPENDÊNCIAS EXTERNAS (fontes, ícones, plugins) ---" | tee -a "$REPORT_FILE"
grep -rE 'cdn|font|icon|plugin|import' "$TEMPLATE_DIR" | tee -a "$REPORT_FILE"

echo -e "\nRelatório salvo em $REPORT_FILE\n"