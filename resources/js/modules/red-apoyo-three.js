import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';

export class RedApoyoGraph {
    constructor(container) {
        this.container = container;
        try {
            this.data = JSON.parse(container.dataset.redApoyo);
        } catch (e) {
            console.error('Error parsing red apoyo data:', e);
            this.data = null;
        }
        
        this.nodes = [];
        this.lines = [];
        this.hoveredNode = null;
        this.selectedNode = null;

        this.tooltip = document.getElementById('red-apoyo-tooltip');
        this.tooltipTitle = document.getElementById('tooltip-title');
        this.tooltipSubtitle = document.getElementById('tooltip-subtitle');

        if (this.data && this.data.adulto) {
            this.init();
        }
    }

    init() {
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;

        if (width === 0 || height === 0) {
            console.warn('Red apoyo: contenedor sin dimensiones visibles.');
        }

        this.scene = new THREE.Scene();

        const aspect = width / height || 1;
        this.camera = new THREE.OrthographicCamera(
            -aspect * 6,
            aspect * 6,
            5,
            -5,
            0.1,
            1000
        );
        this.camera.position.set(0, 0, 20);
        this.camera.lookAt(0, 0, 0);

        this.renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        this.renderer.setSize(width || 100, height || 100);
        this.renderer.setPixelRatio(window.devicePixelRatio);
        this.container.appendChild(this.renderer.domElement);

        this.controls = new OrbitControls(this.camera, this.renderer.domElement);
        this.controls.enableRotate = false;
        this.controls.enableDamping = true;
        this.controls.dampingFactor = 0.05;
        this.controls.enableZoom = true;
        this.controls.minZoom = 0.5;
        this.controls.maxZoom = 2;
        this.controls.enablePan = true;

        this.raycaster = new THREE.Raycaster();
        this.mouse = new THREE.Vector2();

        this.buildGraph();

        this.renderer.domElement.addEventListener('mousemove', this.onMouseMove.bind(this));
        this.renderer.domElement.addEventListener('click', this.onClick.bind(this));
        this.renderer.domElement.addEventListener('mouseleave', () => {
            this.hoveredNode = null;
            this.updateTooltip(null);
            this.updateNodesAndLines();
        });
        window.addEventListener('resize', this.onResize.bind(this));

        this.animate();
    }

    createNodeTexture(params) {
        const canvas = document.createElement('canvas');
        canvas.width = 256;
        canvas.height = 256;
        const ctx = canvas.getContext('2d');

        // Draw Circle
        ctx.beginPath();
        ctx.arc(128, 100, params.radius || 60, 0, 2 * Math.PI);
        ctx.fillStyle = params.bgColor;
        ctx.fill();
        ctx.lineWidth = params.borderWidth || 8;
        ctx.strokeStyle = params.borderColor;
        ctx.stroke();

        // Draw Initials
        ctx.fillStyle = params.textColor;
        ctx.font = '900 ' + (params.fontSize || 48) + 'px "Nunito", "Inter", sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(params.initials, 128, 100);

        // Draw Label Name
        ctx.fillStyle = '#2F3E5C';
        ctx.font = '900 24px "Nunito", "Inter", sans-serif';
        let name = params.name;
        if (name.length > 15) name = name.substring(0, 14) + '.';
        ctx.fillText(name, 128, 190);

        // Draw Label Subtitle
        if (params.subtitle) {
            ctx.fillStyle = '#64748B';
            ctx.font = 'bold 18px "Nunito", "Inter", sans-serif';
            ctx.fillText(params.subtitle, 128, 220);
        }

        const texture = new THREE.CanvasTexture(canvas);
        texture.minFilter = THREE.LinearFilter;
        return texture;
    }

