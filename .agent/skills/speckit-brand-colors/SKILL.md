---
name: "speckit-brand-colors"
description: "Establish and enforce the official color palette and branding assets (including logo harmony) consistently across all public interfaces and Filament panels."
compatibility: "Applies to Filament Panels, public Blade views, Tailwind configurations, and CSS assets."
metadata:
  author: "Antigravity-Brand"
  version: "1.0.0"
---

# Brand Kit & Color Harmonization Skill (`speckit-brand-colors`)

This skill defines the official branding and color harmonization system for the **Lazos de Fe** (CBC Workplace) platform. It ensures that every user-facing visual element—from Filament backend panels to public-facing frontend templates—remains perfectly aligned with the Crossroads Bible Church identity and the official corporate logo.

---

## 🎨 1. The Official Brand Palette

The palette is derived directly from the **Lazos de Fe / Crossroads Bible Church** logo and corporate guidelines. It blends professional trust with a warm, welcoming community feel:

### A. Primary Color: Faith Blue (`cbc-blue`)
- **Hex/Accents**: `#2563EB` (Standard CBC Blue) / HSL Indigo-600 (`rgb(79, 70, 229)`)
- **Symbolism**: Represents stability, professional trust, community, and deep spiritual faith.
- **Application**: Main call-to-actions, active navigation highlights, primary buttons, and link text.

### B. Secondary Accent: Warm Glory (`cbc-amber`)
- **Hex/Accents**: `#D97706` (CBC Amber) / HSL Gold (`rgb(245, 158, 11)`)
- **Symbolism**: Represents light, warmth, dynamic energy, and welcoming hospitality.
- **Application**: Important badges (e.g. pending items), alert banners, subtle highlights, stars/favorites, and interactive focus states.

### C. Neutral Foundation: Slate & Charcoal (`cbc-charcoal`)
- **Hex/Accents**: `#1F2937` (Charcoal) for main texts / `#0F172A` (Slate-900) to `#020617` (Slate-950) for deep premium dark modes.
- **Application**: Main backgrounds, layout borders, body text, and secondary navigation elements.

---

## 🔧 2. Architectural Branding Rules

To apply this palette in code, follow this structured implementation workflow:

### Step 1: Tailwind Configuration Customization
Ensure brand colors are declared as named extend variables in `tailwind.config.js` to prevent magic Tailwind color usage:
```javascript
theme: {
  extend: {
    colors: {
      brand: {
        blue: '#2563eb',     // Faith Blue
        amber: '#d97706',    // Warm Glory
        charcoal: '#1f2937', // Foundation Charcoal
        darkBg: '#0b0f19',   // Premium Slate-Dark
      }
    }
  }
}
```

### Step 2: Filament Panels Synchronization
All three Filament panels (Admin `/admin`, Member `/member`, and Venture `/app`) MUST register cohesive brand colors inside their respective `PanelProvider` classes:
```php
$panel->colors([
    'primary' => \Filament\Support\Colors\Color::Indigo, // Coordinates with Faith Blue
    'gray' => \Filament\Support\Colors\Color::Slate,     // Coordinates with Slate/Charcoal
    'warning' => \Filament\Support\Colors\Color::Amber,  // Coordinates with Warm Glory
]);
```

### Step 3: Public Portal Integration (Blade views)
Public pages MUST utilize CSS utility variables or brand Tailwind classes to keep colors synchronized:
- Backgrounds: Use a radial gradient starting from `brand-darkBg` down to pure black `#000000`.
- Text links & logos: Use `text-brand-blue` or gradients transitioning from `indigo-400` to `purple-400`.
- Badges & labels: Use `bg-brand-amber/10 text-brand-amber` for highlights and state notifications.

---

## 🔍 3. Color Harmonization Audit Checklist

Before launching any new UI component or modifying an existing view, run this audit checklist:

- [ ] **Logo Contrast**: Does the logo (`images/logo.png`) have sufficient contrast against the header/navigation background?
- [ ] **Accents Allocation**: Are we allocating `Faith Blue` for primary success actions/paths and `Warm Glory` exclusively for warnings/alerts/highlights?
- [ ] **Contrast Ratio Compliance**: Does all body and label text maintain at least a **4.5:1** contrast ratio (WCAG AA standard) against dark slate or light backgrounds?
- [ ] **Cohesive Badge System**: Are enums and statuses using the standardized warning/primary/success mapping rather than arbitrary colors?
- [ ] **No Raw Colors**: Have we completely avoided raw, un-curated primary colors (e.g. raw `#ff0000` or raw `#00ff00`)?
