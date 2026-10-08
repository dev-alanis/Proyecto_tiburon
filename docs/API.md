
---
## 0. AUTH
---

### POST /auth/login — Inicia sesión. Devuelve accessToken + refreshToken + usuario.

**POST /auth/login (admin2 / Secreta123)**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/auth/login -H Content-Type: application/json -d {"nombre_usuario":"admin2","password":"Secreta123"}
{"error":{"code":"UNAUTHORIZED","message":"Credenciales inválidas","details":[]}}

### POST /auth/refresh — Renueva tokens. Body: { refreshToken }.

**POST /auth/refresh (ejemplo con token dummy)**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/auth/refresh -H Content-Type: application/json -d {"refreshToken":"dummy"}
{"error":{"code":"UNAUTHORIZED","message":"Refresh token inválido o expirado","details":[]}}

### GET /auth/me — Datos del usuario del token.

**GET /auth/me**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/auth/me -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_usuario":1,"id_empleado":1,"nombre_usuario":"admin","rol":"ADMIN","activo":1,"fecha_creacion":"2026-10-07 21:38:00","nombre_completo":"Juan Pérez López","correo":"juan@acuario.com","puesto":"Supervisor"}}

### POST /auth/logout — Confirma cierre de sesión (stateless).

**POST /auth/logout**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/auth/logout -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"mensaje":"Sesión cerrada","usuario":"admin"}}

---
## 1. EMPLEADOS — solo ADMIN
---

### POST /empleados — Crear empleado.

**POST /empleados**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/empleados -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Ana","apellido_paterno":"García","apellido_materno":"Ruiz","telefono":"5559998888","correo":"ana@acuario.com","puesto":"Cuidador","fecha_ingreso":"2024-05-01","activo":true}
{"data":{"id_empleado":3,"nombre":"Ana","apellido_paterno":"García","apellido_materno":"Ruiz","telefono":"5559998888","correo":"ana@acuario.com","puesto":"Cuidador","fecha_ingreso":"2024-05-01","activo":1}}

### GET /empleados — Listado paginado. Query: page, limit, activo, q.

**GET /empleados?page=1&limit=10**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/empleados?page=1&limit=10 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_empleado":3,"nombre":"Ana","apellido_paterno":"García","apellido_materno":"Ruiz","telefono":"5559998888","correo":"ana@acuario.com","puesto":"Cuidador","fecha_ingreso":"2024-05-01","activo":1},{"id_empleado":2,"nombre":"Ana","apellido_paterno":"García","apellido_materno":"Ruiz","telefono":"5559998888","correo":"ana@acuario.com","puesto":"Cuidador","fecha_ingreso":"2024-05-01","activo":0},{"id_empleado":1,"nombre":"Juan","apellido_paterno":"Pérez","apellido_materno":"López","telefono":"5551234567","correo":"juan@acuario.com","puesto":"Supervisor","fecha_ingreso":"2024-01-15","activo":1}],"meta":{"page":1,"limit":10,"total":3,"totalPages":1}}

### GET /empleados/{id} — Consulta individual.

**GET /empleados/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/empleados/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_empleado":1,"nombre":"Juan","apellido_paterno":"Pérez","apellido_materno":"López","telefono":"5551234567","correo":"juan@acuario.com","puesto":"Supervisor","fecha_ingreso":"2024-01-15","activo":1}}

### PUT /empleados/{id} — Actualización total.

**PUT /empleados/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/empleados/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Juan","apellido_paterno":"Pérez","apellido_materno":"López","telefono":"5551234567","correo":"juan@acuario.com","puesto":"Supervisor","fecha_ingreso":"2024-01-15","activo":true}
{"data":{"id_empleado":1,"nombre":"Juan","apellido_paterno":"Pérez","apellido_materno":"López","telefono":"5551234567","correo":"juan@acuario.com","puesto":"Supervisor","fecha_ingreso":"2024-01-15","activo":1}}

### PATCH /empleados/{id}/activo — Baja lógica.

**PATCH /empleados/2/activo**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/empleados/2/activo -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"activo":false}
{"data":{"id_empleado":2,"nombre":"Ana","apellido_paterno":"García","apellido_materno":"Ruiz","telefono":"5559998888","correo":"ana@acuario.com","puesto":"Cuidador","fecha_ingreso":"2024-05-01","activo":0}}

---
## 2. USUARIOS — solo ADMIN
---

### POST /usuarios — Crear usuario vinculado a un empleado. Password se hashea.

**POST /usuarios**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/usuarios -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_empleado":2,"nombre_usuario":"agarcia","password":"Secreta456","rol":"CUIDADOR","activo":true}
{"data":{"id_usuario":2,"id_empleado":2,"nombre_usuario":"agarcia","rol":"CUIDADOR","activo":1,"fecha_creacion":"2026-10-07 21:46:54","nombre_empleado":"Ana García"}}