    addNode(data, x, y, visualParams) {
        const texture = this.createNodeTexture({
            initials: data.iniciales || '?',
            name: data.nombre || 'Desconocido',
            subtitle: data.parentesco || data.rol || '',
            ...visualParams
        });

        const material = new THREE.SpriteMaterial({ map: texture, transparent: true });
        const sprite = new THREE.Sprite(material);
        
        const scaleBase = visualParams.scale || 80;
        sprite.scale.set(scaleBase, scaleBase, 1);
        sprite.position.set(x, y, 1); // Z=1 to appear above lines

        sprite.userData = {
            id: data.id,
            realId: data.realId,
            tipo: data.tipo,
            baseScale: scaleBase,
            name: data.nombre,
            role: data.parentesco || data.rol || 'Rol no definido',
            isInstitutional: data.tipo === 'voluntario' || data.tipo === 'institucional'
        };

        this.scene.add(sprite);
        this.nodes.push(sprite);
        return sprite;
    }

    addLine(sourceNode, targetNode, params = {}) {
        const material = new THREE.LineBasicMaterial({
            color: params.color || 0x8DA280,
            transparent: true,
            opacity: params.opacity || 0.6,
            linewidth: params.lineWidth || 1 // Note: WebGL standard limits line width to 1 on most platforms, but it's okay for 2.5D diagram
        });

        const geometry = new THREE.BufferGeometry().setFromPoints([
            new THREE.Vector3(sourceNode.position.x, sourceNode.position.y, 0),
            new THREE.Vector3(targetNode.position.x, targetNode.position.y, 0)
        ]);

        const line = new THREE.Line(geometry, material);
        line.userData = {
            sourceId: sourceNode.userData.id,
            targetId: targetNode.userData.id,
            baseOpacity: params.opacity || 0.6,
            isInstitutional: targetNode.userData.isInstitutional
        };

        this.scene.add(line);
        this.lines.push(line);
    }

