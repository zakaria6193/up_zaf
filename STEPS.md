# UP1 MVP - Complete Implementation Plan

## Tech Stack Decision

**Backend:**
- Laravel 13 (PHP 8.4)
- MySQL database
- Laravel Fortify for authentication
- Intervention Image for image processing
- SimpleSoftwareIO/simple-qrcode for QR generation
- hidehalo/nanoid-php for nanoid generation

**Frontend:**
- Inertia.js v3 (Vue 3)
- Laravel Wayfinder for typed routes
- TailwindCSS for styling
- Heroicons for icons

**Development:**
- Laravel Herd (already configured)
- Vite for asset bundling
- PHPUnit for testing
- Laravel Pint for code formatting

---

## Step-by-Step Implementation Plan

### Phase 1: Foundation & Setup

#### Step 1: Install Required Dependencies
- Install hidehalo/nanoid-php
- Install simplesoftwareio/simple-qrcode
- Install intervention/image
- Install maatwebsite/excel (for import feature)
- Verify all dependencies

#### Step 2: Database Schema Design
- Create businesses table (id, nanoid, name, address, lat, lng, logo, color, seo_title, seo_description, seo_keywords, timestamps)
- Create business_links table (id, business_id, type, label, url, is_active, order, timestamps)
- Create menu_categories table (id, business_id, name, order, timestamps)
- Create menu_items table (id, category_id, name, description, price, image, order, timestamps)
- Run migrations

#### Step 3: Simple Admin Authentication
- Configure Fortify for basic auth (login only, no registration)
- Create admin user seeder (username: up1, password: 147235689)
- Create admin middleware
- Setup auth routes and login page
- Test authentication flow

### Phase 2: Core Models & Business Logic

#### Step 4: Business Model & Core Logic
- Create Business model with relationships
- Add nanoid generation on creation
- Add QR code generation method
- Add public URL helper methods
- Create Business factory for testing

#### Step 5: Business Links System
- Create BusinessLink model
- Define link types enum (google_reviews, google_maps, menu, instagram, whatsapp, website, other)
- Add relationship to Business
- Create business link factory

#### Step 6: Menu System Models
- Create MenuCategory model with Business relationship
- Create MenuItem model with MenuCategory relationship
- Setup proper ordering logic
- Create factories for both models

### Phase 3: Admin Dashboard

#### Step 7: Admin Dashboard Layout
- Create clean admin layout component
- Setup navigation (Dashboard, Businesses)
- Add logout functionality
- Style with Tailwind (modern, clean design)

#### Step 8: Admin Dashboard Home
- Create dashboard index page
- Show total businesses count
- Show recent businesses
- Add quick action buttons

#### Step 9: Business CRUD - List & View
- Create businesses index page (table with search)
- Show business cards with nanoid, name, address
- Add pagination
- Create business show page with all details

#### Step 10: Business CRUD - Create & Edit
- Create business form component
- Integrate Google Maps autocomplete for address
- Add lat/lng capture from Google Places
- Handle logo upload with validation
- Add color picker for branding
- Add SEO fields (title, description, keywords)
- Implement store and update controllers

#### Step 11: QR Code Generation & Download
- Generate QR code on business creation
- Store QR code in storage/app/public/qrcodes
- Add download QR button in admin
- Show QR preview in business view

### Phase 4: Business Links Management

#### Step 12: Business Links CRUD
- Create links management page per business
- Add/edit/delete links interface
- Toggle active/inactive
- Drag-and-drop ordering (or simple up/down)
- Limit to 4 active links with validation

#### Step 13: Link Type Handling
- Create link type component with icons
- Handle special formatting for WhatsApp, Instagram
- Validate URLs based on type
- Display link preview in admin

### Phase 5: Menu Management

#### Step 14: Menu Categories Management
- Create menu categories list per business
- Add/edit/delete categories
- Simple ordering (up/down arrows or drag)
- Inline editing for quick updates

#### Step 15: Menu Items Management
- Create menu items list per category
- Add/edit/delete items
- Handle optional image upload
- Simple ordering system
- Price formatting

#### Step 16: Excel Import for Menu Items
- Create import template download
- Implement Excel import for menu items
- Validate import data
- Show import results (success/errors)
- Map columns: category, name, description, price

