---
version: alpha
format: "DESIGN.md"
schema: "design-md"
name: "feito"
title: "feito. - Desenvolvimento web para empresas"
description: "DESIGN.md adaptado para a identidade visual do feito. Use este arquivo como fonte de verdade para gerar interfaces consistentes. Define cores, tipografia Satoshi, espaçamento, raios, componentes, profundidade, aplicações e regras de uso para sites, landing pages e sistemas web."
source: "https://feitojunto.com/"
domain: "feitojunto.com"
generatedAt: "2026-09-01T18:35:47+00:00"
generator: "CamaraUX DESIGN.md Generator"
compatibility:
  stitch: "https://stitch.withgoogle.com/docs/design-md/overview"
  intent: "Token-driven UI guidance for AI tools and design generators"
author:
  name: "feito."
  url: "https://feitojunto.com"

colors:
  primary: "#FF5A00"
  primary-active: "#D94800"
  secondary: "#FF8A4C"
  ink: "#111111"
  body: "#3D3D3D"
  muted: "#8A8A8A"
  hairline: "#2A2A2A"
  canvas: "#090909"
  surface-soft: "#F4F4EF"
  surface-card: "#141414"
  on-primary: "#FFFFFF"
  on-dark: "#FFFFFF"
  scrim: "#090909"
  accent: "#FF5A00"
  --feito-text-color: "#111111"
  --feito-text-color-2: "#6F6F6F"
  --feito-text-color-3: "#999999"
  --feito-dark: "#090909"
  --feito-dark-soft: "#141414"
  --feito-orange-soft: "#FF8A4C"
  background-color: "#090909"
  background-color-2: "#F4F4EF"
  background-color-3: "#EAEAE4"
  background-color-4: "#141414"
  background-color-5: "#242424"
  background-color-6: "#D7D7D0"
  --token-feito-orange: "#FF5A00"
  --token-feito-offwhite: "#F4F4EF"
  --token-feito-black: "#090909"
  --token-feito-white: "#FFFFFF"

typography:
  display-xl:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "80px"
    fontWeight: "700"
    lineHeight: "0.98"
    letterSpacing: "-2.4px"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "h1"
  display-lg:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "56px"
    fontWeight: "700"
    lineHeight: "1.02"
    letterSpacing: "-1.6px"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "h2"
  display-md:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "44px"
    fontWeight: "700"
    lineHeight: "1.05"
    letterSpacing: "-1.2px"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "h3"
  display-sm:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "28px"
    fontWeight: "700"
    lineHeight: "1.15"
    letterSpacing: "-0.5px"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "h4"
  body-md:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "16px"
    fontWeight: "400"
    lineHeight: "1.5"
    letterSpacing: "0"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "body"
  caption:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "12px"
    fontWeight: "500"
    lineHeight: "1.35"
    letterSpacing: "0"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "small"
  button-md:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "14px"
    fontWeight: "600"
    lineHeight: "1.2"
    letterSpacing: "0"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "button"
  link:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "15px"
    fontWeight: "500"
    lineHeight: "normal"
    letterSpacing: "0"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "a"
  input:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "14px"
    fontWeight: "400"
    lineHeight: "1.4"
    letterSpacing: "0"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "input"
  label:
    fontFamily: ""Satoshi", "Satoshi Variable", sans-serif"
    fontSize: "13px"
    fontWeight: "600"
    lineHeight: "1.3"
    letterSpacing: "0"
    fallback: "sans-serif"
    fontSource: "system-or-public"
    isCustomFont: "true"
    sourceSelector: "label"

rounded:
  none: "0px"
  xs: "6px"
  sm: "10px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  full: "9999px"

spacing:
  xxs: "4px"
  xs: "8px"
  sm: "12px"
  md: "16px"
  base: "20px"
  lg: "24px"
  xl: "32px"
  xxl: "48px"
  section: "96px"
  section: "8px"

