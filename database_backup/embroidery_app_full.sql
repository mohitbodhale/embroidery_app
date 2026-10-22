--
-- PostgreSQL database dump
--

\restrict GBRxEv2kjXUgGrcrWmgCcyxwfE7WaWd1TiYpkFcCOrkQlW0UKTaPfD1X8V5cx9C

-- Dumped from database version 18.6
-- Dumped by pg_dump version 18.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_role_id_fkey;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_organization_id_fkey;
ALTER TABLE IF EXISTS ONLY public.password_reset_tokens DROP CONSTRAINT IF EXISTS password_reset_tokens_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.level_rates DROP CONSTRAINT IF EXISTS level_rates_level_id_fkey;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS jobs_status_id_fkey;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS jobs_qc_id_fkey;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS jobs_organization_id_fkey;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS jobs_operator_id_fkey;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS jobs_created_by_fkey;
ALTER TABLE IF EXISTS ONLY public.job_qc_reviews DROP CONSTRAINT IF EXISTS job_qc_reviews_qc_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.job_qc_reviews DROP CONSTRAINT IF EXISTS job_qc_reviews_job_id_fkey;
ALTER TABLE IF EXISTS ONLY public.job_qc_reviews DROP CONSTRAINT IF EXISTS job_qc_reviews_assigned_to_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.job_logs DROP CONSTRAINT IF EXISTS job_logs_user_id_fkey;
ALTER TABLE IF EXISTS ONLY public.job_logs DROP CONSTRAINT IF EXISTS job_logs_job_id_fkey;
ALTER TABLE IF EXISTS ONLY public.job_attachments DROP CONSTRAINT IF EXISTS job_attachments_uploaded_by_fkey;
ALTER TABLE IF EXISTS ONLY public.job_attachments DROP CONSTRAINT IF EXISTS job_attachments_job_id_fkey;
ALTER TABLE IF EXISTS ONLY public.job_assignments DROP CONSTRAINT IF EXISTS job_assignments_job_id_fkey;
ALTER TABLE IF EXISTS ONLY public.job_assignments DROP CONSTRAINT IF EXISTS job_assignments_assigned_to_fkey;
ALTER TABLE IF EXISTS ONLY public.job_assignments DROP CONSTRAINT IF EXISTS job_assignments_assigned_by_fkey;
ALTER TABLE IF EXISTS ONLY public.wallet_transactions DROP CONSTRAINT IF EXISTS fk_wallet_transactions_wallet_id;
ALTER TABLE IF EXISTS ONLY public.wallet_transactions DROP CONSTRAINT IF EXISTS fk_wallet_transactions_user_id;
ALTER TABLE IF EXISTS ONLY public.wallet_transactions DROP CONSTRAINT IF EXISTS fk_wallet_transactions_job_id;
ALTER TABLE IF EXISTS ONLY public.wallet_transactions DROP CONSTRAINT IF EXISTS fk_wallet_transactions_created_by;
ALTER TABLE IF EXISTS ONLY public.operator_wallets DROP CONSTRAINT IF EXISTS fk_operator_wallets_user_id;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS fk_jobs_level_id;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS fk_jobs_assigned_to;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS fk_jobs_assigned_by;
DROP INDEX IF EXISTS public.work_types_name;
DROP INDEX IF EXISTS public.version_plugin_unique;
DROP INDEX IF EXISTS public.users_unique_email_per_org;
DROP INDEX IF EXISTS public.users_role_id;
DROP INDEX IF EXISTS public.users_organization_id;
DROP INDEX IF EXISTS public.user_details_user_id;
DROP INDEX IF EXISTS public.unique_job_number_per_org;
DROP INDEX IF EXISTS public.roles_name;
DROP INDEX IF EXISTS public.password_reset_tokens_user_id;
DROP INDEX IF EXISTS public.password_reset_tokens_token;
DROP INDEX IF EXISTS public.password_reset_tokens_expires_at;
DROP INDEX IF EXISTS public.organizations_domain_or_slug;
DROP INDEX IF EXISTS public.jobs_status_id;
DROP INDEX IF EXISTS public.jobs_status;
DROP INDEX IF EXISTS public.jobs_organization_id;
DROP INDEX IF EXISTS public.job_statuses_name;
DROP INDEX IF EXISTS public.job_logs_user_id;
DROP INDEX IF EXISTS public.job_logs_job_id;
DROP INDEX IF EXISTS public.job_attachments_job_id;
DROP INDEX IF EXISTS public.idx_wallet_transactions_wallet_id;
DROP INDEX IF EXISTS public.idx_wallet_transactions_user_id;
DROP INDEX IF EXISTS public.idx_wallet_transactions_type;
DROP INDEX IF EXISTS public.idx_wallet_transactions_job_id;
DROP INDEX IF EXISTS public.idx_wallet_transactions_created_at;
DROP INDEX IF EXISTS public.idx_qc_review_user;
DROP INDEX IF EXISTS public.idx_qc_review_job;
DROP INDEX IF EXISTS public.idx_operator_wallets_user_id;
DROP INDEX IF EXISTS public.idx_levels_name;
DROP INDEX IF EXISTS public.idx_level_rates_level_from;
DROP INDEX IF EXISTS public.idx_jobs_qc_status;
DROP INDEX IF EXISTS public.idx_jobs_level_id;
DROP INDEX IF EXISTS public.idx_jobs_assigned_to;
DROP INDEX IF EXISTS public.idx_jobs_assigned_by;
DROP INDEX IF EXISTS public.idx_assign_user;
DROP INDEX IF EXISTS public.idx_assign_job;
DROP INDEX IF EXISTS public.idx_assign_by;
ALTER TABLE IF EXISTS ONLY public.work_types DROP CONSTRAINT IF EXISTS work_types_pkey;
ALTER TABLE IF EXISTS ONLY public.wallet_transactions DROP CONSTRAINT IF EXISTS wallet_transactions_pkey;
ALTER TABLE IF EXISTS ONLY public.users DROP CONSTRAINT IF EXISTS users_pkey;
ALTER TABLE IF EXISTS ONLY public.user_details DROP CONSTRAINT IF EXISTS user_details_pkey;
ALTER TABLE IF EXISTS ONLY public.roles DROP CONSTRAINT IF EXISTS roles_pkey;
ALTER TABLE IF EXISTS ONLY public.password_reset_tokens DROP CONSTRAINT IF EXISTS password_reset_tokens_pkey;
ALTER TABLE IF EXISTS ONLY public.organizations DROP CONSTRAINT IF EXISTS organizations_pkey;
ALTER TABLE IF EXISTS ONLY public.operator_wallets DROP CONSTRAINT IF EXISTS operator_wallets_pkey;
ALTER TABLE IF EXISTS ONLY public.levels DROP CONSTRAINT IF EXISTS levels_pkey;
ALTER TABLE IF EXISTS ONLY public.level_rates DROP CONSTRAINT IF EXISTS level_rates_pkey;
ALTER TABLE IF EXISTS ONLY public.jobs DROP CONSTRAINT IF EXISTS jobs_pkey;
ALTER TABLE IF EXISTS ONLY public.job_statuses DROP CONSTRAINT IF EXISTS job_statuses_pkey;
ALTER TABLE IF EXISTS ONLY public.job_qc_reviews DROP CONSTRAINT IF EXISTS job_qc_reviews_pkey;
ALTER TABLE IF EXISTS ONLY public.job_logs DROP CONSTRAINT IF EXISTS job_logs_pkey;
ALTER TABLE IF EXISTS ONLY public.job_counters DROP CONSTRAINT IF EXISTS job_counters_pkey;
ALTER TABLE IF EXISTS ONLY public.job_attachments DROP CONSTRAINT IF EXISTS job_attachments_pkey;
ALTER TABLE IF EXISTS ONLY public.job_assignments DROP CONSTRAINT IF EXISTS job_assignments_pkey;
ALTER TABLE IF EXISTS ONLY public.cake_seeds DROP CONSTRAINT IF EXISTS cake_seeds_pkey;
ALTER TABLE IF EXISTS ONLY public.cake_migrations DROP CONSTRAINT IF EXISTS cake_migrations_pkey;
DROP TABLE IF EXISTS public.work_types;
DROP TABLE IF EXISTS public.wallet_transactions;
DROP TABLE IF EXISTS public.users;
DROP TABLE IF EXISTS public.user_details;
DROP TABLE IF EXISTS public.roles;
DROP TABLE IF EXISTS public.password_reset_tokens;
DROP TABLE IF EXISTS public.organizations;
DROP TABLE IF EXISTS public.operator_wallets;
DROP TABLE IF EXISTS public.levels;
DROP TABLE IF EXISTS public.level_rates;
DROP TABLE IF EXISTS public.jobs;
DROP TABLE IF EXISTS public.job_statuses;
DROP TABLE IF EXISTS public.job_qc_reviews;
DROP TABLE IF EXISTS public.job_logs;
DROP TABLE IF EXISTS public.job_counters;
DROP TABLE IF EXISTS public.job_attachments;
DROP TABLE IF EXISTS public.job_assignments;
DROP TABLE IF EXISTS public.cake_seeds;
DROP TABLE IF EXISTS public.cake_migrations;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cake_migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cake_migrations (
    id integer NOT NULL,
    version bigint NOT NULL,
    migration_name character varying(100) DEFAULT NULL::character varying,
    plugin character varying(100) DEFAULT NULL::character varying,
    start_time timestamp without time zone,
    end_time timestamp without time zone,
    breakpoint boolean DEFAULT false NOT NULL
);


--
-- Name: cake_migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.cake_migrations ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.cake_migrations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: cake_seeds; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cake_seeds (
    id integer NOT NULL,
    plugin character varying(100) DEFAULT NULL::character varying,
    seed_name character varying(100) NOT NULL,
    executed_at timestamp without time zone
);


