// Utilidad para Notificaciones
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    background: '#ffffff',
    color: '#1d1d1f'
});

function notificar(tipo, mensaje) {
    Toast.fire({
        icon: tipo,
        title: mensaje
    });
}

// Logica de Auth
function toggleAuth() {
    const login = document.getElementById('login-form-container');
    const register = document.getElementById('register-form-container');
    if (login && register) {
        if (login.style.display === 'none') {
            login.style.display = 'block';
            register.style.display = 'none';
        } else {
            login.style.display = 'none';
            register.style.display = 'block';
        }
    }
}

const formLogin = document.getElementById('login-form');
if (formLogin) {
    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault();
        const nombre_usuario = document.getElementById('login-usuario').value;
        const clave_secreta = document.getElementById('login-clave').value;
        
        try {
            const res = await fetch('api.php?accion=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre_usuario, clave_secreta })
            });
            const data = await res.json();
            if (data.estado === 'exito') {
                window.location.href = 'dashboard.php';
            } else {
                Swal.fire({ icon: 'error', title: 'Pucha', text: data.mensaje });
            }
        } catch (error) {
            console.error(error);
        }
    });
}

const formRegistro = document.getElementById('register-form');
if (formRegistro) {
    formRegistro.addEventListener('submit', async (e) => {
        e.preventDefault();
        const nombre_usuario = document.getElementById('reg-usuario').value;
        const clave_secreta = document.getElementById('reg-clave').value;
        
        try {
            const res = await fetch('api.php?accion=registro', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nombre_usuario, clave_secreta })
            });
            const data = await res.json();
            if (data.estado === 'exito') {
                Swal.fire({ icon: 'success', title: '¡Buena!', text: data.mensaje });
                toggleAuth();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.mensaje });
            }
        } catch (error) {
            console.error(error);
        }
    });
}

function logout() {
    fetch('api.php?accion=logout')
        .then(() => window.location.href = 'index.php');
}

// Logica de Modales
function abrirModal(id) {
    document.getElementById(id).classList.add('active');
    
    // Si abrimos el panel de admin, cargar los usuarios
    if(id === 'modal-admin') {
        cargarUsuariosAdmin();
    }
    // Si abrimos la gestión de tipos, cargar tabla
    if(id === 'modal-gestionar-tipos') {
        cargarTablaTipos();
    }
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Formato lucas chilena
function formatoPlata(valor) {
    return '$' + parseFloat(valor).toLocaleString('es-CL');
}

// Dashboard
async function cargarDashboard() {
    try {
        const res = await fetch('api.php?accion=obtener_dashboard');
        const json = await res.json();
        
        if (json.estado === 'exito') {
            document.getElementById('val-sueldo').innerText = formatoPlata(json.sueldo);
            document.getElementById('val-gastos').innerText = formatoPlata(json.gastos_mes);
            document.getElementById('val-ahorros').innerText = formatoPlata(json.plata_ahorrada);
            document.getElementById('val-saldo').innerText = formatoPlata(json.saldo);
            
            const saldoEl = document.getElementById('val-saldo');
            if (parseFloat(json.saldo) < 0) {
                saldoEl.style.color = 'var(--danger)';
            } else {
                saldoEl.style.color = 'var(--success)';
            }

            // Lógica para Admin
            if(json.es_admin == 1) {
                document.getElementById('btn-admin').style.display = 'inline-block';
            }
        }
        
        await cargarTiposGasto();
        await cargarGastos();
        await cargarMetas();
    } catch (err) {
        console.error(err);
    }
}

async function cargarTiposGasto() {
    const res = await fetch('api.php?accion=obtener_tipos');
    const json = await res.json();
    const select = document.getElementById('input-tipo-gasto');
    if (select) {
        select.innerHTML = '';
        if (json.data && json.data.length > 0) {
            json.data.forEach(tipo => {
                select.innerHTML += `<option value="${tipo.id_tipo}">${tipo.nombre_tipo}</option>`;
            });
        } else {
            select.innerHTML = `<option value="">Crea un tipo primero po</option>`;
        }
    }
}

async function cargarTablaTipos() {
    const res = await fetch('api.php?accion=obtener_tipos');
    const json = await res.json();
    const tbody = document.querySelector('#tabla-tipos tbody');
    if (tbody) {
        tbody.innerHTML = '';
        if (json.data && json.data.length > 0) {
            json.data.forEach(tipo => {
                tbody.innerHTML += `
                    <tr>
                        <td>
                            <span class="badge" style="background-color: ${tipo.color_tipo};">${tipo.nombre_tipo}</span>
                        </td>
                        <td style="display: flex; gap: 0.5rem;">
                            <button onclick="abrirEditarTipo(${tipo.id_tipo}, '${tipo.nombre_tipo}', '${tipo.color_tipo}')" class="btn btn-sm" style="background-color: var(--primary); color: white; padding: 0.2rem 0.5rem;">Editar</button>
                            <button onclick="eliminarTipo(${tipo.id_tipo})" class="btn btn-sm" style="background-color: var(--danger); color: white; padding: 0.2rem 0.5rem;">Borrar</button>
                        </td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="2" style="text-align: center; color: var(--text-muted);">No hay tipos de gasto creados.</td></tr>`;
        }
    }
}

function abrirEditarTipo(id, nombre, color) {
    cerrarModal('modal-gestionar-tipos');
    document.getElementById('edit-id-tipo').value = id;
    document.getElementById('edit-nombre-tipo').value = nombre;
    document.getElementById('edit-color-tipo').value = color;
    abrirModal('modal-editar-tipo');
}

async function eliminarTipo(id) {
    const result = await Swal.fire({
        title: '¿Eliminar?',
        text: "Ojo que se van a borrar también todos los gastos asociados a este tipo.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--danger)',
        cancelButtonColor: 'var(--text-muted)',
        confirmButtonText: 'Sí, borrar nomás'
    });

    if (result.isConfirmed) {
        const res = await fetch('api.php?accion=eliminar_tipo', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_tipo: id })
        });
        const data = await res.json();
        
        if(data.estado === 'exito') {
            notificar('success', data.mensaje);
            cargarTablaTipos();
            cargarTiposGasto();
            cargarDashboard();
        } else {
            Swal.fire('Error', data.mensaje, 'error');
        }
    }
}