components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "{colors.on-primary}"
    typography: "{typography.button-md}"
    rounded: "{rounded.md}"
    padding: "{spacing.sm} {spacing.lg}"
    height: "44px"
  button-secondary:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.button-md}"
    rounded: "{rounded.md}"
    padding: "{spacing.sm} {spacing.lg}"
    height: "44px"
    border: "1px solid {colors.hairline}"
  text-link:
    backgroundColor: "transparent"
    textColor: "{colors.primary}"
    typography: "{typography.link}"
  text-input:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.input}"
    rounded: "{rounded.md}"
    padding: "{spacing.sm} {spacing.base}"
    height: "44px"
    border: "1px solid {colors.hairline}"
  surface-card:
    backgroundColor: "{colors.surface-card}"
    textColor: "{colors.ink}"
    typography: "{typography.body-md}"
    rounded: "{rounded.lg}"
    padding: "{spacing.lg}"
    border: "1px solid {colors.hairline}"
  badge-pill:
    backgroundColor: "{colors.surface-soft}"
    textColor: "{colors.ink}"
    typography: "{typography.label}"
    rounded: "{rounded.full}"
    padding: "{spacing.xs} {spacing.sm}"
  top-nav:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.link}"
    height: "64px"
    border: "1px solid {colors.hairline}"
  hero-section:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.display-lg}"
    padding: "{spacing.section} {spacing.xl}"
  feature-card:
    backgroundColor: "{colors.surface-card}"
    textColor: "{colors.ink}"
    typography: "{typography.body-md}"
    rounded: "{rounded.lg}"
    padding: "{spacing.lg}"
    border: "1px solid {colors.hairline}"
  application-shell:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.ink}"
    typography: "{typography.body-md}"
    rounded: "{rounded.xl}"
    padding: "{spacing.xl}"
  input-field:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.primary}"
    typography: "{typography.body-md}"
    rounded: "{rounded.none}"
    selector: "input, textarea, select"
  card:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.primary}"
    typography: "{typography.body-md}"
    rounded: "{rounded.none}"
    selector: ".card, [class*=\"card\"], article"
  badge:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.primary}"
    typography: "{typography.body-md}"
    rounded: "{rounded.none}"
    selector: ".badge, .tag, [class*=\"badge\"], [class*=\"tag\"]"
  navigation:
    backgroundColor: "{colors.canvas}"
    textColor: "{colors.primary}"
    typography: "{typography.body-md}"
    rounded: "{rounded.none}"
    selector: "nav, header"

applications:
  landing-page:
    description: "Composição de hero, navegação, CTA e cards usando os tokens principais do arquivo."
    components:
      - "top-nav"
      - "hero-section"
      - "button-primary"
      - "feature-card"
  dashboard-card-grid:
    description: "Grid de cards para apresentar métricas, recursos ou módulos de produto."
    components:
      - "surface-card"
      - "badge-pill"
      - "button-secondary"
  form-flow:
    description: "Fluxo simples com input, link de apoio e CTA principal."
    components:
      - "text-input"
      - "button-primary"
      - "text-link"
  content-preview:
    description: "Página de conteúdo com navegação, card visual e ações rápidas."
    components:
      - "top-nav"
      - "application-shell"
      - "feature-card"

brand:
  name: "feito."
  tagline: "feito junto."
  positioning: "desenvolvimento web para empresas"
  palette:
    orange: "#FF5A00"
    black: "#090909"
    offwhite: "#F4F4EF"
    white: "#FFFFFF"
  typography:
    family: "Satoshi"
    fallback: "sans-serif"
  visualDirection:
    - "premium, minimalista e tecnológico"
    - "equilibrar seções pretas e off-white"
    - "usar laranja com parcimônia como assinatura visual"
    - "priorizar superfícies de vidro, transparências sutis e bordas hairline quando apropriado"
    - "evitar estética genérica de agência, excesso de gradientes e elementos com aparência de IA"
    - "preservar bastante espaço negativo e hierarquia tipográfica forte"