--
-- Name: cake_seeds_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.cake_seeds ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.cake_seeds_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: job_assignments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_assignments (
    id integer NOT NULL,
    job_id integer NOT NULL,
    assigned_to integer NOT NULL,
    assigned_by integer,
    assigned_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    completed_at timestamp without time zone,
    note text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: job_assignments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.job_assignments ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.job_assignments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: job_attachments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_attachments (
    id integer NOT NULL,
    job_id integer NOT NULL,
    file_name character varying(512) NOT NULL,
    file_path character varying(1024) NOT NULL,
    file_type character varying(255) DEFAULT NULL::character varying,
    file_size integer,
    mime_type character varying(255) DEFAULT NULL::character varying,
    comments text,
    uploaded_by integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: job_attachments_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.job_attachments ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.job_attachments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: job_counters; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_counters (
    id integer NOT NULL,
    last_number integer DEFAULT 0 NOT NULL
);


--
-- Name: job_counters_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.job_counters ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.job_counters_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: job_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_logs (
    id integer NOT NULL,
    job_id integer,
    user_id integer,
    action character varying(128) NOT NULL,
    comments text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: job_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.job_logs ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.job_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: job_qc_reviews; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_qc_reviews (
    id integer NOT NULL,
    job_id integer NOT NULL,
    qc_user_id integer NOT NULL,
    assigned_to_user_id integer,
    decision character varying(16) NOT NULL,
    notes text,
    reviewed_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: COLUMN job_qc_reviews.assigned_to_user_id; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.job_qc_reviews.assigned_to_user_id IS 'The user whose work was reviewed (for return-to-sender).';


--
-- Name: COLUMN job_qc_reviews.decision; Type: COMMENT; Schema: public; Owner: -
--

COMMENT ON COLUMN public.job_qc_reviews.decision IS 'approved | rejected';


--
-- Name: job_qc_reviews_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.job_qc_reviews ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.job_qc_reviews_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: job_statuses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_statuses (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    label character varying(128) NOT NULL,
    description text,
    color character varying(16) DEFAULT NULL::character varying,
    sort_order integer,
    is_active boolean DEFAULT true NOT NULL,
    is_terminal boolean DEFAULT false NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: job_statuses_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.job_statuses ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.job_statuses_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id integer NOT NULL,
    job_number character varying(64) NOT NULL,
    title character varying(255) NOT NULL,
    instructions text,
    status character varying(32) DEFAULT 'draft'::character varying NOT NULL,
    created_by integer,
    operator_id integer,
    qc_id integer,
    scheduled_date timestamp without time zone,
    status_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    organization_id integer NOT NULL,
    assigned_to integer,
    assigned_by integer,
    assigned_at timestamp without time zone,
    qc_status character varying(16) DEFAULT 'pending'::character varying NOT NULL,
    qc_review_notes text,
    qc_reviewed_at timestamp without time zone,
    revision_count integer DEFAULT 0 NOT NULL,
    level_id integer,
    level_payment numeric(12,2) DEFAULT NULL::numeric
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.jobs ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: level_rates; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.level_rates (
    id integer NOT NULL,
    level_id integer NOT NULL,
    payment_amount numeric(12,2) DEFAULT 0 NOT NULL,
    valid_from timestamp without time zone,
    valid_to timestamp without time zone,
    created_at timestamp without time zone,
    modified_at timestamp without time zone
);


--
-- Name: level_rates_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.level_rates ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.level_rates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: levels; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.levels (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    label character varying(128) NOT NULL,
    description text,
    payment_amount numeric(12,2) DEFAULT 0 NOT NULL,
    color character varying(16) DEFAULT NULL::character varying,
    sort_order integer,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone,
    modified_at timestamp without time zone
);


--
-- Name: levels_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.levels ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.levels_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: operator_wallets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.operator_wallets (
    id integer NOT NULL,
    user_id integer NOT NULL,
    balance numeric(12,2) DEFAULT 0 NOT NULL,
    total_earned numeric(12,2) DEFAULT 0 NOT NULL,
    total_paid numeric(12,2) DEFAULT 0 NOT NULL,
    notes text,
    created_at timestamp without time zone,
    modified_at timestamp without time zone
);


--
-- Name: operator_wallets_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.operator_wallets ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.operator_wallets_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: organizations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.organizations (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    domain_or_slug character varying(255) NOT NULL,
    status character varying(32) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: organizations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.organizations ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.organizations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    id integer NOT NULL,
    user_id integer NOT NULL,
    token character varying(255) NOT NULL,
    expires_at timestamp without time zone NOT NULL,
    used boolean DEFAULT false NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.password_reset_tokens ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.password_reset_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    label character varying(128) NOT NULL,
    description text,
    color character varying(16) DEFAULT NULL::character varying,
    sort_order integer,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.roles ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: user_details; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_details (
    id integer NOT NULL,
    user_id integer NOT NULL,
    phone character varying(20) DEFAULT NULL::character varying,
    bio text,
    avatar character varying(255) DEFAULT NULL::character varying,
    website character varying(255) DEFAULT NULL::character varying,
    location character varying(100) DEFAULT NULL::character varying,
    work_type_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone
);


--
-- Name: user_details_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.user_details ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.user_details_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    email character varying(255) NOT NULL,
    password text NOT NULL,
    role character varying(50) DEFAULT 'digitizer'::character varying NOT NULL,
    organization_id integer NOT NULL,
    role_id integer,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    work_type character varying(64) DEFAULT NULL::character varying,
    must_change_password boolean DEFAULT false
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.users ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: wallet_transactions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.wallet_transactions (
    id integer NOT NULL,
    wallet_id integer NOT NULL,
    user_id integer NOT NULL,
    job_id integer,
    type character varying(32) DEFAULT 'earning'::character varying NOT NULL,
    amount numeric(12,2) DEFAULT 0 NOT NULL,
    balance_after numeric(12,2) DEFAULT 0 NOT NULL,
    notes text,
    reference character varying(100) DEFAULT NULL::character varying,
    created_by integer,
    created_at timestamp without time zone
);


--
-- Name: wallet_transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.wallet_transactions ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.wallet_transactions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Name: work_types; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.work_types (
    id integer NOT NULL,
    name character varying(64) NOT NULL,
    label character varying(128) NOT NULL,
    description text,
    color character varying(16) DEFAULT NULL::character varying,
    sort_order integer,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: work_types_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

ALTER TABLE public.work_types ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.work_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);


--
-- Data for Name: cake_migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cake_migrations (id, version, migration_name, plugin, start_time, end_time, breakpoint) FROM stdin;
1	20260901183000	AddPendingUserRole	\N	2026-10-15 11:04:52	2026-10-15 11:04:52	f
2	20260901214950	CreateCoreSchema	\N	2026-10-15 11:04:52	2026-10-15 11:04:53	f
3	20260903200000	AddPasswordResetTokens	\N	2026-10-15 11:04:53	2026-10-15 11:04:53	f
4	20260905180000	RefactorJobsForGenericAssignmentAndQcCycle	\N	2026-10-15 11:04:53	2026-10-15 11:04:53	f
5	20260907190000	AddOperatorWorkTypeToUsers	\N	2026-10-15 11:04:53	2026-10-15 11:04:53	f
6	20260907193000	ChangeUsersRoleFromEnumToVarchar	\N	2026-10-15 11:04:53	2026-10-15 11:04:53	f
7	20260908060000	CreateWorkTypes	\N	2026-10-15 11:04:53	2026-10-15 11:04:53	f
8	20260908070000	RenameDigitizerIdToOperatorIdAndMoveWorkType	\N	2026-10-15 11:04:53	2026-10-15 11:04:53	f
9	20260908080000	SeedNewRolesAndSampleUsers	\N	2026-10-15 11:04:53	2026-10-15 11:04:54	f
10	20260915000000	RenameStatuses	\N	2026-10-15 11:04:54	2026-10-15 11:04:54	f
11	20261005090000	CreateLevels	\N	2026-10-15 11:04:54	2026-10-15 11:04:54	f
12	20261005090100	AddLevelToJobs	\N	2026-10-15 11:04:54	2026-10-15 11:04:54	f
13	20261005090200	CreateOperatorWallets	\N	2026-10-15 11:04:54	2026-10-15 11:04:54	f
14	20261005090300	CreateWalletTransactions	\N	2026-10-15 11:04:54	2026-10-15 11:04:55	f
15	20261006000000	CreateLevelRates	\N	2026-10-15 11:04:55	2026-10-15 11:04:55	f
16	20261008000000	CreateJobCounters	\N	2026-10-15 11:04:55	2026-10-15 11:04:55	f
17	20261021112308	InitialSchema	\N	2026-10-21 11:23:09	2026-10-21 11:23:09	f
\.


--
-- Data for Name: cake_seeds; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cake_seeds (id, plugin, seed_name, executed_at) FROM stdin;
1	\N	InitialUsersSeed	2026-10-15 11:05:19
\.


--
-- Data for Name: job_assignments; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_assignments (id, job_id, assigned_to, assigned_by, assigned_at, completed_at, note, created_at) FROM stdin;
\.


--
-- Data for Name: job_attachments; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_attachments (id, job_id, file_name, file_path, file_type, file_size, mime_type, comments, uploaded_by, created_at) FROM stdin;
\.


--
-- Data for Name: job_counters; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_counters (id, last_number) FROM stdin;
1	2
\.


--
-- Data for Name: job_logs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_logs (id, job_id, user_id, action, comments, created_at) FROM stdin;
\.


--
-- Data for Name: job_qc_reviews; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_qc_reviews (id, job_id, qc_user_id, assigned_to_user_id, decision, notes, reviewed_at, created_at) FROM stdin;
\.


--
-- Data for Name: job_statuses; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_statuses (id, name, label, description, color, sort_order, is_active, is_terminal, created_at) FROM stdin;
1	draft	Draft	\N	#6c757d	10	t	f	2026-10-15 11:04:52.616722
2	in_progress	In Progress	\N	#0d6efd	20	t	f	2026-10-15 11:04:52.616722
3	ready_for_qc	Ready for QC	\N	#6610f2	30	t	f	2026-10-15 11:04:52.616722
4	qc_approved	QC Approved	\N	#198754	40	t	f	2026-10-15 11:04:52.616722
5	qc_rejected	QC Rejected	\N	#dc3545	50	t	f	2026-10-15 11:04:52.616722
6	in_production	In Production	\N	#fd7e14	60	t	f	2026-10-15 11:04:52.616722
7	completed	Completed	\N	#20c997	70	t	t	2026-10-15 11:04:52.616722
8	cancelled	Cancelled	\N	#6c757d	80	t	t	2026-10-15 11:04:52.616722
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.jobs (id, job_number, title, instructions, status, created_by, operator_id, qc_id, scheduled_date, status_id, created_at, updated_at, organization_id, assigned_to, assigned_by, assigned_at, qc_status, qc_review_notes, qc_reviewed_at, revision_count, level_id, level_payment) FROM stdin;
1	JOB_2026_002	Test Embroidery Job	Test instructions for embroidery job	in_progress	8	2	3	2026-10-15 00:00:00	2	2026-10-15 11:32:25.319778	2026-10-15 11:32:25.319778	1	\N	\N	\N	pending	\N	\N	0	1	\N
10	JOB-ASSE-32766	assembly Job 1	Test job 1 for assembly	not_a_real_status	1	\N	\N	\N	\N	2026-10-15 18:15:46.232026	2026-10-15 18:15:46.232026	1	20	1	\N	pending	\N	\N	0	1	0.00
2	JOB-VEHI-9068	vehicle_design Production Job	Test job for vehicle_design phase	assigned	1	\N	\N	\N	\N	2026-10-15 18:11:30.932136	2026-10-15 18:11:30.932136	1	5	1	2026-10-15 18:11:30.990614	pending	\N	\N	0	1	0.00
4	JOB-QUAL-4063	quality_control Production Job	Test job for quality_control phase	assigned	1	\N	\N	\N	\N	2026-10-15 18:11:30.973623	2026-10-15 18:11:30.973623	1	3	1	2026-10-15 18:11:31.046472	pending	\N	\N	0	1	0.00
5	JOB-MAIN-3394	maintenance Production Job	Test job for maintenance phase	assigned	1	\N	\N	\N	\N	2026-10-15 18:11:30.975283	2026-10-15 18:11:30.975283	1	18	1	2026-10-15 18:11:31.052581	pending	\N	\N	0	1	0.00
6	JOB-ASSE-6038	assembly Production Job	Test job for assembly phase	assigned	1	\N	\N	\N	\N	2026-10-15 18:11:30.97708	2026-10-15 18:11:30.97708	1	4	1	2026-10-15 18:11:31.053848	pending	\N	\N	0	1	0.00
7	JOB-TEST-4385	testing Production Job	Test job for testing phase	assigned	1	\N	\N	\N	\N	2026-10-15 18:11:30.979166	2026-10-15 18:11:30.979166	1	22	1	2026-10-15 18:11:31.055354	pending	\N	\N	0	1	0.00
8	JOB-LOGI-2240	logistics Production Job	Test job for logistics phase	assigned	1	\N	\N	\N	\N	2026-10-15 18:11:30.986624	2026-10-15 18:11:30.986624	1	24	1	2026-10-15 18:11:31.056531	pending	\N	\N	0	1	0.00
9	JOB-PROD-6371	production_planning Production Job	Test job for production_planning phase	assigned	1	\N	\N	\N	\N	2026-10-15 18:11:30.988132	2026-10-15 18:11:30.988132	1	27	1	2026-10-15 18:11:31.057637	pending	\N	\N	0	1	0.00
3	JOB-VEHI-7699	vehicle_manufacturing Production Job	Test job for vehicle_manufacturing phase	completed	1	\N	\N	\N	\N	2026-10-15 18:11:30.971859	2026-10-15 18:11:30.971859	1	12	1	2026-10-15 18:11:31.044545	approved	Quality check passed - all specs met	2026-10-15 18:11:31.079186	0	1	0.00
11	JOB-ASSE-72285	assembly Job 2	Test job 2 for assembly	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.257692	2026-10-15 18:15:46.257692	1	21	1	\N	pending	\N	\N	0	1	0.00
12	JOB-ASSE-34026	assembly Job 3	Test job 3 for assembly	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.25947	2026-10-15 18:15:46.25947	1	20	1	\N	pending	\N	\N	0	1	0.00
13	JOB-LOGI-29626	logistics Job 1	Test job 1 for logistics	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.261885	2026-10-15 18:15:46.261885	1	24	1	\N	pending	\N	\N	0	1	0.00
14	JOB-LOGI-68923	logistics Job 2	Test job 2 for logistics	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.292273	2026-10-15 18:15:46.292273	1	26	1	\N	pending	\N	\N	0	1	0.00
15	JOB-LOGI-38283	logistics Job 3	Test job 3 for logistics	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.307842	2026-10-15 18:15:46.307842	1	25	1	\N	pending	\N	\N	0	1	0.00
16	JOB-MAIN-96676	maintenance Job 1	Test job 1 for maintenance	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.310268	2026-10-15 18:15:46.310268	1	18	1	\N	pending	\N	\N	0	1	0.00
17	JOB-MAIN-33043	maintenance Job 2	Test job 2 for maintenance	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.311698	2026-10-15 18:15:46.311698	1	19	1	\N	pending	\N	\N	0	1	0.00
18	JOB-MAIN-66447	maintenance Job 3	Test job 3 for maintenance	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.31305	2026-10-15 18:15:46.31305	1	18	1	\N	pending	\N	\N	0	1	0.00
19	JOB-PROD-83807	production_planning Job 1	Test job 1 for production_planning	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.314908	2026-10-15 18:15:46.314908	1	27	1	\N	pending	\N	\N	0	1	0.00
20	JOB-PROD-23935	production_planning Job 2	Test job 2 for production_planning	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.316139	2026-10-15 18:15:46.316139	1	28	1	\N	pending	\N	\N	0	1	0.00
21	JOB-PROD-86033	production_planning Job 3	Test job 3 for production_planning	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.317279	2026-10-15 18:15:46.317279	1	27	1	\N	pending	\N	\N	0	1	0.00
22	JOB-QUAL-44897	quality_control Job 1	Test job 1 for quality_control	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.318997	2026-10-15 18:15:46.318997	1	3	1	\N	pending	\N	\N	0	1	0.00
23	JOB-QUAL-60104	quality_control Job 2	Test job 2 for quality_control	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.323446	2026-10-15 18:15:46.323446	1	16	1	\N	pending	\N	\N	0	1	0.00
24	JOB-QUAL-70384	quality_control Job 3	Test job 3 for quality_control	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.324901	2026-10-15 18:15:46.324901	1	17	1	\N	pending	\N	\N	0	1	0.00
25	JOB-TEST-17070	testing Job 1	Test job 1 for testing	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.327447	2026-10-15 18:15:46.327447	1	22	1	\N	pending	\N	\N	0	1	0.00
26	JOB-TEST-73641	testing Job 2	Test job 2 for testing	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.329017	2026-10-15 18:15:46.329017	1	23	1	\N	pending	\N	\N	0	1	0.00
27	JOB-TEST-93946	testing Job 3	Test job 3 for testing	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.330222	2026-10-15 18:15:46.330222	1	22	1	\N	pending	\N	\N	0	1	0.00
28	JOB-VEHI-60004	vehicle_design Job 1	Test job 1 for vehicle_design	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.331957	2026-10-15 18:15:46.331957	1	8	1	\N	pending	\N	\N	0	1	0.00
29	JOB-VEHI-34521	vehicle_design Job 2	Test job 2 for vehicle_design	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.333246	2026-10-15 18:15:46.333246	1	11	1	\N	pending	\N	\N	0	1	0.00
30	JOB-VEHI-17670	vehicle_design Job 3	Test job 3 for vehicle_design	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.334477	2026-10-15 18:15:46.334477	1	5	1	\N	pending	\N	\N	0	1	0.00
31	JOB-VEHI-39821	vehicle_manufacturing Job 1	Test job 1 for vehicle_manufacturing	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.33841	2026-10-15 18:15:46.33841	1	12	1	\N	pending	\N	\N	0	1	0.00
32	JOB-VEHI-10264	vehicle_manufacturing Job 2	Test job 2 for vehicle_manufacturing	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.339895	2026-10-15 18:15:46.339895	1	13	1	\N	pending	\N	\N	0	1	0.00
33	JOB-VEHI-60442	vehicle_manufacturing Job 3	Test job 3 for vehicle_manufacturing	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.340998	2026-10-15 18:15:46.340998	1	15	1	\N	pending	\N	\N	0	1	0.00
34	JOB-PRIO-HIGH-33352	Priority high Job	High priority urgent job	assigned	1	\N	\N	\N	\N	2026-10-15 18:15:46.360095	2026-10-15 18:15:46.360095	1	13	1	2026-10-15 18:15:46.364918	pending	\N	\N	0	1	0.00
37	JOB-SEQ-1-20723	Sequence Step 0: vehicle_design	Part of production sequence	assigned	1	\N	\N	\N	\N	2026-10-15 18:15:46.367382	2026-10-15 18:15:46.367382	1	8	1	\N	pending	\N	\N	0	1	0.00
38	JOB-SEQ-2-45768	Sequence Step 1: vehicle_manufacturing	Part of production sequence	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.369538	2026-10-15 18:15:46.369538	1	12	1	\N	pending	\N	\N	0	1	0.00
39	JOB-SEQ-3-57524	Sequence Step 2: quality_control	Part of production sequence	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.371217	2026-10-15 18:15:46.371217	1	3	1	\N	pending	\N	\N	0	1	0.00
40	JOB-SEQ-4-72830	Sequence Step 3: assembly	Part of production sequence	draft	1	\N	\N	\N	\N	2026-10-15 18:15:46.372515	2026-10-15 18:15:46.372515	1	20	1	\N	pending	\N	\N	0	1	0.00
41	JOB-INVALID-001	invalid_worktype Job	Test	draft	1	\N	\N	\N	\N	2026-10-15 18:21:07.769118	2026-10-15 18:21:07.769118	1	1	1	\N	pending	\N	\N	0	1	0.00
35	JOB-PRIO-MEDIUM-86236	Priority medium Job	High priority urgent job	in_progress	1	\N	\N	\N	\N	2026-10-15 18:15:46.36132	2026-10-15 18:15:46.36132	1	12	1	\N	pending	\N	\N	0	1	0.00
42	JOB-TRANS-draft-001	Test draft	Status transition test	assigned	1	\N	\N	\N	\N	2026-10-15 18:21:10.104516	2026-10-15 18:21:10.104516	1	1	1	\N	pending	\N	\N	0	1	0.00
43	JOB-TRANS-assigned-001	Test assigned	Status transition test	draft	1	\N	\N	\N	\N	2026-10-15 18:21:10.110374	2026-10-15 18:21:10.110374	1	1	1	\N	pending	\N	\N	0	1	0.00
44	JOB-TRANS-in_progress-001	Test in_progress	Status transition test	assigned	1	\N	\N	\N	\N	2026-10-15 18:21:10.11497	2026-10-15 18:21:10.11497	1	1	1	\N	pending	\N	\N	0	1	0.00
45	JOB-TRANS-ready_for_qc-001	Test ready_for_qc	Status transition test	in_progress	1	\N	\N	\N	\N	2026-10-15 18:21:10.119397	2026-10-15 18:21:10.119397	1	1	1	\N	pending	\N	\N	0	1	0.00
36	JOB-PRIO-LOW-12990	Priority low Job	High priority urgent job	in_progress	1	\N	\N	\N	\N	2026-10-15 18:15:46.362595	2026-10-15 18:15:46.362595	1	12	1	\N	pending	\N	\N	0	1	0.00
46	JOB-TRANS-completed-001	Test completed	Status transition test	completed	1	\N	\N	\N	\N	2026-10-15 18:21:10.127452	2026-10-15 18:21:10.127452	1	1	1	\N	pending	\N	\N	0	1	0.00
48	JOB-INVALID-261367	invalid_worktype Job	Test	draft	1	\N	\N	\N	\N	2026-10-15 18:25:11.725232	2026-10-15 18:25:11.725232	1	1	1	\N	pending	\N	\N	0	1	0.00
338	JOB-1792567713040819	vehicle_design Job 1792567713040819	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.308812	2026-10-21 07:28:33.308812	1	\N	\N	\N	pending	\N	\N	0	2	\N
49	JOB-TRANS-draft-17289	Test draft	Status transition test	assigned	1	\N	\N	\N	\N	2026-10-15 18:25:12.356426	2026-10-15 18:25:12.356426	1	1	1	\N	pending	\N	\N	0	1	0.00
339	JOB-1792567713040820	vehicle_design Job 1792567713040820	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.322162	2026-10-21 07:28:33.322162	1	\N	\N	\N	pending	\N	\N	0	2	\N
340	JOB-1792567713040821	vehicle_design Job 1792567713040821	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.328691	2026-10-21 07:28:33.328691	1	\N	\N	\N	pending	\N	\N	0	2	\N
50	JOB-TRANS-assigned-60693	Test assigned	Status transition test	draft	1	\N	\N	\N	\N	2026-10-15 18:25:12.359457	2026-10-15 18:25:12.359457	1	1	1	\N	pending	\N	\N	0	1	0.00
341	JOB-1792567713040822	vehicle_design Job 1792567713040822	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.341142	2026-10-21 07:28:33.341142	1	\N	\N	\N	pending	\N	\N	0	2	\N
342	JOB-1792567713040823	vehicle_design Job 1792567713040823	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.352728	2026-10-21 07:28:33.352728	1	\N	\N	\N	pending	\N	\N	0	2	\N
51	JOB-TRANS-in_progress-45932	Test in_progress	Status transition test	assigned	1	\N	\N	\N	\N	2026-10-15 18:25:12.36316	2026-10-15 18:25:12.36316	1	1	1	\N	pending	\N	\N	0	1	0.00
343	JOB-1792567713040824	vehicle_design Job 1792567713040824	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.361125	2026-10-21 07:28:33.361125	1	\N	\N	\N	pending	\N	\N	0	2	\N
344	JOB-1792567713040825	vehicle_manufacturing Job 1792567713040825	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.373406	2026-10-21 07:28:33.373406	1	\N	\N	\N	pending	\N	\N	0	2	\N
52	JOB-TRANS-ready_for_qc-75031	Test ready_for_qc	Status transition test	in_progress	1	\N	\N	\N	\N	2026-10-15 18:25:12.370267	2026-10-15 18:25:12.370267	1	1	1	\N	pending	\N	\N	0	1	0.00
53	JOB-TRANS-completed-72611	Test completed	Status transition test	completed	1	\N	\N	\N	\N	2026-10-15 18:25:12.374371	2026-10-15 18:25:12.374371	1	1	1	\N	pending	\N	\N	0	1	0.00
54	JOB-INVALID-872364	invalid_worktype Job	Test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:34.286408	2026-10-15 18:27:34.286408	1	1	1	\N	pending	\N	\N	0	1	0.00
345	JOB-1792567713040826	vehicle_manufacturing Job 1792567713040826	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.38143	2026-10-21 07:28:33.38143	1	\N	\N	\N	pending	\N	\N	0	2	\N
55	JOB-TRANS-draft-81527	Test draft	Status transition test	assigned	1	\N	\N	\N	\N	2026-10-15 18:27:35.48379	2026-10-15 18:27:35.48379	1	1	1	\N	pending	\N	\N	0	1	0.00
346	JOB-1792567713040827	vehicle_manufacturing Job 1792567713040827	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.391401	2026-10-21 07:28:33.391401	1	\N	\N	\N	pending	\N	\N	0	2	\N
347	JOB-1792567713040828	vehicle_manufacturing Job 1792567713040828	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.402192	2026-10-21 07:28:33.402192	1	\N	\N	\N	pending	\N	\N	0	2	\N
56	JOB-TRANS-assigned-52499	Test assigned	Status transition test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.486921	2026-10-15 18:27:35.486921	1	1	1	\N	pending	\N	\N	0	1	0.00
348	JOB-1792567713040829	vehicle_manufacturing Job 1792567713040829	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.40891	2026-10-21 07:28:33.40891	1	\N	\N	\N	pending	\N	\N	0	2	\N
349	JOB-1792567713040830	vehicle_manufacturing Job 1792567713040830	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.417228	2026-10-21 07:28:33.417228	1	\N	\N	\N	pending	\N	\N	0	2	\N
57	JOB-TRANS-in_progress-86027	Test in_progress	Status transition test	assigned	1	\N	\N	\N	\N	2026-10-15 18:27:35.490761	2026-10-15 18:27:35.490761	1	1	1	\N	pending	\N	\N	0	1	0.00
350	JOB-1792567713040831	vehicle_manufacturing Job 1792567713040831	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.425241	2026-10-21 07:28:33.425241	1	\N	\N	\N	pending	\N	\N	0	2	\N
351	JOB-1792567713040832	quality_control Job 1792567713040832	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.432775	2026-10-21 07:28:33.432775	1	\N	\N	\N	pending	\N	\N	0	2	\N
58	JOB-TRANS-ready_for_qc-43562	Test ready_for_qc	Status transition test	in_progress	1	\N	\N	\N	\N	2026-10-15 18:27:35.493985	2026-10-15 18:27:35.493985	1	1	1	\N	pending	\N	\N	0	1	0.00
59	JOB-TRANS-completed-13758	Test completed	Status transition test	completed	1	\N	\N	\N	\N	2026-10-15 18:27:35.50144	2026-10-15 18:27:35.50144	1	1	1	\N	pending	\N	\N	0	1	0.00
352	JOB-1792567713040833	quality_control Job 1792567713040833	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.443284	2026-10-21 07:28:33.443284	1	\N	\N	\N	pending	\N	\N	0	2	\N
60	JOB-ARCHIVE-73014	Archive Test	Test archive	archived	1	\N	\N	\N	\N	2026-10-15 18:27:35.510677	2026-10-15 18:27:35.510677	1	1	1	\N	pending	\N	\N	0	1	0.00
61	JOB-DUPLICATE-667372	Test	Test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.535598	2026-10-15 18:27:35.535598	1	1	1	\N	pending	\N	\N	0	1	0.00
63	JOB-BULK-1092414	Bulk Job 1	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
64	JOB-BULK-8356075	Bulk Job 2	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
65	JOB-BULK-7424455	Bulk Job 3	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
66	JOB-BULK-6108178	Bulk Job 4	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
67	JOB-BULK-3573201	Bulk Job 5	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
68	JOB-BULK-8065954	Bulk Job 6	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
69	JOB-BULK-4592731	Bulk Job 7	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
70	JOB-BULK-7423586	Bulk Job 8	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
71	JOB-BULK-2522253	Bulk Job 9	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
72	JOB-BULK-4303301	Bulk Job 10	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
73	JOB-BULK-4813566	Bulk Job 11	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
74	JOB-BULK-9399639	Bulk Job 12	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
75	JOB-BULK-1874475	Bulk Job 13	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
76	JOB-BULK-3342821	Bulk Job 14	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
77	JOB-BULK-8483449	Bulk Job 15	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
78	JOB-BULK-4314783	Bulk Job 16	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
79	JOB-BULK-8595837	Bulk Job 17	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
80	JOB-BULK-2898856	Bulk Job 18	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
81	JOB-BULK-2825103	Bulk Job 19	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
82	JOB-BULK-1127335	Bulk Job 20	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
83	JOB-BULK-9781298	Bulk Job 21	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
84	JOB-BULK-5169393	Bulk Job 22	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
85	JOB-BULK-6025996	Bulk Job 23	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
86	JOB-BULK-6637668	Bulk Job 24	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
87	JOB-BULK-3278532	Bulk Job 25	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
88	JOB-BULK-1279469	Bulk Job 26	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
89	JOB-BULK-6227169	Bulk Job 27	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
90	JOB-BULK-1696955	Bulk Job 28	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
91	JOB-BULK-8061341	Bulk Job 29	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
92	JOB-BULK-8451136	Bulk Job 30	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
93	JOB-BULK-5006583	Bulk Job 31	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
94	JOB-BULK-2278637	Bulk Job 32	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
95	JOB-BULK-1966672	Bulk Job 33	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
96	JOB-BULK-2911612	Bulk Job 34	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
97	JOB-BULK-5437417	Bulk Job 35	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
98	JOB-BULK-4860373	Bulk Job 36	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
99	JOB-BULK-4336086	Bulk Job 37	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
100	JOB-BULK-7693972	Bulk Job 38	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
101	JOB-BULK-4481967	Bulk Job 39	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
102	JOB-BULK-1191046	Bulk Job 40	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
103	JOB-BULK-4553709	Bulk Job 41	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
104	JOB-BULK-2148269	Bulk Job 42	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
105	JOB-BULK-6352044	Bulk Job 43	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
106	JOB-BULK-8962047	Bulk Job 44	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
107	JOB-BULK-8107648	Bulk Job 45	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
108	JOB-BULK-1054847	Bulk Job 46	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
109	JOB-BULK-2796707	Bulk Job 47	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
110	JOB-BULK-2929635	Bulk Job 48	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
111	JOB-BULK-6485959	Bulk Job 49	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
112	JOB-BULK-7203096	Bulk Job 50	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
113	JOB-BULK-2690987	Bulk Job 51	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
114	JOB-BULK-3251222	Bulk Job 52	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
115	JOB-BULK-9628546	Bulk Job 53	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
116	JOB-BULK-9933049	Bulk Job 54	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
117	JOB-BULK-6665515	Bulk Job 55	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
118	JOB-BULK-9126133	Bulk Job 56	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
119	JOB-BULK-7351361	Bulk Job 57	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
120	JOB-BULK-3906887	Bulk Job 58	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
121	JOB-BULK-1825924	Bulk Job 59	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
122	JOB-BULK-8785537	Bulk Job 60	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
123	JOB-BULK-3898658	Bulk Job 61	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
124	JOB-BULK-8561648	Bulk Job 62	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
125	JOB-BULK-9627113	Bulk Job 63	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
126	JOB-BULK-5224086	Bulk Job 64	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
127	JOB-BULK-8845978	Bulk Job 65	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
128	JOB-BULK-5893845	Bulk Job 66	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
129	JOB-BULK-3412258	Bulk Job 67	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
130	JOB-BULK-5393661	Bulk Job 68	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
131	JOB-BULK-7480122	Bulk Job 69	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
132	JOB-BULK-6332401	Bulk Job 70	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
133	JOB-BULK-8574568	Bulk Job 71	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
134	JOB-BULK-5745378	Bulk Job 72	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
135	JOB-BULK-6934974	Bulk Job 73	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
136	JOB-BULK-3640833	Bulk Job 74	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
137	JOB-BULK-8096606	Bulk Job 75	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
138	JOB-BULK-8090111	Bulk Job 76	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
139	JOB-BULK-2398042	Bulk Job 77	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
140	JOB-BULK-8723522	Bulk Job 78	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
141	JOB-BULK-8372421	Bulk Job 79	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
142	JOB-BULK-8210411	Bulk Job 80	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
143	JOB-BULK-9908693	Bulk Job 81	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
144	JOB-BULK-6994753	Bulk Job 82	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
145	JOB-BULK-2048894	Bulk Job 83	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
146	JOB-BULK-9112459	Bulk Job 84	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
147	JOB-BULK-2176461	Bulk Job 85	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
148	JOB-BULK-9538532	Bulk Job 86	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
149	JOB-BULK-8511061	Bulk Job 87	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
150	JOB-BULK-1328163	Bulk Job 88	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
151	JOB-BULK-3120684	Bulk Job 89	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
152	JOB-BULK-6016742	Bulk Job 90	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
153	JOB-BULK-3276279	Bulk Job 91	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
154	JOB-BULK-5717056	Bulk Job 92	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
155	JOB-BULK-2313882	Bulk Job 93	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
156	JOB-BULK-1672798	Bulk Job 94	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
157	JOB-BULK-7708608	Bulk Job 95	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
158	JOB-BULK-2750426	Bulk Job 96	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
159	JOB-BULK-7288920	Bulk Job 97	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
160	JOB-BULK-9727349	Bulk Job 98	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
161	JOB-BULK-5617080	Bulk Job 99	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
162	JOB-BULK-1376354	Bulk Job 100	Performance test	draft	1	\N	\N	\N	\N	2026-10-15 18:27:35.538859	2026-10-15 18:27:35.538859	1	1	1	\N	pending	\N	\N	0	1	0.00
353	JOB-1792567713040834	quality_control Job 1792567713040834	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.456281	2026-10-21 07:28:33.456281	1	\N	\N	\N	pending	\N	\N	0	2	\N
166	JOB-10002	vehicle_design Job 10002	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.537027	2026-10-21 07:03:50.537027	1	\N	\N	\N	pending	\N	\N	0	2	\N
167	JOB-10003	vehicle_design Job 10003	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.544802	2026-10-21 07:03:50.544802	1	\N	\N	\N	pending	\N	\N	0	2	\N
168	JOB-10004	vehicle_design Job 10004	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.554378	2026-10-21 07:03:50.554378	1	\N	\N	\N	pending	\N	\N	0	2	\N
169	JOB-10005	vehicle_design Job 10005	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.568098	2026-10-21 07:03:50.568098	1	\N	\N	\N	pending	\N	\N	0	2	\N
170	JOB-10006	vehicle_design Job 10006	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.582961	2026-10-21 07:03:50.582961	1	\N	\N	\N	pending	\N	\N	0	2	\N
171	JOB-10007	vehicle_design Job 10007	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.590059	2026-10-21 07:03:50.590059	1	\N	\N	\N	pending	\N	\N	0	2	\N
172	JOB-10008	vehicle_manufacturing Job 10008	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.599927	2026-10-21 07:03:50.599927	1	\N	\N	\N	pending	\N	\N	0	2	\N
173	JOB-10009	vehicle_manufacturing Job 10009	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.606232	2026-10-21 07:03:50.606232	1	\N	\N	\N	pending	\N	\N	0	2	\N
174	JOB-10010	vehicle_manufacturing Job 10010	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.616055	2026-10-21 07:03:50.616055	1	\N	\N	\N	pending	\N	\N	0	2	\N
175	JOB-10011	vehicle_manufacturing Job 10011	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.622281	2026-10-21 07:03:50.622281	1	\N	\N	\N	pending	\N	\N	0	2	\N
176	JOB-10012	vehicle_manufacturing Job 10012	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.629975	2026-10-21 07:03:50.629975	1	\N	\N	\N	pending	\N	\N	0	2	\N
177	JOB-10013	vehicle_manufacturing Job 10013	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.637747	2026-10-21 07:03:50.637747	1	\N	\N	\N	pending	\N	\N	0	2	\N
178	JOB-10014	vehicle_manufacturing Job 10014	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.643655	2026-10-21 07:03:50.643655	1	\N	\N	\N	pending	\N	\N	0	2	\N
179	JOB-10015	quality_control Job 10015	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.653016	2026-10-21 07:03:50.653016	1	\N	\N	\N	pending	\N	\N	0	2	\N
180	JOB-10016	quality_control Job 10016	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.658819	2026-10-21 07:03:50.658819	1	\N	\N	\N	pending	\N	\N	0	2	\N
181	JOB-10017	quality_control Job 10017	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.67294	2026-10-21 07:03:50.67294	1	\N	\N	\N	pending	\N	\N	0	2	\N
182	JOB-10018	quality_control Job 10018	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.682592	2026-10-21 07:03:50.682592	1	\N	\N	\N	pending	\N	\N	0	2	\N
183	JOB-10019	quality_control Job 10019	\N	draft	1	26	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.689198	2026-10-21 07:03:50.689198	1	\N	\N	\N	pending	\N	\N	0	2	\N
184	JOB-10020	quality_control Job 10020	\N	draft	1	2	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.700972	2026-10-21 07:03:50.700972	1	\N	\N	\N	pending	\N	\N	0	2	\N
185	JOB-10021	quality_control Job 10021	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.707087	2026-10-21 07:03:50.707087	1	\N	\N	\N	pending	\N	\N	0	2	\N
186	JOB-10022	maintenance Job 10022	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.717166	2026-10-21 07:03:50.717166	1	\N	\N	\N	pending	\N	\N	0	2	\N
187	JOB-10023	maintenance Job 10023	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.723091	2026-10-21 07:03:50.723091	1	\N	\N	\N	pending	\N	\N	0	2	\N
188	JOB-10024	maintenance Job 10024	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.732894	2026-10-21 07:03:50.732894	1	\N	\N	\N	pending	\N	\N	0	2	\N
189	JOB-10025	maintenance Job 10025	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.739242	2026-10-21 07:03:50.739242	1	\N	\N	\N	pending	\N	\N	0	2	\N
190	JOB-10026	maintenance Job 10026	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.749126	2026-10-21 07:03:50.749126	1	\N	\N	\N	pending	\N	\N	0	2	\N
191	JOB-10027	maintenance Job 10027	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.755351	2026-10-21 07:03:50.755351	1	\N	\N	\N	pending	\N	\N	0	2	\N
192	JOB-10028	maintenance Job 10028	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.762715	2026-10-21 07:03:50.762715	1	\N	\N	\N	pending	\N	\N	0	2	\N
193	JOB-10029	assembly Job 10029	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.770741	2026-10-21 07:03:50.770741	1	\N	\N	\N	pending	\N	\N	0	2	\N
194	JOB-10030	assembly Job 10030	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.779085	2026-10-21 07:03:50.779085	1	\N	\N	\N	pending	\N	\N	0	2	\N
195	JOB-10031	assembly Job 10031	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.78801	2026-10-21 07:03:50.78801	1	\N	\N	\N	pending	\N	\N	0	2	\N
196	JOB-10032	assembly Job 10032	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.795083	2026-10-21 07:03:50.795083	1	\N	\N	\N	pending	\N	\N	0	2	\N
197	JOB-10033	assembly Job 10033	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.805474	2026-10-21 07:03:50.805474	1	\N	\N	\N	pending	\N	\N	0	2	\N
198	JOB-10034	assembly Job 10034	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.81649	2026-10-21 07:03:50.81649	1	\N	\N	\N	pending	\N	\N	0	2	\N
199	JOB-10035	assembly Job 10035	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.823493	2026-10-21 07:03:50.823493	1	\N	\N	\N	pending	\N	\N	0	2	\N
200	JOB-10036	testing Job 10036	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.8359	2026-10-21 07:03:50.8359	1	\N	\N	\N	pending	\N	\N	0	2	\N
201	JOB-10037	testing Job 10037	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.841854	2026-10-21 07:03:50.841854	1	\N	\N	\N	pending	\N	\N	0	2	\N
202	JOB-10038	testing Job 10038	\N	draft	1	26	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.854299	2026-10-21 07:03:50.854299	1	\N	\N	\N	pending	\N	\N	0	2	\N
203	JOB-10039	testing Job 10039	\N	draft	1	2	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.861014	2026-10-21 07:03:50.861014	1	\N	\N	\N	pending	\N	\N	0	2	\N
204	JOB-10040	testing Job 10040	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.870867	2026-10-21 07:03:50.870867	1	\N	\N	\N	pending	\N	\N	0	2	\N
205	JOB-10041	testing Job 10041	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.877974	2026-10-21 07:03:50.877974	1	\N	\N	\N	pending	\N	\N	0	2	\N
206	JOB-10042	testing Job 10042	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.886073	2026-10-21 07:03:50.886073	1	\N	\N	\N	pending	\N	\N	0	2	\N
207	JOB-10043	logistics Job 10043	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.893364	2026-10-21 07:03:50.893364	1	\N	\N	\N	pending	\N	\N	0	2	\N
208	JOB-10044	logistics Job 10044	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.903629	2026-10-21 07:03:50.903629	1	\N	\N	\N	pending	\N	\N	0	2	\N
209	JOB-10045	logistics Job 10045	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.912377	2026-10-21 07:03:50.912377	1	\N	\N	\N	pending	\N	\N	0	2	\N
210	JOB-10046	logistics Job 10046	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.920967	2026-10-21 07:03:50.920967	1	\N	\N	\N	pending	\N	\N	0	2	\N
211	JOB-10047	logistics Job 10047	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.932211	2026-10-21 07:03:50.932211	1	\N	\N	\N	pending	\N	\N	0	2	\N
212	JOB-10048	logistics Job 10048	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.939111	2026-10-21 07:03:50.939111	1	\N	\N	\N	pending	\N	\N	0	2	\N
213	JOB-10049	logistics Job 10049	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.945969	2026-10-21 07:03:50.945969	1	\N	\N	\N	pending	\N	\N	0	2	\N
214	JOB-10050	production_planning Job 10050	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.954319	2026-10-21 07:03:50.954319	1	\N	\N	\N	pending	\N	\N	0	2	\N
215	JOB-10051	production_planning Job 10051	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.961362	2026-10-21 07:03:50.961362	1	\N	\N	\N	pending	\N	\N	0	2	\N
216	JOB-10052	production_planning Job 10052	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.971534	2026-10-21 07:03:50.971534	1	\N	\N	\N	pending	\N	\N	0	2	\N
217	JOB-10053	production_planning Job 10053	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.979992	2026-10-21 07:03:50.979992	1	\N	\N	\N	pending	\N	\N	0	2	\N
218	JOB-10054	production_planning Job 10054	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.987934	2026-10-21 07:03:50.987934	1	\N	\N	\N	pending	\N	\N	0	2	\N
219	JOB-10055	production_planning Job 10055	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:50.994519	2026-10-21 07:03:50.994519	1	\N	\N	\N	pending	\N	\N	0	2	\N
220	JOB-10056	production_planning Job 10056	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:51.003761	2026-10-21 07:03:51.003761	1	\N	\N	\N	pending	\N	\N	0	2	\N
354	JOB-1792567713040835	quality_control Job 1792567713040835	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.466513	2026-10-21 07:28:33.466513	1	\N	\N	\N	pending	\N	\N	0	2	\N
355	JOB-1792567713040836	quality_control Job 1792567713040836	\N	draft	1	26	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.475513	2026-10-21 07:28:33.475513	1	\N	\N	\N	pending	\N	\N	0	2	\N
356	JOB-1792567713040837	quality_control Job 1792567713040837	\N	draft	1	2	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.484159	2026-10-21 07:28:33.484159	1	\N	\N	\N	pending	\N	\N	0	2	\N
357	JOB-1792567713040838	quality_control Job 1792567713040838	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.491404	2026-10-21 07:28:33.491404	1	\N	\N	\N	pending	\N	\N	0	2	\N
358	JOB-1792567713040839	maintenance Job 1792567713040839	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.504622	2026-10-21 07:28:33.504622	1	\N	\N	\N	pending	\N	\N	0	2	\N
359	JOB-1792567713040840	maintenance Job 1792567713040840	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.511438	2026-10-21 07:28:33.511438	1	\N	\N	\N	pending	\N	\N	0	2	\N
360	JOB-1792567713040841	maintenance Job 1792567713040841	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.523105	2026-10-21 07:28:33.523105	1	\N	\N	\N	pending	\N	\N	0	2	\N
361	JOB-1792567713040842	maintenance Job 1792567713040842	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.528596	2026-10-21 07:28:33.528596	1	\N	\N	\N	pending	\N	\N	0	2	\N
362	JOB-1792567713040843	maintenance Job 1792567713040843	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.540211	2026-10-21 07:28:33.540211	1	\N	\N	\N	pending	\N	\N	0	2	\N
363	JOB-1792567713040844	maintenance Job 1792567713040844	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.554534	2026-10-21 07:28:33.554534	1	\N	\N	\N	pending	\N	\N	0	2	\N
364	JOB-1792567713040845	maintenance Job 1792567713040845	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.56154	2026-10-21 07:28:33.56154	1	\N	\N	\N	pending	\N	\N	0	2	\N
165	JOB-10001	vehicle_design Job 10001 B	\N	not_a_real_status	1	7	3	2026-10-21 00:00:00	3	2026-10-21 07:03:50.049926	2026-10-21 07:03:50.049926	1	\N	\N	\N	approved	Needs rework	\N	1	2	\N
221	JOB-99999999	Dup Check	\N	draft	8	2	\N	2026-10-21 00:00:00	1	2026-10-21 07:03:52.135092	2026-10-21 07:03:52.135092	1	\N	\N	\N	pending	\N	\N	0	2	\N
365	JOB-1792567713040846	assembly Job 1792567713040846	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.57326	2026-10-21 07:28:33.57326	1	\N	\N	\N	pending	\N	\N	0	2	\N
366	JOB-1792567713040847	assembly Job 1792567713040847	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.580241	2026-10-21 07:28:33.580241	1	\N	\N	\N	pending	\N	\N	0	2	\N
367	JOB-1792567713040848	assembly Job 1792567713040848	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.590104	2026-10-21 07:28:33.590104	1	\N	\N	\N	pending	\N	\N	0	2	\N
368	JOB-1792567713040849	assembly Job 1792567713040849	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.597442	2026-10-21 07:28:33.597442	1	\N	\N	\N	pending	\N	\N	0	2	\N
369	JOB-1792567713040850	assembly Job 1792567713040850	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.606641	2026-10-21 07:28:33.606641	1	\N	\N	\N	pending	\N	\N	0	2	\N
370	JOB-1792567713040851	assembly Job 1792567713040851	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.614174	2026-10-21 07:28:33.614174	1	\N	\N	\N	pending	\N	\N	0	2	\N
371	JOB-1792567713040852	assembly Job 1792567713040852	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.625236	2026-10-21 07:28:33.625236	1	\N	\N	\N	pending	\N	\N	0	2	\N
372	JOB-1792567713040853	testing Job 1792567713040853	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.638212	2026-10-21 07:28:33.638212	1	\N	\N	\N	pending	\N	\N	0	2	\N
373	JOB-1792567713040854	testing Job 1792567713040854	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.643723	2026-10-21 07:28:33.643723	1	\N	\N	\N	pending	\N	\N	0	2	\N
374	JOB-1792567713040855	testing Job 1792567713040855	\N	draft	1	26	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.657006	2026-10-21 07:28:33.657006	1	\N	\N	\N	pending	\N	\N	0	2	\N
375	JOB-1792567713040856	testing Job 1792567713040856	\N	draft	1	2	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.662015	2026-10-21 07:28:33.662015	1	\N	\N	\N	pending	\N	\N	0	2	\N
376	JOB-1792567713040857	testing Job 1792567713040857	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.67518	2026-10-21 07:28:33.67518	1	\N	\N	\N	pending	\N	\N	0	2	\N
377	JOB-1792567713040858	testing Job 1792567713040858	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.682642	2026-10-21 07:28:33.682642	1	\N	\N	\N	pending	\N	\N	0	2	\N
378	JOB-1792567713040859	testing Job 1792567713040859	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.691098	2026-10-21 07:28:33.691098	1	\N	\N	\N	pending	\N	\N	0	2	\N
379	JOB-1792567713040860	logistics Job 1792567713040860	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.704404	2026-10-21 07:28:33.704404	1	\N	\N	\N	pending	\N	\N	0	2	\N
380	JOB-1792567713040861	logistics Job 1792567713040861	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.71022	2026-10-21 07:28:33.71022	1	\N	\N	\N	pending	\N	\N	0	2	\N
381	JOB-1792567713040862	logistics Job 1792567713040862	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.721421	2026-10-21 07:28:33.721421	1	\N	\N	\N	pending	\N	\N	0	2	\N
382	JOB-1792567713040863	logistics Job 1792567713040863	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.726826	2026-10-21 07:28:33.726826	1	\N	\N	\N	pending	\N	\N	0	2	\N
383	JOB-1792567713040864	logistics Job 1792567713040864	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.736985	2026-10-21 07:28:33.736985	1	\N	\N	\N	pending	\N	\N	0	2	\N
384	JOB-1792567713040865	logistics Job 1792567713040865	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.742853	2026-10-21 07:28:33.742853	1	\N	\N	\N	pending	\N	\N	0	2	\N
385	JOB-1792567713040866	logistics Job 1792567713040866	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.752964	2026-10-21 07:28:33.752964	1	\N	\N	\N	pending	\N	\N	0	2	\N
386	JOB-1792567713040867	production_planning Job 1792567713040867	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.759196	2026-10-21 07:28:33.759196	1	\N	\N	\N	pending	\N	\N	0	2	\N
387	JOB-1792567713040868	production_planning Job 1792567713040868	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.769536	2026-10-21 07:28:33.769536	1	\N	\N	\N	pending	\N	\N	0	2	\N
388	JOB-1792567713040869	production_planning Job 1792567713040869	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.775665	2026-10-21 07:28:33.775665	1	\N	\N	\N	pending	\N	\N	0	2	\N
389	JOB-1792567713040870	production_planning Job 1792567713040870	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.782743	2026-10-21 07:28:33.782743	1	\N	\N	\N	pending	\N	\N	0	2	\N
390	JOB-1792567713040871	production_planning Job 1792567713040871	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.791307	2026-10-21 07:28:33.791307	1	\N	\N	\N	pending	\N	\N	0	2	\N
391	JOB-1792567713040872	production_planning Job 1792567713040872	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.795943	2026-10-21 07:28:33.795943	1	\N	\N	\N	pending	\N	\N	0	2	\N
392	JOB-1792567713040873	production_planning Job 1792567713040873	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:28:33.806444	2026-10-21 07:28:33.806444	1	\N	\N	\N	pending	\N	\N	0	2	\N
337	JOB-1792567713040818	vehicle_design Job 1792567713040818 B	\N	not_a_real_status	1	7	3	2026-10-21 00:00:00	3	2026-10-21 07:28:33.211694	2026-10-21 07:28:33.211694	1	\N	\N	\N	approved	Needs rework	\N	1	2	\N
281	JOB-1792566574740260	vehicle_design Job 1792566574740260	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:34.954245	2026-10-21 07:09:34.954245	1	\N	\N	\N	pending	\N	\N	0	2	\N
282	JOB-1792566574740261	vehicle_design Job 1792566574740261	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:34.969542	2026-10-21 07:09:34.969542	1	\N	\N	\N	pending	\N	\N	0	2	\N
283	JOB-1792566574740262	vehicle_design Job 1792566574740262	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:34.984584	2026-10-21 07:09:34.984584	1	\N	\N	\N	pending	\N	\N	0	2	\N
284	JOB-1792566574740263	vehicle_design Job 1792566574740263	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:34.991248	2026-10-21 07:09:34.991248	1	\N	\N	\N	pending	\N	\N	0	2	\N
285	JOB-1792566574740264	vehicle_design Job 1792566574740264	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.005801	2026-10-21 07:09:35.005801	1	\N	\N	\N	pending	\N	\N	0	2	\N
286	JOB-1792566574740265	vehicle_design Job 1792566574740265	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.019565	2026-10-21 07:09:35.019565	1	\N	\N	\N	pending	\N	\N	0	2	\N
287	JOB-1792566574740266	vehicle_manufacturing Job 1792566574740266	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.034285	2026-10-21 07:09:35.034285	1	\N	\N	\N	pending	\N	\N	0	2	\N
288	JOB-1792566574740267	vehicle_manufacturing Job 1792566574740267	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.041131	2026-10-21 07:09:35.041131	1	\N	\N	\N	pending	\N	\N	0	2	\N
289	JOB-1792566574740268	vehicle_manufacturing Job 1792566574740268	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.054316	2026-10-21 07:09:35.054316	1	\N	\N	\N	pending	\N	\N	0	2	\N
290	JOB-1792566574740269	vehicle_manufacturing Job 1792566574740269	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.0682	2026-10-21 07:09:35.0682	1	\N	\N	\N	pending	\N	\N	0	2	\N
291	JOB-1792566574740270	vehicle_manufacturing Job 1792566574740270	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.074129	2026-10-21 07:09:35.074129	1	\N	\N	\N	pending	\N	\N	0	2	\N
292	JOB-1792566574740271	vehicle_manufacturing Job 1792566574740271	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.090035	2026-10-21 07:09:35.090035	1	\N	\N	\N	pending	\N	\N	0	2	\N
293	JOB-1792566574740272	vehicle_manufacturing Job 1792566574740272	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.10441	2026-10-21 07:09:35.10441	1	\N	\N	\N	pending	\N	\N	0	2	\N
294	JOB-1792566574740273	quality_control Job 1792566574740273	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.120982	2026-10-21 07:09:35.120982	1	\N	\N	\N	pending	\N	\N	0	2	\N
295	JOB-1792566574740274	quality_control Job 1792566574740274	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.138205	2026-10-21 07:09:35.138205	1	\N	\N	\N	pending	\N	\N	0	2	\N
296	JOB-1792566574740275	quality_control Job 1792566574740275	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.151392	2026-10-21 07:09:35.151392	1	\N	\N	\N	pending	\N	\N	0	2	\N
297	JOB-1792566574740276	quality_control Job 1792566574740276	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.157128	2026-10-21 07:09:35.157128	1	\N	\N	\N	pending	\N	\N	0	2	\N
298	JOB-1792566574740277	quality_control Job 1792566574740277	\N	draft	1	26	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.170708	2026-10-21 07:09:35.170708	1	\N	\N	\N	pending	\N	\N	0	2	\N
299	JOB-1792566574740278	quality_control Job 1792566574740278	\N	draft	1	2	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.184131	2026-10-21 07:09:35.184131	1	\N	\N	\N	pending	\N	\N	0	2	\N
300	JOB-1792566574740279	quality_control Job 1792566574740279	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.19007	2026-10-21 07:09:35.19007	1	\N	\N	\N	pending	\N	\N	0	2	\N
301	JOB-1792566574740280	maintenance Job 1792566574740280	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.202245	2026-10-21 07:09:35.202245	1	\N	\N	\N	pending	\N	\N	0	2	\N
302	JOB-1792566574740281	maintenance Job 1792566574740281	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.207574	2026-10-21 07:09:35.207574	1	\N	\N	\N	pending	\N	\N	0	2	\N
303	JOB-1792566574740282	maintenance Job 1792566574740282	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.218121	2026-10-21 07:09:35.218121	1	\N	\N	\N	pending	\N	\N	0	2	\N
304	JOB-1792566574740283	maintenance Job 1792566574740283	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.223282	2026-10-21 07:09:35.223282	1	\N	\N	\N	pending	\N	\N	0	2	\N
305	JOB-1792566574740284	maintenance Job 1792566574740284	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.233329	2026-10-21 07:09:35.233329	1	\N	\N	\N	pending	\N	\N	0	2	\N
306	JOB-1792566574740285	maintenance Job 1792566574740285	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.238521	2026-10-21 07:09:35.238521	1	\N	\N	\N	pending	\N	\N	0	2	\N
307	JOB-1792566574740286	maintenance Job 1792566574740286	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.248998	2026-10-21 07:09:35.248998	1	\N	\N	\N	pending	\N	\N	0	2	\N
308	JOB-1792566574740287	assembly Job 1792566574740287	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.254753	2026-10-21 07:09:35.254753	1	\N	\N	\N	pending	\N	\N	0	2	\N
309	JOB-1792566574740288	assembly Job 1792566574740288	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.26169	2026-10-21 07:09:35.26169	1	\N	\N	\N	pending	\N	\N	0	2	\N
310	JOB-1792566574740289	assembly Job 1792566574740289	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.26899	2026-10-21 07:09:35.26899	1	\N	\N	\N	pending	\N	\N	0	2	\N
311	JOB-1792566574740290	assembly Job 1792566574740290	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.273909	2026-10-21 07:09:35.273909	1	\N	\N	\N	pending	\N	\N	0	2	\N
312	JOB-1792566574740291	assembly Job 1792566574740291	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.283358	2026-10-21 07:09:35.283358	1	\N	\N	\N	pending	\N	\N	0	2	\N
313	JOB-1792566574740292	assembly Job 1792566574740292	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.288233	2026-10-21 07:09:35.288233	1	\N	\N	\N	pending	\N	\N	0	2	\N
314	JOB-1792566574740293	assembly Job 1792566574740293	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.297285	2026-10-21 07:09:35.297285	1	\N	\N	\N	pending	\N	\N	0	2	\N
315	JOB-1792566574740294	testing Job 1792566574740294	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.303018	2026-10-21 07:09:35.303018	1	\N	\N	\N	pending	\N	\N	0	2	\N
316	JOB-1792566574740295	testing Job 1792566574740295	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.307754	2026-10-21 07:09:35.307754	1	\N	\N	\N	pending	\N	\N	0	2	\N
317	JOB-1792566574740296	testing Job 1792566574740296	\N	draft	1	26	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.316396	2026-10-21 07:09:35.316396	1	\N	\N	\N	pending	\N	\N	0	2	\N
318	JOB-1792566574740297	testing Job 1792566574740297	\N	draft	1	2	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.320859	2026-10-21 07:09:35.320859	1	\N	\N	\N	pending	\N	\N	0	2	\N
319	JOB-1792566574740298	testing Job 1792566574740298	\N	draft	1	7	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.327306	2026-10-21 07:09:35.327306	1	\N	\N	\N	pending	\N	\N	0	2	\N
320	JOB-1792566574740299	testing Job 1792566574740299	\N	draft	1	6	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.335708	2026-10-21 07:09:35.335708	1	\N	\N	\N	pending	\N	\N	0	2	\N
321	JOB-1792566574740300	testing Job 1792566574740300	\N	draft	1	5	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.340933	2026-10-21 07:09:35.340933	1	\N	\N	\N	pending	\N	\N	0	2	\N
322	JOB-1792566574740301	logistics Job 1792566574740301	\N	draft	1	10	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.350107	2026-10-21 07:09:35.350107	1	\N	\N	\N	pending	\N	\N	0	2	\N
323	JOB-1792566574740302	logistics Job 1792566574740302	\N	draft	1	11	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.355209	2026-10-21 07:09:35.355209	1	\N	\N	\N	pending	\N	\N	0	2	\N
324	JOB-1792566574740303	logistics Job 1792566574740303	\N	draft	1	12	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.363217	2026-10-21 07:09:35.363217	1	\N	\N	\N	pending	\N	\N	0	2	\N
325	JOB-1792566574740304	logistics Job 1792566574740304	\N	draft	1	13	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.369644	2026-10-21 07:09:35.369644	1	\N	\N	\N	pending	\N	\N	0	2	\N
326	JOB-1792566574740305	logistics Job 1792566574740305	\N	draft	1	14	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.375193	2026-10-21 07:09:35.375193	1	\N	\N	\N	pending	\N	\N	0	2	\N
327	JOB-1792566574740306	logistics Job 1792566574740306	\N	draft	1	15	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.38416	2026-10-21 07:09:35.38416	1	\N	\N	\N	pending	\N	\N	0	2	\N
328	JOB-1792566574740307	logistics Job 1792566574740307	\N	draft	1	18	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.389877	2026-10-21 07:09:35.389877	1	\N	\N	\N	pending	\N	\N	0	2	\N
329	JOB-1792566574740308	production_planning Job 1792566574740308	\N	draft	1	19	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.401059	2026-10-21 07:09:35.401059	1	\N	\N	\N	pending	\N	\N	0	2	\N
330	JOB-1792566574740309	production_planning Job 1792566574740309	\N	draft	1	20	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.40645	2026-10-21 07:09:35.40645	1	\N	\N	\N	pending	\N	\N	0	2	\N
331	JOB-1792566574740310	production_planning Job 1792566574740310	\N	draft	1	21	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.416456	2026-10-21 07:09:35.416456	1	\N	\N	\N	pending	\N	\N	0	2	\N
332	JOB-1792566574740311	production_planning Job 1792566574740311	\N	draft	1	22	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.421321	2026-10-21 07:09:35.421321	1	\N	\N	\N	pending	\N	\N	0	2	\N
333	JOB-1792566574740312	production_planning Job 1792566574740312	\N	draft	1	23	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.428183	2026-10-21 07:09:35.428183	1	\N	\N	\N	pending	\N	\N	0	2	\N
334	JOB-1792566574740313	production_planning Job 1792566574740313	\N	draft	1	24	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.435339	2026-10-21 07:09:35.435339	1	\N	\N	\N	pending	\N	\N	0	2	\N
335	JOB-1792566574740314	production_planning Job 1792566574740314	\N	draft	1	25	\N	2026-10-21 00:00:00	1	2026-10-21 07:09:35.440096	2026-10-21 07:09:35.440096	1	\N	\N	\N	pending	\N	\N	0	2	\N
280	JOB-1792566574740259	vehicle_design Job 1792566574740259 B	\N	not_a_real_status	1	7	3	2026-10-21 00:00:00	3	2026-10-21 07:09:34.903439	2026-10-21 07:09:34.903439	1	\N	\N	\N	approved	Needs rework	\N	1	2	\N
\.


--
-- Data for Name: level_rates; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.level_rates (id, level_id, payment_amount, valid_from, valid_to, created_at, modified_at) FROM stdin;
2	2	0.00	\N	\N	2026-10-15 11:04:55	\N
3	3	0.00	\N	\N	2026-10-15 11:04:55	\N
4	4	0.00	\N	\N	2026-10-15 11:04:55	\N
5	5	0.00	\N	\N	2026-10-15 11:04:55	\N
6	1	120.00	2026-10-01 00:00:00	\N	2026-10-15 11:16:37.877631	2026-10-15 11:16:37.877761
7	1	110.00	\N	2026-09-30 00:00:00	2026-10-15 11:16:54.664631	2026-10-15 11:16:54.664697
\.


--
-- Data for Name: levels; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.levels (id, name, label, description, payment_amount, color, sort_order, is_active, created_at, modified_at) FROM stdin;
2	level_2	Level 2	Set the payment amount before assigning jobs.	0.00	#0dcaf0	20	t	2026-10-15 11:04:54	\N
3	level_3	Level 3	Set the payment amount before assigning jobs.	0.00	#3b82f6	30	t	2026-10-15 11:04:54	\N
4	level_4	Level 4	Set the payment amount before assigning jobs.	0.00	#6f42c1	40	t	2026-10-15 11:04:54	\N
5	level_5	Level 5	Set the payment amount before assigning jobs.	0.00	#20c997	50	t	2026-10-15 11:04:54	\N
1	level_1	Level 1	Entry level. Set the payment amount before assigning jobs.	100.00	#6c757d	10	t	2026-10-15 11:04:54	2026-10-15 11:16:03.319155
\.


--
-- Data for Name: operator_wallets; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.operator_wallets (id, user_id, balance, total_earned, total_paid, notes, created_at, modified_at) FROM stdin;
1	2	0.00	0.00	0.00	\N	2026-10-15 11:04:54	2026-10-15 11:04:54
2	7	0.00	0.00	0.00	\N	2026-10-15 11:15:03.964626	2026-10-15 11:15:03.96469
3	6	0.00	0.00	0.00	\N	2026-10-15 11:15:03.985314	2026-10-15 11:15:03.985381
\.


--
-- Data for Name: organizations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.organizations (id, name, domain_or_slug, status, created_at) FROM stdin;
1	Default Organization	default-organization	active	2026-10-15 11:04:53
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (id, user_id, token, expires_at, used, created_at) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.roles (id, name, label, description, color, sort_order, is_active, created_at) FROM stdin;
1	admin	Admin	\N	\N	10	t	2026-10-15 11:04:52.616722
2	scheduler	Scheduler	\N	\N	20	t	2026-10-15 11:04:52.616722
4	quality_checker	Quality Checker	\N	\N	40	t	2026-10-15 11:04:52.616722
5	production	Production	\N	\N	50	t	2026-10-15 11:04:52.616722
7	operator	Operator	General operator role. Use work_type to define specialty: digitizing, programming, or data_entry.	\N	35	t	2026-10-15 11:04:53
\.


--
-- Data for Name: user_details; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.user_details (id, user_id, phone, bio, avatar, website, location, work_type_id, created_at, updated_at) FROM stdin;
1	2	\N	\N	\N	\N	\N	1	2026-10-15 11:04:53	\N
2	8			uploads/avatars/avatar_1792063157_6ad0b6b5b8dfd.jpg			\N	2026-10-15 11:19:17.801154	\N
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, password, role, organization_id, role_id, created_at, work_type, must_change_password) FROM stdin;
1	Scheduler User	scheduler@stitchcraft.com	$2y$10$hoLjb0xTZPSsCk.o2t4m0.AUASklMZJNpM4rCxlsyj3BV2X54tF6q	scheduler	1	2	2026-10-15 11:04:53	\N	f
2	Operator User	operator@stitchcraft.com	$2y$10$hoLjb0xTZPSsCk.o2t4m0.AUASklMZJNpM4rCxlsyj3BV2X54tF6q	operator	1	7	2026-10-15 11:04:53	\N	f
4	Production User	production@stitchcraft.com	$2y$10$hoLjb0xTZPSsCk.o2t4m0.AUASklMZJNpM4rCxlsyj3BV2X54tF6q	production	1	5	2026-10-15 11:04:53	\N	f
7	Accountant	accountant@stitchcraft.com	$2y$10$9b0wIcgDXLkkSMzp9/fQIuOAGfeUKLEfs.9EusswS9shgAkit1dYe	operator	1	7	2026-10-15 11:05:19	\N	f
6	Programmer	programmer@stitchcraft.com	$2y$10$9b0wIcgDXLkkSMzp9/fQIuOAGfeUKLEfs.9EusswS9shgAkit1dYe	operator	1	7	2026-10-15 11:05:19	\N	f
5	Design Engineer 1	design1@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:02:59.038992	vehicle_design	f
10	Design Engineer 2	design2@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.878213	vehicle_design	f
11	CAD Specialist	cad_specialist@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.891864	vehicle_design	f
12	Assembly Tech 1	assembly1@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.895113	vehicle_manufacturing	f
13	Assembly Tech 2	assembly2@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.898659	vehicle_manufacturing	f
14	Production Line Lead	prod_lead@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.905224	vehicle_manufacturing	f
15	Manufacturing Operator	mfg_operator@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.907173	vehicle_manufacturing	f
3	QC Inspector 1	qc1@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	quality_checker	1	4	2026-10-15 11:04:53	quality_control	f
16	QC Inspector 2	qc2@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	quality_checker	1	\N	2026-10-15 18:05:32.911183	quality_control	f
17	Quality Engineer	quality_eng@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	quality_checker	1	\N	2026-10-15 18:05:32.913279	quality_control	f
18	Maintenance Tech 1	maint1@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.915477	maintenance	f
19	Maintenance Tech 2	maint2@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.916951	maintenance	f
20	Assembly Tech 3	assembly3@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.918367	assembly	f
21	Final Assembly Lead	final_assembly@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.920987	assembly	f
22	Test Engineer 1	test_eng1@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.922757	testing	f
23	Test Engineer 2	test_eng2@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.924162	testing	f
24	Logistics Coordinator	logistics_coord@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:32.932721	logistics	f
25	Warehouse Operator	warehouse_op@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:33.064742	logistics	f
26	Shipping Clerk	shipping@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	operator	1	\N	2026-10-15 18:05:33.066379	logistics	f
27	Production Planner 1	planner1@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	scheduler	1	\N	2026-10-15 18:05:33.06775	production_planning	f
28	Production Planner 2	planner2@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	scheduler	1	\N	2026-10-15 18:05:33.071822	production_planning	f
8	Admin User	admin@stitchcraft.com	$2y$10$.pcUn/Zx8IY5WjXfggMPMeYRILBxKrxTz0uYRl2Z0sRnq74rgRKoi	admin	1	1	2026-10-15 16:35:50.748845	vehicle_design	f
\.


--
-- Data for Name: wallet_transactions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.wallet_transactions (id, wallet_id, user_id, job_id, type, amount, balance_after, notes, reference, created_by, created_at) FROM stdin;
\.


--
-- Data for Name: work_types; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.work_types (id, name, label, description, color, sort_order, is_active, created_at) FROM stdin;
4	vehicle_design	Vehicle Design	CAD modeling, specifications, and design reviews	#3B82F6	1	t	2026-10-15 18:00:55.334435
5	vehicle_manufacturing	Vehicle Manufacturing	Assembly, production line operations	#10B981	2	t	2026-10-15 18:00:55.341379
6	quality_control	Quality Control	Inspection, testing, and compliance	#F59E0B	3	t	2026-10-15 18:00:55.34229
7	maintenance	Maintenance	Preventive and corrective maintenance	#EF4444	4	t	2026-10-15 18:00:55.342952
8	assembly	Assembly	Final assembly and packaging	#8B5CF6	5	t	2026-10-15 18:00:55.343496
9	testing	Testing	Functional and performance testing	#EC4899	6	t	2026-10-15 18:00:55.344401
10	logistics	Logistics	Parts inventory, shipping, and distribution	#06B6D4	7	t	2026-10-15 18:00:55.346482
11	production_planning	Production Planning	Scheduling and resource allocation	#84CC16	8	t	2026-10-15 18:00:55.347088
\.


--
-- Name: cake_migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cake_migrations_id_seq', 17, true);


--
-- Name: cake_seeds_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cake_seeds_id_seq', 1, true);


--
-- Name: job_assignments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.job_assignments_id_seq', 1, false);


--
-- Name: job_attachments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.job_attachments_id_seq', 1, false);


--
-- Name: job_counters_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.job_counters_id_seq', 1, true);


--
-- Name: job_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.job_logs_id_seq', 1, false);


--
-- Name: job_qc_reviews_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.job_qc_reviews_id_seq', 1, false);


--
-- Name: job_statuses_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.job_statuses_id_seq', 8, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 393, true);


--
-- Name: level_rates_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.level_rates_id_seq', 7, true);


--
-- Name: levels_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.levels_id_seq', 5, true);


--
-- Name: operator_wallets_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.operator_wallets_id_seq', 3, true);


--
-- Name: organizations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.organizations_id_seq', 1, true);


--
-- Name: password_reset_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.password_reset_tokens_id_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.roles_id_seq', 7, true);


--
-- Name: user_details_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.user_details_id_seq', 2, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 29, true);


--
-- Name: wallet_transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.wallet_transactions_id_seq', 1, false);


--
-- Name: work_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.work_types_id_seq', 11, true);


--
-- Name: cake_migrations cake_migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cake_migrations
    ADD CONSTRAINT cake_migrations_pkey PRIMARY KEY (id);


--
-- Name: cake_seeds cake_seeds_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cake_seeds
    ADD CONSTRAINT cake_seeds_pkey PRIMARY KEY (id);


--
-- Name: job_assignments job_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_assignments
    ADD CONSTRAINT job_assignments_pkey PRIMARY KEY (id);


--
-- Name: job_attachments job_attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_attachments
    ADD CONSTRAINT job_attachments_pkey PRIMARY KEY (id);


--
-- Name: job_counters job_counters_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_counters
    ADD CONSTRAINT job_counters_pkey PRIMARY KEY (id);


--
-- Name: job_logs job_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_logs
    ADD CONSTRAINT job_logs_pkey PRIMARY KEY (id);


--
-- Name: job_qc_reviews job_qc_reviews_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_qc_reviews
    ADD CONSTRAINT job_qc_reviews_pkey PRIMARY KEY (id);


--
-- Name: job_statuses job_statuses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_statuses
    ADD CONSTRAINT job_statuses_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: level_rates level_rates_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.level_rates
    ADD CONSTRAINT level_rates_pkey PRIMARY KEY (id);


--
-- Name: levels levels_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.levels
    ADD CONSTRAINT levels_pkey PRIMARY KEY (id);


--
-- Name: operator_wallets operator_wallets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operator_wallets
    ADD CONSTRAINT operator_wallets_pkey PRIMARY KEY (id);


--
-- Name: organizations organizations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.organizations
    ADD CONSTRAINT organizations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: user_details user_details_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_details
    ADD CONSTRAINT user_details_pkey PRIMARY KEY (id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: wallet_transactions wallet_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT wallet_transactions_pkey PRIMARY KEY (id);


--
-- Name: work_types work_types_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.work_types
    ADD CONSTRAINT work_types_pkey PRIMARY KEY (id);


--
-- Name: idx_assign_by; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assign_by ON public.job_assignments USING btree (assigned_by);


--
-- Name: idx_assign_job; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assign_job ON public.job_assignments USING btree (job_id);


--
-- Name: idx_assign_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_assign_user ON public.job_assignments USING btree (assigned_to);


--
-- Name: idx_jobs_assigned_by; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_jobs_assigned_by ON public.jobs USING btree (assigned_by);


--
-- Name: idx_jobs_assigned_to; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_jobs_assigned_to ON public.jobs USING btree (assigned_to);


--
-- Name: idx_jobs_level_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_jobs_level_id ON public.jobs USING btree (level_id);


--
-- Name: idx_jobs_qc_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_jobs_qc_status ON public.jobs USING btree (qc_status);


--
-- Name: idx_level_rates_level_from; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_level_rates_level_from ON public.level_rates USING btree (level_id, valid_from);


--
-- Name: idx_levels_name; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_levels_name ON public.levels USING btree (name);


--
-- Name: idx_operator_wallets_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX idx_operator_wallets_user_id ON public.operator_wallets USING btree (user_id);


--
-- Name: idx_qc_review_job; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_qc_review_job ON public.job_qc_reviews USING btree (job_id);


--
-- Name: idx_qc_review_user; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_qc_review_user ON public.job_qc_reviews USING btree (qc_user_id);


--
-- Name: idx_wallet_transactions_created_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wallet_transactions_created_at ON public.wallet_transactions USING btree (created_at);


--
-- Name: idx_wallet_transactions_job_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wallet_transactions_job_id ON public.wallet_transactions USING btree (job_id);


--
-- Name: idx_wallet_transactions_type; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wallet_transactions_type ON public.wallet_transactions USING btree (type);


--
-- Name: idx_wallet_transactions_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wallet_transactions_user_id ON public.wallet_transactions USING btree (user_id);


--
-- Name: idx_wallet_transactions_wallet_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_wallet_transactions_wallet_id ON public.wallet_transactions USING btree (wallet_id);


--
-- Name: job_attachments_job_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX job_attachments_job_id ON public.job_attachments USING btree (job_id);


--
-- Name: job_logs_job_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX job_logs_job_id ON public.job_logs USING btree (job_id);


--
-- Name: job_logs_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX job_logs_user_id ON public.job_logs USING btree (user_id);


--
-- Name: job_statuses_name; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX job_statuses_name ON public.job_statuses USING btree (name);


--
-- Name: jobs_organization_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_organization_id ON public.jobs USING btree (organization_id);


--
-- Name: jobs_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_status ON public.jobs USING btree (status);


--
-- Name: jobs_status_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_status_id ON public.jobs USING btree (status_id);


--
-- Name: organizations_domain_or_slug; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX organizations_domain_or_slug ON public.organizations USING btree (domain_or_slug);


--
-- Name: password_reset_tokens_expires_at; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX password_reset_tokens_expires_at ON public.password_reset_tokens USING btree (expires_at);


--
-- Name: password_reset_tokens_token; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX password_reset_tokens_token ON public.password_reset_tokens USING btree (token);


--
-- Name: password_reset_tokens_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX password_reset_tokens_user_id ON public.password_reset_tokens USING btree (user_id);


--
-- Name: roles_name; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX roles_name ON public.roles USING btree (name);


--
-- Name: unique_job_number_per_org; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX unique_job_number_per_org ON public.jobs USING btree (organization_id, job_number);


--
-- Name: user_details_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX user_details_user_id ON public.user_details USING btree (user_id);


--
-- Name: users_organization_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_organization_id ON public.users USING btree (organization_id);


--
-- Name: users_role_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX users_role_id ON public.users USING btree (role_id);


--
-- Name: users_unique_email_per_org; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX users_unique_email_per_org ON public.users USING btree (organization_id, email);


--
-- Name: version_plugin_unique; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX version_plugin_unique ON public.cake_migrations USING btree (version, plugin);


--
-- Name: work_types_name; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX work_types_name ON public.work_types USING btree (name);


--
-- Name: jobs fk_jobs_assigned_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT fk_jobs_assigned_by FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: jobs fk_jobs_assigned_to; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT fk_jobs_assigned_to FOREIGN KEY (assigned_to) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: jobs fk_jobs_level_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT fk_jobs_level_id FOREIGN KEY (level_id) REFERENCES public.levels(id) ON DELETE SET NULL;


--
-- Name: operator_wallets fk_operator_wallets_user_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.operator_wallets
    ADD CONSTRAINT fk_operator_wallets_user_id FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: wallet_transactions fk_wallet_transactions_created_by; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT fk_wallet_transactions_created_by FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: wallet_transactions fk_wallet_transactions_job_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT fk_wallet_transactions_job_id FOREIGN KEY (job_id) REFERENCES public.jobs(id) ON DELETE SET NULL;


--
-- Name: wallet_transactions fk_wallet_transactions_user_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT fk_wallet_transactions_user_id FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: wallet_transactions fk_wallet_transactions_wallet_id; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.wallet_transactions
    ADD CONSTRAINT fk_wallet_transactions_wallet_id FOREIGN KEY (wallet_id) REFERENCES public.operator_wallets(id) ON DELETE CASCADE;


--
-- Name: job_assignments job_assignments_assigned_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_assignments
    ADD CONSTRAINT job_assignments_assigned_by_fkey FOREIGN KEY (assigned_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: job_assignments job_assignments_assigned_to_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_assignments
    ADD CONSTRAINT job_assignments_assigned_to_fkey FOREIGN KEY (assigned_to) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: job_assignments job_assignments_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_assignments
    ADD CONSTRAINT job_assignments_job_id_fkey FOREIGN KEY (job_id) REFERENCES public.jobs(id) ON DELETE CASCADE;


--
-- Name: job_attachments job_attachments_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_attachments
    ADD CONSTRAINT job_attachments_job_id_fkey FOREIGN KEY (job_id) REFERENCES public.jobs(id) ON DELETE CASCADE;


--
-- Name: job_attachments job_attachments_uploaded_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_attachments
    ADD CONSTRAINT job_attachments_uploaded_by_fkey FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: job_logs job_logs_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_logs
    ADD CONSTRAINT job_logs_job_id_fkey FOREIGN KEY (job_id) REFERENCES public.jobs(id) ON DELETE CASCADE;


--
-- Name: job_logs job_logs_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_logs
    ADD CONSTRAINT job_logs_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: job_qc_reviews job_qc_reviews_assigned_to_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_qc_reviews
    ADD CONSTRAINT job_qc_reviews_assigned_to_user_id_fkey FOREIGN KEY (assigned_to_user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: job_qc_reviews job_qc_reviews_job_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_qc_reviews
    ADD CONSTRAINT job_qc_reviews_job_id_fkey FOREIGN KEY (job_id) REFERENCES public.jobs(id) ON DELETE CASCADE;


--
-- Name: job_qc_reviews job_qc_reviews_qc_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_qc_reviews
    ADD CONSTRAINT job_qc_reviews_qc_user_id_fkey FOREIGN KEY (qc_user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: jobs jobs_created_by_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_created_by_fkey FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: jobs jobs_operator_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_operator_id_fkey FOREIGN KEY (operator_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: jobs jobs_organization_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_organization_id_fkey FOREIGN KEY (organization_id) REFERENCES public.organizations(id) ON DELETE CASCADE;


--
-- Name: jobs jobs_qc_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_qc_id_fkey FOREIGN KEY (qc_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: jobs jobs_status_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_status_id_fkey FOREIGN KEY (status_id) REFERENCES public.job_statuses(id) ON DELETE SET NULL;


--
-- Name: level_rates level_rates_level_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.level_rates
    ADD CONSTRAINT level_rates_level_id_fkey FOREIGN KEY (level_id) REFERENCES public.levels(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: password_reset_tokens password_reset_tokens_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: users users_organization_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_organization_id_fkey FOREIGN KEY (organization_id) REFERENCES public.organizations(id) ON DELETE RESTRICT;


--
-- Name: users users_role_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_role_id_fkey FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict GBRxEv2kjXUgGrcrWmgCcyxwfE7WaWd1TiYpkFcCOrkQlW0UKTaPfD1X8V5cx9C

