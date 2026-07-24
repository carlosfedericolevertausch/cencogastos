<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es-CL">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cencogastos - Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Hola, <?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?> 👋</h1>
            <div>
                <button id="btn-admin" onclick="abrirModal('modal-admin')" class="btn btn-primary btn-sm" style="display: none; background-color: var(--text-main); margin-right: 10px;">Panel Admin</button>
                <button onclick="logout()" class="btn btn-primary btn-sm" style="width: auto; background-color: var(--text-muted);">Salir</button>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="panel stat-card">
                <h3>Tus Lucas (Sueldo)</h3>
                <div class="amount" id="val-sueldo">$0</div>
                <button onclick="abrirModal('modal-sueldo')" class="btn btn-primary btn-sm">Modificar</button>
            </div>
            <div class="panel stat-card expenses">
                <h3>Gastado este mes</h3>
                <div class="amount" id="val-gastos">$0</div>
                <button onclick="abrirModal('modal-gasto')" class="btn btn-primary btn-sm">Anotar Gasto</button>
            </div>
            <div class="panel stat-card savings">
                <h3>En el chanchito</h3>
                <div class="amount" id="val-ahorros">$0</div>
                <button onclick="abrirModal('modal-meta')" class="btn btn-primary btn-sm">Nueva Meta</button>
            </div>
            <div class="panel stat-card balance">
                <h3>Plata sobrante</h3>
                <div class="amount" id="val-saldo">$0</div>
            </div>
            <div class="panel stat-card">
                <h3>Tipos de Gasto</h3>
                <div style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem; margin-top: 0.5rem;">Administra tus categorías.</div>
                <div style="display: flex; gap: 0.5rem; justify-content: flex-start;">
                    <button onclick="abrirModal('modal-tipo')" class="btn btn-primary btn-sm" style="flex: 1;">Crear</button>
                    <button onclick="abrirModal('modal-gestionar-tipos')" class="btn btn-sm" style="flex: 1; background-color: var(--bg-light); border: 1px solid var(--border); color: var(--text-main);">Ver Todos</button>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="panel">
                <h3 style="margin-bottom: 1rem; font-weight: 600;">Últimas compras</h3>
                <table class="data-table" id="tabla-gastos">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Detalle</th>
                            <th>Tipo</th>
                            <th>Plata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Llenado por JS -->
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <h3 style="margin-bottom: 1rem; font-weight: 600;">Tus Metas (Chanchitos)</h3>
                <div id="contenedor-metas">
                    <!-- Llenado por JS -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Modal Sueldo -->
    <div class="modal-overlay" id="modal-sueldo">
        <div class="modal-content panel">
            <div class="modal-header">
                <h2>¿Cuántas lucas ganas?</h2>
                <button class="close-modal" onclick="cerrarModal('modal-sueldo')">&times;</button>
            </div>
            <form id="form-sueldo">
                <div class="form-group">
                    <label>Tu Sueldo Líquido</label>
                    <input type="number" id="input-sueldo" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
        </div>
    </div>

    <!-- Modal Gasto -->
    <div class="modal-overlay" id="modal-gasto">
        <div class="modal-content panel">
            <div class="modal-header">
                <h2>Anotar Gasto</h2>
                <button class="close-modal" onclick="cerrarModal('modal-gasto')">&times;</button>
            </div>
            <form id="form-gasto">
                <div class="form-group">
                    <label>¿En qué tipo de cosa gastaste?</label>
                    <select id="input-tipo-gasto" class="form-control" required></select>
                </div>
                <div class="form-group">
                    <label>¿Cuánta plata fue?</label>
                    <input type="number" id="input-plata-gastada" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Detalle corto</label>
                    <input type="text" id="input-detalle" class="form-control" placeholder="Ej: Lomito palta" required>
                </div>
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" id="input-fecha-gasto" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Anotar</button>
            </form>
        </div>
    </div>

    <!-- Modal Tipo -->
    <div class="modal-overlay" id="modal-tipo">
        <div class="modal-content panel">
            <div class="modal-header">
                <h2>Crear Tipo de Gasto</h2>
                <button class="close-modal" onclick="cerrarModal('modal-tipo')">&times;</button>
            </div>
            <form id="form-tipo">
                <div class="form-group">
                    <label>Nombre (Ej: Carrete, Transporte)</label>
                    <input type="text" id="input-nombre-tipo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Color representativo</label>
                    <input type="color" id="input-color-tipo" class="form-control" value="#007aff" style="height: 50px; padding: 0.2rem;">
                </div>
                <button type="submit" class="btn btn-primary">Crear</button>
            </form>
        </div>
    </div>

    <!-- Modal Meta -->
    <div class="modal-overlay" id="modal-meta">
        <div class="modal-content panel">
            <div class="modal-header">
                <h2>Armar un Chanchito</h2>
                <button class="close-modal" onclick="cerrarModal('modal-meta')">&times;</button>
            </div>
            <form id="form-meta">
                <div class="form-group">
                    <label>¿Para qué estai juntando?</label>
                    <input type="text" id="input-nombre-meta" class="form-control" placeholder="Ej: PlayStation 5" required>
                </div>
                <div class="form-group">
                    <label>¿Cuántas lucas necesitas?</label>
                    <input type="number" id="input-plata-objetivo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Fecha límite (opcional)</label>
                    <input type="date" id="input-fecha-limite" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Crear Meta</button>
            </form>
        </div>
    </div>

    <!-- Modal Agregar Plata Meta -->
    <div class="modal-overlay" id="modal-poner-plata">
        <div class="modal-content panel">
            <div class="modal-header">
                <h2>Ponerle lucas al chanchito</h2>
                <button class="close-modal" onclick="cerrarModal('modal-poner-plata')">&times;</button>
            </div>
            <form id="form-poner-plata">
                <input type="hidden" id="input-id-meta">
                <div class="form-group">
                    <label>Plata a sumar</label>
                    <input type="number" id="input-plata-sumar" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Añadir</button>
            </form>
        </div>
    </div>

    <!-- Modal Admin -->
    <div class="modal-overlay" id="modal-admin">
        <div class="modal-content panel" style="max-width: 600px;">
            <div class="modal-header">
                <h2>Gestión de Usuarios</h2>
                <button class="close-modal" onclick="cerrarModal('modal-admin')">&times;</button>
            </div>
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="data-table" id="tabla-usuarios">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Llenado por JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Gestionar Tipos -->
    <div class="modal-overlay" id="modal-gestionar-tipos">
        <div class="modal-content panel" style="max-width: 500px;">
            <div class="modal-header">
                <h2>Tus Tipos de Gasto</h2>
                <button class="close-modal" onclick="cerrarModal('modal-gestionar-tipos')">&times;</button>
            </div>
            <div style="max-height: 350px; overflow-y: auto;">
                <table class="data-table" id="tabla-tipos">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Llenado por JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Editar Tipo -->
    <div class="modal-overlay" id="modal-editar-tipo">
        <div class="modal-content panel">
            <div class="modal-header">
                <h2>Editar Tipo de Gasto</h2>
                <button class="close-modal" onclick="cerrarModal('modal-editar-tipo')">&times;</button>
            </div>
            <form id="form-editar-tipo">
                <input type="hidden" id="edit-id-tipo">
                <div class="form-group">
                    <label>Nombre</label>
                    <input type="text" id="edit-nombre-tipo" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Color representativo</label>
                    <input type="color" id="edit-color-tipo" class="form-control" style="height: 50px; padding: 0.2rem;">
                </div>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script src="app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            cargarDashboard();
        });
    </script>
</body>
</html>
