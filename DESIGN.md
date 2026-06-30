---
name: Retail Flow
colors:
  surface: '#f8fafb'
  surface-dim: '#d8dadb'
  surface-bright: '#f8fafb'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f2f4f5'
  surface-container: '#eceeef'
  surface-container-high: '#e6e8e9'
  surface-container-highest: '#e1e3e4'
  on-surface: '#191c1d'
  on-surface-variant: '#40484f'
  inverse-surface: '#2e3132'
  inverse-on-surface: '#eff1f2'
  outline: '#707880'
  outline-variant: '#bfc7d0'
  surface-tint: '#006491'
  primary: '#005c86'
  on-primary: '#ffffff'
  primary-container: '#0e76a8'
  on-primary-container: '#ebf5ff'
  inverse-primary: '#8aceff'
  secondary: '#48626e'
  on-secondary: '#ffffff'
  secondary-container: '#cbe7f5'
  on-secondary-container: '#4e6874'
  tertiary: '#7e4b00'
  on-tertiary: '#ffffff'
  tertiary-container: '#9f6109'
  on-tertiary-container: '#fff1e7'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#c9e6ff'
  primary-fixed-dim: '#8aceff'
  on-primary-fixed: '#001e2f'
  on-primary-fixed-variant: '#004c6e'
  secondary-fixed: '#cbe7f5'
  secondary-fixed-dim: '#afcbd8'
  on-secondary-fixed: '#021f29'
  on-secondary-fixed-variant: '#304a55'
  tertiary-fixed: '#ffdcbc'
  tertiary-fixed-dim: '#ffb86b'
  on-tertiary-fixed: '#2c1700'
  on-tertiary-fixed-variant: '#683d00'
  background: '#f8fafb'
  on-background: '#191c1d'
  surface-variant: '#e1e3e4'
  status-safe: '#2E7D32'
  status-warning: '#EF6C00'
  status-danger: '#C62828'
  surface-white: '#FFFFFF'
  border-subtle: '#E0E4E6'
typography:
  display-lg:
    fontFamily: Comfortaa
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Comfortaa
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  headline-lg-mobile:
    fontFamily: Comfortaa
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
  title-md:
    fontFamily: Comfortaa
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
  body-lg:
    fontFamily: Comfortaa
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Comfortaa
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-lg:
    fontFamily: Comfortaa
    fontSize: 12px
    fontWeight: '700'
    lineHeight: 16px
    letterSpacing: 0.05em
  label-md:
    fontFamily: Comfortaa
    fontSize: 11px
    fontWeight: '500'
    lineHeight: 16px
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  base: 8px
  margin-mobile: 16px
  margin-desktop: 32px
  gutter: 16px
  stack-sm: 4px
  stack-md: 12px
  stack-lg: 24px
---

## Brand & Style

The design system is engineered for efficiency and clarity in fast-paced retail environments. The brand personality is professional yet approachable, prioritizing legibility and rapid information processing for workers on the floor. 

The design style follows a **Modern Corporate** aesthetic with subtle **Soft-Minimalist** influences. It utilizes generous whitespace and high-contrast surfaces to ensure that status indicators are immediately recognizable under various lighting conditions (stockrooms, bright aisles). The UI avoids unnecessary decorative elements, focusing instead on utility, reliability, and a friendly "helper" persona that reduces the cognitive load of inventory management.

## Colors

The palette is anchored by a professional "Retail Blue" that conveys trust and stability. The background uses a very light grey to reduce screen glare during long shifts, while pure white is reserved for high-priority interactive cards and containers.

Status colors are the most critical functional element of the design system. They are saturated and distinct to ensure clear differentiation between "Safe," "Near Expired," and "Expired" goods. These colors should be used for status badges, progress bars, and high-priority alerts. Neutral tones are kept cool-leaning to maintain a clean, clinical feel.

## Typography

The design system exclusively uses **Comfortaa** to maintain a consistent, modern, and friendly tone. Given its rounded nature, heavier weights are used for headlines and status labels to ensure they "pop" against the UI. 

For data-heavy retail lists, `body-sm` is the workhorse for item descriptions. `label-lg` is formatted in uppercase for secondary data like SKUs or Barcode numbers to provide a clear visual distinction from product names. Line heights are slightly increased to improve readability on handheld mobile devices.

## Layout & Spacing

This design system uses a **Fluid Grid** approach centered on an 8px baseline. On mobile devices, the layout uses a single-column stack with 16px side margins. On tablets and desktops, it transitions to a 12-column grid to accommodate wider data tables and inventory dashboards.

Vertical rhythm is critical for scanning lists. We utilize a "stack" system where related information (e.g., product name and barcode) is separated by `stack-sm`, while unrelated components are separated by `stack-lg`. Touch targets for all interactive elements (buttons, list items) must maintain a minimum height of 48px to accommodate glove-wearing or fast-moving workers.

## Elevation & Depth

Visual hierarchy is achieved through a combination of **Tonal Layering** and **Soft Ambient Shadows**. 

1.  **Level 0 (Background):** Neutral light grey `#F5F7F8`.
2.  **Level 1 (Cards/Surface):** Pure white `#FFFFFF` with a very soft, diffused shadow (4px blur, 2% opacity) and a subtle `#E0E4E6` border. This is the primary container for inventory items.
3.  **Level 2 (Active Elements/Modals):** Floating Action Buttons (FABs) and active modals use a more pronounced shadow (12px blur, 8% opacity) to signify they are on top of the stack and require immediate interaction.

Avoid heavy dark shadows; the depth should feel airy and clean.

## Shapes

The shape language is consistently **Rounded**. This mirrors the geometry of the Comfortaa typeface and softens the industrial nature of retail data. 

Standard components like cards and input fields use a 0.5rem radius. Status "chips" or badges should use the `rounded-xl` or full pill-shape setting to distinguish them from actionable buttons. The Floating Action Button (FAB) is a perfect circle to maintain its status as the primary action for adding new stock.

## Components

### Summary Cards
Large cards located at the top of the dashboard. They use high-contrast status colors for the numeric values (e.g., "12 Expired" in `status-danger`) to give an immediate overview of stock health.

### Data Tables & List Items
For retail efficiency, list items include a small (48px) square thumbnail for product images on the left, followed by the product name and barcode. The right side is reserved for the expiration date and its corresponding status indicator.

### Floating Action Button (FAB)
The FAB is the primary driver for stock entry. It is styled in `primary_color_hex` with a white plus icon. It should always sit in the bottom-right corner, 24px from the edges.

### Status Indicators
Use pill-shaped chips with a low-opacity background of the status color and high-opacity text (e.g., a light red background with dark red text for "Expired"). This ensures the status is visible without overpowering the layout.

### Bottom Navigation
A clean, white bar with a subtle top border. Icons should be simple line-art style. The active state uses the `primary_color_hex` for both the icon and a small label below it.

### Input Fields
Outlined style using `border-subtle`. On focus, the border transitions to `primary_color_hex` with a 2px thickness. Labels are always visible above the field in `label-md` to ensure clarity during data entry.