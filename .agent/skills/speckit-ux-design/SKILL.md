---
name: "speckit-ux-design"
description: "Apply premium visual aesthetics, responsive UX layouts, and consistent frontend/backend design systems across the application."
compatibility: "Applies to both Filament Admin/Member panels (Backend) and public-facing views (Frontend)"
metadata:
  author: "Antigravity-UX"
  version: "1.0.0"
---

# UX/UI & Premium Visual Design System Skill

This skill is designed to guide developers and agents to achieve **visual excellence** and create a **state-of-the-art "Wow" effect** in both the frontend (public pages) and backend (Filament admin & member panels).

---

## 🎨 1. Core Visual Tokens & Aesthetics

To maintain a premium feel, avoid generic colors (pure red, blue, green). Utilize the following cohesive system:

### Harmonious HSL & Slate Palette
- **Primary / Brand**: Sleek Slate & Dark Indigo/Teal accents.
- **Backgrounds**: Slate dark-modes (`#0f172a` / `#1e293b`) paired with semi-transparent card overlays (Glassmorphism).
- **Text Hierarchy**: 
  - Primary text: `slate-900` (Light) or `slate-50` (Dark).
  - Secondary description text: `slate-500` or `slate-400`.
- **Accents**: Elegant gradients using `from-indigo-500 to-purple-600` or `from-teal-400 to-emerald-500`.

### Premium Typography
- Use modern, high-legibility Google Fonts (e.g., **Inter**, **Outfit**, **Cabinet Grotesk**, or **Roboto**) instead of default browser sans-serif.
- Utilize clean font weights: `font-light` (300) for large headers, `font-bold` (700) for labels, and `font-medium` (500) for buttons.

### Layout Geometry
- **Spacings**: Use generous padding/margins. Card elements should have breathing room (e.g., `p-6` or `p-8` spacing).
- **Borders & Corners**: Avoid sharp corners. Use generous borders with large, smooth rounded corners (`rounded-xl` / `rounded-2xl` / `12px` to `16px` border-radius).
- **Shadows**: Use subtle, layered soft shadows (e.g., `shadow-sm` or custom ambient box-shadows rather than hard borders).

---

## ⚡ 2. Interactive Micro-Animations (Making the UI "Alive")

All interactive elements (buttons, links, form inputs, table cards) MUST feel alive and responsive through CSS transitions:

- **Transition Standard**: Always define `transition-all duration-300 ease-in-out` on interactive components.
- **Hover Scale & Lift**: On hover, elevate cards or primary buttons slightly:
  ```css
  transform: translateY(-2px) scale(1.02);
  box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.1);
  ```
- **Focus Ring Glow**: For inputs, utilize a smooth teal or indigo glow transition:
  ```css
  border-color: rgb(99, 102, 241);
  box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
  ```
- **Active State (Click Press)**: Instantly scale down slightly on click (`active:scale-95`) to give tactile haptic feedback to users.

---

## 🏢 3. Backend Customization (Filament Panels)

Filament v3 panels should feel highly premium, customized, and tailored to the platform identity, rather than "just out of the box":

- **Enriched Infolists**:
  - Always use `Section` or `Grid` groupings to categorize fields.
  - Apply custom icons to relevant data fields (e.g. `heroicon-o-envelope` for email, `heroicon-o-phone` for phone numbers).
  - Use custom colors and styling for action links/buttons.
  - Implement dynamic badges for all state/status enums.
- **Custom Panel CSS**:
  - Register custom styles in Filament via a custom theme or using `viteTheme` to inject micro-animations, rounded buttons, and customized scrollbars.
  - Register brand primary and gray colors in the Panel Providers:
    ```php
    $panel->colors([
        'primary' => Color::Indigo,
        'gray' => Color::Slate,
    ]);
    ```

---

## 🌐 4. Frontend Customization (Public Pages)

Public pages must look high-end, incorporating contemporary design techniques:

- **Glassmorphism**: Create semi-transparent overlay cards for hero sections:
  ```css
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.15);
  ```
- **Gradients**: Use elegant subtle mesh gradients for backgrounds instead of flat colors.
- **Responsive Layout**: Design mobile-first with CSS Grid or Flexbox, ensuring zero horizontal overflow and optimal text sizes at all viewports.
- **Loading & Skeleton States**: Build smooth fading skeleton loading elements rather than generic spinning loaders.

---

## 🔍 5. Pre-Completion Aesthetics Checklist

Before completing any task that modifies a visual interface, verify against this checklist:

- [ ] **No Generic Colors**: Have I avoided generic colors in favor of slate/indigo/teal/custom palette?
- [ ] **Typography Consistency**: Do all headers, labels, and text utilize the premium font family and sizes?
- [ ] **Micro-animations**: Are there smooth hover effects, active click actions, and focus ring transitions on all interactive fields?
- [ ] **Polished Spacing**: Is there sufficient breathing room and padding around all sections and forms?
- [ ] **A11y & Contrast**: Is all text highly readable with adequate color contrast against backgrounds?
- [ ] **Responsive Integrity**: Have we tested the UI across mobile, tablet, and desktop viewports to ensure clean alignments?
