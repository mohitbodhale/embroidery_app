# Database Backup & Restore Guide

Vehicle manufacturing deployment of the embroidery_app CakePHP project.
Database: **PostgreSQL `embroidery_app`** (also works on MySQL).

---

## What is in this folder

| File | Purpose |
|------|---------|
| `embroidery_app_full.sql` | Complete dump — schema **and** data. Use this for a one-shot full restore. |
| `embroidery_app_data_only.sql` | Data only. Use when the schema already exists and only rows are lost. |

Full schema (no data) lives in the migration file, so a schema-only rebuild
is also possible:

```
config/Migrations/20261021112308_InitialSchema.php
```

---

## Option A — Fastest full restore (recommended)

Restores schema **and** data in one step.

```bash
# 1. Drop and recreate the database
psql -h localhost -U postgres -c "DROP DATABASE IF EXISTS embroidery_app;"
psql -h localhost -U postgres -c "CREATE DATABASE embroidery_app;"

# 2. Restore everything
psql -h localhost -U postgres -d embroidery_app -f database_backup/embroidery_app_full.sql
```

The dump contains `DROP TABLE IF EXISTS` statements, so it overwrites cleanly.

> Restore the file with `psql` (not a GUI client) so the encoding and the
> `SET` statements at the top of the file are applied.

---

## Option B — Rebuild from migration + seeds

This is the reproducible path. It rebuilds the schema from the migration and
repopulates the reference data from the seed files.

```bash
# 1. Point .env at the target database
#    edit .env  ->  PG_NAME=embroidery_app

# 2. Create the database (if it does not exist)
psql -h localhost -U postgres -c "CREATE DATABASE embroidery_app;"

# 3. Build the schema
php bin/cake.php migrations migrate -c default

# 4. Load the reference data
php bin/cake.php seeds run ReferenceDataSeed -c default
php bin/cake.php seeds run InitialUsersSeed -c default

# 5. Optional: add eight demo jobs covering every work type
php bin/cake.php seeds run DemoJobsSeed -c default
```

### What each seed restores

| Seed | Tables | Rows |
|------|--------|------|
| `ReferenceDataSeed` | `organizations`, `roles`, `work_types`, `job_statuses`, `levels`, `level_rates`, `job_counters` | 1 / 5 / 8 / 8 / 5 / 6 / 1 |
| `InitialUsersSeed` | `users`, `operator_wallets` | 27 / 17 |
| `DemoJobsSeed` | `jobs` | 8 (one per work type) |

All three seeds are **idempotent** — running them again will not duplicate
rows, so they are safe to re-run after a partial failure.

### Default credentials

| Email | Role | Work type |
|-------|------|-----------|
| `admin@stitchcraft.com` | admin | vehicle_design |
| `scheduler@stitchcraft.com` | scheduler | — |
| `planner1@stitchcraft.com` | scheduler | production_planning |
| `planner2@stitchcraft.com` | scheduler | production_planning |
| `design1@stitchcraft.com` | operator | vehicle_design |
| `design2@stitchcraft.com` | operator | vehicle_design |
| `cad_specialist@stitchcraft.com` | operator | vehicle_design |
| `assembly1@stitchcraft.com` | operator | vehicle_manufacturing |
| `assembly2@stitchcraft.com` | operator | vehicle_manufacturing |
| `prod_lead@stitchcraft.com` | operator | vehicle_manufacturing |
| `mfg_operator@stitchcraft.com` | operator | vehicle_manufacturing |
| `operator@stitchcraft.com` | operator | vehicle_manufacturing |
| `qc1@stitchcraft.com` | quality_checker | quality_control |
| `qc2@stitchcraft.com` | quality_checker | quality_control |
| `quality_eng@stitchcraft.com` | quality_checker | quality_control |
| `maint1@stitchcraft.com` | operator | maintenance |
| `maint2@stitchcraft.com` | operator | maintenance |
| `assembly3@stitchcraft.com` | operator | assembly |
| `final_assembly@stitchcraft.com` | operator | assembly |
| `production@stitchcraft.com` | production | assembly |
| `test_eng1@stitchcraft.com` | operator | testing |
| `test_eng2@stitchcraft.com` | operator | testing |
| `logistics_coord@stitchcraft.com` | operator | logistics |
| `warehouse_op@stitchcraft.com` | operator | logistics |
| `shipping@stitchcraft.com` | operator | logistics |
| `programmer@stitchcraft.com` | operator | — |
| `accountant@stitchcraft.com` | operator | — |

**Password for every account: `Password123!`**

> Change these before going live. They are stored as bcrypt hashes (cost 10).

---

## Option C — Data only, schema already in place

```bash
psql -h localhost -U postgres -d embroidery_app -f database_backup/embroidery_app_data_only.sql
```

This restores all rows but leaves the table structure alone.

---

## MySQL equivalent

The project supports both engines. For MySQL use `mysqldump` instead:

```bash
mysqldump -u root -p --routines --triggers embroidery_app > embroidery_app_full.sql
mysql -u root -p -e "DROP DATABASE IF EXISTS embroidery_app; CREATE DATABASE embroidery_app;"
mysql -u root -p embroidery_app < embroidery_app_full.sql
```

The migration and the seeds are driver-agnostic, so Option B works on MySQL
too.

---

## Post-restore checklist

```bash
# 1. Schema and data present
php bin/cake.php migrations status -c default

# 2. Clear the ORM metadata cache so new columns are picked up
#    (config/app.php has cacheMetadata => true)
rm -rf tmp/cache/models

# 3. Log in and check the job list
#    http://localhost/embroidery_app/users/login
#    admin@stitchcraft.com / Password123!
```

---

## Files that were replaced

The original incremental migration files were consolidated into a single
snapshot. A copy of the old set is kept at:

```
C:\xampp82\htdocs\migrations_backup\
```

The snapshot was regenerated from the live database with
`php bin/cake.php migrations dump`, then its index names were rewritten to
match the live database (CakePHP names a dumped index after its column, which
collides on PostgreSQL because index names are schema-global).