### GET /usuarios — Listado. Query: page, limit, activo, rol.

**GET /usuarios**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/usuarios -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_usuario":2,"id_empleado":2,"nombre_usuario":"agarcia","rol":"CUIDADOR","activo":1,"fecha_creacion":"2026-10-07 21:46:54","nombre_empleado":"Ana García"},{"id_usuario":1,"id_empleado":1,"nombre_usuario":"admin","rol":"ADMIN","activo":1,"fecha_creacion":"2026-10-07 21:38:00","nombre_empleado":"Juan Pérez"}],"meta":{"page":1,"limit":20,"total":2,"totalPages":1}}

### GET /usuarios/{id}

**GET /usuarios/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/usuarios/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_usuario":1,"id_empleado":1,"nombre_usuario":"admin","rol":"ADMIN","activo":1,"fecha_creacion":"2026-10-07 21:38:00","nombre_empleado":"Juan Pérez"}}

### PUT /usuarios/{id} — password es opcional.

**PUT /usuarios/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/usuarios/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_empleado":1,"nombre_usuario":"admin","rol":"ADMIN","activo":true}
{"data":{"id_usuario":1,"id_empleado":1,"nombre_usuario":"admin","rol":"ADMIN","activo":1,"fecha_creacion":"2026-10-07 21:38:00","nombre_empleado":"Juan Pérez"}}

### PATCH /usuarios/{id}/activo

**PATCH /usuarios/2/activo**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/usuarios/2/activo -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"activo":true}
{"data":{"id_usuario":2,"id_empleado":2,"nombre_usuario":"agarcia","rol":"CUIDADOR","activo":1,"fecha_creacion":"2026-10-07 21:46:54","nombre_empleado":"Ana García"}}

---
## 3. TIPOS DE ARTÍCULO
---

### GET /tipos-articulo — Listado (ADMIN y CUIDADOR).

**GET /tipos-articulo**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-articulo -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_tipo_articulo":1,"nombre":"Limpieza","descripcion":"Químicos y utensilios"}]}

### POST /tipos-articulo — Crear (solo ADMIN). Nombre único.

**POST /tipos-articulo**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-articulo -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Limpieza","descripcion":"Químicos y utensilios"}
{"error":{"code":"CONFLICT","message":"Ya existe un tipo de artículo con ese nombre","details":[]}}

### GET /tipos-articulo/{id}

**GET /tipos-articulo/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-articulo/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_tipo_articulo":1,"nombre":"Limpieza","descripcion":"Químicos y utensilios"}}

### PUT /tipos-articulo/{id}

**PUT /tipos-articulo/2**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-articulo/2 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Limpieza y aseo","descripcion":"Químicos y utensilios"}
{"error":{"code":"NOT_FOUND","message":"Tipo de artículo no encontrado","details":[]}}

### DELETE /tipos-articulo/{id} — 409 si tiene artículos.

**DELETE /tipos-articulo/999**
   $ curl -X DELETE http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-articulo/999 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"error":{"code":"NOT_FOUND","message":"Tipo de artículo no encontrado","details":[]}}

---
## 4. ARTÍCULOS
---

### POST /articulos — Crear. codigo único, id_tipo_articulo debe existir.

**POST /articulos**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/articulos -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"codigo":"LIM-001","nombre":"Cloro líquido","unidad_medida":"L","id_tipo_articulo":2,"stock_minimo":3,"activo":true}
{"error":{"code":"VALIDATION","message":"El tipo de artículo indicado no existe","details":[]}}

### GET /articulos — Filtros: page, limit, id_tipo_articulo, activo, q, stock_bajo.

**GET /articulos**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/articulos -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_articulo":1,"codigo":"ALI-ESC-01","nombre":"Escamas tropicales 1kg","descripcion":null,"categoria":null,"marca":null,"unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":"2.00","activo":1,"tipo_articulo":"Limpieza","existencia":"0.01"}],"meta":{"page":1,"limit":20,"total":1,"totalPages":1}}

**GET /articulos?stock_bajo=true**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/articulos?stock_bajo=true -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_articulo":1,"codigo":"ALI-ESC-01","nombre":"Escamas tropicales 1kg","descripcion":null,"categoria":null,"marca":null,"unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":"2.00","activo":1,"tipo_articulo":"Limpieza","existencia":"0.01"}],"meta":{"page":1,"limit":20,"total":1,"totalPages":1}}

### GET /articulos/{id} — Incluye existencia.

**GET /articulos/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/articulos/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_articulo":1,"codigo":"ALI-ESC-01","nombre":"Escamas tropicales 1kg","descripcion":null,"categoria":null,"marca":null,"unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":"2.00","activo":1,"tipo_articulo":"Limpieza","existencia":"0.01"}}

### PUT /articulos/{id}

