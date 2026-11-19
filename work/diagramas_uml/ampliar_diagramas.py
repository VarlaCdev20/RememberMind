from collections import Counter
from copy import deepcopy
from hashlib import sha256
import json
from math import atan2
from pathlib import Path
from uuid import uuid4

from casos_catalogo import ADM, CATALOG, ENF, FAM, GER, MED, PROF, SUPER, TEC
from casos_detallados import CLIN, DETAIL, ENT, GROUPING, SAL, STAFF


WORK = Path(__file__).parent
DESKTOP = Path(r"C:\Users\CARLAENCINAS\Desktop\REMEMBERMIND_DIGRAMAS\REMEMBERMIND_DIGRAMAS.mdj")
STAGE = WORK / "entrega" / "DIAGRAMAS_REMEMBERMIND"
STAGE.mkdir(parents=True, exist_ok=True)
LOGIN_ACTOR_DISTANCE = 1500
LOGIN_SECTION_GAP = 330
source = DESKTOP.read_bytes()
original = json.loads(source.decode("utf-8-sig"))
project = deepcopy(original)
model = next(m for m in project["ownedElements"] if m["name"] == "Diagramas de RememberMind")
folder = next(p for p in model["ownedElements"] if p["name"] == "01 Casos de uso")
parents = {sub["name"]: sub for sub in folder["ownedElements"] if sub["_type"] == "UMLPackage"}


def walk(data):
    if isinstance(data, dict):
        yield data
        for value in data.values():
            yield from walk(value)
    elif isinstance(data, list):
        for value in data:
            yield from walk(value)


def ref(node):
    return {"$ref": node["_id"]}


def item(kind, parent, **fields):
    return {"_type": kind, "_id": uuid4().hex, "_parent": ref(parent), **fields}


def own(kind, parent, collection="ownedElements", **fields):
    node = item(kind, parent, **fields)
    parent.setdefault(collection, []).append(node)
    return node


def wrapped(text, limit):
    lines = []
    for word in text.split():
        if not lines or len(lines[-1]) + len(word) + 1 > limit:
            lines.append(word)
        else:
            lines[-1] += " " + word
    return "\n".join(lines)


def view(diagram, kind, box, model_ref=None, *, font=16, bold=False,
         fill="#FFFFFF", **fields):
    x, y, w, h = box
    if model_ref is not None:
        fields["model"] = ref(model_ref)
    return own(kind, diagram, "ownedViews", left=x, top=y, width=w, height=h,
               fillColor=fill, lineColor="#263344", fontColor="#182434",
               font=f"Arial;{font};{int(bold)}", showShadow=False, **fields)


def centered(x, y, w, h):
    return x - w // 2, y - h // 2, w, h


def center(box):
    return box["left"] + box["width"] // 2, box["top"] + box["height"] // 2


def width(text):
    return max(240, 48 + round(len(text) * 10.2))


def intersects_segment(start, end, other, margin=15):
    """Return whether a straight connector enters the expanded oval."""
    cx, cy, w, h = other
    ax, ay = start
    bx, by = end
    rx, ry = w / 2 + margin, h / 2 + margin
    px, py = (ax - cx) / rx, (ay - cy) / ry
    dx, dy = (bx - ax) / rx, (by - ay) / ry
    t = max(0.0, min(1.0, -(px * dx + py * dy) / (dx * dx + dy * dy)))
    return (px + t * dx) ** 2 + (py + t * dy) ** 2 < 1.0