async function cargarGastos() {
    const res = await fetch('api.php?accion=obtener_gastos');
    const json = await res.json();
    const tbody = document.querySelector('#tabla-gastos tbody');
    if (tbody) {
        tbody.innerHTML = '';
        if (json.data && json.data.length > 0) {
            json.data.forEach(gasto => {
                const [yyyy, mm, dd] = gasto.fecha_gasto.split('-');
                const fecha = `${dd}/${mm}/${yyyy}`;

                tbody.innerHTML += `
                    <tr>
                        <td style="color: var(--text-muted);">${fecha}</td>
                        <td style="font-weight: 500;">${gasto.detalle}</td>
                        <td><span class="badge" style="background-color: ${gasto.color_tipo};">${gasto.nombre_tipo}</span></td>
                        <td style="font-weight: 600;">${formatoPlata(gasto.plata_gastada)}</td>
                    </tr>
                `;
            });
        } else {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: var(--text-muted);">No has gastado nada (todavía)</td></tr>`;
        }
    }
}

async function cargarMetas() {
    const res = await fetch('api.php?accion=obtener_metas');
    const json = await res.json();
    const container = document.getElementById('contenedor-metas');
    if (container) {
        container.innerHTML = '';
        if (json.data && json.data.length > 0) {
            json.data.forEach(meta => {
                const perc = Math.min((meta.plata_juntada / meta.plata_objetivo) * 100, 100).toFixed(1);
                container.innerHTML += `
                    <div style="margin-bottom: 1.5rem; background: #fff; padding: 1.2rem; border: 1px solid var(--border); border-radius: var(--radius); box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                            <strong style="font-size: 1.05rem;">${meta.nombre_meta}</strong>
                            <span style="font-weight: 500;">${formatoPlata(meta.plata_juntada)} / ${formatoPlata(meta.plata_objetivo)}</span>
                        </div>
                        <div style="background: var(--bg-light); height: 10px; border-radius: 5px; overflow: hidden; margin-bottom: 1rem;">
                            <div style="background: var(--primary); height: 100%; width: ${perc}%; border-radius: 5px;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; color: var(--text-muted);">
                            <span>Límite: ${meta.fecha_limite ? meta.fecha_limite : 'Sin apuro'}</span>
                            <button onclick="abrirModalPonerPlata(${meta.id_meta})" class="btn btn-sm" style="background-color: var(--bg-light); border: 1px solid var(--border); color: var(--text-main);">+ Agregar Lucas</button>
                        </div>
                    </div>
                `;
            });
        } else {
            container.innerHTML = '<p style="text-align: center; color: var(--text-muted); margin-top: 2rem;">No tienes chanchitos armados</p>';
        }
    }
}

function abrirModalPonerPlata(id_meta) {
    document.getElementById('input-id-meta').value = id_meta;
    document.getElementById('input-plata-sumar').value = '';
    abrirModal('modal-poner-plata');
}

