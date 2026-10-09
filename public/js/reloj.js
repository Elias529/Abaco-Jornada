(function () {
    const zona = "Europe/Madrid";
    const reloj = document.querySelector("[data-clock]");
    const llevas = document.querySelector("[data-llevas]");
    let ahora = reloj ? Number(reloj.dataset.serverMs) : Date.now();

    const formatoHora = new Intl.DateTimeFormat("en-GB", {
        timeZone: zona,
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hourCycle: "h23",
    });

    function textoHora(fecha) {
        const partes = {};
        formatoHora.formatToParts(fecha).forEach(function (parte) {
            partes[parte.type] = parte.value;
        });
        return partes.hour + ":" + partes.minute + ":" + partes.second;
    }

    function sincronizarHora(caja) {
        const horas = caja.querySelector("[data-parte=h]");
        const minutos = caja.querySelector("[data-parte=m]");
        const oculto = caja.querySelector("[data-hora-valor]");
        const minimo = caja.dataset.min || "";
        const maximo = caja.dataset.max || "";

        Array.from(horas.options).forEach(function (opcion) {
            if (!opcion.value) {
                return;
            }
            const pronto = minimo && opcion.value < minimo.slice(0, 2);
            const tarde = maximo && opcion.value > maximo.slice(0, 2);
            opcion.disabled = pronto || tarde;
        });

        if (horas.selectedOptions[0] && horas.selectedOptions[0].disabled) {
            horas.value = "";
        }

        Array.from(minutos.options).forEach(function (opcion) {
            if (!opcion.value) {
                return;
            }
            let fuera = false;
            if (horas.value && minimo && horas.value === minimo.slice(0, 2) && opcion.value < minimo.slice(3, 5)) {
                fuera = true;
            }
            if (horas.value && maximo && horas.value === maximo.slice(0, 2) && opcion.value > maximo.slice(3, 5)) {
                fuera = true;
            }
            opcion.disabled = fuera;
        });

        if (minutos.selectedOptions[0] && minutos.selectedOptions[0].disabled) {
            minutos.value = "";
        }

        oculto.value = horas.value && minutos.value ? horas.value + ":" + minutos.value : "";
    }

    document.querySelectorAll("[data-hora]").forEach(function (caja) {
        sincronizarHora(caja);
        caja.addEventListener("change", function () {
            sincronizarHora(caja);
        });
    });

    function textoMinutos(total) {
        const horas = Math.floor(total / 60);
        const minutos = total % 60;
        if (horas === 0) {
            return "Hoy llevas " + minutos + " min.";
        }
        return "Hoy llevas " + horas + " h " + minutos + " min.";
    }

    function tick() {
        ahora += 1000;
        if (reloj) {
            reloj.textContent = textoHora(new Date(ahora));
        }
        if (llevas && llevas.dataset.abiertoMs) {
            const cerrados = Number(llevas.dataset.cerrados || 0);
            const extra = Math.max(0, Math.floor((ahora - Number(llevas.dataset.abiertoMs)) / 60000));
            const total = cerrados + extra;
            if (llevas.dataset.corto === "1") {
                const minutos = total % 60;
                llevas.textContent = Math.floor(total / 60) + " h " + (minutos < 10 ? "0" : "") + minutos;
            } else {
                llevas.textContent = textoMinutos(total);
            }
        }
    }

    if (reloj || (llevas && llevas.dataset.abiertoMs)) {
        window.setInterval(tick, 1000);
    }

    const titulo = document.title;
    function tituloPendiente() {
        const escondida = document.hidden && document.body.dataset.pendiente === "1";
        document.title = escondida ? "Pendiente · Jornada" : titulo;
    }
    document.addEventListener("visibilitychange", tituloPendiente);

    document.querySelectorAll("form").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            form.querySelectorAll("[data-hora]").forEach(sincronizarHora);
            if (!navigator.onLine) {
                event.preventDefault();
                const aviso = document.querySelector("[data-sin-conexion]");
                if (aviso) {
                    aviso.hidden = false;
                }
                return;
            }
            if (form.dataset.enviando === "1") {
                event.preventDefault();
                return;
            }
            form.dataset.enviando = "1";
        });
    });

    try {
        localStorage.removeItem("jornada-pendientes");
    } catch (error) {
        // Si el navegador no deja usar el almacén, no hay nada que reenviar.
    }

    window.addEventListener("online", function () {
        const aviso = document.querySelector("[data-sin-conexion]");
        const vuelta = document.querySelector("[data-conexion-vuelta]");
        if (aviso && !aviso.hidden && vuelta) {
            vuelta.hidden = false;
        }
    });
})();
