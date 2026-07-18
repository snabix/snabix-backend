# Production runtime

Production runtime is defined by immutable application images and
`compose.production.yaml`. The local `docker-compose.yml` remains a development
environment and must not be used for staging or production.

## Data ownership

Production Compose does not create PostgreSQL, Redis, RabbitMQ or object-storage
containers and does not declare their volumes. It only receives private service
addresses from the protected env file. Consequently:

- `docker compose down` for this manifest cannot delete the database volume;
- database backups and object-storage versioning belong to the managed
  stateful layer;
- `FILESYSTEM_DISK=s3` is required for horizontally restarted app containers;
- DB, Redis and RabbitMQ do not publish host ports from this stack.

Never run `down -v`, `migrate:fresh`, `db:wipe` or a manual destructive SQL
command during deployment.

## Images

Each repository publishes a GHCR image from `main`/`master` and release tags.
The deployment file accepts only a digest, `sha-*` tag or semantic version. It
rejects `latest`, branch names and untagged references.

```bash
cp deploy/runtime.env.example /protected/snabix/release-2026-07-18.env
php scripts/check-production-runtime.php \
  --env-file /protected/snabix/release-2026-07-18.env
```

Frontend `NEXT_PUBLIC_API_URL` is baked into its image. Build the frontend image
for the target environment and do not promote an image built for a different API
origin.

## Release

1. Create and verify a PostgreSQL backup plus an object-storage snapshot or
   version manifest. Record their identifiers in the release ticket.
2. Store the protected application env outside Git with mode `0600`.
   `SNABIX_BOT_SERVICE_TOKEN` and `SNABIX_BACKEND_SERVICE_TOKEN` are the backend
   and bot names for the same generated credential and must contain the same
   value.
3. Run `task secrets:production` against that application env.
4. Validate the release image file and resolved Compose:

```bash
RUNTIME_ENV_FILE=/protected/snabix/release-2026-07-18.env task runtime:validate
docker compose \
  --env-file /protected/snabix/release-2026-07-18.env \
  -f compose.production.yaml \
  pull
```

5. Run migrations as an explicit controlled step. The normal stack startup never
   runs them:

```bash
docker compose \
  --env-file /protected/snabix/release-2026-07-18.env \
  -f compose.production.yaml \
  --profile operations \
  run --rm migrate
```

6. Start the release and wait for readiness:

```bash
docker compose \
  --env-file /protected/snabix/release-2026-07-18.env \
  -f compose.production.yaml \
  up -d --wait
```

7. Smoke `/health`, frontend `/api/health`, authentication, one read-only listing
   request, queue processing and bot readiness. Keep the previous release env
   until the observation window ends.

The scheduler writes a heartbeat every minute. Backend readiness fails when DB,
migrations, Redis, RabbitMQ or the relevant runtime process is unavailable.
Queue workers receive `SIGTERM`, finish the active job within the two-minute
grace period and recycle hourly through `--max-time`.

## Rollback

Rollback switches only versioned image references. It never rolls the database
back automatically.

```bash
docker compose \
  --env-file /protected/snabix/release-previous.env \
  -f compose.production.yaml \
  pull

docker compose \
  --env-file /protected/snabix/release-previous.env \
  -f compose.production.yaml \
  up -d --wait --force-recreate
```

Before migration, classify it as backward-compatible or blocking. A
backward-compatible migration allows an image rollback. A blocking migration
requires a tested forward fix or a separately approved restore of both database
and object storage. Do not invoke Laravel rollback commands in production: the
application intentionally prohibits them.

For the staging rollback drill, deploy release A, record health and a non-secret
data marker, deploy release B, switch back to A, then confirm health and the same
marker. Attach image digests, backup identifiers, timestamps and smoke results
to the release ticket.