usage:
  instruction: "Use os tokens semânticos abaixo antes de criar novos valores. Preserve nomes, cores, fontes, espaçamentos e raios do arquivo."
  primaryAction: "{components.button-primary}"
  secondaryAction: "{components.button-secondary}"
  mainText: "{colors.ink}"
  pageBackground: "{colors.canvas}"
  cardSurface: "{colors.surface-card}"
  defaultRadius: "{rounded.md}"
  defaultSpacing: "{spacing.base}"
  accessibility:
    minimumTouchTarget: "44px"
    textContrast: "WCAG AA when possible"
    focusVisible: "Use a visible focus ring for keyboard navigation"
  aiRules:
    - "Use token references such as {colors.primary}, {typography.body-md}, {spacing.base} and {rounded.md}."
    - "Do not invent brand colors when an equivalent token exists."
    - "Use components from the components map before creating new components."
    - "Keep generated layouts responsive and accessible."

---

# feito. - Desenvolvimento web para empresas Design System

## Overview

**feito. - Desenvolvimento web para empresas** usa uma linguagem visual premium, minimalista e tecnológica, equilibrando preto profundo, off-white e laranja feito. A interface combina espaços amplos, tipografia Satoshi, superfícies de vidro, bordas sutis e contrastes fortes. O sistema abaixo organiza cores, tipografia, espaçamentos, raios, componentes e exemplos de aplicação para orientar criação de interfaces por humanos e ferramentas de IA.

**Resumo visual:**
- **Marca/ação:** `{colors.primary}` (`#FF5A00`) deve conduzir CTAs, detalhes de marca, estados ativos e elementos de maior prioridade.
- **Base da página:** `{colors.canvas}` (`#090909`) define o fundo principal.
- **Superfícies:** `{colors.surface-card}` (`#090909`) sustenta cards, painéis e blocos destacados.
- **Texto principal:** `{colors.ink}` (`#111111`) é usado em superfícies claras; em superfícies escuras, use `{colors.on-dark}` (`#FFFFFF`).
- **Componentes:** use primeiro os tokens em `components` antes de criar variações novas.

**Dados analisados:**
- **Fonte:** https://feitojunto.com/
- **Domínio:** `feitojunto.com`
- **Cores normalizadas:** 36
- **Estilos tipográficos:** 18
- **Componentes-base:** 14

> Este DESIGN.md é uma base técnica gerada automaticamente. Revise nomes, papéis semânticos e componentes críticos antes de usar em produção.

## Colors

Use a paleta por papel semântico, não apenas por valor HEX. Isso evita que ferramentas de IA usem cores decorativas, semânticas ou de alerta como identidade principal.

### Core roles

- **Primary** (`#FF5A00`): CTAs, links importantes, foco, seleção e principais elementos interativos.
- **Primary active** (`#D94800`): Hover, pressed, active e variações de maior contraste do primário.
- **Secondary** (`#FF8A4C`): Apoio visual, chips, ações secundárias e destaques complementares.
- **Accent** (`#FF8A4C`): Destaques visuais, gradientes, ilustrações e elementos editoriais.
- **Canvas** (`#090909`): Fundo principal da página.
- **Surface** (`#090909`): Cards, painéis, modais e superfícies contidas.
- **Ink** (`#FF5A00`): Títulos, texto forte e ícones principais.
- **Body** (`#FF5A00`): Texto de leitura e conteúdo corrente.
- **Muted** (`#8A8A8A`): Descrições, metadados, placeholders e textos de baixa ênfase.
- **Border** (`#2A2A2A`): Divisórias, bordas, outlines e separadores.

### Supporting tokens