**PUT /articulos/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/articulos/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"codigo":"ALI-001","nombre":"Alimento escamas premium","unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":10,"activo":true}
{"data":{"id_articulo":1,"codigo":"ALI-001","nombre":"Alimento escamas premium","descripcion":null,"categoria":null,"marca":null,"unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":"10.00","activo":1,"tipo_articulo":"Limpieza","existencia":"0.01"}}

### PATCH /articulos/{id}/activo

**PATCH /articulos/1/activo**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/articulos/1/activo -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"activo":true}
{"data":{"id_articulo":1,"codigo":"ALI-001","nombre":"Alimento escamas premium","descripcion":null,"categoria":null,"marca":null,"unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":"10.00","activo":1,"tipo_articulo":"Limpieza","existencia":"0.01"}}

---
## 5. INVENTARIO
---

### GET /inventario — Listado. Query: page, limit, stock_bajo.

**GET /inventario**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/inventario -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_inventario":1,"id_articulo":1,"existencia":"0.01","fecha_actualizacion":"2026-10-07 21:45:42","codigo":"ALI-001","nombre":"Alimento escamas premium","unidad_medida":"kg","stock_minimo":"10.00","activo":1}],"meta":{"page":1,"limit":20,"total":1,"totalPages":1}}

### GET /inventario/{idArticulo} — Consulta existencia de un artículo.

**GET /inventario/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/inventario/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_inventario":1,"id_articulo":1,"existencia":"0.01","fecha_actualizacion":"2026-10-07 21:45:42","codigo":"ALI-001","nombre":"Alimento escamas premium","unidad_medida":"kg","stock_minimo":"10.00","activo":1}}

### PATCH /inventario/{idArticulo} — Ajusta existencia (solo ADMIN).

**PATCH /inventario/2**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/inventario/2 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"existencia":20}
{"error":{"code":"NOT_FOUND","message":"Artículo no encontrado","details":[]}}

---
## 6. ESPECIES
---

### POST /especies — Crear (solo ADMIN).

**POST /especies**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/especies -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre_comun":"Pez cirujano azul","nombre_cientifico":"Paracanthurus hepatus","tipo_agua":"Marina","temperatura_min":24,"temperatura_max":28,"ph_min":8.0,"ph_max":8.4}
{"data":{"id_especie":3,"nombre_comun":"Pez cirujano azul","nombre_cientifico":"Paracanthurus hepatus","descripcion":null,"tipo_agua":"Marina","temperatura_min":"24.0","temperatura_max":"28.0","ph_min":"8.0","ph_max":"8.4"}}

### GET /especies — Listado. Query: page, limit, q.

**GET /especies**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/especies -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_especie":2,"nombre_comun":"Pez cirujano azul","nombre_cientifico":"Paracanthurus hepatus","descripcion":null,"tipo_agua":"Marina","temperatura_min":"24.0","temperatura_max":"28.0","ph_min":"8.0","ph_max":"8.4"},{"id_especie":3,"nombre_comun":"Pez cirujano azul","nombre_cientifico":"Paracanthurus hepatus","descripcion":null,"tipo_agua":"Marina","temperatura_min":"24.0","temperatura_max":"28.0","ph_min":"8.0","ph_max":"8.4"},{"id_especie":1,"nombre_comun":"Pez payaso","nombre_cientifico":"Amphiprion ocellaris","descripcion":null,"tipo_agua":"Marina","temperatura_min":"24.0","temperatura_max":"28.0","ph_min":"8.0","ph_max":"8.4"}],"meta":{"page":1,"limit":20,"total":3,"totalPages":1}}

### GET /especies/{id}

**GET /especies/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/especies/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_especie":1,"nombre_comun":"Pez payaso","nombre_cientifico":"Amphiprion ocellaris","descripcion":null,"tipo_agua":"Marina","temperatura_min":"24.0","temperatura_max":"28.0","ph_min":"8.0","ph_max":"8.4"}}

### PUT /especies/{id}

**PUT /especies/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/especies/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre_comun":"Pez payaso","nombre_cientifico":"Amphiprion ocellaris","tipo_agua":"Marina","temperatura_min":24,"temperatura_max":28,"ph_min":8.0,"ph_max":8.4}
{"data":{"id_especie":1,"nombre_comun":"Pez payaso","nombre_cientifico":"Amphiprion ocellaris","descripcion":null,"tipo_agua":"Marina","temperatura_min":"24.0","temperatura_max":"28.0","ph_min":"8.0","ph_max":"8.4"}}

### DELETE /especies/{id} — 409 si tiene peces.

**DELETE /especies/999**
   $ curl -X DELETE http://localhost:8080/5to_proyecto_tuburon/public/api/v1/especies/999 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"error":{"code":"NOT_FOUND","message":"Especie no encontrada","details":[]}}

---
## 7. PECES
---

