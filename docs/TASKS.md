# TASKS.md — TanaOgi Backend Task Tracker

Update status task ini saat mulai [~] dan selesai [x].
Prompt lengkap per task ada di ~/tanaogi-workspace/PROMPTS.md.

---

## PHASE 1 — Project Setup

- [ ] TASK-001 `feat/task-001-laravel-init` — Laravel Init + Docker
- [ ] TASK-002 `feat/task-002-env-packages` — Environment & Packages
- [ ] TASK-003 `feat/task-003-folder-structure` — Folder Structure & Base Classes

## PHASE 2 — Database & Models

- [ ] TASK-004 `feat/task-004-core-migrations` — Core Migrations & Seeders
- [ ] TASK-005 `feat/task-005-blacklist-migrations` — Blacklist Migrations
- [ ] TASK-006 `feat/task-006-eloquent-models` — Eloquent Models

## PHASE 3 — Auth & Middleware

- [ ] TASK-007 `feat/task-007-authentication` — Authentication
- [ ] TASK-008 `feat/task-008-security-middleware` — Security Middleware Stack

## PHASE 4 — Core Features

- [ ] TASK-009 `feat/task-009-regencies-crud` — Regencies CRUD
- [ ] TASK-010 `feat/task-010-destinations-crud` — Destinations CRUD + R2 Upload
- [ ] TASK-011 `feat/task-011-vehicles-crud` — Vehicles CRUD
- [ ] TASK-012 `feat/task-012-accommodations-crud` — Accommodations CRUD
- [ ] TASK-013 `feat/task-013-drivers-crud` — Drivers CRUD + Schedule
- [ ] TASK-014 `feat/task-014-driver-orders` — Driver Orders + Assign Driver

## PHASE 5 — Admin Full Control

- [ ] TASK-015 `feat/task-015-settings-endpoint` — Settings Endpoint
- [ ] TASK-016 `feat/task-016-admin-users` — Admin Users CRUD
- [ ] TASK-017 `feat/task-017-maintenance-mode` — Maintenance Mode

## PHASE 6 — Blacklist & Security

- [ ] TASK-018 `feat/task-018-blacklist-whitelist` — Blacklist Whitelist Full Control
- [ ] TASK-019 `feat/task-019-auto-ban` — Auto-Ban System

## PHASE 7 — Scheduler & Notifications

- [ ] TASK-020 `feat/task-020-whatsapp-service` — WhatsApp Service via WaAPI
- [ ] TASK-021 `feat/task-021-scheduler-reminder` — Scheduler Reminder Driver

## PHASE 8 — Polish & Deploy

- [ ] TASK-022 `feat/task-022-error-handling` — Error Handling
- [ ] TASK-023 `feat/task-023-production-deploy` — Deploy Railway + R2 Production

---

## Status Legend

```
[ ] = Belum dimulai
[~] = Sedang dikerjakan Claude Code
[r] = Menunggu review di Claude.ai
[x] = Approved dan merged ke main
```

---

## Alur Per Task

```
[ ] → Copy prompt dari PROMPTS.md → paste ke Claude Code
[~] → Claude Code kerjakan
[r] → Copy summary Claude Code → paste ke Claude.ai untuk review
[x] → Approved: developer push → PR → merge → checkout main → pull
```
