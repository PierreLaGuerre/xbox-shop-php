# Release checklist

- [ ] Rotate the old local database password and check that it does not appear in Git history.
- [ ] Import `schema.sql` and `seed.sql` into a clean database.
- [ ] Run `composer lint` and `composer test`.
- [ ] Test catalogue, search, purchase flow, login, logout and CRUD.
- [ ] Test `/api/productos` and EAN search with 200, 404 and 422 responses.
- [ ] Review mobile layout at 360 px, desktop layout, keyboard navigation and reduced motion.
- [ ] Run Lighthouse and fix important accessibility issues.
- [ ] Create screenshots without credentials or private information.
- [ ] Deploy with `APP_DEBUG=false` and `SESSION_SECURE=true`.
- [ ] Configure the daily reset and test it manually.
- [ ] Add repository description, demo URL and topics.
- [ ] Pin the repository from “Customize your pins”.