def plan_satellites(leaves, *, right_only=False, login=False):
    if not leaves:
        return []
    # Unique ranks make every oval appear below its predecessor. Search the
    # horizontal offsets so the straight spokes avoid the other ellipses.
    for gap in (112, 126, 155, 195):
        for limit in (540, 720, 900, 1120, 1450):
            offsets = list(range(-limit, limit + 1, 60))
            if 0 not in offsets:
                offsets.append(0)
            frontier = [(0, [])]
            required_count = sum(condition is None for _, condition in leaves)
            for ix, (name, condition) in enumerate(leaves):
                y = (required_count + 1 if condition else ix + 1) * gap
                possible = []
                for score, existing in frontier:
                    for x in offsets:
                        case_width = width(name)
                        box = (x, y, case_width, 59)
                        if any(py == y and abs(x - px) < (case_width + pw) / 2 + 44
                               for px, py, pw, _ in existing):
                            continue
                        if right_only and x - case_width / 2 < 35:
                            continue
                        if login:
                            total = len(leaves) * gap + LOGIN_SECTION_GAP
                            anchor = round(total * 0.46)
                            if y < anchor:
                                clearance = LOGIN_ACTOR_DISTANCE * (y - 34) / anchor
                            else:
                                clearance = LOGIN_ACTOR_DISTANCE * (total - y - 34) / (total - anchor)
                            if abs(x) + case_width / 2 + 20 > clearance:
                                continue
                        angle = atan2(x, y)
                        if any(abs(angle - atan2(p[0], p[1])) < 0.125 for p in existing):
                            continue
                        if any(intersects_segment((0, 0), (x, y), p) or
                               intersects_segment((0, 0), p[:2], box) for p in existing):
                            continue
                        optional_bias = 28 if condition and abs(x) < 150 else 0
                        cost = score + abs(x) + optional_bias
                        possible.append((cost, [*existing, box]))
                if not possible:
                    break
                frontier = sorted(possible, key=lambda v: v[0])[:110]
            else:
                return frontier[0][1]
    raise ValueError(f"No hay distribución recta para {leaves}")


def edge(diagram, kind, model_, u, v, *, style, end, label_side=1):
    points = [center(u), center(v)]
    fields = {}
    if model_ is not None:
        fields["model"] = ref(model_)
    e = own(kind, diagram, "ownedViews", tail=ref(u), head=ref(v),
            points=";".join(f"{x}:{y}" for x, y in points),
            font="Arial;14;0", fontColor="#182434", lineColor="#3B4858",
            lineMode=style, lineStyle=1, tailEndStyle=0, headEndStyle=end, **fields)
    if kind in {"UMLIncludeView", "UMLExtendView"}:
        text = "«include»" if kind == "UMLIncludeView" else "«extend»"
        label = own("EdgeLabelView", e, "subViews", hostEdge=ref(e),
                    model=ref(model_), edgePosition=1, distance=25,
                    alpha=label_side * 1.5707963267948966,
                    font="Arial;14;0", text=text, fontColor="#182434")
        e["stereotypeLabel"] = ref(label)
    return e