- **`{colors.surface-soft}`** (`#FAFAFA`): Superfícies suaves e áreas secundárias.
- **`{colors.on-primary}`** (`#ffffff`): Texto sobre fundo primário.
- **`{colors.on-dark}`** (`#ffffff`): Cor detectada no site.
- **`{colors.scrim}`** (`#090909`): Cor detectada no site.
- **`{colors.--framer-text-color}`** (`#52525B`): Cor detectada no site.
- **`{colors.--framer-text-color-2}`** (`#A1A1AA`): Cor detectada no site.
- **`{colors.--framer-text-color-3}`** (`#71717A`): Cor detectada no site.
- **`{colors.--framer-text-color-4}`** (`#050714`): Cor detectada no site.
- **`{colors.--framer-text-color-5}`** (`#09090B`): Cor detectada no site.
- **`{colors.background-color}`** (`#70CC8D`): Cor detectada no site.
- **`{colors.background-color-2}`** (`#FFFFFF`): Cor detectada no site.
- **`{colors.background-color-3}`** (`#F4F4F5`): Cor detectada no site.
- **`{colors.background-color-4}`** (`#18181B`): Cor detectada no site.
- **`{colors.background-color-5}`** (`#3F3F46`): Cor detectada no site.
- **`{colors.background-color-6}`** (`#D4D4D8`): Cor detectada no site.
- **`{colors.--token-0607cc41-b3b0-4e98-8426-1a0137d4312b}`** (`#446B5D`): Cor detectada no site.
- **`{colors.--token-07c2ca58-725d-4d70-a1f9-dcb3aa49faac}`** (`#1A2824`): Cor detectada no site.
- **`{colors.--token-0c379339-ff6f-49e7-9998-06733a3b4bea}`** (`#4A4B65`): Cor detectada no site.

### Usage rules

- Use `{colors.primary}` somente para ações e elementos de alta prioridade.
- Use `{colors.canvas}` como base e `{colors.surface-card}` para cards, painéis e áreas elevadas.
- Use `{colors.ink}` e `{colors.body}` para leitura; `{colors.muted}` para apoio e metadados.
- Use `{colors.success}`, `{colors.warning}` e `{colors.danger}` apenas para estados semânticos.
- Não transforme cores de alerta, erro ou decoração em cor principal da marca.

## Typography

A escala tipográfica foi consolidada por função para facilitar uso em prompts, protótipos e componentes. Preserve a pilha `fontFamily` detectada; quando a fonte for customizada, mantenha os fallbacks.

### Display & headings

| Token | Font | Size | Weight | Line | Tracking | Use |
|---|---|---:|---:|---:|---:|---|
| `{typography.display-xl}` | `"Manrope", "Manrope Placeholder", sans-serif` | `88px` | `700` | `110%` | `-0.2px` | Títulos e chamadas |
| `{typography.display-lg}` | `"Inter Variable", "Inter Variable Placeholder", sans-serif` | `36px` | `700` | `1.15` | `0` | Títulos e chamadas |
| `{typography.display-md}` | `"Manrope", "Manrope Placeholder", sans-serif` | `75px` | `700` | `110%` | `-0.2px` | Títulos e chamadas |
| `{typography.display-sm}` | `system-ui, sans-serif` | `22px` | `700` | `1.25` | `0` | Títulos e chamadas |

### Body & reading

| Token | Font | Size | Weight | Line | Tracking | Use |
|---|---|---:|---:|---:|---:|---|
| `{typography.body-md}` | `system-ui, sans-serif` | `16px` | `400` | `1.5` | `0` | Texto e interface |
| `{typography.caption}` | `"Manrope", "Manrope Placeholder", sans-serif` | `12px` | `500` | `1.35` | `0` | Texto e interface |

### Interface

| Token | Font | Size | Weight | Line | Tracking | Use |
|---|---|---:|---:|---:|---:|---|
| `{typography.button-md}` | `sans-serif` | `12px` | `700` | `1.2` | `0` | Botões e CTAs |
| `{typography.label}` | `system-ui, sans-serif` | `14px` | `700` | `1.3` | `0` | Labels e microcopy |
| `{typography.input}` | `sans-serif` | `12px` | `400` | `1.25` | `0` | Texto e interface |
| `{typography.link}` | `var(--framer-link-font-family, var(--framer-font-family)), system-ui, sans-serif` | `16px` | `400` | `normal` | `0.5px` | Links e navegação |

### Additional detected styles

