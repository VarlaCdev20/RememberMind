--
-- PostgreSQL database dump
--

\restrict YWja4Ww0kw7Tey96FfRGTY0lyUnLXK2sDiFmcGq2p6dCGgDPUJYUjh36uB9FZuJ

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
-- Data for Name: actividades_adulto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.actividades_adulto (cod_act_adul, fecha, hora, obs, estado, cod_tipo_act, cod_am, hora_fin, responsable_tipo, responsable_id, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: activity_log; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.activity_log (id, log_name, description, subject_type, subject_id, causer_type, causer_id, properties, created_at, updated_at, event, batch_uuid) FROM stdin;
1	default	Usuario created	App\\Models\\User	2	\N	\N	{"attributes":{"nombres":"Super","correo":"admincasaamandita@gmail.com"}}	2026-04-24 00:27:11	2026-04-24 00:27:11	created	\N
2	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-26 14:21:06	2026-04-26 14:21:06	updated	\N
3	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-27 02:19:04	2026-04-27 02:19:04	updated	\N
4	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-27 12:36:02	2026-04-27 12:36:02	updated	\N
5	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 18:51:42	2026-04-27 18:51:42	accessed	\N
6	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-27 18:53:06	2026-04-27 18:53:06	updated	\N
7	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 18:58:03	2026-04-27 18:58:03	accessed	\N
8	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 19:29:28	2026-04-27 19:29:28	accessed	\N
9	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 19:29:34	2026-04-27 19:29:34	accessed	\N
10	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 19:29:47	2026-04-27 19:29:47	accessed	\N
11	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-27 19:29:57	2026-04-27 19:29:57	updated	\N
12	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 19:37:32	2026-04-27 19:37:32	accessed	\N
13	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 19:47:40	2026-04-27 19:47:40	accessed	\N
14	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 19:53:32	2026-04-27 19:53:32	accessed	\N
15	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 20:12:57	2026-04-27 20:12:57	accessed	\N
16	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 20:23:07	2026-04-27 20:23:07	accessed	\N
17	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 20:49:36	2026-04-27 20:49:36	accessed	\N
18	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 20:50:15	2026-04-27 20:50:15	accessed	\N
19	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:02:50	2026-04-27 21:02:50	accessed	\N
20	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:06:24	2026-04-27 21:06:24	accessed	\N
21	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:09:42	2026-04-27 21:09:42	accessed	\N
22	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:10:37	2026-04-27 21:10:37	accessed	\N
23	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:10:43	2026-04-27 21:10:43	accessed	\N
24	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:10:47	2026-04-27 21:10:47	accessed	\N
25	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:10:58	2026-04-27 21:10:58	accessed	\N
26	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:11:05	2026-04-27 21:11:05	accessed	\N
27	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:11:20	2026-04-27 21:11:20	accessed	\N
28	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:11:28	2026-04-27 21:11:28	accessed	\N
29	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:11:31	2026-04-27 21:11:31	accessed	\N
30	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:11:34	2026-04-27 21:11:34	accessed	\N
31	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:11:42	2026-04-27 21:11:42	accessed	\N
32	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:11:52	2026-04-27 21:11:52	accessed	\N
33	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:11:59	2026-04-27 21:11:59	accessed	\N
34	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:12:11	2026-04-27 21:12:11	accessed	\N
35	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:12:13	2026-04-27 21:12:13	accessed	\N
36	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:12:21	2026-04-27 21:12:21	accessed	\N
37	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:12:25	2026-04-27 21:12:25	accessed	\N
38	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:12:28	2026-04-27 21:12:28	accessed	\N
39	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:12:53	2026-04-27 21:12:53	accessed	\N
40	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:13:01	2026-04-27 21:13:01	accessed	\N
41	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:13:04	2026-04-27 21:13:04	accessed	\N
42	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:13:18	2026-04-27 21:13:18	accessed	\N
43	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:13:40	2026-04-27 21:13:40	accessed	\N
44	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:32:00	2026-04-27 21:32:00	accessed	\N
45	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:32:04	2026-04-27 21:32:04	accessed	\N
46	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:32:08	2026-04-27 21:32:08	accessed	\N
47	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:32:14	2026-04-27 21:32:14	accessed	\N
48	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:32:18	2026-04-27 21:32:18	accessed	\N
49	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:32:33	2026-04-27 21:32:33	accessed	\N
50	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:33:04	2026-04-27 21:33:04	accessed	\N
51	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:33:09	2026-04-27 21:33:09	accessed	\N
52	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:33:19	2026-04-27 21:33:19	accessed	\N
53	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:33:34	2026-04-27 21:33:34	accessed	\N
54	dashboard	Acceso al dashboard	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1"}	2026-04-27 21:33:36	2026-04-27 21:33:36	accessed	\N
55	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-27 23:11:52	2026-04-27 23:11:52	updated	\N
56	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-27 23:16:35	2026-04-27 23:16:35	updated	\N
57	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-28 01:04:30	2026-04-28 01:04:30	updated	\N
58	dashboard	Acceso al dashboard institucional	\N	\N	App\\Models\\User	2	{"ip":"127.0.0.1","agente":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","rol":"admin"}	2026-04-29 13:46:18	2026-04-29 13:46:18	accessed	\N
59	dashboard	Acceso al dashboard institucional	\N	\N	App\\Models\\User	2	{"ip":"127.0.0.1","agente":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","rol":"admin"}	2026-04-29 13:46:36	2026-04-29 13:46:36	accessed	\N
60	dashboard	Acceso al dashboard institucional	\N	\N	App\\Models\\User	2	{"ip":"127.0.0.1","agente":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","rol":"admin"}	2026-04-29 13:46:47	2026-04-29 13:46:47	accessed	\N
61	dashboard	Acceso al dashboard institucional	\N	\N	App\\Models\\User	2	{"ip":"127.0.0.1","agente":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","rol":"admin"}	2026-04-29 13:47:33	2026-04-29 13:47:33	accessed	\N
62	dashboard	Acceso al dashboard institucional	\N	\N	App\\Models\\User	2	{"ip":"127.0.0.1","agente":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","rol":"admin"}	2026-04-29 13:47:43	2026-04-29 13:47:43	accessed	\N
63	dashboard	Acceso al dashboard institucional	\N	\N	App\\Models\\User	2	{"ip":"127.0.0.1","agente":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","rol":"admin"}	2026-04-29 13:48:44	2026-04-29 13:48:44	accessed	\N
64	dashboard	Acceso al dashboard institucional	\N	\N	App\\Models\\User	2	{"ip":"127.0.0.1","agente":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","rol":"admin"}	2026-04-29 14:19:23	2026-04-29 14:19:23	accessed	\N
65	dashboard	Acceso al dashboard institucional	\N	\N	App\\Models\\User	2	{"ip":"127.0.0.1","agente":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","rol":"admin"}	2026-04-29 18:57:52	2026-04-29 18:57:52	accessed	\N
66	dashboard	El usuario Super accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Dashboard Principal"}	2026-04-29 19:08:19	2026-04-29 19:08:19	acceso	\N
67	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:19:16	2026-04-29 19:19:16	acceso	\N
68	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:20:16	2026-04-29 19:20:16	acceso	\N
69	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:20:32	2026-04-29 19:20:32	acceso	\N
70	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:20:47	2026-04-29 19:20:47	acceso	\N
71	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:20:59	2026-04-29 19:20:59	acceso	\N
72	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:21:30	2026-04-29 19:21:30	acceso	\N
73	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:21:48	2026-04-29 19:21:48	acceso	\N
74	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:21:59	2026-04-29 19:21:59	acceso	\N
75	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:22:10	2026-04-29 19:22:10	acceso	\N
76	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:22:40	2026-04-29 19:22:40	acceso	\N
77	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:24:26	2026-04-29 19:24:26	acceso	\N
78	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:25:26	2026-04-29 19:25:26	acceso	\N
79	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 19:25:36	2026-04-29 19:25:36	acceso	\N
80	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:18:49	2026-04-29 20:18:49	acceso	\N
81	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:18:53	2026-04-29 20:18:53	acceso	\N
82	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:20	2026-04-29 20:19:20	acceso	\N
83	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:20	2026-04-29 20:19:20	acceso	\N
84	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:20	2026-04-29 20:19:20	acceso	\N
85	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:20	2026-04-29 20:19:20	acceso	\N
86	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:21	2026-04-29 20:19:21	acceso	\N
87	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:21	2026-04-29 20:19:21	acceso	\N
88	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:21	2026-04-29 20:19:21	acceso	\N
89	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:21	2026-04-29 20:19:21	acceso	\N
90	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:21	2026-04-29 20:19:21	acceso	\N
91	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:21	2026-04-29 20:19:21	acceso	\N
92	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:21	2026-04-29 20:19:21	acceso	\N
93	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:22	2026-04-29 20:19:22	acceso	\N
94	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:22	2026-04-29 20:19:22	acceso	\N
95	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:22	2026-04-29 20:19:22	acceso	\N
96	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:22	2026-04-29 20:19:22	acceso	\N
97	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:22	2026-04-29 20:19:22	acceso	\N
98	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:22	2026-04-29 20:19:22	acceso	\N
99	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:22	2026-04-29 20:19:22	acceso	\N
100	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:23	2026-04-29 20:19:23	acceso	\N
101	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:23	2026-04-29 20:19:23	acceso	\N
102	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:23	2026-04-29 20:19:23	acceso	\N
103	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:23	2026-04-29 20:19:23	acceso	\N
104	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:23	2026-04-29 20:19:23	acceso	\N
105	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:23	2026-04-29 20:19:23	acceso	\N
106	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:23	2026-04-29 20:19:23	acceso	\N
107	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:23	2026-04-29 20:19:23	acceso	\N
108	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:24	2026-04-29 20:19:24	acceso	\N
109	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:24	2026-04-29 20:19:24	acceso	\N
110	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:24	2026-04-29 20:19:24	acceso	\N
111	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:24	2026-04-29 20:19:24	acceso	\N
112	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:24	2026-04-29 20:19:24	acceso	\N
113	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:24	2026-04-29 20:19:24	acceso	\N
114	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:19:24	2026-04-29 20:19:24	acceso	\N
115	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 20:32:27	2026-04-29 20:32:27	updated	\N
116	Usuarios	Se desactivó el usuario Super Administrador.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 20:32:27	2026-04-29 20:32:27	edicion	\N
117	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 20:32:35	2026-04-29 20:32:35	updated	\N
118	Usuarios	Se activó el usuario Super Administrador.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 20:32:35	2026-04-29 20:32:35	edicion	\N
119	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 20:32:38	2026-04-29 20:32:38	updated	\N
120	Usuarios	Se desactivó el usuario Super Administrador.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 20:32:38	2026-04-29 20:32:38	edicion	\N
121	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":{"nombres":"Juan Jose"},"old":{"nombres":"Super"}}	2026-04-29 20:33:52	2026-04-29 20:33:52	updated	\N
122	Usuarios	Se actualizó la información del usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 20:33:52	2026-04-29 20:33:52	edicion	\N
123	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 20:34:55	2026-04-29 20:34:55	updated	\N
124	Usuarios	Se desactivó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 20:34:55	2026-04-29 20:34:55	edicion	\N
125	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 20:34:57	2026-04-29 20:34:57	updated	\N
126	Usuarios	Se activó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 20:34:57	2026-04-29 20:34:57	edicion	\N
127	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:45:09	2026-04-29 20:45:09	acceso	\N
128	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:45:12	2026-04-29 20:45:12	acceso	\N
129	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:45:12	2026-04-29 20:45:12	acceso	\N
130	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:45:18	2026-04-29 20:45:18	acceso	\N
131	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:45:26	2026-04-29 20:45:26	acceso	\N
132	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:45:34	2026-04-29 20:45:34	acceso	\N
133	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:45:47	2026-04-29 20:45:47	acceso	\N
134	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:46:11	2026-04-29 20:46:11	acceso	\N
135	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:46:23	2026-04-29 20:46:23	acceso	\N
136	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:53	2026-04-29 20:47:53	acceso	\N
137	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:53	2026-04-29 20:47:53	acceso	\N
138	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:53	2026-04-29 20:47:53	acceso	\N
139	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:54	2026-04-29 20:47:54	acceso	\N
140	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:54	2026-04-29 20:47:54	acceso	\N
141	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:54	2026-04-29 20:47:54	acceso	\N
142	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:54	2026-04-29 20:47:54	acceso	\N
143	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:54	2026-04-29 20:47:54	acceso	\N
144	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:54	2026-04-29 20:47:54	acceso	\N
145	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:55	2026-04-29 20:47:55	acceso	\N
146	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 20:47:59	2026-04-29 20:47:59	acceso	\N
147	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 21:02:55	2026-04-29 21:02:55	acceso	\N
148	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 21:02:58	2026-04-29 21:02:58	acceso	\N
149	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 21:02:58	2026-04-29 21:02:58	acceso	\N
150	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 21:02:59	2026-04-29 21:02:59	acceso	\N
151	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 21:37:07	2026-04-29 21:37:07	updated	\N
152	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 21:37:52	2026-04-29 21:37:52	acceso	\N
153	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 21:37:55	2026-04-29 21:37:55	acceso	\N
154	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 21:38:15	2026-04-29 21:38:15	acceso	\N
155	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 21:42:17	2026-04-29 21:42:17	updated	\N
156	Usuarios	Se desactivó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 21:42:17	2026-04-29 21:42:17	edicion	\N
157	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 21:44:07	2026-04-29 21:44:07	updated	\N
158	Usuarios	Se activó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 21:44:07	2026-04-29 21:44:07	edicion	\N
159	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 21:44:08	2026-04-29 21:44:08	updated	\N
160	Usuarios	Se desactivó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 21:44:08	2026-04-29 21:44:08	edicion	\N
161	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 22:07:39	2026-04-29 22:07:39	updated	\N
162	Usuarios	Se activó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 22:07:39	2026-04-29 22:07:39	edicion	\N
163	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 23:22:49	2026-04-29 23:22:49	acceso	\N
164	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 23:22:57	2026-04-29 23:22:57	acceso	\N
165	default	Usuario updated	App\\Models\\User	2	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 23:23:01	2026-04-29 23:23:01	updated	\N
166	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 23:26:23	2026-04-29 23:26:23	acceso	\N
167	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 23:26:27	2026-04-29 23:26:27	acceso	\N
168	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 23:26:33	2026-04-29 23:26:33	acceso	\N
169	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-29 23:27:13	2026-04-29 23:27:13	acceso	\N
170	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 23:27:20	2026-04-29 23:27:20	updated	\N
171	Usuarios	Se desactivó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 23:27:20	2026-04-29 23:27:20	edicion	\N
172	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-29 23:27:22	2026-04-29 23:27:22	updated	\N
173	Usuarios	Se activó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-29 23:27:23	2026-04-29 23:27:23	edicion	\N
174	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 01:22:16	2026-04-30 01:22:16	acceso	\N
175	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 01:22:33	2026-04-30 01:22:33	acceso	\N
176	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 02:33:18	2026-04-30 02:33:18	acceso	\N
177	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 02:49:04	2026-04-30 02:49:04	acceso	\N
178	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 03:49:30	2026-04-30 03:49:30	acceso	\N
179	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 03:51:39	2026-04-30 03:51:39	acceso	\N
180	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 05:35:51	2026-04-30 05:35:51	acceso	\N
181	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 05:36:09	2026-04-30 05:36:09	acceso	\N
182	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 05:37:32	2026-04-30 05:37:32	acceso	\N
183	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 05:43:44	2026-04-30 05:43:44	acceso	\N
184	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 05:44:54	2026-04-30 05:44:54	acceso	\N
185	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 07:04:38	2026-04-30 07:04:38	acceso	\N
186	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 10:54:50	2026-04-30 10:54:50	acceso	\N
187	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 12:13:36	2026-04-30 12:13:36	acceso	\N
188	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 12:19:49	2026-04-30 12:19:49	acceso	\N
189	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 12:20:40	2026-04-30 12:20:40	acceso	\N
190	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-30 12:20:46	2026-04-30 12:20:46	updated	\N
191	Usuarios	Se desactivó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-30 12:20:46	2026-04-30 12:20:46	edicion	\N
192	default	Usuario updated	App\\Models\\User	1	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-04-30 12:20:48	2026-04-30 12:20:48	updated	\N
193	Usuarios	Se activó el usuario Juan Jose Espinoza Mendez.	App\\Models\\User	1	App\\Models\\User	2	[]	2026-04-30 12:20:48	2026-04-30 12:20:48	edicion	\N
194	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 19:03:45	2026-04-30 19:03:45	acceso	\N
195	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 19:03:56	2026-04-30 19:03:56	acceso	\N
196	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 19:11:42	2026-04-30 19:11:42	acceso	\N
197	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-04-30 19:23:24	2026-04-30 19:23:24	acceso	\N
198	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 14:22:26	2026-05-01 14:22:26	acceso	\N
199	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 14:22:43	2026-05-01 14:22:43	acceso	\N
200	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 16:28:13	2026-05-01 16:28:13	acceso	\N
201	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 16:28:21	2026-05-01 16:28:21	acceso	\N
202	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 16:28:40	2026-05-01 16:28:40	acceso	\N
203	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 16:28:46	2026-05-01 16:28:46	acceso	\N
204	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 16:55:55	2026-05-01 16:55:55	acceso	\N
205	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 17:38:25	2026-05-01 17:38:25	acceso	\N
206	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 17:39:11	2026-05-01 17:39:11	acceso	\N
207	Adulto Mayor	Se actualizó la ficha institucional del adulto mayor AM_0002.	App\\Models\\AdultoMayor	AM_0002	App\\Models\\User	2	{"attributes":[],"old":[]}	2026-05-01 18:36:12	2026-05-01 18:36:12	updated	\N
208	default	created	App\\Models\\ObsAdulto	1	App\\Models\\User	2	{"attributes":{"fecha":"2026-05-01T00:00:00.000000Z","tipo_obs":"Conductual","descripcion":"Se ha observado un cambio de conducta","cod_am":"AM_0002","cod_est_adul":1}}	2026-05-01 18:47:40	2026-05-01 18:47:40	created	\N
209	adulto_mayor	Se registró una observación de tipo Conductual para el adulto mayor Carlos Condori Gutiérrez con ficha AM_0002.	App\\Models\\ObsAdulto	1	App\\Models\\User	2	[]	2026-05-01 18:47:40	2026-05-01 18:47:40	observacion_creada	\N
210	Adulto Mayor	Se restauró la ficha del adulto mayor AM_0004.	App\\Models\\AdultoMayor	AM_0004	App\\Models\\User	2	{"attributes":{"cod_est_adul":1,"archivado_en":null,"motivo_archivado":null},"old":{"cod_est_adul":2,"archivado_en":"2026-05-01T16:05:21.000000Z","motivo_archivado":"Archivado administrativamente"}}	2026-05-01 19:18:38	2026-05-01 19:18:38	updated	\N
211	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:24:29	2026-05-01 19:24:29	acceso	\N
212	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:24:45	2026-05-01 19:24:45	acceso	\N
213	Adulto Mayor	Se restauró la ficha del adulto mayor AM_0001.	App\\Models\\AdultoMayor	AM_0001	App\\Models\\User	2	{"attributes":{"cod_est_adul":1,"archivado_en":null,"motivo_archivado":null},"old":{"cod_est_adul":2,"archivado_en":"2026-04-30T14:11:03.000000Z","motivo_archivado":"Archivado administrativamente"}}	2026-05-01 19:38:36	2026-05-01 19:38:36	updated	\N
214	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:48:00	2026-05-01 19:48:00	acceso	\N
215	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:48:22	2026-05-01 19:48:22	acceso	\N
216	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:48:26	2026-05-01 19:48:26	acceso	\N
217	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:51:00	2026-05-01 19:51:00	acceso	\N
218	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:52:57	2026-05-01 19:52:57	acceso	\N
219	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:55:23	2026-05-01 19:55:23	acceso	\N
220	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:21	2026-05-01 19:56:21	acceso	\N
221	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:30	2026-05-01 19:56:30	acceso	\N
222	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:41	2026-05-01 19:56:41	acceso	\N
223	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:45	2026-05-01 19:56:45	acceso	\N
224	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:47	2026-05-01 19:56:47	acceso	\N
225	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:47	2026-05-01 19:56:47	acceso	\N
226	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:48	2026-05-01 19:56:48	acceso	\N
227	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:49	2026-05-01 19:56:49	acceso	\N
228	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:50	2026-05-01 19:56:50	acceso	\N
229	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:56:51	2026-05-01 19:56:51	acceso	\N
230	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 19:57:54	2026-05-01 19:57:54	acceso	\N
231	Adulto Mayor	Se archivó la ficha del adulto mayor AM_0003.	App\\Models\\AdultoMayor	AM_0003	App\\Models\\User	2	{"attributes":{"cod_est_adul":2,"archivado_en":"2026-05-01T20:01:00.000000Z","motivo_archivado":"Cambio de estado r\\u00e1pido"},"old":{"cod_est_adul":1,"archivado_en":null,"motivo_archivado":null}}	2026-05-01 20:01:00	2026-05-01 20:01:00	updated	\N
232	Adulto Mayor	Se actualizó la ficha institucional del adulto mayor AM_0005.	App\\Models\\AdultoMayor	AM_0005	App\\Models\\User	2	{"attributes":{"cod_est_adul":3,"archivado_en":"2026-05-01T20:01:14.000000Z","motivo_archivado":"Cambio de estado r\\u00e1pido"},"old":{"cod_est_adul":1,"archivado_en":null,"motivo_archivado":null}}	2026-05-01 20:01:14	2026-05-01 20:01:14	updated	\N
251	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:42:49	2026-05-01 20:42:49	acceso	\N
252	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:42:54	2026-05-01 20:42:54	acceso	\N
253	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:44:11	2026-05-01 20:44:11	acceso	\N
233	Adulto Mayor	Se actualizó la ficha institucional del adulto mayor AM_0002.	App\\Models\\AdultoMayor	AM_0002	App\\Models\\User	2	{"attributes":{"complemento_ci":"1A","expedicion_ci":"LP","fecha_nac":"1945-11-12T00:00:00.000000Z","celular":"69771661","departamento_residencia":"LA PAZ","ciudad_municipio":"EL ALTO","fecha_ing":"2025-10-12T00:00:00.000000Z","grupo_sanguineo":"B+","alergias":"ALERGIA A LAS MANZANAS","seguro_salud":"CAJA NACIONAL CNS","contacto_emergencia_nombre":"ANA MARIA GOMEZ","contacto_emergencia_parentesco":"NIETO\\/A","contacto_emergencia_celular":"67112224","contacto_emergencia_direccion":"MBKLNL","responsable_principal":true,"autorizado_informacion_medica":true,"consentimiento_datos":true},"old":{"complemento_ci":null,"expedicion_ci":null,"fecha_nac":"1961-06-16T00:00:00.000000Z","celular":null,"departamento_residencia":null,"ciudad_municipio":null,"fecha_ing":"2026-04-30T00:00:00.000000Z","grupo_sanguineo":null,"alergias":null,"seguro_salud":null,"contacto_emergencia_nombre":null,"contacto_emergencia_parentesco":null,"contacto_emergencia_celular":null,"contacto_emergencia_direccion":null,"responsable_principal":false,"autorizado_informacion_medica":false,"consentimiento_datos":false}}	2026-05-01 20:11:43	2026-05-01 20:11:43	updated	\N
234	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:39:08	2026-05-01 20:39:08	acceso	\N
235	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:39:28	2026-05-01 20:39:28	acceso	\N
236	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:39:49	2026-05-01 20:39:49	acceso	\N
237	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:40:41	2026-05-01 20:40:41	acceso	\N
238	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:40:55	2026-05-01 20:40:55	acceso	\N
239	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:02	2026-05-01 20:41:02	acceso	\N
240	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:04	2026-05-01 20:41:04	acceso	\N
241	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:06	2026-05-01 20:41:06	acceso	\N
242	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:07	2026-05-01 20:41:07	acceso	\N
243	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:08	2026-05-01 20:41:08	acceso	\N
244	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:09	2026-05-01 20:41:09	acceso	\N
245	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:11	2026-05-01 20:41:11	acceso	\N
246	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:12	2026-05-01 20:41:12	acceso	\N
247	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:13	2026-05-01 20:41:13	acceso	\N
248	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:15	2026-05-01 20:41:15	acceso	\N
249	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:17	2026-05-01 20:41:17	acceso	\N
250	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:41:22	2026-05-01 20:41:22	acceso	\N
254	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:44:25	2026-05-01 20:44:25	acceso	\N
255	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 20:44:26	2026-05-01 20:44:26	acceso	\N
256	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:10:12	2026-05-01 21:10:12	acceso	\N
257	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:10:18	2026-05-01 21:10:18	acceso	\N
258	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:10:28	2026-05-01 21:10:28	acceso	\N
259	Usuarios	Se actualizó la información del usuario Super Administrador.	App\\Models\\User	2	App\\Models\\User	2	[]	2026-05-01 21:10:46	2026-05-01 21:10:46	edicion	\N
260	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:10:58	2026-05-01 21:10:58	acceso	\N
261	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:11:08	2026-05-01 21:11:08	acceso	\N
262	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:11:48	2026-05-01 21:11:48	acceso	\N
263	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:35	2026-05-01 21:14:35	acceso	\N
264	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:39	2026-05-01 21:14:39	acceso	\N
265	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:39	2026-05-01 21:14:39	acceso	\N
266	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:39	2026-05-01 21:14:39	acceso	\N
267	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:40	2026-05-01 21:14:40	acceso	\N
268	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:40	2026-05-01 21:14:40	acceso	\N
269	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:44	2026-05-01 21:14:44	acceso	\N
270	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:47	2026-05-01 21:14:47	acceso	\N
271	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:48	2026-05-01 21:14:48	acceso	\N
272	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:48	2026-05-01 21:14:48	acceso	\N
273	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:48	2026-05-01 21:14:48	acceso	\N
274	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:48	2026-05-01 21:14:48	acceso	\N
275	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:48	2026-05-01 21:14:48	acceso	\N
276	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:54	2026-05-01 21:14:54	acceso	\N
277	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:57	2026-05-01 21:14:57	acceso	\N
278	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:57	2026-05-01 21:14:57	acceso	\N
279	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:14:57	2026-05-01 21:14:57	acceso	\N
280	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:15:00	2026-05-01 21:15:00	acceso	\N
281	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:15:01	2026-05-01 21:15:01	acceso	\N
282	Adulto Mayor	Se actualizó la ficha institucional del adulto mayor AM_0002.	App\\Models\\AdultoMayor	AM_0002	App\\Models\\User	2	{"attributes":{"fecha_nac":"1945-10-12T00:00:00.000000Z","fecha_ing":"2025-12-10T00:00:00.000000Z","consentimiento_datos":false},"old":{"fecha_nac":"1945-11-12T00:00:00.000000Z","fecha_ing":"2025-10-12T00:00:00.000000Z","consentimiento_datos":true}}	2026-05-01 21:16:59	2026-05-01 21:16:59	updated	\N
283	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:17:36	2026-05-01 21:17:36	acceso	\N
284	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-01 21:53:33	2026-05-01 21:53:33	acceso	\N
285	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-02 00:17:21	2026-05-02 00:17:21	acceso	\N
286	default	created	App\\Models\\ObsAdulto	2	App\\Models\\User	2	{"attributes":{"fecha":"2026-05-02T00:00:00.000000Z","tipo_obs":"Conductual","descripcion":"esta malito","cod_am":"AM_0002","cod_est_adul":1,"registrado_por":2,"nivel_importancia":"NORMAL"}}	2026-05-02 00:17:57	2026-05-02 00:17:57	created	\N
287	adulto_mayor	Se registró una observación de tipo Conductual para el adulto mayor Carlos Condori Gutiérrez con ficha AM_0002.	App\\Models\\ObsAdulto	2	App\\Models\\User	2	[]	2026-05-02 00:17:57	2026-05-02 00:17:57	observacion_creada	\N
288	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-02 00:21:19	2026-05-02 00:21:19	acceso	\N
289	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-02 01:12:12	2026-05-02 01:12:12	acceso	\N
290	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-02 01:12:15	2026-05-02 01:12:15	acceso	\N
291	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-02 01:12:18	2026-05-02 01:12:18	acceso	\N
292	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-02 02:21:00	2026-05-02 02:21:00	acceso	\N
293	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-02 02:21:04	2026-05-02 02:21:04	acceso	\N
294	Panel principal	El usuario accedió al panel principal de control.	\N	\N	App\\Models\\User	2	{"rol":"admin","correo":"admincasaamandita@gmail.com","ip":"127.0.0.1","navegador":"Mozilla\\/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit\\/537.36 (KHTML, like Gecko) Chrome\\/147.0.0.0 Safari\\/537.36","modulo":"Panel principal"}	2026-05-02 02:21:06	2026-05-02 02:21:06	acceso	\N
\.


--
-- Data for Name: adulto_mayor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.adulto_mayor (cod_am, nombres, ap_paterno, ap_materno, ci, fecha_nac, genero, estado_civil, telefono, zona, calle, fecha_ing, tipo_ing, permanencia, nivel_educat, observaciones, cod_est_adul, created_at, updated_at, foto, archivado_en, motivo_archivado, hora_ing, complemento_ci, expedicion_ci, celular, telefono_fijo, departamento_residencia, ciudad_municipio, grupo_sanguineo, factor_rh, alergias, seguro_salud, contacto_emergencia_nombre, contacto_emergencia_parentesco, contacto_emergencia_celular, contacto_emergencia_direccion, responsable_principal, autorizado_informacion_medica, consentimiento_datos) FROM stdin;
AM_0006	José	Apaza	Vargas	5574807	1958-07-26	MASCULINO	DIVORCIADO/A	71567630	Achumani	Calle Jaén	2026-04-30	VOLUNTARIO	TEMPORAL	SIN EDUCACIÓN FORMAL	Requiere seguimiento periódico.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0007	Pedro	Choque	Choque	1837561	1963-07-14	MASCULINO	DIVORCIADO/A	72222881	Achumani	Av. Arce	2026-04-30	EMERGENCIA	TEMPORAL	SIN EDUCACIÓN FORMAL	Presenta buena integración social.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0008	María	Laura	Mamani	5219560	1940-11-30	FEMENINO	CASADO/A	72340542	Villa Armonía	Av. Busch	2026-04-30	REGULAR	EVENTUAL	SUPERIOR	Presenta buena integración social.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0009	Ana	Flores	Quispe	4534809	1962-01-18	FEMENINO	DIVORCIADO/A	77131559	Centro	Calle Sagárnaga	2026-04-30	REGULAR	PERMANENTE	NO ESPECIFICADO	Participa regularmente en actividades institucionales.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0010	Carlos	Gutiérrez	Apaza	9513310	1940-09-21	MASCULINO	CASADO/A	76547194	San Pedro	Calle Jaén	2026-04-30	VOLUNTARIO	PERMANENTE	SIN EDUCACIÓN FORMAL	Participa regularmente en actividades institucionales.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0011	Pedro	Calle	Rojas	9696873	1962-03-21	MASCULINO	DIVORCIADO/A	74917047	Centro	Calle Sagárnaga	2026-04-30	EMERGENCIA	PERMANENTE	NO ESPECIFICADO	Paciente estable en seguimiento.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0012	María	Rojas	Paredes	5670262	1961-09-03	FEMENINO	DIVORCIADO/A	75272994	Miraflores	Calle Comercio	2026-04-30	DERIVADO	EVENTUAL	SIN EDUCACIÓN FORMAL	Registro inicial sin observaciones críticas.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0013	Pedro	Mamani	Calle	2324487	1940-06-10	MASCULINO	CASADO/A	74260026	Sopocachi	Calle Jaén	2026-04-30	DERIVADO	EVENTUAL	PRIMARIA	Participa regularmente en actividades institucionales.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0014	Luis	Gutiérrez	Alarcón	7283473	1955-11-27	MASCULINO	CASADO/A	72061982	Sopocachi	Calle Jaén	2026-04-30	REGULAR	TEMPORAL	SUPERIOR	Participa regularmente en actividades institucionales.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0015	Rosa	Mendoza	Apaza	8101457	1958-09-16	FEMENINO	VIUDO/A	75297542	Sopocachi	Av. 6 de Agosto	2026-04-30	EMERGENCIA	EVENTUAL	NO ESPECIFICADO	Requiere seguimiento periódico.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0016	Pedro	Gutiérrez	Vargas	4212424	1944-08-21	MASCULINO	VIUDO/A	71206178	Villa Copacabana	Av. Camacho	2026-04-30	VOLUNTARIO	TEMPORAL	SECUNDARIA	Participa regularmente en actividades institucionales.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0017	Mario	Quispe	Calle	9954268	1961-03-30	MASCULINO	DIVORCIADO/A	72752381	Obrajes	Calle Comercio	2026-04-30	DERIVADO	PERMANENTE	SUPERIOR	Presenta buena integración social.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0018	Juana	Alarcón	Gutiérrez	8945362	1952-07-14	FEMENINO	DIVORCIADO/A	79716128	San Pedro	Calle Comercio	2026-04-30	REGULAR	PERMANENTE	SECUNDARIA	Se recomienda control institucional continuo.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0019	Mario	Vargas	Rojas	2575771	1961-02-24	MASCULINO	SOLTERO/A	78066738	Achumani	Calle Comercio	2026-04-30	VOLUNTARIO	PERMANENTE	SIN EDUCACIÓN FORMAL	Registro inicial sin observaciones críticas.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0020	Elena	Gutiérrez	Mamani	1649379	1941-08-14	FEMENINO	VIUDO/A	77335597	Sopocachi	Av. Saavedra	2026-04-30	DERIVADO	TEMPORAL	PRIMARIA	Paciente estable en seguimiento.	1	2026-04-30 11:50:00	2026-04-30 11:50:00	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0004	Ana	Flores	Apaza	7598476	1951-09-24	FEMENINO	DIVORCIADO/A	78325945	Villa Copacabana	Av. Saavedra	2026-04-30	VOLUNTARIO	PERMANENTE	TÉCNICO	Presenta buena integración social.	1	2026-04-30 11:50:00	2026-05-01 19:18:38	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0001	Carmen	Calle	Paredes	9904346	1941-10-07	FEMENINO	DIVORCIADO/A	73497892	San Pedro	Calle Comercio	2026-04-30	REGULAR	EVENTUAL	NO ESPECIFICADO	Participa regularmente en actividades institucionales.	1	2026-04-30 11:50:00	2026-05-01 19:38:36	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0003	Juana	Alarcón	Mendoza	8170007	1959-08-12	FEMENINO	CASADO/A	77500789	Centro	Av. 6 de Agosto	2026-04-30	EMERGENCIA	PERMANENTE	SIN EDUCACIÓN FORMAL	Requiere seguimiento periódico.	2	2026-04-30 11:50:00	2026-05-01 20:01:00	\N	2026-05-01 20:01:00	Cambio de estado rápido	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0005	Juana	Rojas	Condori	6891811	1959-10-23	FEMENINO	VIUDO/A	76271855	Villa Armonía	Calle Sagárnaga	2026-04-30	DERIVADO	TEMPORAL	TÉCNICO	Paciente estable en seguimiento.	3	2026-04-30 11:50:00	2026-05-01 20:01:14	\N	2026-05-01 20:01:14	Cambio de estado rápido	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	f	f	f
AM_0002	Carlos	Condori	Gutiérrez	9883303	1945-10-12	MASCULINO	VIUDO/A	71698630	Miraflores	Av. Camacho	2025-12-10	REGULAR	PERMANENTE	TÉCNICO	Paciente estable en seguimiento.	1	2026-04-30 11:50:00	2026-05-01 21:16:59	adultos-mayores/ynHG4y1v5cV2qbKwBnV2zLtqKzptCGgill4Ur8E2.jpg	\N	\N	14:32:00	1A	LP	69771661	\N	LA PAZ	EL ALTO	B+	\N	ALERGIA A LAS MANZANAS	CAJA NACIONAL CNS	ANA MARIA GOMEZ	NIETO/A	67112224	MBKLNL	t	t	f
\.


--
-- Data for Name: asignacion_voluntarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.asignacion_voluntarios (cod_asig_vol, fecha_asig, fecha_fin, estado, obser, cod_am, cod_vol) FROM stdin;
\.


--
-- Data for Name: asistencia_voluntarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.asistencia_voluntarios (cod_asis_vol, fecha, hora_entrada, hora_salida, estado, actividad_realizada, observaciones, cod_vol) FROM stdin;
\.


--
-- Data for Name: atenciones_adulto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.atenciones_adulto (cod_aten_adul, fecha, hora, obs, estado, cod_tipo_aten, cod_am, responsable_tipo, responsable_id, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-dashboard_2	a:9:{s:12:"estadisticas";a:8:{s:16:"usuarios_activos";i:2;s:15:"adultos_mayores";i:20;s:11:"voluntarios";i:0;s:18:"alertas_pendientes";i:0;s:16:"usuarios_con_rol";i:2;s:20:"adultos_con_familiar";i:0;s:21:"voluntarios_asignados";i:0;s:23:"actividades_programadas";i:0;}s:14:"usuariosPorRol";a:2:{s:6:"labels";a:5:{i:0;s:10:"voluntario";i:1;s:8:"familiar";i:2;s:5:"admin";i:3;s:14:"personal_admin";i:4;s:14:"personal_salud";}s:4:"data";a:5:{i:0;i:0;i:1;i:0;i:2;i:2;i:3;i:0;i:4;i:0;}}s:16:"actividadMensual";a:2:{s:6:"labels";a:12:{i:0;s:3:"Ene";i:1;s:3:"Feb";i:2;s:3:"Mar";i:3;s:3:"Abr";i:4;s:3:"May";i:5;s:3:"Jun";i:6;s:3:"Jul";i:7;s:3:"Ago";i:8;s:3:"Sep";i:9;s:3:"Oct";i:10;s:3:"Nov";i:11;s:3:"Dic";}s:4:"data";a:12:{i:0;i:0;i:1;i:0;i:2;i:0;i:3;i:197;i:4;i:95;i:5;i:0;i:6;i:0;i:7;i:0;i:8;i:0;i:9;i:0;i:10;i:0;i:11;i:0;}}s:17:"usuariosDashboard";a:2:{i:0;a:5:{s:7:"cod_usu";i:2;s:6:"nombre";s:19:"Super Administrador";s:6:"correo";s:27:"admincasaamandita@gmail.com";s:6:"estado";s:6:"ACTIVO";s:3:"rol";s:5:"admin";}i:1;a:5:{s:7:"cod_usu";i:1;s:6:"nombre";s:18:"Juan Jose Espinoza";s:6:"correo";s:22:"admin@casaamandita.org";s:6:"estado";s:6:"ACTIVO";s:3:"rol";s:5:"admin";}}s:20:"actividadesDashboard";a:0:{}s:22:"alertasAdministrativas";a:3:{i:0;s:62:"Existen adultos mayores sin voluntarios asignados actualmente.";i:1;s:54:"No se han registrado actividades para la fecha de hoy.";i:2;s:68:"Se detectaron registros de adultos mayores sin vínculos familiares.";}s:7:"modulos";a:6:{i:0;a:4:{s:6:"titulo";s:8:"Usuarios";s:11:"descripcion";s:29:"Control de accesos y perfiles";s:5:"icono";s:14:"ph-users-three";s:4:"ruta";s:36:"http://127.0.0.1:8000/admin/usuarios";}i:1;a:4:{s:6:"titulo";s:15:"Adultos Mayores";s:11:"descripcion";s:30:"Seguimiento y fichas clínicas";s:5:"icono";s:22:"ph-identification-card";s:4:"ruta";s:43:"http://127.0.0.1:8000/admin/adultos-mayores";}i:2;a:4:{s:6:"titulo";s:8:"Personal";s:11:"descripcion";s:32:"Gestión de RRHH y especialistas";s:5:"icono";s:14:"ph-stethoscope";s:4:"ruta";s:1:"#";}i:3;a:4:{s:6:"titulo";s:11:"Voluntarios";s:11:"descripcion";s:26:"Apoyo social y asistencias";s:5:"icono";s:13:"ph-hand-heart";s:4:"ruta";s:1:"#";}i:4;a:4:{s:6:"titulo";s:11:"Actividades";s:11:"descripcion";s:34:"Planificación de talleres diarios";s:5:"icono";s:17:"ph-calendar-check";s:4:"ruta";s:1:"#";}i:5;a:4:{s:6:"titulo";s:12:"Evaluaciones";s:11:"descripcion";s:31:"Análisis cognitivo y emocional";s:5:"icono";s:8:"ph-brain";s:4:"ruta";s:1:"#";}}s:17:"bitacoraDashboard";a:10:{i:0;a:5:{s:5:"fecha";s:15:"hace 0 segundos";s:7:"usuario";s:5:"Super";s:6:"accion";s:17:"Acceso al sistema";s:6:"modulo";s:15:"Panel principal";s:7:"detalle";s:50:"El usuario accedió al panel principal de control.";}i:1;a:5:{s:5:"fecha";s:11:"hace 1 hora";s:7:"usuario";s:5:"Super";s:6:"accion";s:17:"Acceso al sistema";s:6:"modulo";s:15:"Panel principal";s:7:"detalle";s:50:"El usuario accedió al panel principal de control.";}i:2;a:5:{s:5:"fecha";s:11:"hace 1 hora";s:7:"usuario";s:5:"Super";s:6:"accion";s:17:"Acceso al sistema";s:6:"modulo";s:15:"Panel principal";s:7:"detalle";s:50:"El usuario accedió al panel principal de control.";}i:3;a:5:{s:5:"fecha";s:11:"hace 1 hora";s:7:"usuario";s:5:"Super";s:6:"accion";s:17:"Acceso al sistema";s:6:"modulo";s:15:"Panel principal";s:7:"detalle";s:50:"El usuario accedió al panel principal de control.";}i:4;a:5:{s:5:"fecha";s:11:"hace 1 hora";s:7:"usuario";s:5:"Super";s:6:"accion";s:17:"Acceso al sistema";s:6:"modulo";s:15:"Panel principal";s:7:"detalle";s:50:"El usuario accedió al panel principal de control.";}i:5;a:5:{s:5:"fecha";s:12:"hace 2 horas";s:7:"usuario";s:5:"Super";s:6:"accion";s:18:"Acción registrada";s:6:"modulo";s:12:"adulto_mayor";s:7:"detalle";s:114:"Se registró una observación de tipo Conductual para el adulto mayor Carlos Condori Gutiérrez con ficha AM_0002.";}i:6;a:5:{s:5:"fecha";s:12:"hace 2 horas";s:7:"usuario";s:5:"Super";s:6:"accion";s:15:"Registro creado";s:6:"modulo";s:7:"General";s:7:"detalle";s:7:"created";}i:7;a:5:{s:5:"fecha";s:12:"hace 2 horas";s:7:"usuario";s:5:"Super";s:6:"accion";s:17:"Acceso al sistema";s:6:"modulo";s:15:"Panel principal";s:7:"detalle";s:50:"El usuario accedió al panel principal de control.";}i:8;a:5:{s:5:"fecha";s:12:"hace 4 horas";s:7:"usuario";s:5:"Super";s:6:"accion";s:17:"Acceso al sistema";s:6:"modulo";s:15:"Panel principal";s:7:"detalle";s:50:"El usuario accedió al panel principal de control.";}i:9;a:5:{s:5:"fecha";s:12:"hace 5 horas";s:7:"usuario";s:5:"Super";s:6:"accion";s:17:"Acceso al sistema";s:6:"modulo";s:15:"Panel principal";s:7:"detalle";s:50:"El usuario accedió al panel principal de control.";}}s:7:"infoRol";a:3:{s:8:"es_admin";b:1;s:8:"es_salud";b:0;s:10:"nombre_rol";s:5:"admin";}}	1777688520
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: disponibilidad_voluntarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.disponibilidad_voluntarios (cod_hor_vol, dia_semana, hora_inicio, hora_fin, observaciones, cod_vol) FROM stdin;
\.


--
-- Data for Name: documentos_adulto_mayor; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.documentos_adulto_mayor (cod_doc_am, nom_doc, tipo_doc, ruta_archivo, extension, fecha_doc, observaciones, cod_am, estado, archivado_en, motivo_archivado, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: documentos_usuarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.documentos_usuarios (cod_doc_usu, nom_doc, tipo_doc, ruta_archivo, extension, fecha_doc, observaciones, cod_usu, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: especialidades; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.especialidades (cod_esp, nombre, descripcion) FROM stdin;
\.


--
-- Data for Name: estado_adulto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.estado_adulto (cod_est_adul, estado, fecha_in, fecha_fin, created_at, updated_at) FROM stdin;
1	ACTIVO	2026-04-30	\N	2026-04-30 11:28:07	2026-04-30 11:28:07
2	ARCHIVADO	2026-04-30	\N	2026-04-30 11:28:07	2026-04-30 11:28:07
3	INACTIVO	2026-04-30	\N	2026-04-30 11:28:07	2026-04-30 11:28:07
\.


--
-- Data for Name: evaluaciones_cognitivas; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.evaluaciones_cognitivas (cod_eval_cog, cod_am, cod_tipo_eval, cod_per_sal, fecha_eval, hora_eval, puntaje_total, puntaje_maximo, resultado_interpretacion, nivel_riesgo, observaciones, estado_eval, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: familiar_adulto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.familiar_adulto (id, created_at, updated_at, cod_fam, cod_am, parentesco_vinculo, es_responsable, estado, observaciones) FROM stdin;
\.


--
-- Data for Name: familiares; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.familiares (cod_fam, parentesco, direccion, ocupacion, es_responsable, observaciones, cod_usu, estado, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: horarios_personal_admin; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.horarios_personal_admin (cod_hor_per_admin, dia_semana, hora_inicio, hora_fin, turno, estado, observaciones, cod_per_adm) FROM stdin;
\.


--
-- Data for Name: horarios_personal_salud; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.horarios_personal_salud (cod_hor_per_sal, dia_semana, hora_inicio, hora_fin, turno, estado, observaciones, cod_per_sal) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_04_14_224919_add_two_factor_columns_to_users_table	1
5	2026_04_14_224954_create_personal_access_tokens_table	1
6	2026_04_15_014137_create_estado_adulto_table	1
7	2026_04_15_014138_create_adulto_mayor_table	1
8	2026_04_15_014145_create_documentos_adulto_mayor_table	1
9	2026_04_15_014145_create_obs_adulto_table	1
10	2026_04_15_014146_create_tipo_actividades_adulto_table	1
11	2026_04_15_014147_create_actividades_adulto_table	1
12	2026_04_15_014147_create_tipo_atenciones_adulto_table	1
13	2026_04_15_014148_create_atenciones_adulto_table	1
14	2026_04_15_014149_create_documentos_usuarios_table	1
15	2026_04_15_014149_create_familiares_table	1
16	2026_04_15_014150_create_familiar_adulto_table	1
17	2026_04_15_014150_create_voluntarios_table	1
18	2026_04_15_014151_create_asignacion_voluntarios_table	1
19	2026_04_15_014152_create_asistencia_voluntarios_table	1
20	2026_04_15_014152_create_disponibilidad_voluntarios_table	1
21	2026_04_15_014153_create_especialidades_table	1
22	2026_04_15_014153_create_personal_salud_table	1
23	2026_04_15_014154_create_horarios_personal_salud_table	1
24	2026_04_15_014155_create_personal_admin_table	1
25	2026_04_15_014156_create_horarios_personal_admin_table	1
26	2026_04_15_014846_create_activity_log_table	1
27	2026_04_15_014847_add_event_column_to_activity_log_table	1
28	2026_04_15_014848_add_batch_uuid_column_to_activity_log_table	1
29	2026_04_19_035621_create_permission_tables	1
30	2026_04_29_223239_fix_familiar_adulto_table	2
31	2026_04_29_223239_strengthen_administrative_tables	2
32	2026_04_29_223240_add_foreign_keys_administrative_tables	3
33	2026_04_30_105713_modify_cod_am_in_adulto_mayor_table	4
34	2026_04_30_131446_add_foto_to_adulto_mayor_table	5
35	2026_04_30_132241_add_archiving_columns_to_adulto_mayor_table	6
36	2026_05_01_174806_add_foto_to_adulto_mayor_table	7
37	2026_05_01_182726_add_hora_ing_to_adulto_mayor_table	8
38	2026_05_01_183434_alter_subject_and_causer_types_in_activity_log_table	9
39	2026_05_01_194838_add_datos_admision_geriatrica_to_adulto_mayor_table	10
40	2026_05_02_004327_set_not_null_on_familiar_adulto_keys	11
41	2026_05_02_004841_remove_default_from_cod_am_on_adulto_mayor_table	11
42	2026_05_02_004951_improve_unique_identity_index_on_adulto_mayor_table	11
43	2026_05_02_020107_create_tipo_evaluacion_cognitiva_table	12
44	2026_05_02_020108_create_evaluaciones_cognitivas_table	12
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
1	App\\Models\\User	1
1	App\\Models\\User	2
\.


--
-- Data for Name: obs_adulto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.obs_adulto (cod_obs_adul, fecha, tipo_obs, descripcion, cod_am, cod_est_adul, nivel_importancia, registrado_por, estado_revision, created_at, updated_at) FROM stdin;
1	2026-05-01	Conductual	Se ha observado un cambio de conducta	AM_0002	1	\N	\N	PENDIENTE	2026-05-01 18:47:40	2026-05-01 18:47:40
2	2026-05-02	Conductual	esta malito	AM_0002	1	NORMAL	2	PENDIENTE	2026-05-02 00:17:57	2026-05-02 00:17:57
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (correo, token, created_at) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_admin; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_admin (cod_per_adm, cargo, fecha_ingreso, area_admin, estado_laboral, observaciones, cod_usu, archivado_en, motivo_archivado, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_salud; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_salud (cod_per_sal, fecha_ing, anios_exp, matricula_prof, estado_laboral, observaciones, cod_usu, cod_esp, archivado_en, motivo_archivado, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	admin	web	2026-04-23 13:37:15	2026-04-23 13:37:15
2	personal_salud	web	2026-04-23 13:37:15	2026-04-23 13:37:15
3	personal_admin	web	2026-04-23 13:37:15	2026-04-23 13:37:15
4	voluntario	web	2026-04-23 13:37:15	2026-04-23 13:37:15
5	familiar	web	2026-04-23 13:37:15	2026-04-23 13:37:15
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
Y3Dmfv5rgbEv021PmR2X0Rit6mYnx69qMUVOFdtt	\N	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJZUmZDZDlsd1VWNmZKSlFURFh2MXh1NnBOWVNVQXpBSjlSRHlBNXRmIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC9hZHVsdG9zLW1heW9yZXMifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvYWR1bHRvcy1tYXlvcmVzIiwicm91dGUiOiJhZG1pbi5hZHVsdG9zLW1heW9yZXMuaW5kZXgifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==	1777681003
jI4gcdZ29F905hq4YMSHkEL6lpNlxoeh7Mzy08b3	2	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	eyJfdG9rZW4iOiJMNDYwR3VHOWRxUTdKQzRKc1JXY0VkUzB2UExtemh0REhQUDJXa2wzIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoyLCJwYXNzd29yZF9oYXNoX3NhbmN0dW0iOiIyNWRjZjI5MDlhNzI2OWYyNGM1ZWVlOGUzNTk1YmVlOTFlOTM2NjlkYTAwOTQ2MDg4YjgyMDAwMzBlMzI4NjdkIn0=	1777688466
\.


--
-- Data for Name: tipo_actividades_adulto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_actividades_adulto (cod_tipo_act, tipo, descripcion) FROM stdin;
\.


--
-- Data for Name: tipo_atenciones_adulto; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_atenciones_adulto (cod_tipo_aten, tipo, descripcion) FROM stdin;
\.


--
-- Data for Name: tipo_evaluacion_cognitiva; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.tipo_evaluacion_cognitiva (cod_tipo_eval, nombre, descripcion, puntaje_maximo, punto_corte_normal, punto_corte_riesgo, estado, created_at, updated_at) FROM stdin;
1	MoCA	Montreal Cognitive Assessment. Evaluación breve para detección de deterioro cognitivo.	30.00	26.00	25.00	ACTIVO	2026-05-02 02:12:15	2026-05-02 02:12:15
2	MMSE	Mini-Mental State Examination. Evaluación cognitiva general del estado mental.	30.00	24.00	23.00	ACTIVO	2026-05-02 02:12:15	2026-05-02 02:12:15
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (cod_usu, nombres, ap_paterno, ap_materno, correo, email_verified_at, password, telefono, foto_de_perfil, estado, ultimo_acceso, remember_token, current_team_id, created_at, updated_at, two_factor_secret, two_factor_recovery_codes, two_factor_confirmed_at) FROM stdin;
2	Super	Administrador	\N	admincasaamandita@gmail.com	\N	$2y$12$SF8/oFsyaampJ5YJPJq4mOj.OLP4.04XnIs/Xybk3sTEtf7KY2TAO	\N	\N	ACTIVO	\N	7uHlOPy5phl2R8UNYPZmDWTR1GgweKVU4Q4Kr3AQ60XtRV3Tb16FBTvJjBJk	\N	2026-04-24 00:27:10	2026-04-24 00:27:10	\N	\N	\N
1	Juan Jose	Espinoza	Mendez	admin@casaamandita.org	\N	$2y$12$LiU9loAp9ONluDCX5lrauuh72QBwtkjLfMwd/Rjnwoga94eUZDQyK	67885434	\N	ACTIVO	\N	\N	\N	2026-04-23 13:37:16	2026-04-30 12:20:48	\N	\N	\N
\.


--
-- Data for Name: voluntarios; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.voluntarios (cod_vol, fecha_ing, area_apoyo, estado, observaciones, cod_usu, archivado_en, motivo_archivado, created_at, updated_at) FROM stdin;
\.


--
-- Name: actividades_adulto_cod_act_adul_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.actividades_adulto_cod_act_adul_seq', 1, false);


--
-- Name: activity_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.activity_log_id_seq', 294, true);


--
-- Name: adulto_mayor_cod_am_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.adulto_mayor_cod_am_seq', 1, false);


--
-- Name: asignacion_voluntarios_cod_asig_vol_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.asignacion_voluntarios_cod_asig_vol_seq', 1, false);


--
-- Name: asistencia_voluntarios_cod_asis_vol_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.asistencia_voluntarios_cod_asis_vol_seq', 1, false);


--
-- Name: atenciones_adulto_cod_aten_adul_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.atenciones_adulto_cod_aten_adul_seq', 1, false);


--
-- Name: disponibilidad_voluntarios_cod_hor_vol_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.disponibilidad_voluntarios_cod_hor_vol_seq', 1, false);


--
-- Name: documentos_adulto_mayor_cod_doc_am_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.documentos_adulto_mayor_cod_doc_am_seq', 1, false);


--
-- Name: documentos_usuarios_cod_doc_usu_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.documentos_usuarios_cod_doc_usu_seq', 1, false);


--
-- Name: especialidades_cod_esp_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.especialidades_cod_esp_seq', 1, false);


--
-- Name: estado_adulto_cod_est_adul_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.estado_adulto_cod_est_adul_seq', 1, false);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: familiar_adulto_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.familiar_adulto_id_seq', 1, false);


--
-- Name: familiares_cod_fam_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.familiares_cod_fam_seq', 1, false);


--
-- Name: horarios_personal_admin_cod_hor_per_admin_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.horarios_personal_admin_cod_hor_per_admin_seq', 1, false);


--
-- Name: horarios_personal_salud_cod_hor_per_sal_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.horarios_personal_salud_cod_hor_per_sal_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 44, true);


--
-- Name: obs_adulto_cod_obs_adul_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.obs_adulto_cod_obs_adul_seq', 2, true);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 1, false);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: personal_admin_cod_per_adm_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_admin_cod_per_adm_seq', 1, false);


--
-- Name: personal_salud_cod_per_sal_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_salud_cod_per_sal_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 5, true);


--
-- Name: tipo_actividades_adulto_cod_tipo_act_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_actividades_adulto_cod_tipo_act_seq', 1, false);


--
-- Name: tipo_atenciones_adulto_cod_tipo_aten_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_atenciones_adulto_cod_tipo_aten_seq', 1, false);


--
-- Name: tipo_evaluacion_cognitiva_cod_tipo_eval_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.tipo_evaluacion_cognitiva_cod_tipo_eval_seq', 2, true);


--
-- Name: users_cod_usu_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_cod_usu_seq', 2, true);


--
-- Name: voluntarios_cod_vol_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.voluntarios_cod_vol_seq', 1, false);


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

\unrestrict YWja4Ww0kw7Tey96FfRGTY0lyUnLXK2sDiFmcGq2p6dCGgDPUJYUjh36uB9FZuJ

