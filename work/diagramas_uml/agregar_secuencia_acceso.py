"""Agrega la secuencia de ingreso al proyecto StarUML existente."""

import argparse
import json
import re
from pathlib import Path
from uuid import uuid5, NAMESPACE_URL

from secuencias_prioritarias import SPECS

DIAGRAM = "DS-01. INICIO DE SESIÓN"
PATH = Path(__file__).parent / "entrega" / "DIAGRAMAS_REMEMBERMIND" / "DIAGRAMAS_REMEMBERMIND.mdj"
INK = {"font": "Arial;13;0", "lineColor": "#263345", "fontColor": "#172335"}


def ref(obj):
    return {"$ref": obj["_id"]}


def own(parent, field, kind, key, **props):
    node = {
        "_type": kind,
        "_id": uuid5(NAMESPACE_URL, f"remembermind/secuencia/{parent['_id']}/{key}").hex,
        "_parent": ref(parent),
        **props,
    }
    parent.setdefault(field, []).append(node)
    return node


def sub(parent, field, kind, key, **props):
    node = own(parent, "subViews", kind, key, **props)
    parent[field] = ref(node)
    return node


def view(parent, kind, key, model, x, y, width, height, **props):
    return own(parent, "ownedViews", kind, key, model=ref(model),
               left=x, top=y, width=width, height=height, **(INK | props))


def walk(obj):
    if isinstance(obj, dict):
        yield obj
        for value in obj.values():
            yield from walk(value)
    elif isinstance(obj, list):
        for value in obj:
            yield from walk(value)


def frame_labels(frame, key):
    for field in ("nameLabel", "frameTypeLabel"):
        sub(frame, field, "LabelView", f"{key}/{field}", parentStyle=True)


def fragment(diagram, interaction, name, operator, top, right, guards):
    key = f"fragment/{top}"
    model = own(interaction, "fragments", "UMLCombinedFragment", key+"/model",
                name=name, interactionOperator=operator)
    left = 64 if operator != "loop" else 209
    bottom = guards[-1][1] + guards[-1][2]
    fv = view(diagram, "UMLCombinedFragmentView", key+"/view", model, left, top,
              right-left, bottom-top, fillColor="#ffffff", showShadow=False)
    frame_labels(fv, key)
    compartment = sub(fv, "operandCompartment", "UMLInteractionOperandCompartmentView",
                      key+"/compartment", model=ref(model), parentStyle=True)
    for num, (guard, y, height) in enumerate(guards):
        item = own(model, "operands", "UMLInteractionOperand", f"{key}/{num}/model", guard=guard)
        operand = own(compartment, "subViews", "UMLInteractionOperandView", f"{key}/{num}/view",
                      model=ref(item), left=left, top=y, width=right-left, height=height, parentStyle=True)
        sub(operand, "guardLabel", "LabelView", f"{key}/{num}/guard", parentStyle=True)


def priority_rows(number, opening, branches):
    rows, guards = [], []
    y = 155
    def add(messages):
        nonlocal y
        for message in messages:
            source, direction, target, label = re.fullmatch(r"(\d)([<>])(\d) (.+)", message).groups()
            rows.append((y, source, target, label, direction == "<", 22))
            y += 37 if source != target else 48
    add(opening)
    top = y - 7
    y = top + 62
    extra = None
    for idx, (guard, messages) in enumerate(branches):
        start = y - (36 if idx else 41)
        if number == 3 and idx == 0:
            add(messages[:4])
            loop_top = y - 8
            y += 43
            add(messages[4:7])
            extra = (loop_top, [("Por cada control observado", loop_top+24, y-loop_top-18)])
            y += 24
            add(messages[7:])
        else:
            add(messages)
        end = y - 10
        guards.append((guard, start, end - start))
        y = end + 36
    return rows, top, guards, extra, y+16