### POST /peces — Crear. id_especie debe existir.

**POST /peces**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/peces -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_especie":2,"nombre":"Dory","sexo":"Hembra","fecha_ingreso":"2024-06-01"}
{"data":{"id_pez":2,"id_especie":2,"nombre":"Dory","sexo":"Hembra","fecha_ingreso":"2024-06-01","fecha_nacimiento":null,"estado":"Activo","especie":"Pez cirujano azul"}}

### GET /peces — Filtros: id_especie, estado, q.

**GET /peces**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/peces -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_pez":2,"id_especie":2,"nombre":"Dory","sexo":"Hembra","fecha_ingreso":"2024-06-01","fecha_nacimiento":null,"estado":"Activo","especie":"Pez cirujano azul"},{"id_pez":1,"id_especie":1,"nombre":"Nemo","sexo":"Macho","fecha_ingreso":"2024-03-10","fecha_nacimiento":null,"estado":"Activo","especie":"Pez payaso"}],"meta":{"page":1,"limit":20,"total":2,"totalPages":1}}

**GET /peces?estado=Activo**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/peces?estado=Activo -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_pez":2,"id_especie":2,"nombre":"Dory","sexo":"Hembra","fecha_ingreso":"2024-06-01","fecha_nacimiento":null,"estado":"Activo","especie":"Pez cirujano azul"},{"id_pez":1,"id_especie":1,"nombre":"Nemo","sexo":"Macho","fecha_ingreso":"2024-03-10","fecha_nacimiento":null,"estado":"Activo","especie":"Pez payaso"}],"meta":{"page":1,"limit":20,"total":2,"totalPages":1}}

### GET /peces/{id}

**GET /peces/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/peces/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_pez":1,"id_especie":1,"nombre":"Nemo","sexo":"Macho","fecha_ingreso":"2024-03-10","fecha_nacimiento":null,"estado":"Activo","especie":"Pez payaso"}}

### PUT /peces/{id}

**PUT /peces/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/peces/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_especie":1,"nombre":"Nemo","sexo":"Macho","fecha_ingreso":"2024-03-10","estado":"Activo"}
{"data":{"id_pez":1,"id_especie":1,"nombre":"Nemo","sexo":"Macho","fecha_ingreso":"2024-03-10","fecha_nacimiento":null,"estado":"Activo","especie":"Pez payaso"}}

### PATCH /peces/{id}/estado — Estados: Activo, Enfermo, Cuarentena, Fallecido, Transferido.

**PATCH /peces/1/estado**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/peces/1/estado -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"estado":"Enfermo"}
{"data":{"id_pez":1,"id_especie":1,"nombre":"Nemo","sexo":"Macho","fecha_ingreso":"2024-03-10","fecha_nacimiento":null,"estado":"Enfermo","especie":"Pez payaso"}}

**PATCH /peces/1/estado (revertir)**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/peces/1/estado -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"estado":"Activo"}
{"data":{"id_pez":1,"id_especie":1,"nombre":"Nemo","sexo":"Macho","fecha_ingreso":"2024-03-10","fecha_nacimiento":null,"estado":"Activo","especie":"Pez payaso"}}

---
## 8. TANQUES
---

### POST /tanques — Crear. capacidad_litros > 0.

**POST /tanques**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tanques -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Tanque Tropical 1","capacidad_litros":300,"ubicacion":"Sala B","tipo_agua":"Dulce"}
{"data":{"id_tanque":3,"nombre":"Tanque Tropical 1","capacidad_litros":"300.00","ubicacion":"Sala B","tipo_agua":"Dulce","temperatura_actual":null,"ph_actual":null,"estado":"Activo"}}

### GET /tanques — Filtros: estado, tipo_agua, q.

**GET /tanques**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tanques -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_tanque":1,"nombre":"Tanque Marino 1","capacidad_litros":"500.00","ubicacion":"Sala A","tipo_agua":"Marina","temperatura_actual":"25.5","ph_actual":"8.2","estado":"Activo"},{"id_tanque":2,"nombre":"Tanque Tropical 1","capacidad_litros":"300.00","ubicacion":"Sala B","tipo_agua":"Dulce","temperatura_actual":null,"ph_actual":null,"estado":"Activo"},{"id_tanque":3,"nombre":"Tanque Tropical 1","capacidad_litros":"300.00","ubicacion":"Sala B","tipo_agua":"Dulce","temperatura_actual":null,"ph_actual":null,"estado":"Activo"}],"meta":{"page":1,"limit":20,"total":3,"totalPages":1}}

### GET /tanques/{id}

**GET /tanques/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tanques/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_tanque":1,"nombre":"Tanque Marino 1","capacidad_litros":"500.00","ubicacion":"Sala A","tipo_agua":"Marina","temperatura_actual":"25.5","ph_actual":"8.2","estado":"Activo"}}