actor_models = {o["name"]: o for o in walk(folder) if o.get("_type") == "UMLActor"}
SMTP = "Servicio de correo Gmail (SMTP)"
RECOVERY_MAIL = "Enviar enlace de recuperación por correo"
ACCOUNT_AUTHORIZATION = "Verificar permiso para la operación"
ACCESS_USER = "Usuario con cuenta"
actor_specs = {
    ACCESS_USER: "Actor abstracto que comparte las operaciones de acceso entre el personal institucional y el familiar vinculado. Cada perfil conserva sus propios permisos.",
    STAFF: "Personal con cuenta institucional activa. Inicia y cierra su sesión; sus permisos se determinan por su perfil.",
    FAM: "Familiar con cuenta activa y vínculo vigente con el residente. Consulta únicamente la información habilitada.",
    SUPER: "Administra las cuentas institucionales y los permisos aprobados. Consulta auditoría y conocimiento experto dentro de su alcance.",
    GER: "Planifica personal y turnos por área. Consulta indicadores institucionales autorizados.",
    ADM: "Administrador de operación diaria. Gestiona admisiones, alojamiento y coordinación según permisos. La función administrativa no confiere atribuciones clínicas.",
    ENF: "Personal de Enfermería habilitado. Consulta y registra actos dentro de su asignación y jornada. Administra según la orden vigente.",
    MED: "Médico general o geriatra autorizado. Registra la atención y prescribe dentro de su competencia y alcance.",
    PROF: "Profesional con autorización y competencia para el instrumento o la valoración correspondiente.",
    CLIN: "Profesional clínico habilitado para consultar la evolución del residente dentro de su alcance.",
    TEC: "Responsable autorizado de ejecutar pruebas controladas con datos sintéticos y revisar las trazas del motor.",
    SAL: "Personal autorizado de Enfermería del turno saliente. Prepara y entrega el pase de los residentes asignados.",
    ENT: "Personal autorizado de Enfermería del turno entrante. Consulta el pase recibido y confirma la recepción.",
    SMTP: "Servicio externo que RememberMind utiliza para entregar enlaces de recuperación de contraseña por SMTP.",
}
aliases = {STAFF: "PERSONAL INSTITUCIONAL", FAM: "FAMILIAR VINCULADO AUTORIZADO",
           SUPER: "SUPERADMINISTRADOR", GER: "GERENTE", ADM: "ADMINISTRACIÓN",
           ENF: "ENFERMERÍA AUTORIZADA", MED: "MÉDICO GENERAL O GERIATRA",
           PROF: "PROFESIONAL AUTORIZADO", TEC: "RESPONSABLE TÉCNICO DE PRUEBAS"}


def actor(name):
    model_name = name if name == SMTP else aliases.get(name, name.upper())
    if model_name not in actor_models:
        actor_models[model_name] = own("UMLActor", folder, name=model_name)
    element = actor_models[model_name]
    element["documentation"] = actor_specs[name]
    if name == ACCESS_USER:
        element["isAbstract"] = True
    return element


def associate(pkg, diagram, a_model, a_view, case, case_view):
    association = own("UMLAssociation", pkg)
    for key, endpoint in (("end1", a_model), ("end2", case)):
        association[key] = item("UMLAssociationEnd", association, reference=ref(endpoint),
                                navigable="unspecified", aggregation="none")
    edge(diagram, "UMLAssociationView", association, a_view, case_view, style=0, end=0)


packages = {}
for scope, collection in parents.items():
    for p in collection["ownedElements"]:
        code = p.get("name", "").split(".", 1)[0]
        if len(code) == 5 and code.startswith("CU-"):
            packages[int(code[-2:])] = scope, p


