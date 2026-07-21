# LightCar — Car Sales Management System

BIT 2 – Internet and Web Development — Individual Assignment
College of Business Education (CBE)

## 1. Problem Background
Small car dealerships often track inventory and sales on paper or in
spreadsheets. This causes lost records, no central sales history, and no
easy way to see which cars are still available. LightCar is a small
web system that lets dealership staff log in, manage the car inventory,
record sales, and see a basic sales report — all in one place.

## 2. Objectives
- Let staff (Admin / Sales Agent) log in securely.
- Manage car inventory: add, view, search, edit, delete.
- Record a sale against an available car and mark it "Sold".
- Show a simple sales report (count + total revenue).
- Protect sensitive data using encryption and secure coding practices.

## 3. Requirements Analysis
**Functional**
- User registration & login (roles: Admin, Agent)
- CRUD on cars (create, read, update, delete)
- Search cars by make/model
- Record sales, auto-update car status
- Sales report (totals)

**Non-functional**
- Passwords hashed (bcrypt), never stored in plain text
- Sensitive row data encrypted at rest (AES-256-CBC)
- CSRF protection on all forms
- Input sanitized/validated before use

## 4. Database Design (3NF)
Three tables, each attribute depends only on its table's primary key —
no repeating groups, no transitive dependencies:

- **users** (user_id PK, username, password_hash, role)
- **cars** (car_id PK, encrypted_make, encrypted_model, encrypted_price, status)
- **sales** (sale_id PK, car_id FK→cars, user_id FK→users,
  encrypted_customer_name, encrypted_sale_price, sale_date)

`sales` links `cars` and `users` instead of duplicating their data —
this is what keeps the design in 3NF.

## 5. OOP Concepts Used
| Concept        | Where |
|----------------|-------|
| Abstraction    | `abstract class DatabaseModel` (db.php) — abstract `getAll()` |
| Inheritance    | `Auth`, `CarManager`, `SalesManager` extend `DatabaseModel` |
| Encapsulation  | `protected $db` in `DatabaseModel`; private encryption method/state in `SecurityHelper` |
| Polymorphism   | `interface Searchable` implemented by `CarManager::search()` |
| Constructors   | Every class initializes its DB connection / session in `__construct()` |

## 6. Encryption Approach & Key Management
- **Algorithm**: AES-256-CBC (`openssl_encrypt`/`openssl_decrypt`).
- **What is encrypted**: car make/model/price, sale customer name/price —
  i.e. business data at rest in `cars` and `sales`.
- **What is NOT encrypted**: `username`. It must stay searchable in plain
  SQL (`WHERE username = ?`) for login to work; AES-CBC with a random IV
  is non-deterministic, so an encrypted username could not be looked up
  directly. Passwords are protected instead by one-way bcrypt hashing
  (`password_hash`), which is the correct control for credentials.
- **IV handling**: a new random IV (`random_bytes`) is generated for
  *every* encryption call and stored together with the ciphertext
  (`iv + ciphertext`, base64-encoded). The original code reused one fixed
  IV for every value, which is insecure (identical plaintexts produced
  identical ciphertext) — this was fixed in `security.php`.
- **Key storage**: the key is read from the `ENCRYPTION_KEY` environment
  variable (falls back to a default only for local testing). On AWS, set
  this as a real environment variable on the instance / in the web
  server config — never commit the real key to Git.

## 7. Security Practices Implemented
- Passwords hashed with bcrypt (`password_hash` / `password_verify`)
- All database queries use PDO prepared statements (no raw SQL concatenation)
- CSRF tokens on every form, validated server-side before any write
- Input sanitized with `htmlspecialchars`/`strip_tags`, output escaped on display
- Sessions used for auth state; `session_unset()` + `session_destroy()` on logout
- DB credentials and encryption key loaded from environment variables, not hardcoded

## 8. Deployment (AWS)
1. Launch an EC2 instance (Ubuntu), install `apache2`, `php`, `php-mysql`, `mysql-server`.
2. Copy project files to `/var/www/html/`.
3. Import `schema.sql` into MySQL: `mysql -u root -p < schema.sql`.
4. Set environment variables for `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`,
   `ENCRYPTION_KEY` (e.g. in `/etc/apache2/envvars` or a systemd unit).
5. Open port 80 (and 443 if using HTTPS/Let's Encrypt) in the EC2 security group.
6. Visit the instance's public URL, register a user, and log in.

Default seeded login from `schema.sql`: **admin / admin123** — change
this password immediately after first deployment.

## 9. Screenshots
*(Insert screenshots here: login page, register page, dashboard, car
inventory list, add/edit car form, sales & report page.)*

## 10. Testing Evidence
*(Insert test cases here, e.g.: empty form submission → validation
error shown; wrong password → login rejected; SQL injection attempt in
search box → no effect due to prepared statements; encrypted DB columns
verified unreadable directly in MySQL; CSRF token missing → request
rejected.)*

## 11. Challenges Encountered
*(Add your own notes — e.g. fixing the IV-reuse encryption bug, adding
CSRF protection, deciding what data could/couldn't be encrypted.)*

## 12. Recommendations
- Add HTTPS (via AWS + Let's Encrypt) for production use.
- Add role-based access control (e.g. only Admin can register new users).
- Add pagination for large inventory/sales lists.
- Add rate-limiting on login to reduce brute-force risk.
