# Design System Documentation

This document describes the complete design system, layout, colors, typography, and component styles used in the UP1 application. Use this as a reference to recreate the same visual design in any Laravel/PHP project.

---

## 1. Color Palette

### Primary Colors (Light Mode)
```css
--background: hsl(0 0% 100%)           /* Pure white background */
--foreground: hsl(224 15% 25%)         /* Dark blue-gray text (#353d50) */
--primary: hsl(237 65% 58%)            /* Main brand color - Purple-blue (#5d66eb) */
--primary-foreground: hsl(0 0% 100%)   /* White text on primary */
```

### Secondary & Muted Colors
```css
--secondary: hsl(220 14.3% 96%)        /* Light gray background (#f4f5f7) */
--secondary-foreground: hsl(224 15% 25%)
--muted: hsl(220 14.3% 96%)            /* Same as secondary */
--muted-foreground: hsl(220 8.9% 50%)  /* Medium gray text (#777c8a) */
--accent: hsl(237 65% 58%)             /* Same as primary for hover states */
--accent-foreground: hsl(0 0% 100%)
```

### Status Colors
```css
--destructive: hsl(0 84.2% 60.2%)      /* Red for errors/delete (#e84855) */
--destructive-foreground: hsl(0 0% 98%)
```

### Border & Input
```css
--border: hsl(220 13% 91%)             /* Light gray borders (#e3e5e8) */
--input: hsl(220 13% 91%)              /* Same as border */
--ring: hsl(237 65% 58%)               /* Focus ring - same as primary */
```

### Sidebar Specific
```css
--sidebar-background: hsl(0 0% 100%)   /* White sidebar */
--sidebar-foreground: hsl(224 15% 30%) /* Dark text in sidebar */
--sidebar-primary: hsl(237 65% 58%)    /* Active item - purple-blue */
--sidebar-primary-foreground: hsl(0 0% 100%)
--sidebar-accent: hsl(237 83% 97%)     /* Light purple background (#f3f3fe) */
--sidebar-accent-foreground: hsl(237 65% 58%)
--sidebar-border: hsl(220 13% 91%)     /* Light gray borders */
--sidebar-ring: hsl(237 65% 58%)
```

### Dark Mode Colors
```css
--background: hsl(0 0% 3.9%)           /* Almost black (#0a0a0a) */
--foreground: hsl(0 0% 98%)            /* Almost white (#fafafa) */
--primary: hsl(0 0% 98%)               /* Inverted - white becomes primary */
--primary-foreground: hsl(0 0% 9%)
--border: hsl(0 0% 14.9%)              /* Dark gray borders (#262626) */
--input: hsl(0 0% 14.9%)
--sidebar-background: hsl(0 0% 7%)     /* Very dark gray (#121212) */
--sidebar: hsl(240 5.9% 10%)           /* Slightly lighter dark (#18181b) */
```

### Chart Colors
```css
/* Light mode charts */
--chart-1: hsl(237 65% 58%)  /* Purple-blue */
--chart-2: hsl(221 83% 53%)  /* Blue */
--chart-3: hsl(210 100% 50%) /* Light blue */
--chart-4: hsl(252 100% 62%) /* Purple */
--chart-5: hsl(266 100% 66%) /* Violet */
```

---

## 2. Typography

### Font Family
```css
font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif,
             'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol',
             'Noto Color Emoji';
```

**Note**: Import "Instrument Sans" from Google Fonts or use system sans-serif as fallback.

### Font Sizes & Weights

#### Headings
- **Page Title (h1)**: `text-2xl font-semibold` (1.5rem / 24px, font-weight: 600)
- **Card Title**: `text-lg font-semibold` (1.125rem / 18px, font-weight: 600)
- **Section Title**: `text-base font-semibold` (1rem / 16px, font-weight: 600)

#### Body Text
- **Desktop**: `text-sm` (0.875rem / 14px)
- **Mobile**: `text-base` (1rem / 16px) - uses `md:text-sm` responsive
- **Muted/Description**: `text-sm text-muted-foreground`
- **Extra Small**: `text-xs` (0.75rem / 12px) - for hints, badges

