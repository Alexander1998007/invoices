# Invoice Management System

A full-stack Invoice Management Application built with **Nuxt 4** and **Laravel 12**, fully dockerized for instant local setup.

---

## Technical Stack

* **Frontend:** Nuxt 4.5.2 (Vue 3.5.41), Tailwind CSS 4, Vee-Validate 4, Zod
* **Backend:** Laravel 12 (PHP 8.3)
* **Database:** MySQL 8.4
* **Environment:** Docker & Docker Compose
* **Node.js:** 24 LTS (Local host dependencies for IDE/TS support)

---

## System Requirements

* Docker Engine with Docker Compose v2+
* Node.js 24 LTS + `npm` *(required on host only for IDE TypeScript indexer)*

> **Note:** Host-side PHP, Composer, or MySQL are **not** required. All application runtimes are fully containerized.

---

## Quick Start

### PowerShell (Windows)

```powershell
.\scripts\setup.ps1
```

### Bash (macOS / Linux / Git Bash)

```powershell
./scripts/setup.sh
```

### Manual Run
```powershell
cd frontend
npm install
cd ..
docker compose up -d --build
```

---

## Application URLs

* Frontend: http://localhost:3000
* Backend API: http://localhost:8000
* Database Port: localhost:3307
---

## Answers to Technical Questions
**1. Як ти структурував frontend і backend?**

    Backend (Laravel 12):

    - REST API Endpoints: Визначено в routes/api.php через Route::apiResource('invoices', InvoiceController::class).
    - Form Requests: Валідація повністю винесена з контролерів. StoreInvoiceRequest перевіряє унікальність інвойсу та рівність сум ($Gross = Net + VAT$). UpdateInvoiceRequest гарантує, що редагування доступне тільки для записів зі статусом pending.
    - Business Logic: Автоматичний перерахунок gross_amount виконується на рівні сервера під час update() для захисту від маніпуляцій з клієнта.
    - Filtering & Sorting: GET /api/invoices підтримує серверний пошук (search) за номером/постачальником та сортування (sort_by, sort_order).

    Frontend (Nuxt 4):

    - File-based Routing: Організовано всередині директорії app/pages/:
        app/pages/invoices/index.vue: Список, серверний пошук, сортування та адаптивна клієнтська пагінація.
        app/pages/invoices/create.vue: Форма створення нового інвойсу з автоперерахунком gross_amount.
        app/pages/invoices/[id].vue: Детальний перегляд та редагування інвойсу.
    - Validation Layer: Синхронне зв'язування полів через defineField від vee-validate та суворі схеми типів через zod.

**2. Які компроміси ти зробив і чому?**  

    - Client-side vs Server-side Pagination: Для даного тестового завдання застосовано гібридний підхід. Backend підтримує фільтрацію та сортування, а пагінація відображення розбивається на фронтенді (по 10 елементів). Це забезпечує миттєвий відгук UI при невеликих обсягах даних.
    - Автоматичний підрахунок Gross Amount: Обчислюється динамічно на фронтенді в реальному часі для підвищення UX, але бекенд залишається єдиним джерелом правди й повторно обчислює значення перед збереженням у БД.
    - Відсутність авторизації (Auth): Модуль автентифікації свідомо пропущено для спрощення перевірки тестового завдання.

**3. Що б ти покращив у production-версії?**  

    - Повна серверна пагінація: Переведення пагінації на LengthAwarePaginator у Laravel (paginated() в InvoiceController) для ефективної роботи з мільйонами записів.
    - Індексація бази даних: Додавання складених індексів (Composite Indexes) у міграціях на (status, due_date) та (number, supplier_name) для прискорення запитів пошуку.
    - Автентифікація та RBAC: Впровадження Laravel Sanitizer / Passport та роли для обмеження прав редагування інвойсів.
    - Кешування: Кешування списків та агрегованої статистики за допомогою Redis.

**4. Які UX edge cases ти врахував?**

    - Захист від випадкового редагування: Якщо статус інвойсу відрізняється від pending (approved, rejected), вся форма редагування блокується через елемент <fieldset :disabled>.
    - Обробка due_date: null: Інтерфейс безпечно форматує порожні дати (виводить —), запобігаючи JS-помилкам видачі Invalid Date.
    - Валідація хронології дат: due_date не може бути раніше за issue_date (контролюється як на Zod-рівні фронтенду, так і правил під час POST/PUT запитів бекенду).
    - Адаптивність пагінатора: Блок пагінації показується тільки тоді, коли кількість записів перевищує 10. На мобільних пристроях елементи зручно шикуються вертикально (flex-col), а на десктопі — у рядок.
