/**
 * app.js
 * Utilidades generales y navegación de módulos.
 */


/* =========================================================
   CERRAR SESIÓN
========================================================= */

function cerrarSesion() {

    if (confirm("¿Desea cerrar la sesión?")) {

        window.location.href =
            "../../app/logout.php";

    }

}


/* =========================================================
   CARGAR MÓDULOS
========================================================= */

async function cargarModulo(ruta, boton = null) {

    const contenido =
        document.getElementById("contenido");


    if (!contenido) {
        return;
    }


    /* -----------------------------------------
       Estado de carga
    ----------------------------------------- */

    contenido.innerHTML = `
        <div class="modulo-cargando">
            <p>Cargando módulo...</p>
        </div>
    `;


    try {

        const respuesta =
            await fetch(ruta, {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });


        if (!respuesta.ok) {

            throw new Error(
                "No se pudo cargar el módulo."
            );

        }


        const html =
            await respuesta.text();


        contenido.innerHTML = html;

        /* =========================================
   CARGAR CSS DEL MÓDULO
========================================= */

const estilos = contenido.querySelectorAll('link[rel="stylesheet"]');

estilos.forEach(link => {

    const href = link.getAttribute("href");

    if (!href) {
        return;
    }

    const rutaCSS = new URL(
        href,
        new URL(ruta, window.location.href)
    ).href;

    const yaExiste = Array.from(
        document.querySelectorAll('link[rel="stylesheet"]')
    ).some(css => css.href === rutaCSS);

    if (!yaExiste) {

        const nuevoCSS = document.createElement("link");

        nuevoCSS.rel = "stylesheet";
        nuevoCSS.href = rutaCSS;

        document.head.appendChild(nuevoCSS);
    }

    link.remove();

});


        /* -----------------------------------------
           Botón activo
        ----------------------------------------- */

        document
            .querySelectorAll(".nav-item")
            .forEach(item => {

                item.classList.remove("active");

            });


        if (boton) {

            boton.classList.add("active");

        }


        /* -----------------------------------------
           Ejecutar scripts del módulo
        ----------------------------------------- */

        const scripts =
            contenido.querySelectorAll("script");


        scripts.forEach(script => {

            const nuevoScript =
                document.createElement("script");


            if (script.src) {

                nuevoScript.src =
                    script.src;

            } else {

                nuevoScript.textContent =
                    script.textContent;

            }


            document.body.appendChild(
                nuevoScript
            );


            script.remove();

        });


        /* -----------------------------------------
           Volver arriba del contenido
        ----------------------------------------- */

        contenido.scrollTop = 0;

    }


    catch (error) {

        console.error(error);


        contenido.innerHTML = `

            <div class="modulo-error">

                <h2>
                    No se pudo cargar el módulo
                </h2>

                <p>
                    Verifique que el archivo
                    exista y vuelva a intentarlo.
                </p>

            </div>

        `;

    }

}


/* =========================================================
   CONFIGURACIÓN GENERAL
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    () => {


        /* =========================================
           BOTÓN CERRAR SESIÓN
        ========================================== */

        const header =
            document.querySelector(".topbar");


        if (
            header &&
            !document.getElementById("btnLogout")
        ) {

            const logoutBtn =
                document.createElement("button");


            logoutBtn.id =
                "btnLogout";


            logoutBtn.textContent =
                "Cerrar sesión";


            logoutBtn.className =
                "btn-logout";


            logoutBtn.addEventListener(
                "click",
                cerrarSesion
            );


            header
                .querySelector(".header-actions")
                ?.appendChild(logoutBtn);

        }



        /* =========================================
           NAVEGACIÓN DE MÓDULOS
        ========================================== */

        const botones =
            document.querySelectorAll(
                ".nav-item[data-module]"
            );


        botones.forEach(boton => {

            boton.addEventListener(
                "click",
                () => {

                    const ruta =
                        boton.dataset.module;

                        console.log("RUTA DEL BOTÓN:", ruta);


                    if (!ruta) {
                        return;
                    }


                    cargarModulo(
                        ruta,
                        boton
                    );


                    /* Cerrar menú móvil */

                    const sidebar =
                        document.getElementById(
                            "sidebar"
                        );


                    if (sidebar) {

                        sidebar.classList.remove(
                            "mobile-visible"
                        );

                    }


                    document.body.classList.remove(
                        "menu-open"
                    );

                }
            );

        });

        /* =========================================
                        BOTÓN INICIO
        ========================================= */

        const btnInicio = document.getElementById("btnInicio");


        if (btnInicio) {

            btnInicio.addEventListener(
                "click",
                () => {

                    window.location.reload();

                }
            );

        }



        /* =========================================
                            TOAST
        ========================================== */

        window.showToast =
            function (
                message,
                type = "success"
            ) {

                let toast =
                    document.getElementById(
                        "app-toast"
                    );


                if (!toast) {

                    toast =
                        document.createElement(
                            "div"
                        );


                    toast.id =
                        "app-toast";


                    toast.style.position =
                        "fixed";

                    toast.style.bottom =
                        "30px";

                    toast.style.right =
                        "30px";

                    toast.style.padding =
                        "14px 24px";

                    toast.style.borderRadius =
                        "8px";

                    toast.style.color =
                        "white";

                    toast.style.fontSize =
                        "14px";

                    toast.style.fontWeight =
                        "500";

                    toast.style.boxShadow =
                        "0 6px 24px rgba(0,0,0,0.2)";

                    toast.style.zIndex =
                        "9999";

                    toast.style.transition =
                        "opacity 0.3s, transform 0.3s";

                    toast.style.opacity =
                        "0";

                    toast.style.transform =
                        "translateY(20px)";


                    document.body.appendChild(
                        toast
                    );

                }


                toast.style.backgroundColor =
                    type === "error"
                        ? "#d9534f"
                        : "#0A1F44";


                toast.style.borderLeft =
                    `4px solid ${type === "error"
                        ? "#c9302c"
                        : "#D4AF37"
                    }`;


                toast.textContent =
                    message;


                setTimeout(
                    () => {

                        toast.style.opacity =
                            "1";

                        toast.style.transform =
                            "translateY(0)";

                    },
                    10
                );


                setTimeout(
                    () => {

                        toast.style.opacity =
                            "0";

                        toast.style.transform =
                            "translateY(20px)";

                    },
                    3000
                );

            };

    }
);