    buildGraph() {
        const adulto = this.data.adulto;
        if (!adulto) return;

        adulto.tipo = 'adulto';
        const nodeAdulto = this.addNode(adulto, 0, 3.2, {
            bgColor: '#334155', borderColor: '#475E88', textColor: '#FFFFFF',
            radius: 80, fontSize: 60, scale: 1.6, subtitle: 'Adulto Mayor'
        });

        // Agrupar familiares
        const familiares = this.data.familiares || [];
        const grupo1 = []; // Familia directa y responsable
        const grupo2 = []; // Familiares secundarios

        const parentescosG1 = ['cónyuge', 'esposo', 'esposa', 'hijo', 'hija', 'hijo/a', 'hermano', 'hermana', 'hermano/a'];

        familiares.forEach(fam => {
            const p = (fam.parentesco || '').toLowerCase();
            const isG1 = fam.es_responsable || parentescosG1.some(g1 => p.includes(g1));
            
            if (isG1) {
                grupo1.push(fam);
            } else {
                grupo2.push(fam);
            }
        });

        const drawGroup = (group, yLevel, isG1) => {
            const nodes = [];
            const count = group.length;
            const spacing = 2.0;
            const startX = -((count - 1) * spacing) / 2;

            group.forEach((fam, index) => {
                fam.tipo = 'familiar';
                const x = startX + (index * spacing);
                const y = yLevel;

                let bgColor = '#F1F5F9';
                let borderColor = '#94A3B8';
                let textColor = '#475569';

                if (fam.es_responsable) {
                    bgColor = '#FEF2F2';
                    borderColor = '#F9735B';
                    textColor = '#991B1B';
                } else if (fam.es_contacto) {
                    bgColor = '#FEF3C7';
                    borderColor = '#D9A441';
                    textColor = '#92400E';
                } else {
                    bgColor = '#F0FDF4';
                    borderColor = '#7A9B76';
                    textColor = '#166534';
                }

                const scale = fam.es_responsable ? 1.4 : 1.2;

                const nodeFam = this.addNode(fam, x, y, {
                    bgColor, borderColor, textColor, radius: 70, fontSize: 44, scale: scale
                });

                nodes.push(nodeFam);

                // Conexiones
                if (isG1) {
                    this.addLine(nodeAdulto, nodeFam, {
                        color: fam.es_responsable ? 0xF9735B : (fam.es_contacto ? 0xD9A441 : 0x94A3B8),
                        opacity: fam.es_responsable ? 0.9 : 0.6,
                        lineWidth: fam.es_responsable ? 2 : 1
                    });
                } else {
                    // Si es G2, conectar a Adulto de forma suave
                    this.addLine(nodeAdulto, nodeFam, {
                        color: fam.es_contacto ? 0xD9A441 : 0xCBD5E1,
                        opacity: 0.4
                    });
                }
            });
            return nodes;
        };

        const g1Nodes = drawGroup(grupo1, 0.8, true);
        const g2Nodes = drawGroup(grupo2, -1.2, false);

        // Voluntarios (Rama externa)
        const voluntarios = this.data.voluntarios || [];
        if (voluntarios.length > 0 || (familiares.length > 0)) { 
            // Solo dibujar rama institucional si hay data de algun tipo para no dejarla colgada.
            const instX = 4.5;
            const instY = 0.8;

            const instNodeData = {
                id: 'institucional-group',
                realId: null,
                tipo: 'institucional',
                nombre: 'Apoyo',
                rol: 'Institucional',
                iniciales: 'AI'
            };

            const nodeInst = this.addNode(instNodeData, instX, instY, {
                bgColor: '#F5F3FF', borderColor: '#8B7BB8', textColor: '#5B21B6',
                radius: 70, fontSize: 40, scale: 1.3
            });

            // Conexión diferente (punteada conceptual)
            this.addLine(nodeAdulto, nodeInst, { color: 0xA78BFA, opacity: 0.5, lineWidth: 1 });

            // Dibujar voluntarios como rama hacia abajo del grupo institucional
            const volSpacing = 1.6;
            if (voluntarios.length > 0) {
                voluntarios.forEach((vol, index) => {
                    vol.tipo = 'voluntario';
                    const vx = instX + ((index % 2 === 0 ? 1 : -1) * 0.8); // Zigzag
                    const vy = instY - 1.5 - (Math.floor(index / 2) * volSpacing);

                    const nodeVol = this.addNode(vol, vx, vy, {
                        bgColor: '#F0F9FF', borderColor: '#5BA7C8', textColor: '#0369A1',
                        radius: 60, fontSize: 36, scale: 1.1
                    });

                    this.addLine(nodeInst, nodeVol, { color: 0x7DD3FC, opacity: 0.6 });
                });
            } else {
                // Nodo indicando que no hay voluntarios
                const nodeVolEmpty = this.addNode({
                    id: 'empty-vol',
                    realId: null,
                    tipo: 'institucional',
                    nombre: 'Sin voluntarios',
                    rol: '',
                    iniciales: '-'
                }, instX, instY - 1.5, {
                    bgColor: '#F8FAFC', borderColor: '#CBD5E1', textColor: '#64748B',
                    radius: 50, fontSize: 30, scale: 1.0
                });
                this.addLine(nodeInst, nodeVolEmpty, { color: 0xCBD5E1, opacity: 0.4 });
            }
        }
    }

    onMouseMove(event) {
        const rect = this.renderer.domElement.getBoundingClientRect();
        this.mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        this.mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

        this.raycaster.setFromCamera(this.mouse, this.camera);
        const intersects = this.raycaster.intersectObjects(this.nodes);

        if (intersects.length > 0) {
            const object = intersects[0].object;
            if (this.hoveredNode !== object) {
                this.hoveredNode = object;
                this.renderer.domElement.style.cursor = 'pointer';
                this.updateTooltip(event);
                this.updateNodesAndLines();
            } else {
                this.updateTooltipPosition(event);
            }
        } else {
            if (this.hoveredNode !== null) {
                this.hoveredNode = null;
                this.renderer.domElement.style.cursor = 'default';
                this.updateTooltip(null);
                this.updateNodesAndLines();
            }
        }
    }