### PUT /tanques/{id}

**PUT /tanques/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tanques/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Tanque Marino 1","capacidad_litros":500,"ubicacion":"Sala A","tipo_agua":"Marina","estado":"Activo"}
{"data":{"id_tanque":1,"nombre":"Tanque Marino 1","capacidad_litros":"500.00","ubicacion":"Sala A","tipo_agua":"Marina","temperatura_actual":null,"ph_actual":null,"estado":"Activo"}}

### PATCH /tanques/{id}/parametros — ADMIN y CUIDADOR.

**PATCH /tanques/1/parametros**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tanques/1/parametros -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"temperatura_actual":25.5,"ph_actual":8.2}
{"data":{"id_tanque":1,"nombre":"Tanque Marino 1","capacidad_litros":"500.00","ubicacion":"Sala A","tipo_agua":"Marina","temperatura_actual":"25.5","ph_actual":"8.2","estado":"Activo"}}

---
## 9. EQUIPAMIENTO
---

### POST /equipamiento — id_tanque debe existir.

**POST /equipamiento**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/equipamiento -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":"2024-02-01","estado":"Activo"}
{"data":{"id_equipamiento":3,"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":"2024-02-01","estado":"Activo","tanque_nombre":"Tanque Marino 1"}}

### GET /equipamiento — Filtros: id_tanque, estado.

**GET /equipamiento?id_tanque=1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/equipamiento?id_tanque=1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_equipamiento":3,"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":"2024-02-01","estado":"Activo","tanque_nombre":"Tanque Marino 1"},{"id_equipamiento":2,"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":"2024-02-01","estado":"Activo","tanque_nombre":"Tanque Marino 1"},{"id_equipamiento":1,"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":null,"estado":"Activo","tanque_nombre":"Tanque Marino 1"}],"meta":{"page":1,"limit":20,"total":3,"totalPages":1}}

### GET /equipamiento/{id}

**GET /equipamiento/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/equipamiento/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_equipamiento":1,"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":null,"estado":"Activo","tanque_nombre":"Tanque Marino 1"}}

### PUT /equipamiento/{id}

**PUT /equipamiento/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/equipamiento/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","estado":"Mantenimiento"}
{"data":{"id_equipamiento":1,"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":null,"estado":"Mantenimiento","tanque_nombre":"Tanque Marino 1"}}

### PATCH /equipamiento/{id}/estado — Estados: Activo, Mantenimiento, Fuera de servicio, Retirado.

**PATCH /equipamiento/1/estado**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/equipamiento/1/estado -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"estado":"Activo"}
{"data":{"id_equipamiento":1,"id_tanque":1,"nombre":"Skimmer","tipo":"Filtración","marca":"Reef Octopus","modelo":"Classic 150","fecha_instalacion":null,"estado":"Activo","tanque_nombre":"Tanque Marino 1"}}

---
## 10. TIPOS DE MANTENIMIENTO
---

### POST /tipos-mantenimiento — Crear. periodicidad_dias para alertas.

**POST /tipos-mantenimiento**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-mantenimiento -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Limpieza de cristales","descripcion":"Limpieza interior","periodicidad_dias":7}
{"error":{"code":"CONFLICT","message":"Ya existe un tipo con ese nombre","details":[]}}

### GET /tipos-mantenimiento

**GET /tipos-mantenimiento**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-mantenimiento -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_tipo_mantenimiento":1,"nombre":"Cambio parcial de agua","descripcion":"Cambio 20%","periodicidad_dias":7},{"id_tipo_mantenimiento":2,"nombre":"Limpieza de cristales","descripcion":"Limpieza interior","periodicidad_dias":7}]}

### GET /tipos-mantenimiento/{id}

**GET /tipos-mantenimiento/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-mantenimiento/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_tipo_mantenimiento":1,"nombre":"Cambio parcial de agua","descripcion":"Cambio 20%","periodicidad_dias":7}}

### PUT /tipos-mantenimiento/{id}

**PUT /tipos-mantenimiento/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-mantenimiento/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Cambio parcial de agua","descripcion":"Cambio 20%","periodicidad_dias":7}
{"data":{"id_tipo_mantenimiento":1,"nombre":"Cambio parcial de agua","descripcion":"Cambio 20%","periodicidad_dias":7}}

### DELETE /tipos-mantenimiento/{id} — 409 si tiene mantenimientos.

**DELETE /tipos-mantenimiento/999**
   $ curl -X DELETE http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-mantenimiento/999 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"error":{"code":"NOT_FOUND","message":"Tipo de mantenimiento no encontrado","details":[]}}

---
## 11. MANTENIMIENTO — ADMIN y CUIDADOR crean
---

### POST /mantenimientos — id_tanque, id_tipo, id_empleado deben existir.

