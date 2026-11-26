# Agenda de contactos con PHP y PDO

Esta aplicación permite registrar, listar, buscar, consultar, editar y eliminar contactos con fotografía desde un navegador. Está pensada para la Unidad 4 de Programación Web: usa PHP 7.4+, PDO con MySQL/MariaDB, HTML5 y CSS externo, con validación y protección básica tanto en el cliente como en el servidor.

## Quick path

1. Importe `schema.sql` en su base de datos MySQL/MariaDB.
2. Edite las constantes de conexión en `config/db.php` con las credenciales de su hosting.
3. Suba todo el proyecto al servidor, asegúrese de que `archivos/` tenga permisos de escritura y abra la URL pública de despliegue.
4. Compruebe el flujo completo: crear un contacto, buscarlo, ver su detalle, editarlo y eliminarlo.

## Details

| Tema | Decisión |
|------|----------|
| Equipo | Sebastian Emiliano Anzueto Escobar<br>Cesar Francisco Salazar Sanchez<br>Manuel de Jesus Flores de la Cruz |
| Materia | Programación Web — Unidad 4 |
| Tecnologías | PHP 7.4+, PDO, MySQL/MariaDB, HTML5, CSS |
| Funcionalidades | Alta, listado, búsqueda, detalle, edición, baja, carga de foto, mensajes flash y protección CSRF |
| Repositorio público | `[pendiente de completar]` |
| URL desplegada | `[pendiente de completar]` |

### Estructura del proyecto

```text
AGENDA_PW/
├── index.php              Listado y búsqueda
├── crear.php              Formulario de alta
├── guardar.php            Procesador de alta
├── editar.php             Formulario de edición
├── actualizar.php         Procesador de edición
├── ver.php                Detalle de un contacto
├── eliminar.php           Confirmación y baja
├── config/
│   └── db.php             Conexión PDO
├── includes/
│   ├── functions.php      Helpers compartidos
│   ├── header.php         Cabecera y mensajes flash
│   ├── menu.php           Navegación
│   └── footer.php         Cierre del documento
├── css/
│   └── styles.css         Estilos externos
├── archivos/              Fotos subidas (no versionadas)
├── schema.sql             Definición de la tabla contactos
└── README.md
```

### Seguridad

- **PDO:** conexión única con `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, `EMULATE_PREPARES => false` y `charset=utf8mb4`.
- **Prepared statements:** todas las consultas usan `prepare()` y `execute()` con valores enlazados; los identificadores se convierten a `int`.
- **CSRF:** token de sesión único, campo oculto `_csrf` en los tres formularios POST y validación con `hash_equals()` antes de cualquier cambio.
- **Salida:** `h()` aplica `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` a todo valor dinámico, incluidos atributos y rutas de fotos.
- **Validación:** campos obligatorios, formato de teléfono y correo, longitudes y foto (tipo MIME, tamaño máximo de 2 MB y nombre generado) se validan en el servidor.
- **Errores:** los detalles de PDO se registran con `error_log()` y nunca se muestran al usuario.

### Decisión sobre los campos

`nombre` es obligatorio en HTML5 y en el servidor; `apellido` es opcional y se almacena como `NULL` cuando se deja vacío. `foto` es obligatoria al crear y opcional al editar: si no se sube una nueva, se conserva el archivo existente y la sentencia `UPDATE` no incluye la columna `foto`.

## Checklist

- [ ] **PDO connection** — capture `config/db.php` con las opciones de PDO y credenciales placeholder.
- [ ] **prepare/execute** — capture una consulta preparada con `execute()` y valores enlazados.
- [ ] **CREATE** — capture `crear.php`, el `INSERT` de `guardar.php` y el mensaje de éxito.
- [ ] **READ list** — capture `index.php` con el listado ordenado y sus acciones.
- [ ] **READ detail** — capture `ver.php` mostrando todos los campos y la foto.
- [ ] **UPDATE** — capture `editar.php` y el `UPDATE` condicional de `actualizar.php`.
- [ ] **DELETE** — capture la confirmación de `eliminar.php`, su `DELETE` y la eliminación del archivo.
- [ ] **HTML5+server validation** — capture `required`, `type="email"`, `accept` y las funciones `validate_contact()` y `upload_photo()`.
- [ ] **htmlspecialchars** — capture `h()` en `includes/functions.php` y su uso en las vistas.
- [ ] **search** — capture `index.php?q=...` con la consulta `LIKE` preparada.

## Siguiente paso

Complete los marcadores de repositorio público y URL desplegada, rellene `config/db.php` en el servidor y adjunte capturas de cada elemento del checklist a la entrega.