    onClick(event) {
        if (this.hoveredNode && this.hoveredNode.userData.tipo !== 'institucional') {
            this.selectedNode = this.hoveredNode;
            this.updateNodesAndLines();
            
            // Dispatch event to Alpine/Livewire
            window.dispatchEvent(new CustomEvent('red-apoyo-click', {
                detail: {
                    id: this.selectedNode.userData.id,
                    realId: this.selectedNode.userData.realId,
                    tipo: this.selectedNode.userData.tipo
                }
            }));
        } else if (!this.hoveredNode) {
            // Optional: click outside to deselect
            // this.selectedNode = null;
            // this.updateNodesAndLines();
        }
    }

    updateTooltip(event) {
        if (!this.hoveredNode || this.hoveredNode.userData.tipo === 'institucional') {
            this.tooltip.style.opacity = '0';
            return;
        }

        const data = this.hoveredNode.userData;
        this.tooltipTitle.textContent = data.name;
        this.tooltipSubtitle.textContent = data.role;
        this.tooltip.style.opacity = '1';
        this.updateTooltipPosition(event);
    }

    updateTooltipPosition(event) {
        if (!event) return;
        const rect = this.container.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;
        
        this.tooltip.style.left = (x + 15) + 'px';
        this.tooltip.style.top = (y + 15) + 'px';
    }

    updateNodesAndLines() {
        const activeNode = this.hoveredNode || this.selectedNode;

        this.nodes.forEach(node => {
            const data = node.userData;
            if (!activeNode) {
                // Default state
                node.scale.setScalar(data.baseScale);
                node.material.opacity = 1;
            } else {
                // Is this node active?
                const isActive = node === activeNode || node === this.selectedNode || node === this.hoveredNode;
                const isCenter = data.id === 'adulto';
                
                if (isActive) {
                    node.scale.setScalar(data.baseScale * 1.15); // Grow 15%
                    node.material.opacity = 1;
                } else if (isCenter) {
                    node.scale.setScalar(data.baseScale);
                    node.material.opacity = 1;
                } else {
                    node.scale.setScalar(data.baseScale);
                    node.material.opacity = 0.5; // Dim
                }
            }
        });

        this.lines.forEach(line => {
            if (!activeNode) {
                line.material.opacity = line.userData.baseOpacity;
            } else {
                const isRelated = line.userData.sourceId === activeNode.userData.id || line.userData.targetId === activeNode.userData.id;
                if (isRelated) {
                    line.material.opacity = 1.0;
                } else {
                    line.material.opacity = 0.15; // Dim
                }
            }
        });
    }

    onResize() {
        if (!this.container || !this.camera) return;
        const width = this.container.clientWidth;
        const height = this.container.clientHeight;
        if (width === 0 || height === 0) return;

        const aspect = width / height;

        this.camera.left = -aspect * 6;
        this.camera.right = aspect * 6;
        this.camera.top = 5;
        this.camera.bottom = -5;
        this.camera.updateProjectionMatrix();

        this.renderer.setSize(width, height);
    }

    animate() {
        requestAnimationFrame(this.animate.bind(this));

        // Subtle floating animation
        const time = Date.now() * 0.001;
        this.nodes.forEach((node, index) => {
            if (node.userData.id !== 'adulto') { // Don't move center node much
                node.position.y += Math.sin(time * 2 + index) * 0.05;
            }
        });

        // Need to update lines geometry based on floating nodes
        this.lines.forEach(line => {
            const sourceId = line.userData.sourceId;
            const targetId = line.userData.targetId;
            const sourceNode = this.nodes.find(n => n.userData.id === sourceId);
            const targetNode = this.nodes.find(n => n.userData.id === targetId);
            
            if (sourceNode && targetNode) {
                const positions = line.geometry.attributes.position.array;
                positions[0] = sourceNode.position.x;
                positions[1] = sourceNode.position.y;
                positions[3] = targetNode.position.x;
                positions[4] = targetNode.position.y;
                line.geometry.attributes.position.needsUpdate = true;
            }
        });

        this.controls.update();
        this.renderer.render(this.scene, this.camera);
    }
}
