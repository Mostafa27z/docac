---
name: Vibrant Enterprise
colors:
  surface: '#f7fafc'
  surface-dim: '#d7dadc'
  surface-bright: '#f7fafc'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f1f4f6'
  surface-container: '#ebeef0'
  surface-container-high: '#e5e9eb'
  surface-container-highest: '#e0e3e5'
  on-surface: '#181c1e'
  on-surface-variant: '#564334'
  inverse-surface: '#2d3133'
  inverse-on-surface: '#eef1f3'
  outline: '#897362'
  outline-variant: '#ddc1ae'
  surface-tint: '#904d00'
  primary: '#904d00'
  on-primary: '#ffffff'
  primary-container: '#ff8c00'
  on-primary-container: '#623200'
  inverse-primary: '#ffb77d'
  secondary: '#585e6c'
  on-secondary: '#ffffff'
  secondary-container: '#dde2f3'
  on-secondary-container: '#5e6473'
  tertiary: '#555f71'
  on-tertiary: '#ffffff'
  tertiary-container: '#a0aabf'
  on-tertiary-container: '#353f50'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#ffdcc3'
  primary-fixed-dim: '#ffb77d'
  on-primary-fixed: '#2f1500'
  on-primary-fixed-variant: '#6e3900'
  secondary-fixed: '#dde2f3'
  secondary-fixed-dim: '#c1c6d7'
  on-secondary-fixed: '#161c27'
  on-secondary-fixed-variant: '#414754'
  tertiary-fixed: '#d9e3f9'
  tertiary-fixed-dim: '#bdc7dc'
  on-tertiary-fixed: '#121c2c'
  on-tertiary-fixed-variant: '#3d4759'
  background: '#f7fafc'
  on-background: '#181c1e'
  surface-variant: '#e0e3e5'
typography:
  headline-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 30px
    fontWeight: '700'
    lineHeight: 38px
  headline-md:
    fontFamily: Be Vietnam Pro
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  body-lg:
    fontFamily: Be Vietnam Pro
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 26px
  body-md:
    fontFamily: Be Vietnam Pro
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Be Vietnam Pro
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.02em
  label-sm:
    fontFamily: Be Vietnam Pro
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.04em
  headline-lg-mobile:
    fontFamily: Be Vietnam Pro
    fontSize: 24px
    fontWeight: '700'
    lineHeight: 32px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  sidebar_width: 260px
  topbar_height: 72px
  gutter: 24px
  margin-page: 32px
  stack-sm: 8px
  stack-md: 16px
  stack-lg: 24px
---

## Brand & Style
The design system is engineered for high-performance administrative environments within the educational sector. It balances authoritative professionalism with energetic clarity. The brand personality is efficient, modern, and trustworthy.

The visual style follows a **Corporate / Modern** aesthetic with elements of **Minimalism**. It prioritizes data legibility through generous whitespace and a clear hierarchy. The contrast between deep structural backgrounds and vibrant action colors ensures that primary user paths are unmistakable. The interface should feel "utility-first," reducing cognitive load for administrators managing complex data sets.

## Colors
The palette is anchored by a high-energy **Vibrant Orange (#FF8C00)** used exclusively for primary actions, active navigation states, and critical highlights. This color is the "signal" within the interface.

- **Primary:** Orange (#FF8C00). Used for CTA buttons, active sidebar icons, and primary chart data.
- **Secondary (Dark Navy):** Used for the sidebar background and primary navigation text to provide a grounded, high-contrast frame.
- **Surface/Neutral:** The main workspace uses a light gray background (#F7FAFC) to separate the content area from the sidebar, while cards and tables use pure white (#FFFFFF).
- **Status:** Standard semantic colors (Green/Red) are used for "Success" and "Alert" states but should be applied with lower saturation than the primary Orange to maintain the brand's visual hierarchy.

## Typography
The design system utilizes **Be Vietnam Pro** for its excellent legibility and modern, slightly warm sans-serif terminals, which provide a professional yet approachable feel. It offers robust support for multi-language environments and maintains clarity at small sizes in dense data tables.

- **Headlines:** Use Bold (700) or SemiBold (600) to anchor sections.
- **Data Labels:** Use the `label-md` or `label-sm` weights for table headers and form labels to create a distinct visual layer from body text.
- **Numbers:** In stats cards, use `headline-lg` with the primary orange color to emphasize key metrics.

## Layout & Spacing
This design system employs a **Fixed Sidebar + Fluid Content** model. The sidebar remains fixed at 260px to provide a persistent navigation anchor, while the main dashboard area expands to fill the viewport.

- **Grid:** Content should be organized on a 12-column grid within the main fluid container.
- **Spacing Scale:** A strictly linear 8px baseline is used. All margins and paddings must be multiples of 8 (8, 16, 24, 32, 48, 64).
- **Responsive Behavior:** 
  - **Desktop (>1024px):** Full sidebar visible.
  - **Tablet (768px - 1024px):** Sidebar collapses to icons only (64px width) or hides behind a burger menu.
  - **Mobile (<768px):** Sidebar is hidden; Top bar becomes sticky; Margins reduce to 16px.

## Elevation & Depth
The design system uses **Tonal Layers** and **Low-Contrast Outlines** to define hierarchy. In an admin context, heavy shadows are avoided to keep the interface feeling light and fast.

- **Level 0 (Background):** #F7FAFC. The lowest layer.
- **Level 1 (Cards/Tables):** White (#FFFFFF) with a 1px border of #E2E8F0. This is the primary surface for data.
- **Level 2 (Dropdowns/Popovers):** White (#FFFFFF) with a soft, ambient shadow (0px 4px 12px rgba(0,0,0,0.05)) to suggest interaction.
- **Sidebar:** The Dark Navy (#1A202C) acts as a high-contrast vertical slab, visually separating navigation from the workspace.

## Shapes
The design system uses a **Rounded** (0.5rem) corner strategy. This softens the "industrial" feel of data-heavy dashboards, making the tool feel modern and user-friendly.

- **Standard Elements:** 8px (0.5rem) radius for buttons, input fields, and small cards.
- **Large Elements:** 16px (1rem) radius for main dashboard containers and charts.
- **Interactive States:** Subtle transitions on hover (color shifts) are preferred over shape changes.

## Components

### Buttons
- **Primary:** Solid Orange (#FF8C00) with white text. Height: 40px (Medium) / 48px (Large).
- **Secondary:** Ghost style with an Orange border and Orange text. Used for less critical actions like "Export" or "Filter."

### Stats Cards
- Background: White. Border: 1px Gray. 
- Content: Large orange number (Headline-lg) followed by a muted label. Include a small trend indicator (sparkline or percentage) in the corner.

### Tables
- **Header:** Light gray background (#EDF2F7) with uppercase labels.
- **Rows:** White background with subtle hover state (#F7FAFC). 
- **Badges:** Use "Pill-shaped" roundedness. Backgrounds should be 10% opacity of the status color (e.g., light orange background for "Pending" with dark orange text).

### Charts
- Use the primary Orange as the main data series.
- Secondary series should use a neutral blue or teal to avoid clashing with the brand orange.
- Grid lines in charts should be kept at 0.5px thickness and light gray.

### Form Inputs
- 1px border (#E2E8F0). On focus, the border transitions to Primary Orange with a 2px outer ring at 20% opacity.