def add_sequence(project, number=1, spec=None):
    nodes = list(walk(project))
    section = next(x for x in nodes if x.get("_type") == "UMLPackage" and x.get("name") == "02 Secuencia")
    title = f"DS-{number:02}. {spec[0]}" if spec else DIAGRAM
    if any(x.get("name") == title for x in walk(section)):
        raise ValueError(f"{title} ya existe. Se conservó el archivo sin cambios.")
    collaboration = own(section, "ownedElements", "UMLCollaboration", f"collaboration/{number}",
                        name=spec[0].capitalize() if spec else "Acceso institucional")
    access_actor = next(
        (x for x in nodes if x.get("_type") == "UMLActor" and x.get("name") == "USUARIO CON CUENTA"),
        None,
    )
    if spec is None and access_actor is None:
        access_actor = own(collaboration, "ownedElements", "UMLActor", "actor",
                           name="Usuario con cuenta", documentation="Personal institucional o familiar autorizado.")
    interaction = own(
        collaboration, "ownedElements", "UMLInteraction", "interaction", name=spec[0] if spec else "SESIONES Y SEGURIDAD",
        documentation=f"{spec[1]}. Secuencia de diseño funcional ICONIX." if spec else ("CU-01 · HU-01. Solicitud de ingreso y decisión de acceso. La rama 2FA termina con "
                       "un desafío pendiente; Fortify establece la sesión tras validar el código "
                       "en una solicitud posterior. El flujo directo muestra la selección del "
                       "destino autorizado. Fuentes: FortifyServiceProvider, LoginResponse y "
                       "las acciones de autenticación de Laravel Fortify."),
    )
    diagram = own(interaction, "ownedElements", "UMLSequenceDiagram", "diagram",
                  name=title, showActivation=True, showSequenceNumber=True, showSignature=False)
    custom_rows = priority_rows(number, spec[3], spec[4]) if spec else None
    layout = [
        (str(i), name, 164+i*270, max(120, 36+len(name)*7))
        for i, name in enumerate(spec[2].split("|"))
    ] if spec else [
        ("persona", "Usuario con cuenta", 164, 170),
        ("pantalla", "Pantalla de acceso", 422, 162),
        ("fortify", "Fortify", 678, 112),
        ("auth", "Autenticación institucional", 940, 194),
        ("usuario", "USUARIO", 1182, 132),
        ("rol", "ROL", 1410, 116),
    ]
    right = layout[-1][2] + 124
    bottom = custom_rows[-1] if custom_rows else 1024
    frame = view(diagram, "UMLFrameView", "frame", interaction, 24, 24, right-24, bottom-24)
    frame_labels(frame, "frame")
    participants = {}
    for index, (key, name, cx, width) in enumerate(layout):
        is_actor = index == 0 or (number == 8 and index == 4) or (number == 11 and index == 1)
        actor = access_actor if not spec else next(
            (x for x in nodes if x.get("_type") == "UMLActor" and x.get("name", "").casefold() == name.casefold()), None)
        if is_actor and actor is None:
            actor = own(collaboration, "ownedElements", "UMLActor", f"{key}/actor", name=name)
        attr_props = {"type": ref(actor)} if is_actor else {}
        role = own(collaboration, "attributes", "UMLAttribute", f"{key}/role",
                   name=name, **attr_props)
        model = own(interaction, "participants", "UMLLifeline", f"{key}/model",
                    name=name, represent=ref(role))
        line = view(diagram, "UMLSeqLifelineView", f"{key}/view", model,
                    cx-width//2, 66, width, bottom-86, showProperty=False, showType=False,
                    stereotypeDisplay="icon" if is_actor else "label",
                    fillColor="#ffffff", showShadow=False)
        name_view = sub(line, "nameCompartment", "UMLNameCompartmentView",
                        f"{key}/name", parentStyle=True)
        for field in ("stereotypeLabel", "nameLabel", "namespaceLabel", "propertyLabel"):
            sub(name_view, field, "LabelView", f"{key}/{field}", parentStyle=True,
                horizontalAlignment=2)
        line_part = sub(line, "linePart", "UMLLinePartView", f"{key}/line",
                        model=ref(model), left=cx, top=123, width=1, height=bottom-143, **INK)
        participants[key] = (model, line_part, cx)

    guard_rows = [
        ("credenciales inválidas", 398, 112),
        ("contraseña válida y cuenta inactiva", 510, 112),
        ("cuenta activa con segundo factor confirmado", 622, 141),
        ("cuenta activa sin desafío de segundo factor", 763, 227),
    ]
    if custom_rows:
        _, top, guards, extra, _ = custom_rows
        fragment(diagram, interaction, "", "opt" if number == 6 else "alt", top, right-50, guards)
        if extra:
            fragment(diagram, interaction, "", "loop", extra[0], right-85, extra[1])
    else:
        fragment(diagram, interaction, "Verificar credenciales", "alt", 373, right-50, guard_rows)

    # Calls and returns are horizontal and sorted by execution order.
    rows = [
        (155, "persona", "pantalla", "Ingresar correo y contraseña", False, 70),
        (194, "pantalla", "fortify", "Enviar solicitud de ingreso", False, 150),
        (233, "fortify", "auth", "Validar credenciales", False, 105),
        (273, "auth", "usuario", "Buscar cuenta por correo", False, 28),
        (310, "usuario", "auth", "Cuenta o ausencia de coincidencia", True, 0),
        (347, "auth", "auth", "Comprobar contraseña y estado", False, 20),
        (437, "auth", "fortify", "Rechazar credenciales", True, 0),
        (474, "fortify", "pantalla", "Mostrar error de autenticación", True, 0),
        (549, "auth", "fortify", "Rechazar cuenta inactiva", True, 0),
        (586, "fortify", "pantalla", "Informar cuenta inactiva", True, 0),
        (662, "auth", "fortify", "Devolver identidad válida", True, 0),
        (700, "fortify", "fortify", "Registrar desafío pendiente", False, 21),
        (738, "fortify", "pantalla", "Solicitar código 2FA", True, 0),
        (804, "auth", "fortify", "Devolver identidad válida", True, 0),
        (841, "fortify", "fortify", "Autenticar y regenerar sesión", False, 22),
        (881, "fortify", "rol", "Consultar rol y acceso al destino", False, 26),
        (916, "rol", "fortify", "Devolver destino autorizado", True, 0),
        (952, "fortify", "pantalla", "Redirigir al inicio autorizado", True, 0),
    ]
    if custom_rows:
        rows = custom_rows[0]
    for n, (y, source, target, label, reply, duration) in enumerate(rows, 1):
        a, tail, x0 = participants[source]
        b, head, x1 = participants[target]
        key = f"message/{n}"
        msg = own(interaction, "messages", "UMLMessage", key+"/model",
                  name=label, source=ref(a), target=ref(b),
                  messageSort="reply" if reply else "synchCall")
        points = f"{x0}:{y};{x1}:{y}" if source != target else \
                 f"{x0}:{y};{x0+64}:{y};{x0+64}:{y+18};{x0}:{y+18}"
        edge = own(diagram, "ownedViews", "UMLSeqMessageView", key+"/view", model=ref(msg),
                   head=ref(head), tail=ref(tail), points=points, showType=False,
                   showProperty=False, **INK)
        for field, distance, alpha in (
            ("nameLabel", 12, 1.5707963267948966),
            ("stereotypeLabel", 25, 1.5707963267948966),
            ("propertyLabel", 10, -1.5707963267948966),
        ):
            sub(edge, field, "EdgeLabelView", key+"/"+field, hostEdge=ref(edge),
                edgePosition=1, distance=distance, alpha=alpha, **INK)
        sub(edge, "activation", "UMLActivationView", key+"/activation",
            height=duration or 20, fillColor="#ffffff", **INK)

    return diagram


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--input", type=Path, default=PATH)
    parser.add_argument("--output", type=Path, default=PATH)
    args = parser.parse_args()
    project = json.loads(args.input.read_text(encoding="utf-8-sig"))
    sequence = add_sequence(project)
    other_sequences = [add_sequence(project, number, spec) for number, spec in SPECS.items()]
    nodes = list(walk(project))
    ids = [n["_id"] for n in nodes if "_id" in n]
    assert len(ids) == len(set(ids))
    assert len([n for n in nodes if n.get("_type") == "UMLUseCaseDiagram"]) == 20
    assert len([n for n in sequence["ownedViews"] if n["_type"] == "UMLSeqLifelineView"]) == 6
    assert len([n for n in sequence["ownedViews"] if n["_type"] == "UMLSeqMessageView"]) == 18
    assert len(next(n for n in nodes if n.get("_type") == "UMLCombinedFragment")["operands"]) == 4
    assert len(other_sequences) == 11
    assert all(sum(v["_type"] == "UMLSeqMessageView" for v in d["ownedViews"]) >= 8 for d in other_sequences)
    known = set(ids)
    new = [n for n in nodes if n.get("_id") == sequence["_parent"]["$ref"]][0]
    section = next(n for n in nodes if n.get("name") == "02 Secuencia")
    assert all(n["$ref"] in known for n in walk(section) if "$ref" in n)
    tmp = args.output.with_suffix(".mdj.tmp")
    tmp.write_text(json.dumps(project, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    tmp.replace(args.output)
    print(f"{args.output.resolve()} | 20 casos de uso y 12 secuencias UML")


if __name__ == "__main__":
    main()