- **`{typography.font-face-1}`**: `16px` / weight `400` / line `1.5` using `'Geist', system-ui, sans-serif`.
- **`{typography.font-face-2}`**: `16px` / weight `400` / line `1.5` using `'Geist', system-ui, sans-serif`.
- **`{typography.font-face-3}`**: `16px` / weight `400` / line `1.5` using `'Geist', system-ui, sans-serif`.
- **`{typography.font-face-4}`**: `16px` / weight `400` / line `1.5` using `'Geist', system-ui, sans-serif`.
- **`{typography.font-face-5}`**: `16px` / weight `400` / line `1.5` using `'Geist', system-ui, sans-serif`.
- **`{typography.font-face-6}`**: `16px` / weight `700` / line `1.5` using `'Geist', system-ui, sans-serif`.
- **`{typography.font-family-1}`**: `16px` / weight `400` / line `1.5` using `'Geist', system-ui, sans-serif`.
- **`{typography.font-family-2}`**: `16px` / weight `400` / line `1.5` using `'Instrument Serif'`.

### Typography principles

- Use display/headline tokens para hierarquia principal e body tokens para leitura contínua.
- Não adicione uma nova família se a fonte original não carregar; mantenha a pilha de fallback.
- Preserve pesos e alturas de linha detectados para evitar mudança de personalidade visual.
- Labels, badges e botões devem usar os tokens de interface para manter densidade e legibilidade.

## Layout

### Escala de espaçamento
| Token | Valor | Uso sugerido |
|---|---:|---|
| `{spacing.xxs}` | `0em` | Micro ajustes |
| `{spacing.xs}` | `1px` | Ícones e chips |
| `{spacing.sm}` | `2px` | Elementos compactos |
| `{spacing.md}` | `3px` | Inputs e grupos |
| `{spacing.base}` | `4px` | Ritmo base |
| `{spacing.lg}` | `5px` | Cards |
| `{spacing.xl}` | `6px` | Blocos |
| `{spacing.xxl}` | `7px` | Seções grandes |
| `{spacing.section}` | `8px` | Separação entre seções |

### Escala de raios
| Token | Valor | Uso sugerido |
|---|---:|---|
| `{rounded.none}` | `0px` | Elementos retos |
| `{rounded.xs}` | `4px` | Pequenos controles |
| `{rounded.sm}` | `6px` | Botões compactos |
| `{rounded.md}` | `20px` | Inputs e cards pequenos |
| `{rounded.lg}` | `33px` | Cards |
| `{rounded.xl}` | `99px` | Painéis grandes |
| `{rounded.full}` | `9999px` | Pills e círculos |

### Princípios de layout
- Use espaçamento consistente entre seções, cards e grupos de formulário.
- Reserve `{spacing.section}` para blocos grandes e `{spacing.lg}` / `{spacing.xl}` para cards.
- Use `{rounded.full}` apenas para pills, avatares e botões circulares.
- Não misture raios intermediários fora da escala documentada.

## Elevation & Depth

| Nível | Tratamento | Uso |
|---|---|---|
| Flat | Sem sombra | Fundos, seções e blocos editoriais |
| Hairline | `1px solid {colors.hairline}` | Inputs, cards e divisórias |
| Soft card | Borda leve + superfície `{colors.surface-card}` | Cards, painéis e módulos de conteúdo |
| Modal / overlay | `{colors.scrim}` com opacidade aplicada no CSS | Modais, drawers e estados focados |

A extração automática prioriza bordas, cores de superfície e raios. Sombras complexas devem ser revisadas manualmente quando forem essenciais para a identidade visual.

## Shapes

A linguagem de forma foi derivada dos raios detectados em botões, inputs, cards e superfícies.

| Token | Valor | Uso sugerido |
|---|---:|---|
| `{rounded.none}` | `0px` | Elementos retos |
| `{rounded.xs}` | `4px` | Pequenos controles |
| `{rounded.sm}` | `6px` | Botões compactos |
| `{rounded.md}` | `20px` | Inputs e cards pequenos |
| `{rounded.lg}` | `33px` | Cards |
| `{rounded.xl}` | `99px` | Painéis grandes |
| `{rounded.full}` | `9999px` | Pills e círculos |

