# Moodle Implementation Project Overview

## Purpose
This project provides a professional Moodle baseline for university and company use:
- Local development and evaluation first.
- Structured documentation for handover.
- Path to production deployment.

## Scope
- Source of truth: official Moodle repository.
- Local runtime stack: Docker, PHP/Apache, MariaDB.
- Operational support tools: phpMyAdmin and Moodle cron worker.
- Configuration and documentation standards for traceability.

## Architecture (Local)
- `web`: PHP 8.2 + Apache, serves Moodle.
- `db`: MariaDB 11, stores Moodle data.
- `cron`: executes `admin/cli/cron.php` every minute.
- `phpmyadmin`: optional DB inspection and debugging.
- Docker volume `moodledata`: persistent Moodle file storage.

## Repository Structure Additions
- `docker-compose.yml`: full local stack orchestration.
- `docker/php/Dockerfile`: Moodle-ready PHP image.
- `.env.example`: environment template for local secrets/ports.
- `docs/`: implementation and operations documentation.

## Professional Delivery Principles
- Reproducible setup through infrastructure-as-code.
- Clear separation of code, data, secrets, and runtime.
- Documented runbooks for onboarding and operations.
- Upgrade-safe customization strategy (themes/plugins, avoid core hacks).