def build(number, blocks, note):
    scope, package = packages[number]
    hu = "HU-01" if number == 1 else CATALOG[number][1]
    code = f"CU-{number:02}"
    diagram = next(d for d in package["ownedElements"] if d["_type"] == "UMLUseCaseDiagram")
    original_subject = next(u for u in package["ownedElements"] if u["_type"] == "UMLUseCaseSubject")
    package["ownedElements"] = [x for x in package["ownedElements"]
                                if x["_type"] not in {"UMLAssociation", "UMLUseCaseSubject"}]
    package["ownedElements"].append(original_subject)
    original_subject["ownedElements"] = []
    diagram["ownedViews"] = []
    diagram["documentation"] = f"RememberMind · {scope} · {hu}\n{note}"
    if number in GROUPING:
        diagram["documentation"] += "\n" + GROUPING[number]
    for original_goal, _, required, options in DETAIL[number][0]:
        diagram["documentation"] += "\n\nProceso: " + original_goal
        for index, step in enumerate(required, 1):
            diagram["documentation"] += f"\n{index}. {step}."
        for alternative, condition in options:
            diagram["documentation"] += f"\nVariante o excepción: {alternative}. {condition}"

    local_groups = []
    min_x, max_x = 0, 0
    repeated_roles = any(count > 1 for count in Counter(
        role for _, roles, _, _ in blocks for role in roles).values())
    for i, (goal, roles, mandatory, optional) in enumerate(blocks):
        leaves = [(s, None) for s in mandatory] + list(optional)
        nodes = plan_satellites(leaves, right_only=repeated_roles or len(roles) > 1)
        group_nodes = [(0, 0, width(goal), 70), *nodes]
        min_x = min(min_x, *(x - w // 2 for x, _, w, _ in group_nodes))
        max_x = max(max_x, *(x + w // 2 for x, _, w, _ in group_nodes))
        local_groups.append((goal, roles, leaves, group_nodes))

    spine = (720 if number == 1 else 380) - min_x
    frame_x = spine + min_x - 66
    frame_right = spine + max_x + 62
    frame_y = 23
    cursor = frame_y + 79
    groups = []
    for i, (goal, roles, leaves, local) in enumerate(local_groups):
        coords = [(spine + x, cursor + y, w, h) for x, y, w, h in local]
        groups.append((goal, roles, leaves, coords))
        cursor = coords[-1][1] + (225 if len(roles) > 1 else 160)
    frame_bottom = max(y + h // 2 for _, _, _, boxes in groups for _, y, _, h in boxes) + 30
    shared_coords = None
    if number == 2:
        shared_coords = (spine + 960, groups[2][3][0][1], width(ACCOUNT_AUTHORIZATION), 59)
        frame_right = shared_coords[0] + shared_coords[2] // 2 + 62
    original_subject["name"] = diagram["name"]
    frame_width = max(frame_right - frame_x, len(diagram["name"]) * 11 + 48)
    frame_right = frame_x + frame_width
    frame = view(diagram, "UMLUseCaseSubjectView",
                 (frame_x, frame_y, frame_width, frame_bottom - frame_y),
                 original_subject, font=17, bold=True)

    def add_case(text, coords, primary=False, extra=""):
        detail = f"{code} · {hu}\n{note}"
        for original_goal, _, required, _ in DETAIL[number][0]:
            if original_goal == text:
                detail += "\nReglas y pasos del proceso: " + "; ".join(required) + "."
                break
        case = own("UMLUseCase", original_subject, name=text, documentation=detail + extra)
        cv = view(diagram, "UMLUseCaseView", centered(*coords), case, font=16, bold=True,
                  fill="#EEF5FC" if primary else "#FFFFFF", containerView=ref(frame),
                  suppressAttributes=True, suppressOperations=True)
        return case, cv

    actor_views = {}
    actor_rows = {}
    for _, roles, _, boxes in groups:
        for role in roles:
            actor_rows.setdefault(role, []).append(boxes[0][1])
    if number == 1:
        actor_rows = {ACCESS_USER: actor_rows[STAFF]}
    ordered_roles = sorted(actor_rows, key=lambda role: sum(actor_rows[role]) / len(actor_rows[role]))
    previous_y = 15
    for role in ordered_roles:
        who = actor(role)
        aw = max(220, 10 * len(who["name"]) + 18)
        rows = actor_rows[role]
        ay = max(previous_y + 135, round((rows[0] + rows[-1]) / 2))
        previous_y = ay
        ax = frame_x - 35 - aw // 2
        av = view(diagram, "UMLActorView", centered(ax, ay, aw, 92), who,
                  font=13, bold=True, suppressAttributes=True, suppressOperations=True)
        actor_views[role] = who, av

    if number == 1:
        common, common_view = actor_views[ACCESS_USER]
        common_x, common_y = center(common_view)
        for role, offset in ((STAFF, -120), (FAM, 120)):
            specific = actor(role)
            aw = max(220, 10 * len(specific["name"]) + 18)
            specific_view = view(
                diagram, "UMLActorView", centered(common_x - 315, common_y + offset, aw, 92),
                specific, font=13, bold=True, suppressAttributes=True, suppressOperations=True,
            )
            specialization = own(
                "UMLGeneralization", specific, source=ref(specific), target=ref(common),
            )
            edge(diagram, "UMLGeneralizationView", specialization, specific_view, common_view,
                 style=0, end=2)

    primary_cases = []
    for goal, roles, leaves, boxes in groups:
        participants = "\n\nActores\n" + "\n".join(f"{role}: {actor_specs[role]}" for role in roles)
        steps = "\n\nRecorrido del caso\n" + "\n".join(
            f"{index}. {name}" for index, (name, condition) in enumerate(
                ((n, c) for n, c in leaves if c is None), 1
            )
        ) if any(condition is None for _, condition in leaves) else ""
        main_case, main_view = add_case(goal, boxes[0], primary=True, extra=participants + steps)
        primary_cases.append((main_case, main_view))
        for role in ((ACCESS_USER,) if number == 1 else roles):
            who, av = actor_views[role]
            associate(package, diagram, who, av, main_case, main_view)

        for (name, condition), coords in zip(leaves, boxes[1:]):
            leaf, lv = add_case(name, coords, extra=f"\n\nCaso base: {goal}.")
            if name == RECOVERY_MAIL:
                service = actor(SMTP)
                service_view = view(diagram, "UMLActorView",
                                    (frame_right + 40, coords[1] - 46, 280, 92),
                                    service, font=13, bold=True,
                                    suppressAttributes=True, suppressOperations=True)
                associate(package, diagram, service, service_view, leaf, lv)
            leaf_side = 1 if coords[0] >= spine else -1
            if condition:
                # The base owns the extension point and the relationship stores the condition.
                point = own("UMLExtensionPoint", main_case,
                            name=f"Punto de extensión para {name.lower()}", location=condition)
                extension = own("UMLExtend", leaf, name="", source=ref(leaf), target=ref(main_case),
                                condition=condition, extensionLocations=[ref(point)])
                edge(diagram, "UMLExtendView", extension, lv, main_view,
                     style=2, end=1, label_side=-leaf_side)
                leaf["documentation"] += f"\nCondición de extensión: {condition}"
            else:
                inclusion = own("UMLInclude", main_case, name="", source=ref(main_case), target=ref(leaf))
                edge(diagram, "UMLIncludeView", inclusion, main_view, lv,
                     style=2, end=1, label_side=leaf_side)
    if shared_coords:
        common_case, common_view = add_case(
            ACCOUNT_AUTHORIZATION, shared_coords,
            extra="\nComprueba la autorización específica de la operación solicitada.",
        )
        for main_case, main_view in primary_cases:
            inclusion = own("UMLInclude", main_case, name="", source=ref(main_case), target=ref(common_case))
            edge(diagram, "UMLIncludeView", inclusion, main_view, common_view, style=2, end=1)


def revisar_grupos(number, groups):
    """No convertir denegaciones ni operaciones independientes en extensiones."""
    if number == 1:
        title, roles, required, _ = groups[0]
        groups = [
            (title, roles, required, ()),
            ("Solicitar enlace de recuperación", roles, (RECOVERY_MAIL,), ()),
            ("Restablecer contraseña con el enlace", roles, ("Validar el token de recuperación",), ()),
            groups[1],
        ]
    elif number == 2:
        roles = groups[0][1]
        groups = [
            ("Crear cuenta institucional", roles, ("Identificar al titular de la cuenta",), ()),
            ("Actualizar datos de una cuenta", roles, ("Identificar al titular de la cuenta",), ()),
            ("Activar o desactivar cuenta", roles, ("Identificar al titular de la cuenta",), ()),
            *groups[1:],
        ]
    elif number == 4:
        roles = groups[0][1]
        groups = [
            ("Registrar solicitud de preadmisión", roles, ("Registrar los datos requeridos de la solicitud",), ()),
            ("Revisar solicitud de preadmisión", roles, ("Revisar la información presentada",), ()),
            ("Aprobar la preadmisión", roles, ("Conservar la decisión sobre la solicitud",), ()),
            ("Rechazar la preadmisión", roles, ("Conservar la decisión sobre la solicitud",), ()),
            (groups[1][0], groups[1][1], groups[1][2], ()),
        ]
    fixed = []
    for title, roles, mandatory, options in groups:
        valid = []
        for name, condition in options:
            if name.startswith(("Impedir ", "Rechazar ", "Denegar ")):
                continue  # La excepción va en la ficha, no es un objetivo del actor.
            if number == 2 and name in {"Crear cuenta institucional", "Actualizar datos de una cuenta", "Activar o desactivar cuenta"}:
                fixed.append((name, roles, ("Verificar autorización administrativa",), ()))
                continue
            if number == 5 and name == "Actualizar el expediente administrativo":
                fixed.append((name, roles, ("Identificar al residente",), ()))
                continue
            if number == 4 and name in {"Aprobar la preadmisión", "Rechazar la preadmisión"}:
                fixed.append((name, roles, ("Revisar la información presentada", "Conservar la decisión sobre la solicitud"), ()))
                continue
            valid.append((name, condition))
        fixed.append((title, roles, mandatory, tuple(valid)))
    # Keep technical persistence and automatic provenance in the flow text.
    prose_only = {
        "Invalidar el acceso de la sesión activa",
        "Vincular fecha, autor y jornada al residente",
        "Vincular la atención a la historia clínica",
        "Guardar el registro en el historial",
        "Vincular la prescripción al residente",
        "Vincular el resultado a la jornada",
        "Vincular el registro al historial del residente",
        "Vincular los resultados al residente",
        "Conservar responsable y cambios de estado",
        "Mostrar solo la información autorizada",
        "Mostrar residentes dentro del alcance asignado",
        "Mostrar las prescripciones registradas y permitidas",
    }
    detailed = []
    for title, roles, mandatory, options in fixed:
        children = tuple(step for step in mandatory if step not in prose_only)
        if number == 2:
            children = ()
        if number == 18:
            children = (
                "Seleccionar un residente vinculado",
                "Verificar vínculo familiar y autorización vigente",
                "Consultar el resumen habilitado para el familiar",
            )
        if number == 17:
            detailed.append((title, roles, children, ()))
            for name, _ in options:
                if name.startswith("Cerrar alerta"):
                    detailed.append((name, roles, (), ()))
            continue
        detailed.append((title, roles, children, options))
    return detailed


for n, (groups, note) in DETAIL.items():
    if n == 1:
        note = "HU-01. Las cuentas son preexistentes. El acceso exige credenciales correctas y cuenta activa. La recuperación solicita un enlace por el correo registrado y requiere un token válido para restablecer la contraseña."
    build(n, revisar_grupos(n, groups), note)


def split_points(edge_view):
    return [tuple(map(int, s.split(":"))) for s in edge_view["points"].split(";")]


def orientation(a, b, c):
    return (b[0]-a[0])*(c[1]-a[1]) - (b[1]-a[1])*(c[0]-a[0])


def proper_intersection(ab, cd):
    a, b = ab
    c, d = cd
    return orientation(a, b, c) * orientation(a, b, d) < 0 and \
        orientation(c, d, a) * orientation(c, d, b) < 0


nodes = list(walk(project))
ids = [m["_id"] for m in nodes if "_id" in m]
refs = {m["$ref"] for m in nodes if "$ref" in m}
assert len(ids) == len(set(ids))
assert not refs - set(ids), refs - set(ids)
by_id = {m["_id"]: m for m in nodes if "_id" in m}
for m in nodes:
    if m.get("_type") == "UMLExtend":
        assert m["condition"].strip() and len(m["extensionLocations"]) == 1
        assert by_id[m["extensionLocations"][0]["$ref"]]["_parent"] == m["target"]
        assert by_id[m["source"]["$ref"]]["_type"] == "UMLUseCase"
    if m.get("_type") == "UMLUseCase":
        point_names = [part["name"] for part in m.get("ownedElements", [])
                       if part["_type"] == "UMLExtensionPoint"]
        assert len(point_names) == len(set(point_names)), m["name"]

diagrams = [x for x in walk(folder) if x.get("_type") == "UMLUseCaseDiagram"]
assert len(diagrams) == 20
for diagram in diagrams:
    all_views = diagram["ownedViews"]
    stats = Counter(v["_type"] for v in all_views)
    assert stats["UMLUseCaseView"] >= 4
    frame = next(v for v in all_views if v["_type"] == "UMLUseCaseSubjectView")
    right = frame["left"] + frame["width"]
    for v in all_views:
        if v["_type"] == "UMLActorView":
            role = by_id[v["model"]["$ref"]]
            assert role["documentation"].strip()
            if role["name"] == SMTP:
                assert v["left"] > right
            else:
                assert v["left"] + v["width"] < frame["left"]
        assert v["_type"] != "UMLNoteView"
    line_views = [v for v in all_views if "points" in v]
    actors_here = [v["model"]["$ref"] for v in all_views if v["_type"] == "UMLActorView"]
    assert actors_here
    assert len(actors_here) == len(set(actors_here)), diagram["name"]
    endpoints = {v[end]["$ref"] for v in line_views for end in ("head", "tail")}
    ovals = [v for v in all_views if v["_type"] == "UMLUseCaseView"]
    assert all(v["_id"] in endpoints for v in ovals), diagram["name"]
    ranks = [center(v)[1] for v in ovals
             if by_id[v["model"]["$ref"]]["name"] != ACCOUNT_AUTHORIZATION]
    assert ranks == sorted(ranks), diagram["name"]
    for index, oval in enumerate(ovals):
        for previous in ovals[:index]:
            assert (
                oval["left"] >= previous["left"] + previous["width"]
                or previous["left"] >= oval["left"] + oval["width"]
                or oval["top"] >= previous["top"] + previous["height"]
                or previous["top"] >= oval["top"] + oval["height"]
            ), diagram["name"]
    assert all(frame["left"] < v["left"] and v["left"] + v["width"] < right
               and frame["top"] < v["top"] and v["top"] + v["height"] < frame["top"] + frame["height"]
               for v in ovals), diagram["name"]
    for i, edge_a in enumerate(line_views):
        seg_a = split_points(edge_a)
        assert len(seg_a) == 2
        u, v = edge_a["tail"]["$ref"], edge_a["head"]["$ref"]
        for edge_b in line_views[:i]:
            if {u, v} & {edge_b["tail"]["$ref"], edge_b["head"]["$ref"]}:
                continue
            assert not proper_intersection(seg_a, split_points(edge_b)), (diagram["name"], u, v)
        for ov in (v_ for v_ in all_views if v_["_type"] == "UMLUseCaseView"):
            if ov["_id"] not in {u, v}:
                ox, oy = center(ov)
                assert not intersects_segment(*seg_a, (ox, oy, ov["width"], ov["height"]), 3), (
                    diagram["name"], edge_a["_type"], seg_a, (ox, oy), ov["width"])
    print(f"{diagram['name'][:5]} | {stats['UMLUseCaseView']} óvalos | "
          f"{stats['UMLIncludeView']} include | {stats['UMLExtendView']} extend")

old_m = next(m for m in original["ownedElements"] if m["name"] == "Diagramas de RememberMind")
assert [m for m in model["ownedElements"] if m["name"] != "01 Casos de uso"] == [
    m for m in old_m["ownedElements"] if m["name"] != "01 Casos de uso"]
project["name"] = "DIAGRAMAS_REMEMBERMIND"
(STAGE / "DIAGRAMAS_REMEMBERMIND.mdj").write_text(json.dumps(project, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
(STAGE.parent / "base.sha256").write_text(sha256(source).hexdigest(), encoding="ascii")