- Use a escala de raios documentada no frontmatter antes de criar novos valores.
- Preserve consistência entre botões, cards, inputs e pills.

## Components

Componentes são descritos como contratos reutilizáveis. Ao gerar uma tela, escolha primeiro um componente abaixo e depois aplique variações apenas quando necessário.

### Buttons & actions

#### `button-primary`

CTA principal com maior prioridade visual.

- **Background:** `{colors.primary}`
- **Text color:** `{colors.on-primary}`
- **Typography:** `{typography.button-md}`
- **Radius:** `{rounded.md}`
- **Padding:** `{spacing.sm} {spacing.lg}`
- **Height:** `44px`

#### `button-secondary`

Ação secundária ou alternativa.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.ink}`
- **Typography:** `{typography.button-md}`
- **Radius:** `{rounded.md}`
- **Padding:** `{spacing.sm} {spacing.lg}`
- **Height:** `44px`
- **Border:** `1px solid {colors.hairline}`

#### `text-link`

Link textual para navegação ou ação discreta.

- **Background:** `transparent`
- **Text color:** `{colors.primary}`
- **Typography:** `{typography.link}`

### Forms

#### `text-input`

Campo de formulário padrão.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.ink}`
- **Typography:** `{typography.input}`
- **Radius:** `{rounded.md}`
- **Padding:** `{spacing.sm} {spacing.base}`
- **Height:** `44px`
- **Border:** `1px solid {colors.hairline}`

### Cards & surfaces

#### `surface-card`

Superfície neutra para conteúdo agrupado.

- **Background:** `{colors.surface-card}`
- **Text color:** `{colors.ink}`
- **Typography:** `{typography.body-md}`
- **Radius:** `{rounded.lg}`
- **Padding:** `{spacing.lg}`
- **Border:** `1px solid {colors.hairline}`

#### `feature-card`

Card visual para recurso, produto ou chamada editorial.

- **Background:** `{colors.surface-card}`
- **Text color:** `{colors.ink}`
- **Typography:** `{typography.body-md}`
- **Radius:** `{rounded.lg}`
- **Padding:** `{spacing.lg}`
- **Border:** `1px solid {colors.hairline}`

#### `application-shell`

Container base para aplicações e previews.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.ink}`
- **Typography:** `{typography.body-md}`
- **Radius:** `{rounded.xl}`
- **Padding:** `{spacing.xl}`

#### `card`

Componente detectado automaticamente no site.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.primary}`
- **Typography:** `{typography.body-md}`
- **Radius:** `{rounded.none}`
- **Detected selector:** `.card, [class*="card"], article`

### Navigation & badges

#### `top-nav`

Barra de navegação ou cabeçalho.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.ink}`
- **Typography:** `{typography.link}`
- **Height:** `64px`
- **Border:** `1px solid {colors.hairline}`

#### `badge-pill`

Marcador compacto para status, categoria ou rótulo.

- **Background:** `{colors.surface-soft}`
- **Text color:** `{colors.ink}`
- **Typography:** `{typography.label}`
- **Radius:** `{rounded.full}`
- **Padding:** `{spacing.xs} {spacing.sm}`

#### `badge`

Componente detectado automaticamente no site.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.primary}`
- **Typography:** `{typography.body-md}`
- **Radius:** `{rounded.none}`
- **Detected selector:** `.badge, .tag, [class*="badge"], [class*="tag"]`

### Hero & composition

#### `hero-section`

Composição de abertura de página.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.ink}`
- **Typography:** `{typography.display-lg}`
- **Padding:** `{spacing.section} {spacing.xl}`

### Additional detected components

#### `input-field`

Componente detectado automaticamente no site.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.primary}`
- **Typography:** `{typography.body-md}`
- **Radius:** `{rounded.none}`
- **Detected selector:** `input, textarea, select`

#### `navigation`

Componente detectado automaticamente no site.

