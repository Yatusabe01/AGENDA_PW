# Agenda de contactos con PHP y PDO

Esta aplicación permite registrar, listar, buscar, consultar, editar y eliminar contactos con fotografía desde un navegador. Está pensada para la Unidad 4 de Programación Web: usa PHP 7.4+, PDO con MySQL/MariaDB, HTML5 y CSS externo, con validación y protección básica tanto en el cliente como en el servidor.

## Quick path

1. Importe `schema.sql` en su base de datos MySQL/MariaDB. Si la tabla ya existía, agregue las restricciones de unicidad indicadas abajo.
2. Edite las constantes de conexión en `config/db.php` con las credenciales de su hosting. El archivo versionado usa placeholders; los valores reales solo deben configurarse en el servidor.
3. Suba todo el proyecto al servidor, asegúrese de que `archivos/` tenga permisos de escritura y abra la URL pública de despliegue.
4. Compruebe el flujo completo: crear un contacto, buscarlo, ver su detalle, editarlo y eliminarlo.

Si la base de datos ya estaba creada antes de las validaciones de duplicados, ejecute:

```sql
ALTER TABLE contactos
  ADD UNIQUE KEY unique_telefono (telefono),
  ADD UNIQUE KEY unique_email (email);
```

## Details

| Tema | Decisión |
|------|----------|
| Equipo | Sebastian Emiliano Anzueto Escobar<br>Cesar Francisco Salazar Sanchez<br>Manuel de Jesus Flores de la Cruz |
| Materia | Programación Web — Unidad 4 |
| Tecnologías | PHP 7.4+, PDO, MySQL/MariaDB, HTML5, CSS |
| Funcionalidades | Alta, listado, búsqueda, detalle, edición, baja, carga de foto, mensajes flash, protección CSRF y validación de duplicados |
| Repositorio público | https://github.com/Yatusabe01/AGENDA_PW |
| URL desplegada | https://agendabd.ct.ws/index.php |
| Reporte de evidencias | `Reporte_Agenda_PW.pdf` |

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
- **Validación:** campos obligatorios y longitudes; teléfono de 7 a 15 dígitos; correo con `filter_var`; foto con MIME real, tamaño máximo de 2 MB y nombre generado.
- **Duplicados:** `telefono` y `email` tienen restricciones `UNIQUE`, además de verificaciones preparadas antes de `INSERT` y `UPDATE` (excluyendo el registro actual al editar). Los errores de restricción se muestran como mensajes de usuario.
- **Errores:** los detalles de PDO se registran con `error_log()` y nunca se muestran al usuario.

### Decisión sobre los campos

`nombre` es obligatorio en HTML5 y en el servidor; `apellido` es opcional y se almacena como `NULL` cuando se deja vacío. `telefono` es obligatorio y solo acepta dígitos; `email` es opcional, se valida como correo y se almacena como `NULL` cuando se deja vacío. Ambos se comprueban como únicos. `foto` es obligatoria al crear y opcional al editar: si no se sube una nueva, se conserva el archivo existente y la sentencia `UPDATE` no incluye la columna `foto`.

## Checklist

- [x] **PDO connection** — `config/db.php` con las opciones de PDO y credenciales placeholder.
- [x] **prepare/execute** — consultas preparadas con `execute()` y valores enlazados.
- [x] **CREATE** — `crear.php`, el `INSERT` de `guardar.php` y el mensaje de éxito.
- [x] **READ list** — `index.php` con el listado ordenado y sus acciones.
- [x] **READ detail** — `ver.php` mostrando todos los campos y la foto.
- [x] **UPDATE** — `editar.php` y el `UPDATE` condicional de `actualizar.php`.
- [x] **DELETE** — confirmación de `eliminar.php`, su `DELETE` y la eliminación del archivo.
- [x] **HTML5+server validation** — `required`, `type="email"`, `pattern="[0-9]{7,15}"`, `accept` y las funciones `validate_contact()` y `upload_photo()`.
- [x] **htmlspecialchars** — `h()` en `includes/functions.php` y su uso en las vistas.
- [x] **search** — `index.php?q=...` con la consulta `LIKE` preparada.

Las capturas de soporte están en `Reporte_Agenda_PW.pdf`.

## Entrega

- Repositorio público: https://github.com/Yatusabe01/AGENDA_PW
- Sistema publicado: https://agendabd.ct.ws/index.php
- Evidencias: `Reporte_Agenda_PW.pdf`
- La conexión en `config/db.php` se mantiene con placeholders en el repositorio; las credenciales reales se configuran únicamente en el servidor.