**POST /mantenimientos**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/mantenimientos -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:00:00","observaciones":"Cambio 20% de agua"}
{"data":{"id_mantenimiento":3,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:00:00","observaciones":"Cambio 20% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"}}

### GET /mantenimientos — Filtros: id_tanque, id_tipo_mantenimiento, id_empleado, fecha_desde, fecha_hasta.

**GET /mantenimientos**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/mantenimientos -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_mantenimiento":1,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:30:00","observaciones":"Cambio 25% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"},{"id_mantenimiento":2,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:00:00","observaciones":"Cambio 20% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"},{"id_mantenimiento":3,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:00:00","observaciones":"Cambio 20% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"}],"meta":{"page":1,"limit":20,"total":3,"totalPages":1}}

**GET /mantenimientos?id_tanque=1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/mantenimientos?id_tanque=1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_mantenimiento":1,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:30:00","observaciones":"Cambio 25% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"},{"id_mantenimiento":2,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:00:00","observaciones":"Cambio 20% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"},{"id_mantenimiento":3,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:00:00","observaciones":"Cambio 20% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"}],"meta":{"page":1,"limit":20,"total":3,"totalPages":1}}

### GET /mantenimientos/{id}

**GET /mantenimientos/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/mantenimientos/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_mantenimiento":1,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:30:00","observaciones":"Cambio 25% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"}}

### PUT /mantenimientos/{id} — solo ADMIN.

**PUT /mantenimientos/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/mantenimientos/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:30:00","observaciones":"Cambio 25% de agua"}
{"data":{"id_mantenimiento":1,"id_tanque":1,"id_tipo_mantenimiento":1,"id_empleado":1,"fecha":"2026-10-07 09:30:00","observaciones":"Cambio 25% de agua","tanque_nombre":"Tanque Marino 1","tipo_nombre":"Cambio parcial de agua","empleado_nombre":"Juan Pérez"}}

### DELETE /mantenimientos/{id} — solo ADMIN.

**DELETE /mantenimientos/999**
   $ curl -X DELETE http://localhost:8080/5to_proyecto_tuburon/public/api/v1/mantenimientos/999 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"error":{"code":"NOT_FOUND","message":"Mantenimiento no encontrado","details":[]}}

---
## 12. TIPOS DE ALIMENTO
---

### POST /tipos-alimento

**POST /tipos-alimento**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-alimento -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Copépodos congelados","tipo":"Congelado","descripcion":"Alimento premium"}
{"error":{"code":"CONFLICT","message":"Ya existe un tipo de alimento con ese nombre","details":[]}}

### GET /tipos-alimento

**GET /tipos-alimento**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-alimento -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_alimento":2,"nombre":"Copépodos congelados","tipo":"Congelado","descripcion":"Alimento premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"articulo_existencia":"0.00"},{"id_alimento":3,"nombre":"Escamas tropicales","tipo":"Seco","descripcion":null,"id_articulo":1,"articulo_codigo":"ALI-001","articulo_nombre":"Alimento escamas premium","articulo_unidad":"kg","articulo_existencia":"0.01"},{"id_alimento":1,"nombre":"Escamas tropicales premium","tipo":"Seco","descripcion":"Alimento base","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"articulo_existencia":"0.00"}]}

### GET /tipos-alimento/{id}

**GET /tipos-alimento/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-alimento/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_alimento":1,"nombre":"Escamas tropicales premium","id_articulo":null,"tipo":"Seco","descripcion":"Alimento base","articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"articulo_existencia":"0.00"}}

### PUT /tipos-alimento/{id}

**PUT /tipos-alimento/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-alimento/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Escamas tropicales premium","tipo":"Seco","descripcion":"Alimento base"}
{"data":{"id_alimento":1,"nombre":"Escamas tropicales premium","id_articulo":null,"tipo":"Seco","descripcion":"Alimento base","articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"articulo_existencia":"0.00"}}

### DELETE /tipos-alimento/{id} — 409 si tiene alimentaciones.

**DELETE /tipos-alimento/999**
   $ curl -X DELETE http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-alimento/999 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"error":{"code":"NOT_FOUND","message":"Tipo de alimento no encontrado","details":[]}}

---
## 13. ALIMENTACIÓN — ADMIN y CUIDADOR crean
---

### POST /alimentacion — cantidad > 0.

**POST /alimentacion**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_tanque":1,"id_alimento":1,"id_empleado":1,"cantidad":0.05,"observaciones":"Ración matutina"}
{"data":{"id_alimentacion":5,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:46:55","cantidad":"0.05","observaciones":"Ración matutina","tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"}}

### GET /alimentacion — Filtros: id_tanque, id_alimento, id_empleado, fecha_desde, fecha_hasta.

**GET /alimentacion**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_alimentacion":5,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:46:55","cantidad":"0.05","observaciones":"Ración matutina","tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"},{"id_alimentacion":3,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:45:42","cantidad":"0.05","observaciones":null,"tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"},{"id_alimentacion":4,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:45:42","cantidad":"0.05","observaciones":null,"tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"},{"id_alimentacion":2,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:38:00","cantidad":"0.05","observaciones":"Ración matutina","tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"}],"meta":{"page":1,"limit":20,"total":4,"totalPages":1}}

**GET /alimentacion?id_tanque=1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion?id_tanque=1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_alimentacion":5,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:46:55","cantidad":"0.05","observaciones":"Ración matutina","tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"},{"id_alimentacion":3,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:45:42","cantidad":"0.05","observaciones":null,"tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"},{"id_alimentacion":4,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:45:42","cantidad":"0.05","observaciones":null,"tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"},{"id_alimentacion":2,"id_tanque":1,"id_alimento":1,"id_empleado":1,"fecha_hora":"2026-10-07 21:38:00","cantidad":"0.05","observaciones":"Ración matutina","tanque_nombre":"Tanque Marino 1","alimento_nombre":"Escamas tropicales premium","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"}],"meta":{"page":1,"limit":20,"total":4,"totalPages":1}}