// LOGICA ADMIN
async function cargarUsuariosAdmin() {
    const res = await fetch('api.php?accion=obtener_usuarios');
    const json = await res.json();
    const tbody = document.querySelector('#tabla-usuarios tbody');
    if (tbody) {
        tbody.innerHTML = '';
        if (json.data && json.data.length > 0) {
            json.data.forEach(user => {
                const rol = user.es_admin == 1 ? '<span style="color:var(--primary); font-weight:bold;">Admin</span>' : 'Usuario';
                
                // Botón de eliminar, oculto si es el mismo usuario (no se puede auto-borrar, pero igual el backend lo bloquea)
                let btnEliminar = `<button onclick="eliminarUsuario(${user.id_usuario})" class="btn btn-sm" style="background-color: var(--danger); color: white; padding: 0.2rem 0.5rem;">Echar</button>`;
                
                tbody.innerHTML += `
                    <tr>
                        <td>${user.id_usuario}</td>
                        <td style="font-weight: 500;">${user.nombre_usuario}</td>
                        <td>${rol}</td>
                        <td>${btnEliminar}</td>
                    </tr>
                `;
            });
        }
    }
}

async function eliminarUsuario(id) {
    const result = await Swal.fire({
        title: '¿Estai seguro?',
        text: "Vas a borrarle toda la cuenta a este wn.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--danger)',
        cancelButtonColor: 'var(--text-muted)',
        confirmButtonText: 'Sí, pa fuera'
    });

    if (result.isConfirmed) {
        const res = await fetch('api.php?accion=eliminar_usuario', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_usuario: id })
        });
        const data = await res.json();
        
        if(data.estado === 'exito') {
            notificar('success', data.mensaje);
            cargarUsuariosAdmin(); // Recargar tabla
        } else {
            Swal.fire('Error', data.mensaje, 'error');
        }
    }
}


// Envío de Formularios
document.getElementById('form-sueldo')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const sueldo = document.getElementById('input-sueldo').value;
    const res = await fetch('api.php?accion=actualizar_sueldo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sueldo })
    });
    const data = await res.json();
    if(data.estado === 'exito') {
        notificar('success', data.mensaje);
        cerrarModal('modal-sueldo');
        cargarDashboard();
    }
});

document.getElementById('form-tipo')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const nombre_tipo = document.getElementById('input-nombre-tipo').value;
    const color_tipo = document.getElementById('input-color-tipo').value;
    const res = await fetch('api.php?accion=crear_tipo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre_tipo, color_tipo })
    });
    const data = await res.json();
    if(data.estado === 'exito') {
        notificar('success', data.mensaje);
        cerrarModal('modal-tipo');
        document.getElementById('form-tipo').reset();
        cargarTiposGasto();
    }
});

document.getElementById('form-editar-tipo')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id_tipo = document.getElementById('edit-id-tipo').value;
    const nombre_tipo = document.getElementById('edit-nombre-tipo').value;
    const color_tipo = document.getElementById('edit-color-tipo').value;
    
    const res = await fetch('api.php?accion=editar_tipo', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_tipo, nombre_tipo, color_tipo })
    });
    const data = await res.json();
    if(data.estado === 'exito') {
        notificar('success', data.mensaje);
        cerrarModal('modal-editar-tipo');
        abrirModal('modal-gestionar-tipos');
        cargarTiposGasto();
        cargarDashboard();
    }
});

document.getElementById('form-gasto')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id_tipo = document.getElementById('input-tipo-gasto').value;
    if (!id_tipo) {
        notificar('error', 'Crea un tipo de gasto primero');
        return;
    }
    const plata_gastada = document.getElementById('input-plata-gastada').value;
    const detalle = document.getElementById('input-detalle').value;
    const fecha_gasto = document.getElementById('input-fecha-gasto').value;
    
    const res = await fetch('api.php?accion=crear_gasto', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_tipo, plata_gastada, detalle, fecha_gasto })
    });
    const data = await res.json();
    if(data.estado === 'exito') {
        notificar('success', data.mensaje);
        cerrarModal('modal-gasto');
        document.getElementById('form-gasto').reset();
        cargarDashboard();
    }
});

document.getElementById('form-meta')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const nombre_meta = document.getElementById('input-nombre-meta').value;
    const plata_objetivo = document.getElementById('input-plata-objetivo').value;
    const fecha_limite = document.getElementById('input-fecha-limite').value;
    
    const res = await fetch('api.php?accion=crear_meta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre_meta, plata_objetivo, fecha_limite })
    });
    const data = await res.json();
    if(data.estado === 'exito') {
        notificar('success', data.mensaje);
        cerrarModal('modal-meta');
        document.getElementById('form-meta').reset();
        cargarDashboard();
    }
});

document.getElementById('form-poner-plata')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id_meta = document.getElementById('input-id-meta').value;
    const plata = document.getElementById('input-plata-sumar').value;
    
    const res = await fetch('api.php?accion=poner_plata_meta', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_meta, plata })
    });
    const data = await res.json();
    if(data.estado === 'exito') {
        notificar('success', data.mensaje);
        cerrarModal('modal-poner-plata');
        cargarDashboard();
    }
});