- **Background:** `{colors.canvas}`
- **Text color:** `{colors.primary}`
- **Typography:** `{typography.body-md}`
- **Radius:** `{rounded.none}`
- **Detected selector:** `nav, header`

## Do's and Don'ts

### Do

- Use `{colors.primary}` apenas para ações, links importantes e elementos que precisam de atenção visual.
- Use `{colors.canvas}` para o fundo base e `{colors.surface-card}` para cards e painéis.
- Use a escala tipográfica do frontmatter antes de criar novos tamanhos.
- Mantenha contraste AA em textos e controles interativos.
- Preserve os tokens de componentes em `components` para botões, inputs, cards e navegação.

### Don't

- Não invente cores de marca fora do frontmatter.
- Não use cores de erro, alerta ou destrutivas como cor primária quando não forem a identidade do site.
- Não misture raios e espaçamentos que não aparecem na escala documentada.
- Não troque a fonte principal por outra família se a fonte custom não estiver disponível; preserve a pilha de fallback.

## Application Examples

Use estes exemplos para transformar tokens em telas. Cada aplicação combina componentes base e evita que a IA crie layouts sem aderência ao sistema.

### Landing Page

Composição de hero, navegação, CTA e cards usando os tokens principais do arquivo.

**Recommended components:**
- `{components.top-nav}`
- `{components.hero-section}`
- `{components.button-primary}`
- `{components.feature-card}`

**Implementation note:** preserve `{colors.primary}`, `{typography.body-md}`, `{spacing.base}` and the documented radius scale before adding custom values.

### Dashboard Card Grid

Grid de cards para apresentar métricas, recursos ou módulos de produto.

**Recommended components:**
- `{components.surface-card}`
- `{components.badge-pill}`
- `{components.button-secondary}`

**Implementation note:** preserve `{colors.primary}`, `{typography.body-md}`, `{spacing.base}` and the documented radius scale before adding custom values.

### Form Flow

Fluxo simples com input, link de apoio e CTA principal.

**Recommended components:**
- `{components.text-input}`
- `{components.button-primary}`
- `{components.text-link}`

**Implementation note:** preserve `{colors.primary}`, `{typography.body-md}`, `{spacing.base}` and the documented radius scale before adding custom values.

### Content Preview

Página de conteúdo com navegação, card visual e ações rápidas.

**Recommended components:**
- `{components.top-nav}`
- `{components.application-shell}`
- `{components.feature-card}`

**Implementation note:** preserve `{colors.primary}`, `{typography.body-md}`, `{spacing.base}` and the documented radius scale before adding custom values.

## Agent Prompt Guide

Use esta seção como instrução direta para ChatGPT, Claude, v0, Lovable, Bolt, Replit, Figma Make, Stitch e outras ferramentas de IA.

### Quick Token Reference

- **Primary CTA:** `{colors.primary}` (`#FF5A00`)
- **Primary text / headings:** `{colors.ink}` (`#FF5A00`)
- **Body text:** `{colors.body}` (`#FF5A00`)
- **Secondary text:** `{colors.muted}` (`#8A8A8A`)
- **Background:** `{colors.canvas}` (`#090909`)
- **Card surface:** `{colors.surface-card}` (`#090909`)
- **Borders:** `{colors.hairline}` (`#2A2A2A`)
- **Focus / active states:** `{colors.primary-active}` (`#D94800`)

### Generation Rules

1. Use only tokens declared in the YAML frontmatter before creating new visual values.
2. Treat `{colors.primary}` as the main brand/action color. Do not replace it with warning, error, or decorative colors.
3. Use `{colors.canvas}` for page backgrounds and `{colors.surface-card}` for cards, modals, panels, and contained surfaces.
4. Use `{typography.display-*}` for major headings, `{typography.body-md}` for reading content, and `{typography.button-md}` for CTAs.
5. When a custom font is unavailable, keep the full `fontFamily` stack and let the fallback defined in the token resolve the rendering.
6. Use the documented spacing and radius scale. Do not invent arbitrary values unless the target component cannot be represented by the system.
7. Preserve contrast: pair `{colors.primary}` with `{colors.on-primary}`, and use `{colors.ink}` or `{colors.body}` on light surfaces.