### GET /alimentacion/{id}

**GET /alimentacion/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"error":{"code":"NOT_FOUND","message":"Registro de alimentación no encontrado","details":[]}}

### PUT /alimentacion/{id} — solo ADMIN.

**PUT /alimentacion/1**
   $ curl -X PUT http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_tanque":1,"id_alimento":1,"id_empleado":1,"cantidad":0.08,"observaciones":"Ración ajustada"}
{"error":{"code":"NOT_FOUND","message":"Registro de alimentación no encontrado","details":[]}}

### DELETE /alimentacion/{id} — solo ADMIN.

**DELETE /alimentacion/999**
   $ curl -X DELETE http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion/999 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"error":{"code":"NOT_FOUND","message":"Registro de alimentación no encontrado","details":[]}}

---
## 13b. FLUJO ALIMENTACIÓN ↔ INVENTARIO
---

### 1) Crear artículo físico para el alimento.

**POST /articulos (alimento)**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/articulos -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"codigo":"ALI-ESC-02","nombre":"Escamas tropicales 1kg","unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":2}
{"data":{"id_articulo":2,"codigo":"ALI-ESC-02","nombre":"Escamas tropicales 1kg","descripcion":null,"categoria":null,"marca":null,"unidad_medida":"kg","id_tipo_articulo":1,"stock_minimo":"2.00","activo":1,"tipo_articulo":"Limpieza","existencia":"0.00"}}

### 2) Cargar stock: existencia = 10.

**PATCH /inventario/{id}**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/inventario/2 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"existencia":10}
{"data":{"id_inventario":6,"id_articulo":2,"existencia":"10.00","fecha_actualizacion":"2026-10-07 21:46:55","codigo":"ALI-ESC-02","nombre":"Escamas tropicales 1kg","unidad_medida":"kg","stock_minimo":"2.00","activo":1}}

### 3) Crear tipo_alimento vinculado al artículo.

**POST /tipos-alimento (con id_articulo)**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/tipos-alimento -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"nombre":"Escamas vinculadas","tipo":"Seco","id_articulo":2}
{"data":{"id_alimento":4,"nombre":"Escamas vinculadas","id_articulo":2,"tipo":"Seco","descripcion":null,"articulo_codigo":"ALI-ESC-02","articulo_nombre":"Escamas tropicales 1kg","articulo_unidad":"kg","articulo_existencia":"10.00"}}

### 4) Registrar alimentación → descuenta 0.05 del inventario.

**POST /alimentacion**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_tanque":1,"id_alimento":2,"id_empleado":1,"cantidad":0.05,"observaciones":"Ración matutina"}
{"data":{"id_alimentacion":6,"id_tanque":1,"id_alimento":2,"id_empleado":1,"fecha_hora":"2026-10-07 21:46:55","cantidad":"0.05","observaciones":"Ración matutina","tanque_nombre":"Tanque Marino 1","alimento_nombre":"Copépodos congelados","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"}}

### 5) Verificar inventario descontado (10 - 0.05 = 9.95).

**GET /inventario/2**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/inventario/2 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_inventario":6,"id_articulo":2,"existencia":"10.00","fecha_actualizacion":"2026-10-07 21:46:55","codigo":"ALI-ESC-02","nombre":"Escamas tropicales 1kg","unidad_medida":"kg","stock_minimo":"2.00","activo":1}}

### 6) Forzar stock bajo → próxima alimentación debe dar 409 STOCK_INSUFICIENTE.

**PATCH /inventario/2 (bajar a 0.01)**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/inventario/2 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"existencia":0.01}
{"data":{"id_inventario":6,"id_articulo":2,"existencia":"0.01","fecha_actualizacion":"2026-10-07 21:46:55","codigo":"ALI-ESC-02","nombre":"Escamas tropicales 1kg","unidad_medida":"kg","stock_minimo":"2.00","activo":1}}

