#!/usr/bin/env bash
# ============================================================
#  Acuario Tiburón Feliz — Documentación y prueba de APIs
#  Uso: bash tests/api-docs.sh > docs/api-log.txt 2>&1
# ============================================================

set -u

BASE="http://localhost:8080/5to_proyecto_tuburon/public/api/v1"
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
H2="Content-Type: application/json"

# ---------- Helpers ----------
sep() {
    printf '\n'
    printf '════════════════════════════════════════════════════════════════\n'
    printf '  %s\n' "$1"
    printf '════════════════════════════════════════════════════════════════\n'
}

doc() {
    printf '\n📘 %s\n' "$1"
}

do_curl() {
    local label="$1"; shift
    printf '\n▶  %s\n' "$label"
    printf '   $ curl %s\n' "$*"
    curl -s "$@"
    printf '\n'
}

# ---------- Token ADMIN generado localmente ----------
TOKEN=$(php -r '
require "'"$ROOT_DIR"'/vendor/autoload.php";
App\Config\Config::load("'"$ROOT_DIR"'");
echo App\Security\Security::buildTokenPair([
    "id_usuario" => 1, "rol" => "ADMIN",
    "id_empleado" => 1, "nombre_usuario" => "admin",
])["token"];
')

H1="Authorization: Bearer $TOKEN"

# ============================================================
#  0. AUTH
# ============================================================
sep "0. AUTH"
doc "POST /auth/login — Inicia sesión. Devuelve accessToken + refreshToken + usuario."
do_curl "POST /auth/login (admin2 / Secreta123)" \
    -X POST "$BASE/auth/login" -H "$H2" \
    -d '{"nombre_usuario":"admin2","password":"Secreta123"}'

doc "POST /auth/refresh — Renueva tokens. Body: { refreshToken }."
do_curl "POST /auth/refresh (ejemplo con token dummy)" \
    -X POST "$BASE/auth/refresh" -H "$H2" \
    -d '{"refreshToken":"dummy"}'

doc "GET /auth/me — Datos del usuario del token."
do_curl "GET /auth/me" "$BASE/auth/me" -H "$H1"

doc "POST /auth/logout — Confirma cierre de sesión (stateless)."
do_curl "POST /auth/logout" -X POST "$BASE/auth/logout" -H "$H1"

# ============================================================
#  1. EMPLEADOS (ADMIN)
# ============================================================
sep "1. EMPLEADOS — solo ADMIN"

doc "POST /empleados — Crear empleado."
do_curl "POST /empleados" -X POST "$BASE/empleados" -H "$H1" -H "$H2" \
    -d '{"nombre":"Ana","apellido_paterno":"García","apellido_materno":"Ruiz","telefono":"5559998888","correo":"ana@acuario.com","puesto":"Cuidador","fecha_ingreso":"2024-05-01","activo":true}'

doc "GET /empleados — Listado paginado. Query: page, limit, activo, q."
do_curl "GET /empleados?page=1&limit=10" "$BASE/empleados?page=1&limit=10" -H "$H1"

doc "GET /empleados/{id} — Consulta individual."
do_curl "GET /empleados/1" "$BASE/empleados/1" -H "$H1"

doc "PUT /empleados/{id} — Actualización total."
do_curl "PUT /empleados/1" -X PUT "$BASE/empleados/1" -H "$H1" -H "$H2" \
    -d '{"nombre":"Juan","apellido_paterno":"Pérez","apellido_materno":"López","telefono":"5551234567","correo":"juan@acuario.com","puesto":"Supervisor","fecha_ingreso":"2024-01-15","activo":true}'

doc "PATCH /empleados/{id}/activo — Baja lógica."
do_curl "PATCH /empleados/2/activo" -X PATCH "$BASE/empleados/2/activo" -H "$H1" -H "$H2" \
    -d '{"activo":false}'

# ============================================================
#  2. USUARIOS (ADMIN)
# ============================================================
sep "2. USUARIOS — solo ADMIN"

doc "POST /usuarios — Crear usuario vinculado a un empleado. Password se hashea."
do_curl "POST /usuarios" -X POST "$BASE/usuarios" -H "$H1" -H "$H2" \
    -d '{"id_empleado":2,"nombre_usuario":"agarcia","password":"Secreta456","rol":"CUIDADOR","activo":true}'

doc "GET /usuarios — Listado. Query: page, limit, activo, rol."
do_curl "GET /usuarios" "$BASE/usuarios" -H "$H1"

doc "GET /usuarios/{id}"
do_curl "GET /usuarios/1" "$BASE/usuarios/1" -H "$H1"

doc "PUT /usuarios/{id} — password es opcional."
do_curl "PUT /usuarios/1" -X PUT "$BASE/usuarios/1" -H "$H1" -H "$H2" \
    -d '{"id_empleado":1,"nombre_usuario":"admin","rol":"ADMIN","activo":true}'

doc "PATCH /usuarios/{id}/activo"
do_curl "PATCH /usuarios/2/activo" -X PATCH "$BASE/usuarios/2/activo" -H "$H1" -H "$H2" \
    -d '{"activo":true}'

# ============================================================
#  3. TIPOS DE ARTÍCULO
# ============================================================
sep "3. TIPOS DE ARTÍCULO"

doc "GET /tipos-articulo — Listado (ADMIN y CUIDADOR)."
do_curl "GET /tipos-articulo" "$BASE/tipos-articulo" -H "$H1"

doc "POST /tipos-articulo — Crear (solo ADMIN). Nombre único."
do_curl "POST /tipos-articulo" -X POST "$BASE/tipos-articulo" -H "$H1" -H "$H2" \
    -d '{"nombre":"Limpieza","descripcion":"Químicos y utensilios"}'

doc "GET /tipos-articulo/{id}"
do_curl "GET /tipos-articulo/1" "$BASE/tipos-articulo/1" -H "$H1"

doc "PUT /tipos-articulo/{id}"
do_curl "PUT /tipos-articulo/2" -X PUT "$BASE/tipos-articulo/2" -H "$H1" -H "$H2" \
    -d '{"nombre":"Limpieza y aseo","descripcion":"Químicos y utensilios"}'

doc "DELETE /tipos-articulo/{id} — 409 si tiene artículos."
do_curl "DELETE /tipos-articulo/999" -X DELETE "$BASE/tipos-articulo/999" -H "$H1"

# ============================================================
#  4. ARTÍCULOS
# ============================================================
sep "4. ARTÍCULOS"

doc "POST /articulos — Crear. codigo único, id_tipo_articulo debe existir."
do_curl "POST /articulos" -X POST "$BASE/articulos" -H "$H1" -H "$H2" \
    -d '{"codigo":"LIM-001","nombre":"Cloro líquido","unidad_medida":"L","id_tipo_articulo":2,"stock_minimo":3,"activo":true}'

doc "GET /articulos — Filtros: page, limit, id_tipo_articulo, activo, q, stock_bajo."
do_curl "GET /articulos" "$BASE/articulos" -H "$H1"
do_curl "GET /articulos?stock_bajo=true" "$BASE/articulos?stock_bajo=true" -H "$H1"

doc "GET /articulos/{id} — Incluye existencia."
do_curl "GET /articulos/1" "$BASE/articulos/1" -H "$H1"

doc "PUT /articulos/{id}"
do_curl "PUT /articulos/1" -X PUT "$BASE/articulos/1" -H "$H1" -H "$H2" \
    -d '{"codigo":"ALI-001","nombre":"Alimento escamas premium","unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":10,"activo":true}'

doc "PATCH /articulos/{id}/activo"
do_curl "PATCH /articulos/1/activo" -X PATCH "$BASE/articulos/1/activo" -H "$H1" -H "$H2" \
    -d '{"activo":true}'

# ============================================================
#  5. INVENTARIO
# ============================================================
sep "5. INVENTARIO"

doc "GET /inventario — Listado. Query: page, limit, stock_bajo."
do_curl "GET /inventario" "$BASE/inventario" -H "$H1"

doc "GET /inventario/{idArticulo} — Consulta existencia de un artículo."
do_curl "GET /inventario/1" "$BASE/inventario/1" -H "$H1"

doc "PATCH /inventario/{idArticulo} — Ajusta existencia (solo ADMIN)."
do_curl "PATCH /inventario/2" -X PATCH "$BASE/inventario/2" -H "$H1" -H "$H2" \
    -d '{"existencia":20}'

# ============================================================
#  6. ESPECIES
# ============================================================
sep "6. ESPECIES"

doc "POST /especies — Crear (solo ADMIN)."
do_curl "POST /especies" -X POST "$BASE/especies" -H "$H1" -H "$H2" \
    -d '{"nombre_comun":"Pez cirujano azul","nombre_cientifico":"Paracanthurus hepatus","tipo_agua":"Marina","temperatura_min":24,"temperatura_max":28,"ph_min":8.0,"ph_max":8.4}'

doc "GET /especies — Listado. Query: page, limit, q."
do_curl "GET /especies" "$BASE/especies" -H "$H1"

doc "GET /especies/{id}"
do_curl "GET /especies/1" "$BASE/especies/1" -H "$H1"

doc "PUT /especies/{id}"
do_curl "PUT /especies/1" -X PUT "$BASE/especies/1" -H "$H1" -H "$H2" \
    -d '{"nombre_comun":"Pez payaso","nombre_cientifico":"Amphiprion ocellaris","tipo_agua":"Marina","temperatura_min":24,"temperatura_max":28,"ph_min":8.0,"ph_max":8.4}'

doc "DELETE /especies/{id} — 409 si tiene peces."
do_curl "DELETE /especies/999" -X DELETE "$BASE/especies/999" -H "$H1"

# ============================================================
#  7. PECES
# ============================================================
sep "7. PECES"

doc "POST /peces — Crear. id_especie debe existir."
do_curl "POST /peces" -X POST "$BASE/peces" -H "$H1" -H "$H2" \
    -d '{"id_especie":2,"nombre":"Dory","sexo":"Hembra","fecha_ingreso":"2024-06-01"}'

doc "GET /peces — Filtros: id_especie, estado, q."
do_curl "GET /peces" "$BASE/peces" -H "$H1"
do_curl "GET /peces?estado=Activo" "$BASE/peces?estado=Activo" -H "$H1"

doc "GET /peces/{id}"
do_curl "GET /peces/1" "$BASE/peces/1" -H "$H1"

doc "PUT /peces/{id}"
do_curl "PUT /peces/1" -X PUT "$BASE/peces/1" -H "$H1" -H "$H2" \
    -d '{"id_especie":1,"nombre":"Nemo","sexo":"Macho","fecha_ingreso":"2024-03-10","estado":"Activo"}'

doc "PATCH /peces/{id}/estado — Estados: Activo, Enfermo, Cuarentena, Fallecido, Transferido."
do_curl "PATCH /peces/1/estado" -X PATCH "$BASE/peces/1/estado" -H "$H1" -H "$H2" \
    -d '{"estado":"Enfermo"}'
do_curl "PATCH /peces/1/estado (revertir)" -X PATCH "$BASE/peces/1/estado" -H "$H1" -H "$H2" \
    -d '{"estado":"Activo"}'

# ============================================================
#  8. TANQUES
# ============================================================
sep "8. TANQUES"

doc "POST /tanques — Crear. capacidad_litros > 0."
do_curl "POST /tanques" -X POST "$BASE/tanques" -H "$H1" -H "$H2" \
    -d '{"nombre":"Tanque Tropical 1","capacidad_litros":300,"ubicacion":"Sala B","tipo_agua":"Dulce"}'

doc "GET /tanques — Filtros: estado, tipo_agua, q."
do_curl "GET /tanques" "$BASE/tanques" -H "$H1"

doc "GET /tanques/{id}"
do_curl "GET /tanques/1" "$BASE/tanques/1" -H "$H1"

doc "PUT /tanques/{id}"
do_curl "PUT /tanques/1" -X PUT "$BASE/tanques/1" -H "$H1" -H "$H2" \
    -d '{"nombre":"Tanque Marino 1","capacidad_litros":500,"ubicacion":"Sala A","tipo_agua":"Marina","estado":"Activo"}'

doc "PATCH /tanques/{id}/parametros — ADMIN y CUIDADOR."
do_curl "PATCH /tanques/1/parametros" -X PATCH "$BASE/tanques/1/parametros" -H "$H1" -H "$H2" \
    -d '{"temperatura_actual":25.5,"ph_actual":8.2}'

# ============================================================
#  9. EQUIPAMIENTO
# ============================================================
sep "9. EQUIPAMIENTO"

doc "POST /equipamiento — id_tanque debe existir."
do_curl "POST /equipamiento" -X POST "$BASE/equipamiento" -H "$H1" -H "$H2" \
    -d '{"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":"2024-02-01","estado":"Activo"}'

doc "GET /equipamiento — Filtros: id_tanque, estado."
do_curl "GET /equipamiento?id_tanque=1" "$BASE/equipamiento?id_tanque=1" -H "$H1"

doc "GET /equipamiento/{id}"
do_curl "GET /equipamiento/1" "$BASE/equipamiento/1" -H "$H1"

doc "PUT /equipamiento/{id}"
do_curl "PUT /equipamiento/1" -X PUT "$BASE/equipamiento/1" -H "$H1" -H "$H2" \
    -d '{"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","estado":"Mantenimiento"}'

doc "PATCH /equipamiento/{id}/estado — Estados: Activo, Mantenimiento, Fuera de servicio, Retirado."
do_curl "PATCH /equipamiento/1/estado" -X PATCH "$BASE/equipamiento/1/estado" -H "$H1" -H "$H2" \
    -d '{"estado":"Activo"}'

# ============================================================
#  10. TIPOS DE MANTENIMIENTO
# ============================================================
sep "10. TIPOS DE MANTENIMIENTO"

doc "POST /tipos-mantenimiento — Crear. periodicidad_dias para alertas."
do_curl "POST /tipos-mantenimiento" -X POST "$BASE/tipos-mantenimiento" -H "$H1" -H "$H2" \
    -d '{"nombre":"Limpieza de cristales","descripcion":"Limpieza interior","periodicidad_dias":7}'

doc "GET /tipos-mantenimiento"
do_curl "GET /tipos-mantenimiento" "$BASE/tipos-mantenimiento" -H "$H1"

doc "GET /tipos-mantenimiento/{id}"
do_curl "GET /tipos-mantenimiento/1" "$BASE/tipos-mantenimiento/1" -H "$H1"

doc "PUT /tipos-mantenimiento/{id}"
do_curl "PUT /tipos-mantenimiento/1" -X PUT "$BASE/tipos-mantenimiento/1" -H "$H1" -H "$H2" \
    -d '{"nombre":"Cambio parcial de agua","descripcion":"Cambio 20%","periodicidad_dias":7}'

doc "DELETE /tipos-mantenimiento/{id} — 409 si tiene mantenimientos."
do_curl "DELETE /tipos-mantenimiento/999" -X DELETE "$BASE/tipos-mantenimiento/999" -H "$H1"

# ============================================================
#  11. MANTENIMIENTO
# ============================================================
sep "11. MANTENIMIENTO — ADMIN y CUIDADOR crean"

doc "POST /mantenimientos — id_tanque, id_tipo, id_empleado deben existir."
do_curl "POST /mantenimientos" -X POST "$BASE/mantenimientos" -H "$H1" -H "$H2" \
    -d '{"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:00:00","observaciones":"Cambio 20% de agua"}'

doc "GET /mantenimientos — Filtros: id_tanque, id_tipo_mantenimiento, id_empleado, fecha_desde, fecha_hasta."
do_curl "GET /mantenimientos" "$BASE/mantenimientos" -H "$H1"
do_curl "GET /mantenimientos?id_tanque=1" "$BASE/mantenimientos?id_tanque=1" -H "$H1"

doc "GET /mantenimientos/{id}"
do_curl "GET /mantenimientos/1" "$BASE/mantenimientos/1" -H "$H1"

doc "PUT /mantenimientos/{id} — solo ADMIN."
do_curl "PUT /mantenimientos/1" -X PUT "$BASE/mantenimientos/1" -H "$H1" -H "$H2" \
    -d '{"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:30:00","observaciones":"Cambio 25% de agua"}'

doc "DELETE /mantenimientos/{id} — solo ADMIN."
do_curl "DELETE /mantenimientos/999" -X DELETE "$BASE/mantenimientos/999" -H "$H1"

# ============================================================
#  12. TIPOS DE ALIMENTO
# ============================================================
sep "12. TIPOS DE ALIMENTO"

doc "POST /tipos-alimento"
do_curl "POST /tipos-alimento" -X POST "$BASE/tipos-alimento" -H "$H1" -H "$H2" \
    -d '{"nombre":"Copépodos congelados","tipo":"Congelado","descripcion":"Alimento premium"}'

doc "GET /tipos-alimento"
do_curl "GET /tipos-alimento" "$BASE/tipos-alimento" -H "$H1"

doc "GET /tipos-alimento/{id}"
do_curl "GET /tipos-alimento/1" "$BASE/tipos-alimento/1" -H "$H1"

doc "PUT /tipos-alimento/{id}"
do_curl "PUT /tipos-alimento/1" -X PUT "$BASE/tipos-alimento/1" -H "$H1" -H "$H2" \
    -d '{"nombre":"Escamas tropicales premium","tipo":"Seco","descripcion":"Alimento base"}'

doc "DELETE /tipos-alimento/{id} — 409 si tiene alimentaciones."
do_curl "DELETE /tipos-alimento/999" -X DELETE "$BASE/tipos-alimento/999" -H "$H1"

# ============================================================
#  13. ALIMENTACIÓN
# ============================================================
sep "13. ALIMENTACIÓN — ADMIN y CUIDADOR crean"

doc "POST /alimentacion — cantidad > 0."
do_curl "POST /alimentacion" -X POST "$BASE/alimentacion" -H "$H1" -H "$H2" \
    -d '{"id_tanque":1,"id_alimento":1,"id_empleado":1,"cantidad":0.05,"observaciones":"Ración matutina"}'

doc "GET /alimentacion — Filtros: id_tanque, id_alimento, id_empleado, fecha_desde, fecha_hasta."
do_curl "GET /alimentacion" "$BASE/alimentacion" -H "$H1"
do_curl "GET /alimentacion?id_tanque=1" "$BASE/alimentacion?id_tanque=1" -H "$H1"

doc "GET /alimentacion/{id}"
do_curl "GET /alimentacion/1" "$BASE/alimentacion/1" -H "$H1"

doc "PUT /alimentacion/{id} — solo ADMIN."
do_curl "PUT /alimentacion/1" -X PUT "$BASE/alimentacion/1" -H "$H1" -H "$H2" \
    -d '{"id_tanque":1,"id_alimento":1,"id_empleado":1,"cantidad":0.08,"observaciones":"Ración ajustada"}'

doc "DELETE /alimentacion/{id} — solo ADMIN."
do_curl "DELETE /alimentacion/999" -X DELETE "$BASE/alimentacion/999" -H "$H1"

# ============================================================
#  13b. FLUJO ALIMENTACIÓN ↔ INVENTARIO
# ============================================================
sep "13b. FLUJO ALIMENTACIÓN ↔ INVENTARIO"

doc "1) Crear artículo físico para el alimento."
do_curl "POST /articulos (alimento)" -X POST "$BASE/articulos" -H "$H1" -H "$H2" \
    -d '{"codigo":"ALI-ESC-02","nombre":"Escamas tropicales 1kg","unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":2}'

doc "2) Cargar stock: existencia = 10."
do_curl "PATCH /inventario/{id}" -X PATCH "$BASE/inventario/2" -H "$H1" -H "$H2" \
    -d '{"existencia":10}'

doc "3) Crear tipo_alimento vinculado al artículo."
do_curl "POST /tipos-alimento (con id_articulo)" -X POST "$BASE/tipos-alimento" -H "$H1" -H "$H2" \
    -d '{"nombre":"Escamas vinculadas","tipo":"Seco","id_articulo":2}'

doc "4) Registrar alimentación → descuenta 0.05 del inventario."
do_curl "POST /alimentacion" -X POST "$BASE/alimentacion" -H "$H1" -H "$H2" \
    -d '{"id_tanque":1,"id_alimento":2,"id_empleado":1,"cantidad":0.05,"observaciones":"Ración matutina"}'

doc "5) Verificar inventario descontado (10 - 0.05 = 9.95)."
do_curl "GET /inventario/2" "$BASE/inventario/2" -H "$H1"

doc "6) Forzar stock bajo → próxima alimentación debe dar 409 STOCK_INSUFICIENTE."
do_curl "PATCH /inventario/2 (bajar a 0.01)" -X PATCH "$BASE/inventario/2" -H "$H1" -H "$H2" \
    -d '{"existencia":0.01}'

do_curl "POST /alimentacion (sin stock)" -X POST "$BASE/alimentacion" -H "$H1" -H "$H2" \
    -d '{"id_tanque":1,"id_alimento":2,"id_empleado":1,"cantidad":0.05}'

doc "7) Eliminar alimentación → devuelve stock al inventario."
do_curl "DELETE /alimentacion/2" -X DELETE "$BASE/alimentacion/2" -H "$H1"
do_curl "GET /inventario/2 (stock devuelto)" "$BASE/inventario/2" -H "$H1"

# ============================================================
#  14. PEZ–TANQUE (Historial)
# ============================================================
sep "14. PEZ–TANQUE"

doc "POST /pez-tanque — Asignar pez a tanque. Un pez solo puede tener una asignación activa."
do_curl "POST /pez-tanque" -X POST "$BASE/pez-tanque" -H "$H1" -H "$H2" \
    -d '{"id_pez":1,"id_tanque":1,"fecha_ingreso":"2026-10-07 10:00:00","observaciones":"Ingreso inicial"}'

doc "GET /pez-tanque — Filtros: id_pez, id_tanque, activo."
do_curl "GET /pez-tanque" "$BASE/pez-tanque" -H "$H1"
do_curl "GET /pez-tanque?activo=true" "$BASE/pez-tanque?activo=true" -H "$H1"

doc "GET /pez-tanque/historial/{idPez} — Historial completo de un pez."
do_curl "GET /pez-tanque/historial/1" "$BASE/pez-tanque/historial/1" -H "$H1"

doc "PATCH /pez-tanque/{idPez}/{idTanque}/{fechaIngreso} — Cerrar asignación."
doc "fecha_salida debe ser > fecha_ingreso. URL-encode: %20 para espacio."
do_curl "PATCH /pez-tanque/1/1/2026-10-07%2010:00:00" \
    -X PATCH "$BASE/pez-tanque/1/1/2026-10-07%2010:00:00" -H "$H1" -H "$H2" \
    -d '{"fecha_salida":"2026-10-08 12:00:00","observaciones":"Traslado a cuarentena"}'

# ============================================================
#  FIN
# ============================================================
sep "FIN DE LA PRUEBA"
printf 'Todas las rutas del contrato han sido probadas.\n'