### Phase 6: Public Pages

#### Step 17: Public Business Page - Smart Redirect Logic
- Create public business controller
- Implement smart redirect logic:
  - 0 active links → 404 or placeholder
  - 1 active link → direct redirect
  - 2+ active links → show landing page
- Handle nanoid lookup
- Add view tracking (optional analytics)

#### Step 18: Public Business Landing Page
- Create beautiful landing page component
- Show business logo and name
- Display active links with icons
- Apply business branding color
- Make fully responsive
- Add WhatsApp click-to-chat integration
- Add Google Maps integration for location link

#### Step 19: Public Menu Page
- Create menu page route and component
- Display categories and items beautifully
- Show item images when available
- Show prices formatted in MAD
- Apply business branding
- Mobile-first design
- Add share button

#### Step 20: SEO & Meta Tags
- Add meta tags to public pages
- Use business SEO fields
- Add Open Graph tags
- Add Twitter Card tags
- Create dynamic sitemap
- Add robots.txt

### Phase 7: Polish & Production

#### Step 21: Image Upload & Optimization
- Implement image upload with validation
- Create image optimization pipeline
- Handle different sizes (logo, menu items)
- Store in organized structure
- Add image deletion on update

#### Step 22: Validation & Error Handling
- Add comprehensive form validation
- Create custom validation rules where needed
- Add user-friendly error messages
- Handle 404s gracefully
- Add error logging

#### Step 23: Admin UI Polish
- Refine all admin pages design
- Add loading states
- Add success/error notifications
- Improve form UX
- Add helpful tooltips
- Ensure consistent spacing and colors

#### Step 24: Public Pages Polish
- Refine public landing page design
- Add animations and transitions
- Optimize mobile experience
- Add favicon generation per business (optional)
- Test all link types

#### Step 25: Seed Demo Data
- Create comprehensive seeder
- Add 3-5 demo businesses
- Add various link types
- Add complete menu for one business
- Seed admin user

#### Step 26: Testing Suite
- Write feature tests for business CRUD
- Test authentication flow
- Test public page logic
- Test menu CRUD
- Test links CRUD
- Test Excel import
- Test QR generation
- Run full test suite

#### Step 27: Code Quality & Formatting
- Run Laravel Pint on all files
- Review code for best practices
- Add PHPDoc blocks where needed
- Check for N+1 queries
- Optimize database indexes

#### Step 28: Production Readiness
- Configure environment variables for production
- Set up proper file storage (public disk)
- Add database indexes
- Configure rate limiting
- Set up proper logging
- Add maintenance mode handling
- Configure CORS if needed
- Optimize autoloader

#### Step 29: Documentation
- Document admin usage
- Document public URL structure
- Document API endpoints (if any)
- Add inline code comments for complex logic
- Create simple README for deployment

#### Step 30: Final Testing & Launch Checklist
- Test full admin flow end-to-end
- Test all public page scenarios
- Test on multiple devices/browsers
- Verify QR codes work
- Verify Google Maps integration
- Test Excel import with real data
- Check performance and load times
- Verify SEO tags are working
- Test error scenarios
- Deploy to staging/production

---

## Implementation Notes

**Order of Execution:**
- Follow steps sequentially
- Complete and test each step before moving to next
- Run tests after backend changes
- Run Pint after PHP code changes
- Build frontend after component changes

**Key Decisions:**
- Use nanoid (8 chars) for public URLs: `up1.test/ABC12xyz`
- Store QR codes as PNG files
- Use single admin user for MVP (can extend later)
- Use eager loading to prevent N+1 queries
- Use Inertia's form helper for better UX
- Apply business color using CSS variables on public pages

**Constraints:**
- Maximum 4 active links per business
- Logo max 2MB, JPG/PNG only
- Menu item images max 1MB
- Excel import max 500 rows per batch
- Public business pages are fully public (no auth)

**Future Extensions (Not in MVP):**
- Multi-admin support
- Business owner portal
- Analytics dashboard
- Online ordering
- Table reservations
- Payment integration
- Customer reviews
- Email notifications
- SMS notifications
- Multi-language support