**POST /alimentacion (sin stock)**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_tanque":1,"id_alimento":2,"id_empleado":1,"cantidad":0.05}
{"data":{"id_alimentacion":7,"id_tanque":1,"id_alimento":2,"id_empleado":1,"fecha_hora":"2026-10-07 21:46:55","cantidad":"0.05","observaciones":null,"tanque_nombre":"Tanque Marino 1","alimento_nombre":"Copépodos congelados","id_articulo":null,"articulo_codigo":null,"articulo_nombre":null,"articulo_unidad":null,"empleado_nombre":"Juan Pérez"}}

### 7) Eliminar alimentación → devuelve stock al inventario.

**DELETE /alimentacion/2**
   $ curl -X DELETE http://localhost:8080/5to_proyecto_tuburon/public/api/v1/alimentacion/2 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic


**GET /inventario/2 (stock devuelto)**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/inventario/2 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":{"id_inventario":6,"id_articulo":2,"existencia":"0.01","fecha_actualizacion":"2026-10-07 21:46:55","codigo":"ALI-ESC-02","nombre":"Escamas tropicales 1kg","unidad_medida":"kg","stock_minimo":"2.00","activo":1}}

---
## 14. PEZ–TANQUE
---

### POST /pez-tanque — Asignar pez a tanque. Un pez solo puede tener una asignación activa.

**POST /pez-tanque**
   $ curl -X POST http://localhost:8080/5to_proyecto_tuburon/public/api/v1/pez-tanque -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"id_pez":1,"id_tanque":1,"fecha_ingreso":"2026-10-07 10:00:00","observaciones":"Ingreso inicial"}
{"error":{"code":"CONFLICT","message":"El pez ya tiene una asignación activa. Ciérrala antes de crear otra.","details":{"asignacion_activa":{"id_pez":1,"id_tanque":1,"fecha_ingreso":"2026-10-07 10:00:00","fecha_salida":null,"observaciones":"Ingreso inicial"}}}}

### GET /pez-tanque — Filtros: id_pez, id_tanque, activo.

**GET /pez-tanque**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/pez-tanque -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_pez":1,"id_tanque":1,"fecha_ingreso":"2026-10-07 10:00:00","fecha_salida":null,"observaciones":"Ingreso inicial","pez_nombre":"Nemo","especie":"Pez payaso","tanque_nombre":"Tanque Marino 1"}],"meta":{"page":1,"limit":20,"total":1,"totalPages":1}}

**GET /pez-tanque?activo=true**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/pez-tanque?activo=true -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_pez":1,"id_tanque":1,"fecha_ingreso":"2026-10-07 10:00:00","fecha_salida":null,"observaciones":"Ingreso inicial","pez_nombre":"Nemo","especie":"Pez payaso","tanque_nombre":"Tanque Marino 1"}],"meta":{"page":1,"limit":20,"total":1,"totalPages":1}}

### GET /pez-tanque/historial/{idPez} — Historial completo de un pez.

**GET /pez-tanque/historial/1**
   $ curl http://localhost:8080/5to_proyecto_tuburon/public/api/v1/pez-tanque/historial/1 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic
{"data":[{"id_pez":1,"id_tanque":1,"fecha_ingreso":"2026-10-07 10:00:00","fecha_salida":null,"observaciones":"Ingreso inicial","tanque_nombre":"Tanque Marino 1"}]}

### PATCH /pez-tanque/{idPez}/{idTanque}/{fechaIngreso} — Cerrar asignación.

### fecha_salida debe ser > fecha_ingreso. URL-encode: %20 para espacio.

**PATCH /pez-tanque/1/1/2026-10-07%2010:00:00**
   $ curl -X PATCH http://localhost:8080/5to_proyecto_tuburon/public/api/v1/pez-tanque/1/1/2026-10-07%2010:00:00 -H Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJhY3VhcmlvLXRpYnVyb24tZmVsaXoiLCJhdWQiOiJwd2EiLCJpYXQiOjE3OTE0MzEyMTQsImV4cCI6MTc5MTQzNDgxNCwic3ViIjoxLCJyb2wiOiJBRE1JTiIsImlkX2VtcGxlYWRvIjoxLCJub21icmVfdXN1YXJpbyI6ImFkbWluIn0.r9c5UuLh7iPK3iUbcrqZGls6IauROttRhQpfra7Dpic -H Content-Type: application/json -d {"fecha_salida":"2026-10-08 12:00:00","observaciones":"Traslado a cuarentena"}
{"error":{"code":"NOT_FOUND","message":"Asignación no encontrada","details":[]}}

---
  FIN DE LA PRUEBA
---
Todas las rutas del contrato han sido probadas.
