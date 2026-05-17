--
-- PostgreSQL database dump
--

\restrict WpaBgv4dLwpflmsWh0JCTVVa99iBdHRLhmdqkImJtSsWrZllI1vvdVLjm3C2mwl

-- Dumped from database version 16.13
-- Dumped by pg_dump version 16.13

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: actividades_adulto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.actividades_adulto (
    cod_act_adul integer NOT NULL,
    fecha date NOT NULL,
    hora time(0) without time zone,
    obs text,
    estado character varying(100) NOT NULL,
    cod_tipo_act integer NOT NULL,
    cod_am character varying(10) NOT NULL,
    hora_fin time(0) without time zone,
    responsable_tipo character varying(255),
    responsable_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.actividades_adulto OWNER TO postgres;

--
-- Name: actividades_adulto_cod_act_adul_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.actividades_adulto_cod_act_adul_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.actividades_adulto_cod_act_adul_seq OWNER TO postgres;

--
-- Name: actividades_adulto_cod_act_adul_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.actividades_adulto_cod_act_adul_seq OWNED BY public.actividades_adulto.cod_act_adul;


--
-- Name: activity_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.activity_log (
    id bigint NOT NULL,
    log_name character varying(255),
    description text NOT NULL,
    subject_type character varying(255),
    subject_id character varying(36),
    causer_type character varying(255),
    causer_id character varying(36),
    properties json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    event character varying(255),
    batch_uuid uuid
);


ALTER TABLE public.activity_log OWNER TO postgres;

--
-- Name: activity_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.activity_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activity_log_id_seq OWNER TO postgres;

--
-- Name: activity_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.activity_log_id_seq OWNED BY public.activity_log.id;


--
-- Name: adulto_mayor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.adulto_mayor (
    cod_am character varying(10) NOT NULL,
    nombres character varying(100) NOT NULL,
    ap_paterno character varying(80) NOT NULL,
    ap_materno character varying(80),
    ci character varying(20),
    fecha_nac date NOT NULL,
    genero character varying(100) NOT NULL,
    estado_civil character varying(100),
    telefono character varying(20),
    zona character varying(100),
    calle character varying(150),
    fecha_ing date NOT NULL,
    tipo_ing character varying(100) NOT NULL,
    permanencia character varying(50),
    nivel_educat character varying(100),
    observaciones text,
    cod_est_adul integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    foto character varying(2048),
    archivado_en timestamp(0) without time zone,
    motivo_archivado text,
    hora_ing time(0) without time zone,
    complemento_ci character varying(2),
    expedicion_ci character varying(2),
    celular character varying(8),
    telefono_fijo character varying(10),
    departamento_residencia character varying(50),
    ciudad_municipio character varying(100),
    grupo_sanguineo character varying(3),
    factor_rh character varying(1),
    alergias text,
    seguro_salud character varying(80),
    contacto_emergencia_nombre character varying(150),
    contacto_emergencia_parentesco character varying(80),
    contacto_emergencia_celular character varying(8),
    contacto_emergencia_direccion character varying(200),
    responsable_principal boolean DEFAULT false NOT NULL,
    autorizado_informacion_medica boolean DEFAULT false NOT NULL,
    consentimiento_datos boolean DEFAULT false NOT NULL
);


ALTER TABLE public.adulto_mayor OWNER TO postgres;

--
-- Name: adulto_mayor_cod_am_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.adulto_mayor_cod_am_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.adulto_mayor_cod_am_seq OWNER TO postgres;

--
-- Name: adulto_mayor_cod_am_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.adulto_mayor_cod_am_seq OWNED BY public.adulto_mayor.cod_am;


--
-- Name: asignacion_voluntarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.asignacion_voluntarios (
    cod_asig_vol integer NOT NULL,
    fecha_asig date NOT NULL,
    fecha_fin date,
    estado character varying(100) NOT NULL,
    obser text,
    cod_am character varying(10) NOT NULL,
    cod_vol integer NOT NULL
);


ALTER TABLE public.asignacion_voluntarios OWNER TO postgres;

--
-- Name: asignacion_voluntarios_cod_asig_vol_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.asignacion_voluntarios_cod_asig_vol_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.asignacion_voluntarios_cod_asig_vol_seq OWNER TO postgres;

--
-- Name: asignacion_voluntarios_cod_asig_vol_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.asignacion_voluntarios_cod_asig_vol_seq OWNED BY public.asignacion_voluntarios.cod_asig_vol;


--
-- Name: asistencia_voluntarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.asistencia_voluntarios (
    cod_asis_vol integer NOT NULL,
    fecha date NOT NULL,
    hora_entrada time(0) without time zone,
    hora_salida time(0) without time zone,
    estado character varying(100) NOT NULL,
    actividad_realizada text,
    observaciones text,
    cod_vol integer NOT NULL
);


ALTER TABLE public.asistencia_voluntarios OWNER TO postgres;

--
-- Name: asistencia_voluntarios_cod_asis_vol_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.asistencia_voluntarios_cod_asis_vol_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.asistencia_voluntarios_cod_asis_vol_seq OWNER TO postgres;

--
-- Name: asistencia_voluntarios_cod_asis_vol_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.asistencia_voluntarios_cod_asis_vol_seq OWNED BY public.asistencia_voluntarios.cod_asis_vol;


--
-- Name: atenciones_adulto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.atenciones_adulto (
    cod_aten_adul integer NOT NULL,
    fecha date NOT NULL,
    hora time(0) without time zone,
    obs text,
    estado character varying(100) NOT NULL,
    cod_tipo_aten integer NOT NULL,
    cod_am character varying(10) NOT NULL,
    responsable_tipo character varying(255),
    responsable_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.atenciones_adulto OWNER TO postgres;

--
-- Name: atenciones_adulto_cod_aten_adul_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.atenciones_adulto_cod_aten_adul_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.atenciones_adulto_cod_aten_adul_seq OWNER TO postgres;

--
-- Name: atenciones_adulto_cod_aten_adul_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.atenciones_adulto_cod_aten_adul_seq OWNED BY public.atenciones_adulto.cod_aten_adul;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: disponibilidad_voluntarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.disponibilidad_voluntarios (
    cod_hor_vol integer NOT NULL,
    dia_semana character varying(100) NOT NULL,
    hora_inicio time(0) without time zone,
    hora_fin time(0) without time zone,
    observaciones text,
    cod_vol integer NOT NULL
);


ALTER TABLE public.disponibilidad_voluntarios OWNER TO postgres;

--
-- Name: disponibilidad_voluntarios_cod_hor_vol_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.disponibilidad_voluntarios_cod_hor_vol_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.disponibilidad_voluntarios_cod_hor_vol_seq OWNER TO postgres;

--
-- Name: disponibilidad_voluntarios_cod_hor_vol_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.disponibilidad_voluntarios_cod_hor_vol_seq OWNED BY public.disponibilidad_voluntarios.cod_hor_vol;


--
-- Name: documentos_adulto_mayor; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.documentos_adulto_mayor (
    cod_doc_am integer NOT NULL,
    nom_doc character varying(120) NOT NULL,
    tipo_doc character varying(100) NOT NULL,
    ruta_archivo character varying(255) NOT NULL,
    extension character varying(100) NOT NULL,
    fecha_doc date,
    observaciones text,
    cod_am character varying(10) NOT NULL,
    estado character varying(255) DEFAULT 'ACTIVO'::character varying NOT NULL,
    archivado_en timestamp(0) without time zone,
    motivo_archivado text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.documentos_adulto_mayor OWNER TO postgres;

--
-- Name: documentos_adulto_mayor_cod_doc_am_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.documentos_adulto_mayor_cod_doc_am_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documentos_adulto_mayor_cod_doc_am_seq OWNER TO postgres;

--
-- Name: documentos_adulto_mayor_cod_doc_am_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.documentos_adulto_mayor_cod_doc_am_seq OWNED BY public.documentos_adulto_mayor.cod_doc_am;


--
-- Name: documentos_usuarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.documentos_usuarios (
    cod_doc_usu integer NOT NULL,
    nom_doc character varying(120) NOT NULL,
    tipo_doc character varying(100) NOT NULL,
    ruta_archivo character varying(255) NOT NULL,
    extension character varying(100) NOT NULL,
    fecha_doc date,
    observaciones text,
    cod_usu integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.documentos_usuarios OWNER TO postgres;

--
-- Name: documentos_usuarios_cod_doc_usu_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.documentos_usuarios_cod_doc_usu_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documentos_usuarios_cod_doc_usu_seq OWNER TO postgres;

--
-- Name: documentos_usuarios_cod_doc_usu_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.documentos_usuarios_cod_doc_usu_seq OWNED BY public.documentos_usuarios.cod_doc_usu;


--
-- Name: especialidades; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.especialidades (
    cod_esp integer NOT NULL,
    nombre character varying(50) NOT NULL,
    descripcion text
);


ALTER TABLE public.especialidades OWNER TO postgres;

--
-- Name: especialidades_cod_esp_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.especialidades_cod_esp_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.especialidades_cod_esp_seq OWNER TO postgres;

--
-- Name: especialidades_cod_esp_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.especialidades_cod_esp_seq OWNED BY public.especialidades.cod_esp;


--
-- Name: estado_adulto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.estado_adulto (
    cod_est_adul integer NOT NULL,
    estado character varying(100) NOT NULL,
    fecha_in date,
    fecha_fin date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.estado_adulto OWNER TO postgres;

--
-- Name: estado_adulto_cod_est_adul_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.estado_adulto_cod_est_adul_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.estado_adulto_cod_est_adul_seq OWNER TO postgres;

--
-- Name: estado_adulto_cod_est_adul_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.estado_adulto_cod_est_adul_seq OWNED BY public.estado_adulto.cod_est_adul;


--
-- Name: evaluaciones_cognitivas; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.evaluaciones_cognitivas (
    cod_eval_cog character varying(12) NOT NULL,
    cod_am character varying(10) NOT NULL,
    cod_tipo_eval bigint NOT NULL,
    cod_per_sal integer NOT NULL,
    fecha_eval date NOT NULL,
    hora_eval time(0) without time zone,
    puntaje_total numeric(5,2),
    puntaje_maximo numeric(5,2),
    resultado_interpretacion character varying(120),
    nivel_riesgo character varying(30),
    observaciones text,
    estado_eval character varying(30) DEFAULT 'BORRADOR'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.evaluaciones_cognitivas OWNER TO postgres;

--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: familiar_adulto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.familiar_adulto (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    cod_fam integer NOT NULL,
    cod_am character varying(10) NOT NULL,
    parentesco_vinculo character varying(255),
    es_responsable boolean DEFAULT false NOT NULL,
    estado character varying(255) DEFAULT 'ACTIVO'::character varying NOT NULL,
    observaciones text
);


ALTER TABLE public.familiar_adulto OWNER TO postgres;

--
-- Name: familiar_adulto_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.familiar_adulto_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.familiar_adulto_id_seq OWNER TO postgres;

--
-- Name: familiar_adulto_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.familiar_adulto_id_seq OWNED BY public.familiar_adulto.id;


--
-- Name: familiares; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.familiares (
    cod_fam integer NOT NULL,
    parentesco character varying(100) NOT NULL,
    direccion character varying(150),
    ocupacion character varying(100),
    es_responsable character varying(255) NOT NULL,
    observaciones text,
    cod_usu integer NOT NULL,
    estado character varying(255) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT familiares_es_responsable_check CHECK (((es_responsable)::text = ANY ((ARRAY['SI'::character varying, 'NO'::character varying])::text[])))
);


ALTER TABLE public.familiares OWNER TO postgres;

--
-- Name: familiares_cod_fam_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.familiares_cod_fam_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.familiares_cod_fam_seq OWNER TO postgres;

--
-- Name: familiares_cod_fam_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.familiares_cod_fam_seq OWNED BY public.familiares.cod_fam;


--
-- Name: horarios_personal_admin; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.horarios_personal_admin (
    cod_hor_per_admin integer NOT NULL,
    dia_semana character varying(100) NOT NULL,
    hora_inicio time(0) without time zone,
    hora_fin time(0) without time zone,
    turno character varying(100) NOT NULL,
    estado character varying(100) NOT NULL,
    observaciones text,
    cod_per_adm integer NOT NULL
);


ALTER TABLE public.horarios_personal_admin OWNER TO postgres;

--
-- Name: horarios_personal_admin_cod_hor_per_admin_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.horarios_personal_admin_cod_hor_per_admin_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.horarios_personal_admin_cod_hor_per_admin_seq OWNER TO postgres;

--
-- Name: horarios_personal_admin_cod_hor_per_admin_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.horarios_personal_admin_cod_hor_per_admin_seq OWNED BY public.horarios_personal_admin.cod_hor_per_admin;


--
-- Name: horarios_personal_salud; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.horarios_personal_salud (
    cod_hor_per_sal integer NOT NULL,
    dia_semana character varying(100) NOT NULL,
    hora_inicio time(0) without time zone,
    hora_fin time(0) without time zone,
    turno character varying(100) NOT NULL,
    estado character varying(100) NOT NULL,
    observaciones text,
    cod_per_sal integer NOT NULL
);


ALTER TABLE public.horarios_personal_salud OWNER TO postgres;

--
-- Name: horarios_personal_salud_cod_hor_per_sal_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.horarios_personal_salud_cod_hor_per_sal_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.horarios_personal_salud_cod_hor_per_sal_seq OWNER TO postgres;

--
-- Name: horarios_personal_salud_cod_hor_per_sal_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.horarios_personal_salud_cod_hor_per_sal_seq OWNED BY public.horarios_personal_salud.cod_hor_per_sal;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO postgres;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO postgres;

--
-- Name: obs_adulto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.obs_adulto (
    cod_obs_adul integer NOT NULL,
    fecha date NOT NULL,
    tipo_obs character varying(100) NOT NULL,
    descripcion text,
    cod_am character varying(10) NOT NULL,
    cod_est_adul integer,
    nivel_importancia character varying(255),
    registrado_por integer,
    estado_revision character varying(255) DEFAULT 'PENDIENTE'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.obs_adulto OWNER TO postgres;

--
-- Name: obs_adulto_cod_obs_adul_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.obs_adulto_cod_obs_adul_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.obs_adulto_cod_obs_adul_seq OWNER TO postgres;

--
-- Name: obs_adulto_cod_obs_adul_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.obs_adulto_cod_obs_adul_seq OWNED BY public.obs_adulto.cod_obs_adul;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    correo character varying(120) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: personal_admin; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_admin (
    cod_per_adm integer NOT NULL,
    cargo character varying(100) NOT NULL,
    fecha_ingreso date NOT NULL,
    area_admin character varying(100) NOT NULL,
    estado_laboral character varying(100) NOT NULL,
    observaciones text,
    cod_usu integer NOT NULL,
    archivado_en timestamp(0) without time zone,
    motivo_archivado text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_admin OWNER TO postgres;

--
-- Name: personal_admin_cod_per_adm_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_admin_cod_per_adm_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_admin_cod_per_adm_seq OWNER TO postgres;

--
-- Name: personal_admin_cod_per_adm_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_admin_cod_per_adm_seq OWNED BY public.personal_admin.cod_per_adm;


--
-- Name: personal_salud; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_salud (
    cod_per_sal integer NOT NULL,
    fecha_ing date NOT NULL,
    anios_exp integer,
    matricula_prof character varying(50),
    estado_laboral character varying(100) NOT NULL,
    observaciones text,
    cod_usu integer NOT NULL,
    cod_esp integer,
    archivado_en timestamp(0) without time zone,
    motivo_archivado text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_salud OWNER TO postgres;

--
-- Name: personal_salud_cod_per_sal_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_salud_cod_per_sal_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_salud_cod_per_sal_seq OWNER TO postgres;

--
-- Name: personal_salud_cod_per_sal_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_salud_cod_per_sal_seq OWNED BY public.personal_salud.cod_per_sal;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id integer,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: tipo_actividades_adulto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipo_actividades_adulto (
    cod_tipo_act integer NOT NULL,
    tipo character varying(50) NOT NULL,
    descripcion text
);


ALTER TABLE public.tipo_actividades_adulto OWNER TO postgres;

--
-- Name: tipo_actividades_adulto_cod_tipo_act_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipo_actividades_adulto_cod_tipo_act_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipo_actividades_adulto_cod_tipo_act_seq OWNER TO postgres;

--
-- Name: tipo_actividades_adulto_cod_tipo_act_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipo_actividades_adulto_cod_tipo_act_seq OWNED BY public.tipo_actividades_adulto.cod_tipo_act;


--
-- Name: tipo_atenciones_adulto; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipo_atenciones_adulto (
    cod_tipo_aten integer NOT NULL,
    tipo character varying(50) NOT NULL,
    descripcion text
);


ALTER TABLE public.tipo_atenciones_adulto OWNER TO postgres;

--
-- Name: tipo_atenciones_adulto_cod_tipo_aten_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipo_atenciones_adulto_cod_tipo_aten_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipo_atenciones_adulto_cod_tipo_aten_seq OWNER TO postgres;

--
-- Name: tipo_atenciones_adulto_cod_tipo_aten_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipo_atenciones_adulto_cod_tipo_aten_seq OWNED BY public.tipo_atenciones_adulto.cod_tipo_aten;


--
-- Name: tipo_evaluacion_cognitiva; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.tipo_evaluacion_cognitiva (
    cod_tipo_eval bigint NOT NULL,
    nombre character varying(80) NOT NULL,
    descripcion text,
    puntaje_maximo numeric(5,2) DEFAULT '30'::numeric NOT NULL,
    punto_corte_normal numeric(5,2),
    punto_corte_riesgo numeric(5,2),
    estado character varying(20) DEFAULT 'ACTIVO'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.tipo_evaluacion_cognitiva OWNER TO postgres;

--
-- Name: tipo_evaluacion_cognitiva_cod_tipo_eval_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.tipo_evaluacion_cognitiva_cod_tipo_eval_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.tipo_evaluacion_cognitiva_cod_tipo_eval_seq OWNER TO postgres;

--
-- Name: tipo_evaluacion_cognitiva_cod_tipo_eval_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.tipo_evaluacion_cognitiva_cod_tipo_eval_seq OWNED BY public.tipo_evaluacion_cognitiva.cod_tipo_eval;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    cod_usu integer NOT NULL,
    nombres character varying(100) NOT NULL,
    ap_paterno character varying(80) NOT NULL,
    ap_materno character varying(80),
    correo character varying(120) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    telefono character varying(20),
    foto_de_perfil character varying(255),
    estado character varying(100) DEFAULT 'ACTIVO'::character varying NOT NULL,
    ultimo_acceso timestamp(0) without time zone,
    remember_token character varying(100),
    current_team_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    two_factor_secret text,
    two_factor_recovery_codes text,
    two_factor_confirmed_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_cod_usu_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_cod_usu_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_cod_usu_seq OWNER TO postgres;

--
-- Name: users_cod_usu_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_cod_usu_seq OWNED BY public.users.cod_usu;


--
-- Name: voluntarios; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.voluntarios (
    cod_vol integer NOT NULL,
    fecha_ing date NOT NULL,
    area_apoyo character varying(100) NOT NULL,
    estado character varying(100) NOT NULL,
    observaciones text,
    cod_usu integer NOT NULL,
    archivado_en timestamp(0) without time zone,
    motivo_archivado text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.voluntarios OWNER TO postgres;

--
-- Name: voluntarios_cod_vol_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.voluntarios_cod_vol_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.voluntarios_cod_vol_seq OWNER TO postgres;

--
-- Name: voluntarios_cod_vol_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.voluntarios_cod_vol_seq OWNED BY public.voluntarios.cod_vol;


--
-- Name: actividades_adulto cod_act_adul; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividades_adulto ALTER COLUMN cod_act_adul SET DEFAULT nextval('public.actividades_adulto_cod_act_adul_seq'::regclass);


--
-- Name: activity_log id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log ALTER COLUMN id SET DEFAULT nextval('public.activity_log_id_seq'::regclass);


--
-- Name: asignacion_voluntarios cod_asig_vol; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignacion_voluntarios ALTER COLUMN cod_asig_vol SET DEFAULT nextval('public.asignacion_voluntarios_cod_asig_vol_seq'::regclass);


--
-- Name: asistencia_voluntarios cod_asis_vol; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asistencia_voluntarios ALTER COLUMN cod_asis_vol SET DEFAULT nextval('public.asistencia_voluntarios_cod_asis_vol_seq'::regclass);


--
-- Name: atenciones_adulto cod_aten_adul; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.atenciones_adulto ALTER COLUMN cod_aten_adul SET DEFAULT nextval('public.atenciones_adulto_cod_aten_adul_seq'::regclass);


--
-- Name: disponibilidad_voluntarios cod_hor_vol; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.disponibilidad_voluntarios ALTER COLUMN cod_hor_vol SET DEFAULT nextval('public.disponibilidad_voluntarios_cod_hor_vol_seq'::regclass);


--
-- Name: documentos_adulto_mayor cod_doc_am; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documentos_adulto_mayor ALTER COLUMN cod_doc_am SET DEFAULT nextval('public.documentos_adulto_mayor_cod_doc_am_seq'::regclass);


--
-- Name: documentos_usuarios cod_doc_usu; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documentos_usuarios ALTER COLUMN cod_doc_usu SET DEFAULT nextval('public.documentos_usuarios_cod_doc_usu_seq'::regclass);


--
-- Name: especialidades cod_esp; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.especialidades ALTER COLUMN cod_esp SET DEFAULT nextval('public.especialidades_cod_esp_seq'::regclass);


--
-- Name: estado_adulto cod_est_adul; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estado_adulto ALTER COLUMN cod_est_adul SET DEFAULT nextval('public.estado_adulto_cod_est_adul_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: familiar_adulto id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familiar_adulto ALTER COLUMN id SET DEFAULT nextval('public.familiar_adulto_id_seq'::regclass);


--
-- Name: familiares cod_fam; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familiares ALTER COLUMN cod_fam SET DEFAULT nextval('public.familiares_cod_fam_seq'::regclass);


--
-- Name: horarios_personal_admin cod_hor_per_admin; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.horarios_personal_admin ALTER COLUMN cod_hor_per_admin SET DEFAULT nextval('public.horarios_personal_admin_cod_hor_per_admin_seq'::regclass);


--
-- Name: horarios_personal_salud cod_hor_per_sal; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.horarios_personal_salud ALTER COLUMN cod_hor_per_sal SET DEFAULT nextval('public.horarios_personal_salud_cod_hor_per_sal_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: obs_adulto cod_obs_adul; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.obs_adulto ALTER COLUMN cod_obs_adul SET DEFAULT nextval('public.obs_adulto_cod_obs_adul_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: personal_admin cod_per_adm; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_admin ALTER COLUMN cod_per_adm SET DEFAULT nextval('public.personal_admin_cod_per_adm_seq'::regclass);


--
-- Name: personal_salud cod_per_sal; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_salud ALTER COLUMN cod_per_sal SET DEFAULT nextval('public.personal_salud_cod_per_sal_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: tipo_actividades_adulto cod_tipo_act; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_actividades_adulto ALTER COLUMN cod_tipo_act SET DEFAULT nextval('public.tipo_actividades_adulto_cod_tipo_act_seq'::regclass);


--
-- Name: tipo_atenciones_adulto cod_tipo_aten; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_atenciones_adulto ALTER COLUMN cod_tipo_aten SET DEFAULT nextval('public.tipo_atenciones_adulto_cod_tipo_aten_seq'::regclass);


--
-- Name: tipo_evaluacion_cognitiva cod_tipo_eval; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_evaluacion_cognitiva ALTER COLUMN cod_tipo_eval SET DEFAULT nextval('public.tipo_evaluacion_cognitiva_cod_tipo_eval_seq'::regclass);


--
-- Name: users cod_usu; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN cod_usu SET DEFAULT nextval('public.users_cod_usu_seq'::regclass);


--
-- Name: voluntarios cod_vol; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.voluntarios ALTER COLUMN cod_vol SET DEFAULT nextval('public.voluntarios_cod_vol_seq'::regclass);


--
-- Name: actividades_adulto actividades_adulto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividades_adulto
    ADD CONSTRAINT actividades_adulto_pkey PRIMARY KEY (cod_act_adul);


--
-- Name: activity_log activity_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log
    ADD CONSTRAINT activity_log_pkey PRIMARY KEY (id);


--
-- Name: adulto_mayor adulto_mayor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.adulto_mayor
    ADD CONSTRAINT adulto_mayor_pkey PRIMARY KEY (cod_am);


--
-- Name: asignacion_voluntarios asignacion_voluntarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignacion_voluntarios
    ADD CONSTRAINT asignacion_voluntarios_pkey PRIMARY KEY (cod_asig_vol);


--
-- Name: asistencia_voluntarios asistencia_voluntarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asistencia_voluntarios
    ADD CONSTRAINT asistencia_voluntarios_pkey PRIMARY KEY (cod_asis_vol);


--
-- Name: atenciones_adulto atenciones_adulto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.atenciones_adulto
    ADD CONSTRAINT atenciones_adulto_pkey PRIMARY KEY (cod_aten_adul);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: disponibilidad_voluntarios disponibilidad_voluntarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.disponibilidad_voluntarios
    ADD CONSTRAINT disponibilidad_voluntarios_pkey PRIMARY KEY (cod_hor_vol);


--
-- Name: documentos_adulto_mayor documentos_adulto_mayor_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documentos_adulto_mayor
    ADD CONSTRAINT documentos_adulto_mayor_pkey PRIMARY KEY (cod_doc_am);


--
-- Name: documentos_usuarios documentos_usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documentos_usuarios
    ADD CONSTRAINT documentos_usuarios_pkey PRIMARY KEY (cod_doc_usu);


--
-- Name: especialidades especialidades_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.especialidades
    ADD CONSTRAINT especialidades_nombre_unique UNIQUE (nombre);


--
-- Name: especialidades especialidades_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.especialidades
    ADD CONSTRAINT especialidades_pkey PRIMARY KEY (cod_esp);


--
-- Name: estado_adulto estado_adulto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.estado_adulto
    ADD CONSTRAINT estado_adulto_pkey PRIMARY KEY (cod_est_adul);


--
-- Name: evaluaciones_cognitivas evaluaciones_cognitivas_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluaciones_cognitivas
    ADD CONSTRAINT evaluaciones_cognitivas_pkey PRIMARY KEY (cod_eval_cog);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: familiar_adulto familiar_adulto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familiar_adulto
    ADD CONSTRAINT familiar_adulto_pkey PRIMARY KEY (id);


--
-- Name: familiares familiares_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familiares
    ADD CONSTRAINT familiares_pkey PRIMARY KEY (cod_fam);


--
-- Name: horarios_personal_admin horarios_personal_admin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.horarios_personal_admin
    ADD CONSTRAINT horarios_personal_admin_pkey PRIMARY KEY (cod_hor_per_admin);


--
-- Name: horarios_personal_salud horarios_personal_salud_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.horarios_personal_salud
    ADD CONSTRAINT horarios_personal_salud_pkey PRIMARY KEY (cod_hor_per_sal);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: obs_adulto obs_adulto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.obs_adulto
    ADD CONSTRAINT obs_adulto_pkey PRIMARY KEY (cod_obs_adul);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (correo);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: personal_admin personal_admin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_admin
    ADD CONSTRAINT personal_admin_pkey PRIMARY KEY (cod_per_adm);


--
-- Name: personal_salud personal_salud_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_salud
    ADD CONSTRAINT personal_salud_pkey PRIMARY KEY (cod_per_sal);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: tipo_actividades_adulto tipo_actividades_adulto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_actividades_adulto
    ADD CONSTRAINT tipo_actividades_adulto_pkey PRIMARY KEY (cod_tipo_act);


--
-- Name: tipo_actividades_adulto tipo_actividades_adulto_tipo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_actividades_adulto
    ADD CONSTRAINT tipo_actividades_adulto_tipo_unique UNIQUE (tipo);


--
-- Name: tipo_atenciones_adulto tipo_atenciones_adulto_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_atenciones_adulto
    ADD CONSTRAINT tipo_atenciones_adulto_pkey PRIMARY KEY (cod_tipo_aten);


--
-- Name: tipo_atenciones_adulto tipo_atenciones_adulto_tipo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_atenciones_adulto
    ADD CONSTRAINT tipo_atenciones_adulto_tipo_unique UNIQUE (tipo);


--
-- Name: tipo_evaluacion_cognitiva tipo_evaluacion_cognitiva_nombre_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_evaluacion_cognitiva
    ADD CONSTRAINT tipo_evaluacion_cognitiva_nombre_unique UNIQUE (nombre);


--
-- Name: tipo_evaluacion_cognitiva tipo_evaluacion_cognitiva_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.tipo_evaluacion_cognitiva
    ADD CONSTRAINT tipo_evaluacion_cognitiva_pkey PRIMARY KEY (cod_tipo_eval);


--
-- Name: familiar_adulto uidx_familiar_adulto; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familiar_adulto
    ADD CONSTRAINT uidx_familiar_adulto UNIQUE (cod_fam, cod_am);


--
-- Name: users users_correo_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_correo_unique UNIQUE (correo);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (cod_usu);


--
-- Name: voluntarios voluntarios_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.voluntarios
    ADD CONSTRAINT voluntarios_pkey PRIMARY KEY (cod_vol);


--
-- Name: activity_log_log_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX activity_log_log_name_index ON public.activity_log USING btree (log_name);


--
-- Name: adulto_mayor_identidad_unica_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX adulto_mayor_identidad_unica_idx ON public.adulto_mayor USING btree (ci, expedicion_ci, COALESCE(complemento_ci, ''::character varying)) WHERE ((ci IS NOT NULL) AND (expedicion_ci IS NOT NULL));


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: causer; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX causer ON public.activity_log USING btree (causer_type, causer_id);


--
-- Name: evaluaciones_cognitivas_cod_am_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evaluaciones_cognitivas_cod_am_index ON public.evaluaciones_cognitivas USING btree (cod_am);


--
-- Name: evaluaciones_cognitivas_cod_per_sal_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evaluaciones_cognitivas_cod_per_sal_index ON public.evaluaciones_cognitivas USING btree (cod_per_sal);


--
-- Name: evaluaciones_cognitivas_cod_tipo_eval_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evaluaciones_cognitivas_cod_tipo_eval_index ON public.evaluaciones_cognitivas USING btree (cod_tipo_eval);


--
-- Name: evaluaciones_cognitivas_estado_eval_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evaluaciones_cognitivas_estado_eval_index ON public.evaluaciones_cognitivas USING btree (estado_eval);


--
-- Name: evaluaciones_cognitivas_fecha_eval_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evaluaciones_cognitivas_fecha_eval_index ON public.evaluaciones_cognitivas USING btree (fecha_eval);


--
-- Name: evaluaciones_cognitivas_nivel_riesgo_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX evaluaciones_cognitivas_nivel_riesgo_index ON public.evaluaciones_cognitivas USING btree (nivel_riesgo);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: subject; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX subject ON public.activity_log USING btree (subject_type, subject_id);


--
-- Name: actividades_adulto actividades_adulto_cod_am_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividades_adulto
    ADD CONSTRAINT actividades_adulto_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES public.adulto_mayor(cod_am);


--
-- Name: actividades_adulto actividades_adulto_cod_tipo_act_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.actividades_adulto
    ADD CONSTRAINT actividades_adulto_cod_tipo_act_foreign FOREIGN KEY (cod_tipo_act) REFERENCES public.tipo_actividades_adulto(cod_tipo_act) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: adulto_mayor adulto_mayor_cod_est_adul_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.adulto_mayor
    ADD CONSTRAINT adulto_mayor_cod_est_adul_foreign FOREIGN KEY (cod_est_adul) REFERENCES public.estado_adulto(cod_est_adul) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: asignacion_voluntarios asignacion_voluntarios_cod_am_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignacion_voluntarios
    ADD CONSTRAINT asignacion_voluntarios_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES public.adulto_mayor(cod_am);


--
-- Name: asignacion_voluntarios asignacion_voluntarios_cod_vol_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asignacion_voluntarios
    ADD CONSTRAINT asignacion_voluntarios_cod_vol_foreign FOREIGN KEY (cod_vol) REFERENCES public.voluntarios(cod_vol) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: asistencia_voluntarios asistencia_voluntarios_cod_vol_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.asistencia_voluntarios
    ADD CONSTRAINT asistencia_voluntarios_cod_vol_foreign FOREIGN KEY (cod_vol) REFERENCES public.voluntarios(cod_vol) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: atenciones_adulto atenciones_adulto_cod_am_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.atenciones_adulto
    ADD CONSTRAINT atenciones_adulto_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES public.adulto_mayor(cod_am);


--
-- Name: atenciones_adulto atenciones_adulto_cod_tipo_aten_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.atenciones_adulto
    ADD CONSTRAINT atenciones_adulto_cod_tipo_aten_foreign FOREIGN KEY (cod_tipo_aten) REFERENCES public.tipo_atenciones_adulto(cod_tipo_aten) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: disponibilidad_voluntarios disponibilidad_voluntarios_cod_vol_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.disponibilidad_voluntarios
    ADD CONSTRAINT disponibilidad_voluntarios_cod_vol_foreign FOREIGN KEY (cod_vol) REFERENCES public.voluntarios(cod_vol) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: documentos_adulto_mayor documentos_adulto_mayor_cod_am_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documentos_adulto_mayor
    ADD CONSTRAINT documentos_adulto_mayor_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES public.adulto_mayor(cod_am);


--
-- Name: documentos_usuarios documentos_usuarios_cod_usu_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documentos_usuarios
    ADD CONSTRAINT documentos_usuarios_cod_usu_foreign FOREIGN KEY (cod_usu) REFERENCES public.users(cod_usu) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: evaluaciones_cognitivas evaluaciones_cognitivas_cod_am_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluaciones_cognitivas
    ADD CONSTRAINT evaluaciones_cognitivas_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES public.adulto_mayor(cod_am) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: evaluaciones_cognitivas evaluaciones_cognitivas_cod_per_sal_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluaciones_cognitivas
    ADD CONSTRAINT evaluaciones_cognitivas_cod_per_sal_foreign FOREIGN KEY (cod_per_sal) REFERENCES public.personal_salud(cod_per_sal) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: evaluaciones_cognitivas evaluaciones_cognitivas_cod_tipo_eval_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.evaluaciones_cognitivas
    ADD CONSTRAINT evaluaciones_cognitivas_cod_tipo_eval_foreign FOREIGN KEY (cod_tipo_eval) REFERENCES public.tipo_evaluacion_cognitiva(cod_tipo_eval) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: familiar_adulto familiar_adulto_cod_am_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familiar_adulto
    ADD CONSTRAINT familiar_adulto_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES public.adulto_mayor(cod_am);


--
-- Name: familiar_adulto familiar_adulto_cod_fam_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familiar_adulto
    ADD CONSTRAINT familiar_adulto_cod_fam_foreign FOREIGN KEY (cod_fam) REFERENCES public.familiares(cod_fam) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: familiares familiares_cod_usu_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.familiares
    ADD CONSTRAINT familiares_cod_usu_foreign FOREIGN KEY (cod_usu) REFERENCES public.users(cod_usu) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: horarios_personal_admin horarios_personal_admin_cod_per_adm_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.horarios_personal_admin
    ADD CONSTRAINT horarios_personal_admin_cod_per_adm_foreign FOREIGN KEY (cod_per_adm) REFERENCES public.personal_admin(cod_per_adm) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: horarios_personal_salud horarios_personal_salud_cod_per_sal_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.horarios_personal_salud
    ADD CONSTRAINT horarios_personal_salud_cod_per_sal_foreign FOREIGN KEY (cod_per_sal) REFERENCES public.personal_salud(cod_per_sal) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: obs_adulto obs_adulto_cod_am_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.obs_adulto
    ADD CONSTRAINT obs_adulto_cod_am_foreign FOREIGN KEY (cod_am) REFERENCES public.adulto_mayor(cod_am);


--
-- Name: obs_adulto obs_adulto_cod_est_adul_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.obs_adulto
    ADD CONSTRAINT obs_adulto_cod_est_adul_foreign FOREIGN KEY (cod_est_adul) REFERENCES public.estado_adulto(cod_est_adul) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: obs_adulto obs_adulto_registrado_por_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.obs_adulto
    ADD CONSTRAINT obs_adulto_registrado_por_foreign FOREIGN KEY (registrado_por) REFERENCES public.users(cod_usu) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: personal_admin personal_admin_cod_usu_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_admin
    ADD CONSTRAINT personal_admin_cod_usu_foreign FOREIGN KEY (cod_usu) REFERENCES public.users(cod_usu) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: personal_salud personal_salud_cod_esp_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_salud
    ADD CONSTRAINT personal_salud_cod_esp_foreign FOREIGN KEY (cod_esp) REFERENCES public.especialidades(cod_esp) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: personal_salud personal_salud_cod_usu_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_salud
    ADD CONSTRAINT personal_salud_cod_usu_foreign FOREIGN KEY (cod_usu) REFERENCES public.users(cod_usu) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: voluntarios voluntarios_cod_usu_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.voluntarios
    ADD CONSTRAINT voluntarios_cod_usu_foreign FOREIGN KEY (cod_usu) REFERENCES public.users(cod_usu) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict WpaBgv4dLwpflmsWh0JCTVVa99iBdHRLhmdqkImJtSsWrZllI1vvdVLjm3C2mwl