#### Button Text
- Default: `text-sm font-medium` (0.875rem, font-weight: 500)

---

## 3. Spacing & Layout

### Border Radius
```css
--radius: 0.5rem        /* 8px - base radius */
--radius-lg: 0.5rem     /* 8px - large radius (same as base) */
--radius-md: 0.375rem   /* 6px - medium radius (base - 2px) */
--radius-sm: 0.25rem    /* 4px - small radius (base - 4px) */
```

**Component-specific radius:**
- Cards: `rounded-xl` (0.75rem / 12px)
- Buttons: `rounded-md` (0.375rem / 6px)
- Inputs: `rounded-md` (0.375rem / 6px)
- Badges: `rounded-full` (fully rounded)

### Shadows
- **Card Shadow**: `shadow-sm` - Subtle shadow for cards
- **Popover/Dropdown**: `shadow-md` - Medium shadow for floating elements
- **Input Shadow**: `shadow-xs` - Very subtle shadow

### Padding & Gap
- **Card**: `py-6` (1.5rem / 24px vertical padding)
- **Card Internal Gap**: `gap-6` (1.5rem / 24px between card elements)
- **Button Padding**: `px-4 py-2` (h-9 / 36px height)
- **Input Padding**: `px-3 py-1` (h-9 / 36px height)

---

## 4. Component Styles

### Buttons

#### Base Button Classes
```css
/* Base button */
inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md
text-sm font-medium transition-all disabled:pointer-events-none
disabled:opacity-50 outline-none h-9 px-4 py-2
```

#### Button Variants

