(() => {
    'use strict';

    const widget = document.querySelector('colsoft-ayuda');
    if (!widget) return;
    const scriptActual = document.currentScript || document.querySelector('script[src$="soporte.js"]');
    const raiz = new URL('../../', scriptActual ? scriptActual.src : location.href);
    const rol = widget.dataset.rol || '';
    const permitidos = new Set((widget.dataset.modulos || '').split(' '));
    const visitante = !rol;
    const admin = rol === 'administrador';
    const operario = rol === 'operario';

    // Las rutas apuntan a vistas navegables. Las operaciones siguen en sus formularios.
    const temas = {
        acceso: {
            titulo: 'Iniciar sesión', claves: ['login', 'ingresar', 'entrar al sistema', 'acceso', 'documento'],
            ruta: 'view/login/login.php', publico: true,
            texto: 'En Inicio, pulsa Login. Escribe tu documento y contraseña e inicia sesión. ColSoftCO abre el panel que corresponde al rol de tu cuenta.'
        },
        clave: {
            titulo: 'Recuperar contraseña', claves: ['clave', 'olvide mi contraseña', 'restablecer contraseña', 'cambiar contraseña', 'enlace vencido'],
            ruta: 'view/recuperar_contrasena/recuperar_contrasena.php', publico: true,
            texto: 'Desde el acceso, abre la recuperación de contraseña. Envía el correo registrado y abre el enlace que recibas. Escribe la nueva clave dos veces y guarda. Necesita al menos 8 caracteres, una mayúscula, una minúscula, un número y un carácter especial. Si el enlace caducó o ya se usó, solicita otro.'
        },
        permisos: {
            titulo: 'Roles y permisos', claves: ['mis permisos', 'mi rol', 'administrador', 'bodeguero', 'operario', 'acceso denegado', 'no veo un modulo'],
            texto: (rol ? 'Tu rol es ' + rol + '. ' : '') + 'El administrador gestiona usuarios, proveedores, recetas e informes. El bodeguero controla stock y registra productos terminados. El operario registra materia prima y consulta inventarios y recetas. Los tres usan tareas, mensajes e historial de fabricación. Una ruta restringida te devuelve a tu panel.'
        },
        panel: {
            titulo: 'Volver a mi panel', claves: ['navegacion', 'menu', 'inicio', 'panel principal', 'acciones rapidas'],
            ruta: 'app/ir_panel.php',
            texto: 'Pulsa el logo de ColSoftCO o Panel Principal en el menú lateral. En móvil, abre primero el menú. El panel reúne los accesos rápidos, el resumen y las tareas de tu rol.'
        },
        materia: {
            titulo: 'Registrar materia prima', claves: ['registrar material', 'crear insumo', 'agregar materia prima', 'unidad de medida', 'cantidad inicial'],
            ruta: 'view/registromp/registromp.php',
            texto: 'Abre Registrar Materia Prima. Escribe el nombre, selecciona la unidad o elige Crear una unidad e indica nombre y sigla. Completa cantidad inicial y stock mínimo, selecciona un proveedor si corresponde y pulsa Registrar.'
        },
        inventario: {
            titulo: 'Inventario de materia prima', claves: ['inventario', 'existencias', 'buscar material', 'stock bajo', 'consultar stock', 'insumos'],
            ruta: 'view/inventario_materia_prima/inventario_materia_prima.php',
            texto: 'Abre Inventario de Materia Prima. Busca por nombre y cambia Solo activos a Todos si falta un material. Compara stock actual con stock mínimo. STOCK BAJO aparece cuando la existencia es igual o inferior al mínimo. El operario consulta esta lista.'
        },
        stock: {
            titulo: 'Editar stock de un material', claves: ['actualizar existencias', 'control de stock', 'registrar stock', 'ajustar stock', 'habilitar material', 'deshabilitar material'],
            ruta: 'view/control_de_stock/control_de_stock.php',
            texto: 'El administrador y el bodeguero pueden abrir Control de Stock. Usa Registrar Stock para crear un material o Ver Inventario para localizar uno existente. Con Editar puedes ajustar existencias, mínimo, unidad y proveedor. Habilitar o Deshabilitar cambia el estado del registro.'
        },
        alertas: {
            titulo: 'Configurar alertas de stock', claves: ['alerta', 'aviso de stock', 'correo de notificacion', 'alerta de minimo', 'umbral'],
            ruta: 'view/control_de_stock/control_de_stock.php',
            texto: 'En Control de Stock, elige el material e indica la cantidad mínima. Si seleccionas la notificación por correo, completa la dirección. Pulsa Registrar. Desde Ver Alertas de Stock puedes revisar, editar o eliminar las alertas.'
        },
        terminados: {
            titulo: 'Inventario de productos terminados', claves: ['inventario de productos', 'existencias de colchones', 'actualizar producto', 'eliminar producto'],
            ruta: 'view/inventario_productos_terminados/inventario_productos_terminados.php',
            texto: 'Abre Inventario de Productos para consultar productos terminados y sus cantidades. Usa la búsqueda y el orden de la lista. El administrador y el bodeguero pueden actualizar y eliminar registros. El operario tiene acceso de consulta.'
        },
        registrarProducto: {
            titulo: 'Registrar producto terminado', claves: ['registrar produccion', 'ingresar producto terminado', 'registrar colchones terminados'],
            ruta: 'view/registro_de_producto_terminado/registro_producto_terminado.php',
            texto: 'El administrador o el bodeguero abre Registrar Producto Terminado, escoge el modelo, indica una cantidad entera positiva y pulsa Registrar. El sistema verifica la receta y las existencias. Si alcanzan, descuenta los materiales, suma el producto terminado y registra la producción. Si faltan, revisa el detalle mostrado.'
        },
        receta: {
            titulo: 'Consultar receta de colchón', claves: ['receta', 'calcular materiales', 'consultar modelo', 'cuanta materia prima', 'simular produccion'],
            ruta: 'view/Receta_de_colchones/receta_colchones.php',
            texto: 'En Receta de Colchones, selecciona el producto, indica cuántas unidades quieres preparar y pulsa Generar. La tabla compara las cantidades requeridas y disponibles. El operario puede hacer esta consulta. El bodeguero gestiona la producción desde Registrar Producto Terminado.'
        },
        modelo: {
            titulo: 'Registrar modelo de colchón', claves: ['crear modelo', 'nuevo modelo', 'nueva receta', 'agregar modelo'],
            ruta: 'view/Receta_de_colchones/registrar_modelo.php',
            texto: 'Como administrador, entra en Receta de Colchones y pulsa Registrar Nuevo Modelo de Colchón. Escribe el nombre y agrega los materiales de la receta con la cantidad por unidad. Registra el modelo con al menos un material y cantidades positivas.'
        },
        editarReceta: {
            titulo: 'Editar receta', claves: ['modificar receta', 'cambiar receta', 'guardar receta', 'quitar material de receta'],
            ruta: 'view/Receta_de_colchones/editar_receta.php',
            texto: 'Como administrador, entra en Receta de Colchones → Editar Receta. Selecciona el modelo y pulsa Cargar Receta. Ajusta las cantidades o agrega y quita materiales. Conserva al menos un material, con cantidad positiva y sin repetirlo. Pulsa Guardar Cambios y confirma.'
        },
        fabricar: {
            titulo: 'Fabricar colchones', claves: ['fabricar', 'confirmar fabricacion', 'produccion imposible', 'faltan materiales', 'stock insuficiente'],
            ruta: 'view/Receta_de_colchones/receta_colchones.php',
            texto: 'En Receta de Colchones, genera el cálculo. Si hay material suficiente, el administrador puede pulsar Fabricar y confirmar. Esto descuenta insumos y aumenta los terminados. Ante un faltante, revisa el material marcado y la cantidad requerida. El recibo está en Historial de Fabricación.'
        },
        recibo: {
            titulo: 'Descargar recibo de fabricación', claves: ['comprobante', 'recibo pdf', 'historial de fabricacion', 'comprobante de produccion'],
            ruta: 'view/historial_fabricacion/historial_fabricacion.php',
            texto: 'Abre Historial de Fabricación. Localiza la producción por fecha, modelo, cantidad o usuario y pulsa Ver / Descargar PDF en esa fila. Los tres roles pueden consultar los recibos.'
        },
        proveedores: {
            titulo: 'Gestionar proveedores', claves: ['proveedores', 'registrar proveedor', 'editar proveedor', 'deshabilitar proveedor'],
            ruta: 'view/lista_proveedores/lista_proveedores.php',
            texto: 'Abre Lista de Proveedores. El administrador puede registrar, editar y cambiar el estado de un proveedor. El bodeguero y el operario pueden consultar la lista y solicitar pedidos al administrador.'
        },
        pedido: {
            titulo: 'Solicitar un pedido', claves: ['pedido', 'pedir material', 'contactar proveedor', 'solicitar insumos'],
            ruta: 'view/lista_proveedores/lista_proveedores.php',
            texto: 'En Lista de Proveedores, el administrador elige Contactar → Realizar Pedido. El bodeguero y el operario eligen Solicitar pedido y envían la solicitud al administrador. Selecciona una materia prima asociada y una cantidad positiva. Pulsa Confirmar Pedido y revisa el resultado del envío.'
        },
        tareas: {
            titulo: 'Crear una tarea', claves: ['tareas', 'nueva tarea', 'asignar tarea', 'registrar tarea', 'vencimiento'],
            ruta: 'view/tareas/tareas.php',
            texto: 'Abre Tareas o el tablero de tu panel y pulsa + Nueva tarea. Escribe el título, elige prioridad y, si corresponde, una fecha desde hoy. Pulsa Guardar tarea. Solo el administrador puede asignarla a otro usuario.'
        },
        tablero: {
            titulo: 'Mover o eliminar tareas', claves: ['kanban', 'calendario', 'completar tarea', 'mover tarea', 'eliminar tarea', 'estado de tarea'],
            ruta: 'view/tareas/tareas.php',
            texto: 'En Kanban, mueve la tarjeta entre Pendientes, Por hacer y Terminadas, o usa los controles de la tarjeta. Puedes gestionar tus propias tareas y confirmar su eliminación. El administrador también gestiona las de otros usuarios. Cambia a Calendario para consultar vencimientos.'
        },
        mensajes: {
            titulo: 'Enviar un mensaje', claves: ['mensajeria', 'bandeja de entrada', 'mensajes', 'correo interno', 'componer mensaje'],
            ruta: 'view/mensajeria/mensajeria.php',
            texto: 'En Mensajes, abre Componer mensaje, selecciona al destinatario y completa asunto y contenido. Pulsa Enviar Mensaje. Revisa las respuestas en Bandeja de entrada y tus envíos en Enviados.'
        },
        chat: {
            titulo: 'Usar el chat directo', claves: ['chat', 'conversar', 'responder mensaje', 'citar mensaje'],
            ruta: 'view/mensajeria/mensajeria.php',
            texto: 'En Mensajes → Chat directo, elige a la persona en Conversar con. Escribe y pulsa Enviar. Usa Responder en un mensaje para citarlo, o Cancelar para quitar la cita.'
        },
        informes: {
            titulo: 'Descargar informe PDF o Excel', claves: ['reporte', 'informes', 'excel', 'pdf', 'comparar meses', 'informe comparativo', 'exportar'],
            ruta: 'view/generar_informe/generar_informe.php',
            texto: 'Como administrador, abre Generar Informe. Elige dos períodos disponibles, revisa entradas y salidas por material en Vista previa, selecciona PDF o Excel y pulsa Comparar y descargar. El selector ofrece meses con actividad.'
        },
        movimientos: {
            titulo: 'Consultar historial de movimientos', claves: ['movimientos', 'trazabilidad', 'filtrar historial', 'movimientos en pdf'],
            ruta: 'view/historial_movimientos/historial.php',
            texto: 'Como administrador, abre Historial de Movimientos. Filtra por módulo, acción, usuario, fechas o descripción y pulsa Filtrar. En Generar informe en PDF elige día, semana o mes para descargar el historial del período.'
        },
        estadisticas: {
            titulo: 'Consultar estadísticas', claves: ['estadisticas', 'rendimiento', 'indicadores', 'produccion por modelo', 'consumo de materiales'],
            ruta: 'view/estadisticas/index.php',
            texto: 'El panel de estadísticas del administrador permite elegir Día, Semana o Mes. Muestra producción por modelo, tareas terminadas y pendientes, consumo de materias primas e indicadores de rendimiento.'
        },
        auditoria: {
            titulo: 'Descargar auditoría técnica', claves: ['auditoria de seguridad', 'auditoria de codigo', 'reporte tecnico'],
            ruta: 'view/generar_informe/generar_auditoria.php',
            texto: 'El administrador puede descargar el PDF de auditoría de seguridad y código. Para revisar acciones de usuarios por fecha, abre Historial de Movimientos.'
        },
        usuarios: {
            titulo: 'Gestionar usuarios', claves: ['usuarios', 'desactivar cuenta', 'activar usuario', 'usuarios conectados'],
            ruta: 'view/panel_admin/gestion_usuarios.php',
            texto: 'En el panel del administrador, abre Acciones rápidas → Gestión de usuarios. Revisa la lista y el estado de las cuentas. Para activar o desactivar un usuario, pulsa la acción disponible en su fila y confirma. La desactivación invalida su sesión.'
        },
        alta: {
            titulo: 'Registrar usuario', claves: ['crear cuenta', 'nuevo usuario', 'registrarme', 'dar de alta usuario'],
            ruta: 'view/registro/registro.php',
            texto: 'El administrador abre su menú de perfil y elige Registrar usuario. Completa los datos de identidad y contacto, selecciona el rol, escribe y confirma la contraseña y pulsa Registrar.'
        },
        perfil: {
            titulo: 'Editar mi perfil', claves: ['perfil', 'telefono', 'cambiar correo', 'cambiar foto', 'avatar', 'editar mis datos'],
            ruta: 'app/ir_panel.php',
            texto: 'Abre el menú de tu nombre en la parte superior. En Editar mis datos puedes cambiar nombre, apellido, correo y teléfono. Para actualizar el avatar, usa Cambiar foto de perfil y elige un JPG, PNG o WebP de hasta 1 MB.'
        },
        tema: {
            titulo: 'Cambiar el tema', claves: ['modo oscuro', 'tema claro', 'modo noche', 'apariencia', 'tema oscuro'],
            ruta: 'app/ir_panel.php',
            texto: 'Abre el menú de perfil y pulsa Activar tema oscuro o Activar tema claro. La preferencia queda guardada en este navegador y se aplica también al asistente y a los avisos.'
        },
        sesion: {
            titulo: 'Cerrar sesión o volver a entrar', claves: ['cerrar sesion', 'salir', 'sesion expirada', 'inactividad', 'otro dispositivo', 'cuenta desactivada'],
            texto: 'Pulsa tu nombre → Cerrar sesión y confirma. Si la sesión caduca tras 30 minutos de inactividad o se reemplaza al ingresar de nuevo, vuelve a identificarte. Si tu cuenta fue desactivada, solicita al administrador que revise su estado.'
        },
        problemas: {
            titulo: 'Resolver un error al guardar', claves: ['csrf', 'token invalido', 'error de conexion', 'no puedo guardar', 'sesion invalida', 'no responde'],
            texto: 'Ante un aviso de sesión o formulario vencido, recarga la vista e inicia sesión si se solicita. Si hubo un error de conexión al guardar, revisa primero el inventario, historial o listado correspondiente para comprobar si el cambio se registró antes de repetirlo.'
        }
    };

    const sinonimos = {
        contrasena: 'clave', password: 'clave', claves: 'clave', restablecer: 'recuperar',
        reportes: 'informe', reporte: 'informe', informes: 'informe',
        tareas: 'tarea', mensajes: 'mensaje', modelos: 'modelo', insumos: 'material',
        insumo: 'material', materiales: 'material', proveedores: 'proveedor',
        colchones: 'colchon', recetas: 'receta', productos: 'producto', usuarios: 'usuario',
        modificar: 'editar', cambiar: 'editar', ingresar: 'entrar',
        anadir: 'agregar', borrar: 'eliminar'
    };
    const vacias = new Set('a al de del el la las los en un una o y por para mi mis me se es que como puedo puede quiero necesito donde'.split(' '));
    function normalizar(texto) {
        return texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, ' ').trim().split(/\s+/)
            .filter(p => p && !vacias.has(p)).map(p => Object.hasOwn(sinonimos, p) ? sinonimos[p] : p).join(' ');
    }
    const indice = Object.entries(temas).map(([id, tema]) => ({
        id, frases: [tema.titulo, ...tema.claves].map(normalizar)
    }));
    const frecuentes = visitante ? ['acceso', 'clave', 'permisos']
        : ['inventario', admin ? 'informes' : operario ? 'receta' : 'stock', 'tareas', 'permisos'];

    function puntuacion(consulta, palabras, frases) {
        let mejor = 0;
        for (const frase of frases) {
            const tokens = frase.split(' ');
            const comunes = tokens.filter(p => palabras.has(p)).length;
            let puntos = comunes * 2;
            if (consulta === frase) puntos += 30;
            else if ((' ' + consulta + ' ').includes(' ' + frase + ' ')) puntos += tokens.length > 1 ? 15 : 5;
            mejor = Math.max(mejor, puntos);
        }
        return mejor;
    }

    async function responder(pregunta, idElegido) {
        let id = idElegido;
        if (!id) {
            const consulta = normalizar(pregunta);
            const palabras = new Set(consulta.split(' '));
            const ranking = indice.map(tema => ({
                id: tema.id, puntos: puntuacion(consulta, palabras, tema.frases)
            })).filter(t => t.puntos > 0).sort((a, b) => b.puntos - a.puntos);
            if (!ranking.length || ranking[0].puntos < 4 || ranking[0].puntos === ranking[1]?.puntos) {
                const opciones = [...new Set([...ranking.map(t => t.id), ...frecuentes])].slice(0, 3);
                return { texto: '¿Sobre cuál de estos temas necesitas ayuda?', opciones };
            }
            id = ranking[0].id;
        }
        const tema = temas[id];
        let texto = tema.texto;
        let enlace;
        if (tema.ruta) {
            const vista = tema.ruta.split('/').pop();
            if (tema.publico || (!visitante && (permitidos.has(vista) || vista === 'ir_panel.php'))) {
                enlace = { titulo: tema.titulo, url: new URL(tema.ruta, raiz).href };
            } else if (visitante) {
                texto += ' Inicia sesión para ver los módulos de tu rol.';
            } else {
                texto += ' Tu rol tiene restringido el acceso a esta pantalla.';
            }
        }
        return { texto, enlace };
    }

    // Plantilla estática. Los mensajes y las preguntas se insertan como nodos de texto.
    widget.innerHTML = '<section id="ayuda-ventana" aria-labelledby="ayuda-titulo" inert>' +
        '<div class="ayuda-cabecera"><div><span>ColSoftCO</span><h2 id="ayuda-titulo">Asistente de soporte</h2></div>' +
        '<button class="ayuda-cerrar" type="button" aria-label="Cerrar asistente">×</button></div>' +
        '<div class="ayuda-historial" role="log" aria-label="Conversación de soporte" aria-live="polite" tabindex="0"></div>' +
        '<form class="ayuda-form"><input aria-label="Tu pregunta" placeholder="Escribe tu pregunta…" maxlength="400" required autocomplete="off">' +
        '<button type="submit" aria-label="Enviar pregunta">➜</button></form></section>' +
        '<button class="ayuda-lanzador" type="button" aria-label="Abrir asistente de soporte" aria-controls="ayuda-ventana" aria-expanded="false">' +
        '<svg aria-hidden="true" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M4 13v-2a8 8 0 0 1 16 0v3M20 16v1a4 4 0 0 1-4 4h-3"/>' +
        '<rect x="2.5" y="11" width="4.5" height="7" rx="2"/><rect x="17" y="11" width="4.5" height="7" rx="2"/></svg></button>';

    const ventana = widget.querySelector('section');
    const lanzador = widget.querySelector('.ayuda-lanzador');
    const historial = widget.querySelector('.ayuda-historial');
    const entrada = widget.querySelector('input');
    const formulario = widget.querySelector('form');

    function abrir(abierto) {
        lanzador.setAttribute('aria-expanded', String(abierto));
        lanzador.setAttribute('aria-label', abierto ? 'Cerrar asistente de soporte' : 'Abrir asistente de soporte');
        ventana.inert = !abierto;
        widget.classList.toggle('abierto', abierto);
        (abierto ? entrada : lanzador).focus({ preventScroll: true });
    }

    function agregar(texto, propio = false) {
        const burbuja = document.createElement('div');
        burbuja.className = propio ? 'ayuda-pregunta' : 'ayuda-respuesta';
        burbuja.textContent = texto;
        historial.append(burbuja);
        return burbuja;
    }
    function mostrarOpciones(burbuja, ids) {
        const opciones = document.createElement('div');
        opciones.className = 'ayuda-opciones';
        for (const id of ids) {
            const boton = document.createElement('button');
            boton.type = 'button';
            boton.textContent = temas[id].titulo;
            boton.addEventListener('click', () => preguntar(temas[id].titulo, id));
            opciones.append(boton);
        }
        burbuja.append(opciones);
    }
    async function preguntar(pregunta, id) {
        agregar(pregunta, true);
        const respuesta = await responder(pregunta, id);
        const burbuja = agregar(respuesta.texto);
        if (respuesta.opciones) mostrarOpciones(burbuja, respuesta.opciones);
        if (respuesta.enlace) {
            const enlace = document.createElement('a');
            enlace.textContent = respuesta.enlace.titulo + ' →';
            enlace.href = respuesta.enlace.url;
            burbuja.append(enlace);
        }
        historial.scrollTop = historial.scrollHeight;
    }
    mostrarOpciones(agregar('Hola. Soy el asistente de ColSoftCO. ¿Qué necesitas hacer?'), frecuentes);
    lanzador.addEventListener('click', () => abrir(ventana.inert));
    widget.querySelector('.ayuda-cerrar').addEventListener('click', () => abrir(false));
    widget.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !ventana.inert) { e.preventDefault(); abrir(false); }
    });
    formulario.addEventListener('submit', e => {
        e.preventDefault();
        const pregunta = entrada.value.trim().slice(0, 400);
        if (!pregunta) return;
        entrada.value = '';
        preguntar(pregunta);
    });
})();
