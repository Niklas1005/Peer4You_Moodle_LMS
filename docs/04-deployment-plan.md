# Deployment Plan (Local to Production)

## Environments
- **Local**: developer setup with Docker.
- **Staging**: production-like environment for validation.
- **Production**: company usage.

## Migration Checklist
1. Provision server (Linux recommended).
2. Install web server, PHP runtime, DB server, TLS certificates.
3. Deploy Moodle code (tagged release/approved branch).
4. Create production `moodledata` path outside web root.
5. Import database from staging or initialize fresh.
6. Configure `config.php` for production domain and paths.
7. Configure cron (`admin/cli/cron.php` every minute).
8. Set backup jobs:
   - DB backups
   - `moodledata` backups
9. Run smoke tests (login, course access, uploads, notifications).

## Security Baseline
- HTTPS enforced.
- Strong admin credentials + MFA if available.
- Least-privilege DB users.
- Regular patch cycle for OS, PHP, DB, Moodle, plugins.
- Log monitoring and alerting.

## Rollback Strategy
- Keep previous application build and DB backup snapshot.
- Document exact rollback sequence before release.
- Validate rollback in staging at least once.