**Default (Primary)**
```css
bg-primary text-primary-foreground hover:bg-primary/90
focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]
```
- Background: Purple-blue (#5d66eb)
- Hover: 90% opacity
- Focus: 3px ring with 50% opacity

**Destructive**
```css
bg-destructive text-white hover:bg-destructive/90
```
- Background: Red (#e84855)
- Text: White
- Hover: 90% opacity

**Outline**
```css
border bg-background shadow-xs hover:bg-accent hover:text-accent-foreground
```
- Border: Light gray
- Background: White/transparent
- Hover: Accent background

**Ghost**
```css
hover:bg-accent hover:text-accent-foreground
```
- No background by default
- Hover: Light accent background

**Secondary**
```css
bg-secondary text-secondary-foreground hover:bg-secondary/80
```
- Background: Light gray (#f4f5f7)
- Hover: 80% opacity

#### Button Sizes
- **Default**: `h-9` (36px height)
- **Small**: `h-8` (32px)
- **Large**: `h-10` (40px)
- **Icon**: `size-9` (36x36px square)
- **Icon Small**: `size-8` (32x32px)
- **Icon Large**: `size-10` (40x40px)

#### Icon Buttons
- Icons are automatically sized to `size-4` (16x16px) inside buttons
- Icon-only buttons use `size-9` for the button itself

---

### Inputs

#### Base Input Classes
```css
h-9 w-full rounded-md border border-input bg-transparent px-3 py-1
text-base shadow-xs transition-[color,box-shadow] outline-none
placeholder:text-muted-foreground
md:text-sm
```

#### Focus State
```css
focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]
```
- Border changes to primary color
- 3px ring appears with 50% opacity

#### Error State
```css
aria-invalid:ring-destructive/20 aria-invalid:border-destructive
```
- Border becomes red
- 20% red ring appears

#### Dark Mode
```css
dark:bg-input/30
```
- Semi-transparent background in dark mode

---

### Cards

#### Base Card
```css
bg-card text-card-foreground flex flex-col gap-6 rounded-xl border py-6 shadow-sm
```
- Border radius: 12px
- Vertical padding: 24px
- Gap between children: 24px
- Subtle shadow

#### Card Structure
```html
<Card>
  <CardHeader>
    <CardTitle>Title Here</CardTitle>
    <CardDescription>Optional description</CardDescription>
  </CardHeader>
  <CardContent>
    <!-- Main content -->
  </CardContent>
  <CardFooter>
    <!-- Optional footer with actions -->
  </CardFooter>
</Card>
```

#### Card Header
```css
flex flex-col gap-1.5 px-6
```
- Horizontal padding: 24px
- Gap between title and description: 6px

#### Card Content
```css
px-6
```
- Horizontal padding: 24px

#### Card Title
```css
text-lg font-semibold leading-none tracking-tight
```
- Font size: 18px
- Font weight: 600

#### Card Description
```css
text-sm text-muted-foreground
```
- Font size: 14px
- Color: Medium gray

---

### Badges

#### Base Badge
```css
inline-flex items-center justify-center rounded-full border px-2 py-0.5
text-xs font-medium w-fit whitespace-nowrap transition-[color,box-shadow]
```
- Fully rounded (pill shape)
- Font size: 12px
- Font weight: 500
- Horizontal padding: 8px
- Vertical padding: 2px

#### Badge Variants

**Default**
```css
border-transparent bg-primary text-primary-foreground
```
- Purple-blue background
- White text

**Secondary**
```css
border-transparent bg-secondary text-secondary-foreground
```
- Light gray background
- Dark text

**Destructive**
```css
border-transparent bg-destructive text-white
```
- Red background
- White text

**Outline**
```css
border text-foreground
```
- Transparent background
- Visible border
- Foreground text color

---

### Tables

Tables are typically implemented with custom styling inside cards:

```css
/* Table container */
overflow-x-auto rounded-lg border

/* Table */
w-full border-collapse text-sm

/* Table header */
bg-muted/50 text-left font-medium text-muted-foreground

/* Table header cell */
px-4 py-3 font-medium

/* Table body row */
border-t transition-colors hover:bg-muted/50

/* Table data cell */
px-4 py-3
```

---

### Modals/Dialogs

#### Dialog Overlay
```css
fixed inset-0 z-50 bg-black/80
```
- Semi-transparent black overlay (80% opacity)

#### Dialog Content
```css
fixed left-1/2 top-1/2 z-50 -translate-x-1/2 -translate-y-1/2
grid w-full max-w-lg gap-4 border bg-background p-6 shadow-lg
rounded-xl sm:rounded-xl
```
- Centered on screen
- Max width: 512px
- Padding: 24px
- Border radius: 12px

#### Dialog Header
```css
flex flex-col gap-1.5 text-center sm:text-left
```

#### Dialog Title
```css
text-lg font-semibold leading-none tracking-tight
```

#### Dialog Description
```css
text-sm text-muted-foreground
```

---

## 5. Layout Structure

### Overall Layout: Sidebar + Content

```
┌─────────────────────────────────────────┐
│ ┌─────────┬───────────────────────────┐ │
│ │         │ Header (Breadcrumbs)      │ │
│ │         ├───────────────────────────┤ │
│ │ Sidebar │                           │ │
│ │         │                           │ │
│ │         │   Main Content Area       │ │
│ │         │   (Pages go here)         │ │
│ │         │                           │ │
│ │ (Fixed) │                           │ │
│ │         │                           │ │
│ │         │                           │ │
│ │ User    │                           │ │
│ └─────────┴───────────────────────────┘ │
└─────────────────────────────────────────┘
```

### Sidebar Layout

**Width**:
- Expanded: `16rem` (256px)
- Collapsed: `3.5rem` (56px)
- Collapsible with icon mode

**Structure**:
```
┌─────────────────┐
│  Logo (Top)     │
├─────────────────┤
│                 │
│  Navigation     │
│  Items          │
│  (Scrollable)   │
│                 │
├─────────────────┤
│  User Profile   │
│  (Bottom Fixed) │
└─────────────────┘
```

**Sidebar Background**: White (light mode) / `hsl(0 0% 7%)` (dark mode)

### Navigation Items

#### Active State
```css
bg-sidebar-accent text-sidebar-accent-foreground
```
- Light purple background (#f3f3fe)
- Purple-blue text

#### Hover State (Inactive)
```css
hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground
```
- 50% opacity of accent color

#### Navigation Item Layout
```css
flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium
transition-colors
```
- Icon + Text layout
- 12px gap between icon and text
- 12px horizontal padding
- 8px vertical padding
- 6px border radius

#### Icon Size in Nav
```css
size-4 (16x16px)
```

### Header/Breadcrumb Area

**Layout**: Sticky top header above content
**Padding**: `p-4` (16px all around)
**Background**: Transparent or matches content background

**Breadcrumb Styling**:
```css
text-sm text-muted-foreground
/* Active/last item */
text-foreground font-medium
```

### Main Content Area

**Padding**: `p-4` (16px all around)
**Background**: `bg-background`
**Max Width**: Full width, constrained by natural content

#### Typical Page Structure
```html
<div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
  <!-- Header section -->
  <div class="flex items-center justify-between">
    <div>
      <h1 class="text-2xl font-semibold">Page Title</h1>
      <p class="text-sm text-muted-foreground">Page description</p>
    </div>
    <button>Action Button</button>
  </div>

  <!-- Content sections -->
  <div class="space-y-4">
    <Card>...</Card>
    <Card>...</Card>
  </div>
</div>
```

---

## 6. Responsive Design

### Breakpoints (Tailwind defaults)
- **sm**: 640px
- **md**: 768px
- **lg**: 1024px
- **xl**: 1280px
- **2xl**: 1536px

### Common Responsive Patterns

**Typography**:
```css
text-base md:text-sm  /* 16px mobile, 14px desktop */
```

**Sidebar**:
```css
/* Mobile: Overlay sidebar */
/* Desktop: Fixed sidebar */
```

**Grid Layouts**:
```css
grid gap-4 md:grid-cols-2 lg:grid-cols-3
/* 1 column mobile, 2 tablet, 3 desktop */
```

**Spacing**:
```css
gap-2 md:gap-4  /* Smaller gaps on mobile */
```

---

## 7. List/Table Pages Pattern

### Business List Example Structure

```html
<div class="space-y-3">
  <div class="group rounded-lg border bg-card p-4 hover:shadow-md transition-all">
    <div class="flex items-center gap-4">
      <!-- Logo/Image -->
      <div class="h-16 w-16 shrink-0 rounded-lg overflow-hidden">
        <img src="..." class="h-full w-full object-cover" />
      </div>

      <!-- Info -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <h3 class="font-semibold text-lg truncate">Title</h3>
          <badge>Status</badge>
        </div>
        <p class="text-sm text-muted-foreground">Description</p>
      </div>

      <!-- Stats (Desktop only) -->
      <div class="hidden lg:flex items-center gap-6 shrink-0">
        <!-- Statistics -->
      </div>

      <!-- Actions -->
      <div class="flex items-center gap-1 shrink-0">
        <button>Icon</button>
        <button>Icon</button>
      </div>
    </div>
  </div>
</div>
```

### Empty State Pattern

```html
<div class="flex min-h-[400px] flex-col items-center justify-center
            rounded-lg border border-dashed p-8 text-center">
  <svg class="mb-4 h-12 w-12 text-muted-foreground">...</svg>
  <h3 class="mb-2 text-lg font-semibold">No items yet</h3>
  <p class="mb-4 text-sm text-muted-foreground">
    Get started by creating your first item
  </p>
  <button>Create Item</button>
</div>
```

---

## 8. Form Pages Pattern

### Form Structure (Edit/Create Pages)

```html
<form class="space-y-6">
  <Card>
    <CardHeader>
      <CardTitle>Section Title</CardTitle>
      <CardDescription>Section description</CardDescription>
    </CardHeader>
    <CardContent class="space-y-4">
      <!-- Form fields -->
      <div class="space-y-2">
        <label class="text-sm font-medium">Field Label</label>
        <input class="..." />
        <p class="text-xs text-muted-foreground">Help text</p>
      </div>
    </CardContent>
  </Card>

  <div class="flex gap-3">
    <button type="submit">Save Changes</button>
    <button type="button" variant="outline">Cancel</button>
  </div>
</form>
```

### Label Styling
```css
text-sm font-medium leading-none
```

### Field Spacing
- Use `space-y-2` (8px) between label and input
- Use `space-y-4` (16px) between form fields
- Use `space-y-6` (24px) between form sections

---

## 9. Tabs Pattern

### Tab Component Structure

```html
<div class="w-full">
  <!-- Tab List -->
  <div class="grid w-full grid-cols-4 rounded-md bg-muted p-1">
    <button class="rounded-md px-3 py-1.5 text-sm font-medium
                   data-[state=active]:bg-background
                   data-[state=active]:shadow-sm">
      Tab 1
    </button>
    <!-- More tabs -->
  </div>

  <!-- Tab Content -->
  <div class="mt-6">
    <div data-state="active">Content 1</div>
    <div data-state="inactive">Content 2</div>
  </div>
</div>
```

### Tab Styling
- **Tab List Background**: `bg-muted` (light gray)
- **Active Tab**: White background with subtle shadow
- **Tab Padding**: `px-3 py-1.5`
- **Tab Font**: `text-sm font-medium`
- **Content Margin**: `mt-6` (24px gap from tabs)

---

## 10. Special UI Patterns

### Search Input with Icon

```html
<div class="relative max-w-sm">
  <input type="search" placeholder="Search..." class="..." />
  <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4
              text-muted-foreground">...</svg>
</div>
```

### Logo Upload Preview

```html
<div class="relative inline-block">
  <img src="preview" class="h-24 w-24 rounded-lg border object-cover" />
  <button class="absolute -right-2 -top-2 h-6 w-6 rounded-full
                 bg-destructive text-white">
    <X class="h-3 w-3" />
  </button>
</div>
```

### Progress Steps

```html
<div class="flex gap-2">
  <div class="h-2 flex-1 rounded-full bg-primary"></div>
  <div class="h-2 flex-1 rounded-full bg-muted"></div>
  <div class="h-2 flex-1 rounded-full bg-muted"></div>
</div>
```

### Stat Display with Chart

```html
<div class="text-right">
  <div class="flex items-baseline gap-1 justify-end">
    <span class="text-xl font-bold">{{ value }}</span>
    <span class="text-xs text-muted-foreground">{{ unit }}</span>
  </div>
  <div class="text-xs font-medium text-green-600">
    +{{ percentage }}%
  </div>
</div>
```

### Mini Chart (Sparkline)

```html
<div class="flex items-end gap-0.5 h-10">
  <div class="w-1 rounded-t bg-primary/70" style="height: 60%"></div>
  <div class="w-1 rounded-t bg-primary/70" style="height: 80%"></div>
  <div class="w-1 rounded-t bg-primary/70" style="height: 40%"></div>
  <!-- More bars -->
</div>
```

---

## 11. User Account Dropdown (Footer)

### Location
Bottom of sidebar (fixed position)

### Structure
```html
<div class="p-2">
  <button class="flex items-center gap-2 rounded-md px-2 py-1.5
                 w-full hover:bg-sidebar-accent transition-colors">
    <!-- Avatar -->
    <div class="h-8 w-8 rounded-full bg-primary text-primary-foreground
                flex items-center justify-center text-sm font-medium">
      {{ initials }}
    </div>

    <!-- User Info -->
    <div class="flex-1 text-left min-w-0">
      <p class="text-sm font-medium truncate">{{ name }}</p>
      <p class="text-xs text-muted-foreground truncate">{{ email }}</p>
    </div>

    <!-- Chevron -->
    <svg class="h-4 w-4 text-muted-foreground">...</svg>
  </button>
</div>
```

### Dropdown Menu (when opened)
```css
/* Dropdown container */
rounded-md border bg-popover shadow-md
min-w-[200px]

/* Menu item */
flex items-center gap-2 px-3 py-2 text-sm cursor-pointer
hover:bg-accent rounded-sm transition-colors
```

---

## 12. Authentication Pages

### Login Page Layout

**Split Layout** (Image + Form):
```
┌──────────────┬──────────────┐
│              │              │
│   Image/     │   Form       │
│   Branding   │   Card       │
│   (Left 50%) │ (Right 50%)  │
│              │              │
└──────────────┴──────────────┘
```

### Form Card Styling
```css
max-w-md mx-auto p-8 rounded-xl border bg-card shadow-sm
```

### Form Elements
- Logo at top
- Title: `text-2xl font-semibold`
- Description: `text-sm text-muted-foreground`
- Form fields with `space-y-4`
- Full-width submit button
- Footer links: `text-sm text-muted-foreground`

---

## 13. Transitions & Animations

### Standard Transitions
```css
transition-all        /* For buttons, cards with multiple properties */
transition-colors     /* For hover states with only color changes */
transition-[color,box-shadow]  /* For inputs with focus states */
```

### Hover Effects
- **Cards**: `hover:shadow-md hover:border-sidebar-border`
- **Buttons**: Opacity change or background color shift
- **Links**: `hover:text-primary`

### Focus States
All interactive elements should have visible focus states:
```css
focus-visible:border-ring
focus-visible:ring-ring/50
focus-visible:ring-[3px]
focus-visible:outline-none
```

---

## 14. Icon System

### Icon Library
**Lucide Icons** (or Heroicons as alternative)

### Icon Sizes
- **Nav Icons**: `size-4` (16px)
- **Button Icons**: `size-4` (16px)
- **Large Icons**: `size-6` (24px) for headers
- **Extra Large**: `size-12` (48px) for empty states

### Icon Colors
- **Default**: Inherits text color
- **Muted**: `text-muted-foreground`
- **Destructive**: `text-destructive`

---

## 15. Utility Classes Reference

### Common Spacing
- `gap-1`: 4px
- `gap-2`: 8px
- `gap-3`: 12px
- `gap-4`: 16px
- `gap-6`: 24px

### Common Padding
- `p-2`: 8px
- `p-3`: 12px
- `p-4`: 16px
- `p-6`: 24px

### Flex Utilities
```css
flex items-center justify-between    /* Horizontal bar with space-between */
flex flex-col gap-4                  /* Vertical stack with 16px gap */
flex-1 min-w-0                       /* Flexbox truncation fix */
shrink-0                             /* Prevent shrinking */
```

### Truncation
```css
truncate           /* Single line truncate */
line-clamp-2       /* Multi-line truncate (2 lines) */
min-w-0            /* Required for truncate in flex */
```

---

## 16. Implementation Notes for Non-Vue Projects

### How to Recreate This Design in Laravel Blade/PHP

1. **Use Tailwind CSS v4**: Install Tailwind CSS and configure with the exact color values above

2. **Create Blade Components**:
   - `components/ui/button.blade.php`
   - `components/ui/card.blade.php`
   - `components/ui/input.blade.php`
   - `components/ui/badge.blade.php`
   etc.

3. **Import Instrument Sans Font**:
   ```html
   <link rel="preconnect" href="https://fonts.googleapis.com">
   <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
   ```

4. **Create CSS Variables**: Add the `:root` color definitions to your main CSS file

5. **Use PHP for Dynamic Classes**:
   ```php
   @php
   $buttonClasses = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-all h-9 px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90';
   @endphp
   <button class="{{ $buttonClasses }}">Click Me</button>
   ```

6. **Lucide Icons**: Use Blade Icons package or include Lucide via CDN/NPM

7. **Dark Mode**: Add class-based dark mode switcher using Alpine.js or vanilla JS

8. **Sidebar**: Create a fixed sidebar layout with Alpine.js for collapse/expand

---

## 17. Key Design Principles

1. **Consistent Spacing**: Always use multiples of 4px (Tailwind's spacing scale)
2. **Subtle Shadows**: Avoid heavy shadows, prefer `shadow-sm` and `shadow-md`
3. **Rounded Corners**: Cards use `rounded-xl`, buttons/inputs use `rounded-md`
4. **Color Restraint**: Primary purple-blue is the only accent color in most cases
5. **Typography Hierarchy**: Clear distinction between headings and body text
6. **Hover States**: All interactive elements should have visible hover feedback
7. **Focus Rings**: Accessible focus states with 3px rings at 50% opacity
8. **Mobile-First**: Design scales down to mobile gracefully
9. **Status Colors**: Use destructive (red) only for errors/deletions
10. **Whitespace**: Generous spacing between elements for breathing room

---

This design system provides a clean, modern, professional interface suitable for business/SaaS applications. The color scheme is calming with the purple-blue primary color, and the overall aesthetic is minimal and focused on content.