### Component Defaults

- **`{components.button-primary}`**: use for ações principais, envio de formulários e CTAs de alta prioridade.
- **`{components.button-secondary}`**: use for ações secundárias e alternativas sem competir com o CTA principal.
- **`{components.text-link}`**: use for componentes de interface compatíveis com os tokens extraídos.
- **`{components.text-input}`**: use for campos de formulário, busca e entrada de dados.
- **`{components.surface-card}`**: use for cards, painéis e blocos de conteúdo.
- **`{components.badge-pill}`**: use for componentes de interface compatíveis com os tokens extraídos.
- **`{components.top-nav}`**: use for navegação principal e cabeçalhos.
- **`{components.hero-section}`**: use for componentes de interface compatíveis com os tokens extraídos.

## feito. Visual Rules

### Core visual language

- Use **Satoshi** em todos os textos da interface.
- Priorize preto profundo (`#090909`) em hero, projetos e blocos de maior impacto.
- Use off-white (`#F4F4EF`) para criar respiro e equilibrar a experiência.
- Use laranja feito. (`#FF5A00`) como detalhe de marca e CTA, evitando saturar a interface.
- Branco (`#FFFFFF`) deve ser usado para texto sobre fundos escuros e CTAs de alto contraste.
- Use bordas finas e discretas para sugerir profundidade sem depender de sombras pesadas.
- O efeito liquid glass deve ser sutil: transparência, blur, highlights e bordas suaves, sem transformar toda a interface em vidro.
- Cards podem usar superfícies translúcidas em preto, com `backdrop-filter: blur(...)` quando houver suporte.
- Evite layouts com aparência de template, excesso de cards idênticos ou elementos decorativos sem função.
- Evite estética de "site de agência": o feito. deve parecer um estúdio/produto digital autoral e próximo.
- Não usar elementos 3D genéricos, imagens artificiais ou ornamentos com aparência de geração por IA.
- CTAs devem ser claros, curtos e orientados à ação: "iniciar um projeto", "ver projetos", "conversar".
- Manter o conceito verbal **"feito junto."** como assinatura da marca.

### Preferred section rhythm

- Hero: preto profundo, composição ampla, headline grande e elemento visual de produto/site.
- Soluções: off-white, cards claros e tipografia escura.
- Projetos: preto profundo, imagens grandes e composição editorial.
- Processo: off-white, linha do tempo ou sequência horizontal limpa.
- Prova social: preto profundo, bastante espaço negativo.
- CTA final: alternar entre preto e off-white para evitar monotonia.
- Footer: preto profundo, compacto e funcional.

## Quality Notes

Este arquivo foi adaptado a partir de um DESIGN.md de referência para representar a identidade visual do feito. Ele deve ser tratado como guia de design e revisado junto ao site real antes de uso em produção.

### O que foi normalizado
- Cores em `rgb()`, `rgba()`, `hsl()` e HEX curto foram convertidas para HEX de 6 dígitos quando possível.
- Cores de erro, alerta e perigo foram rebaixadas para evitar que virem cor primária por engano.
- Tipografia foi consolidada em papéis semânticos: display, body, caption, button, link, input e label.
- Fontes custom ou externas foram preservadas com fallback no próprio token.
- Espaçamentos e raios foram reduzidos para uma escala reutilizável.

### Possíveis limitações
- Sites que dependem de JavaScript pesado podem expor menos CSS para o WordPress do que para um navegador real.
- Fontes proprietárias podem não aparecer na prévia se não estiverem disponíveis no ambiente de destino.
- Tokens detectados em CSS de terceiros podem aparecer como apoio, mas não devem substituir a identidade principal sem revisão.
- Para maior fidelidade visual, uma etapa futura com navegador headless pode capturar estilos computados e screenshots reais.

## Notes

### Fontes analisadas

- HTML principal

