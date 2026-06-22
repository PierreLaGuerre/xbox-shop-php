# Checklist de publicación

- [ ] Rotar la contraseña antigua de la base local y comprobar que no aparece en el historial Git.
- [ ] Importar `schema.sql` y `seed.sql` en una base limpia.
- [ ] Ejecutar `composer lint` y `composer test`.
- [ ] Probar catálogo, búsqueda, compra, login, logout y CRUD.
- [ ] Probar `/api/productos` y búsqueda EAN con respuestas 200, 404 y 422.
- [ ] Revisar móvil (360 px), escritorio, teclado y reducción de movimiento.
- [ ] Ejecutar Lighthouse y corregir errores importantes de accesibilidad.
- [ ] Crear capturas sin credenciales ni información privada.
- [ ] Desplegar con `APP_DEBUG=false` y `SESSION_SECURE=true`.
- [ ] Configurar restauración diaria y probarla manualmente.
- [ ] Añadir descripción, URL y topics al repositorio.
- [ ] Fijar el repositorio desde “Customize your pins”.
