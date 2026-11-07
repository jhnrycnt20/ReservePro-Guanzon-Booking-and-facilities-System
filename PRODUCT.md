# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

ReservePro serves four connected user groups:

- Guests browse accommodations, make and manage bookings, pay, report incidents, and submit feedback.
- Front desk staff manage reservations and walk-ins, check guests in and out, collect or verify payments, and resolve operational incidents.
- Security staff investigate guest incident reports and verify or invalidate them.
- Administrators manage users, accommodations, amenities, pricing, promotions, reports, feedback, messages, settings, and analytics.

## Product Purpose

ReservePro centralizes Guanzon Beach Resort's guest booking journey and day-to-day facility operations. It replaces fragmented manual handling with one role-based system for availability, reservations, payments, arrivals, departures, incident response, promotions, feedback, and administration. Success means guests can complete and track their stay-related tasks while staff can move the same operational records through clear, accountable workflows.

## Positioning

ReservePro connects the guest experience directly to resort operations. A booking can move from online reservation and payment through front desk approval, check-in, check-out, and feedback, while reported incidents move through guest submission, security investigation, front desk resolution, and administrator oversight in the same system.

## Operating Context

- Guests use public browsing pages and an authenticated guest portal before and during a resort stay.
- Front desk staff use operational queues for reservations, walk-ins, arrivals, departures, payments, and incident resolution.
- Security staff use a focused investigation workflow for incident verification.
- Administrators use management tables, dashboards, settings, reports, and analytics to oversee the resort.
- Notifications connect users to records that need their attention.

## Capabilities and Constraints

- Preserve the four existing roles and their authorization boundaries: guest, front desk, security, and administrator.
- Preserve accommodation availability checks and safeguards against overlapping bookings.
- Preserve reservation approval, rejection, cancellation, walk-in, check-in, and check-out workflows.
- Preserve payment records and the existing GCash/PayMongo payment support.
- Preserve guest incident reporting and its security verification and front desk resolution stages.
- Preserve promotion, feedback, notification, receipt, contact-message, system-setting, and analytics capabilities.
- The implementation is an existing Laravel 10 web application using PHP 8.1+, Blade, Bootstrap, JavaScript, and Vite.

## Brand Commitments

- Preserve the Guanzon and Guanzon Beach Resort identity.
- Preserve the existing Guanzon logo assets and resort naming unless the product owner explicitly authorizes a rebrand.
- Keep ReservePro as the system's product name.
- Keep operational language clear, direct, and appropriate for resort guests and staff.

## Evidence on Hand

- Guanzon logo and app-icon assets under `public/images/` and `resources/images/`.
- Resort photography and accommodation imagery under `public/images/`.
- Existing public resort story and guest-facing content in `resources/views/welcome.blade.php` and related public views.
- Implemented role workflows in `routes/web.php`, controllers, services, policies, models, and Blade views.
- Automated feature tests under `tests/Feature/`.
- No confirmed testimonials, customer benchmarks, press claims, or third-party awards are recorded; future work must not fabricate them.

## Product Principles

1. Keep one reliable operational record from guest intent through staff completion.
2. Make the next required action obvious for each role.
3. Protect booking availability, payment state, and incident status from ambiguous or conflicting updates.
4. Give guests clear visibility into reservations, payments, reports, and outcomes.
5. Preserve Guanzon's identity while keeping high-frequency staff workflows efficient.
