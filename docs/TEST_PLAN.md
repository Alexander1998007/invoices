# Suggested implementation order

1. Backend migration + Invoice model.
2. Form Requests for create/update validation.
3. Invoice API controller and routes.
4. Server-side gross amount calculation and pending-only update rule.
5. Frontend API composable.
6. Invoice list page.
7. Invoice details page.
8. vee-validate + Zod edit form.
9. Loading/error/empty states.
10. README answers and final manual API/UI checks.

Keep money values as decimal values and never trust `gross_amount` calculated by the browser.
