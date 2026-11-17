# WHMCS Gateway Preferences & Order Automation

An advanced WHMCS addon module designed to optimize payment gateway routing, manage fraudulent orders, and automate the cleanup of pending orders. 

This module provides administrators with granular control over which payment methods are available to specific clients, while also introducing powerful automation to handle abandoned or high-risk orders without manual intervention.

## ✨ Key Features

* **Dynamic Gateway Allocation:** Restrict or assign specific payment gateways to clients or invoices based on custom rules and country-specific configurations.
* **Smart Fraud Handling:** Automatically intercepts orders flagged as `Fraud` and converts them to `Pending`. This allows you to offer alternative payment methods or manually review the order instead of losing the sale.
* **Automated Order Cancellation (Auto-Cancel):** Keeps your WHMCS clean by automatically canceling `Pending` orders after a customizable number of days. It runs safely alongside the WHMCS Daily Cron Job.
* **Zero-Amount Bypass:** Option to skip free orders (Amount = 0.00) from fraud conversion and auto-cancellation routines.
* **Automated Client Notes:** Automatically adds sticky notes to a client's profile whenever the module intervenes and alters an order's status.
* **Extensive Logging:** Built-in debug logging system to track every action the module takes during cron executions.

## ⚙️ Configuration Options

Once installed, you can manage the module behavior directly from the WHMCS Addon Modules configuration page:

* **Enable Fraud Status Change:** Toggles the automatic conversion of Fraud orders to Pending.
* **Fraud Cron Hook Selection:** Choose exactly when the fraud routines should run (`AfterCronJob`, `FraudOrder`, or `AfterFraudCheck`).
* **Enable Auto-Cancel:** Toggles the automatic cleanup of old pending orders.
* **Auto-Cancel Days:** Define the maximum age (in days) a pending order can reach before being canceled.
* **Cancel Paid Orders:** Define whether the auto-cancel should ignore orders that already have paid invoices attached.
* **Enable Client Notes:** Toggles the automatic creation of audit notes on the client's profile.

## 🪝 WHMCS Hooks Used

This module integrates deeply with WHMCS using the following native hooks:
* `DailyCronJob` & `AfterCronJob` (For auto-cancellation and fraud sweeps)
* `FraudOrder` & `AfterFraudCheck` (For real-time fraud handling)
* `InvoiceCreation` & `ClientAreaPageViewInvoice` (For dynamic gateway assignment)
* `ShoppingCartCheckoutOutput` (To filter available gateways during checkout)

## 🛠️ System Requirements
* **WHMCS:** Version 8.x or higher
* **PHP:** Version 7.4 / 8.1+ (Fully compatible with strict typing)

---
*Developed and maintained for seamless WHMCS automation.*

## Endpoint público: gateways por país

O checkout do tema pode consultar quais gateways estão permitidos para um país,
inclusive para visitantes (sem cliente logado).

- **Arquivo:** `modules/addons/lkngatewaypreferences/public.php`
- **Ação:** `gateways-by-country`
- **Método:** `POST` com JSON (requisição same-origin)
- **Corpo:** `{"a":"gateways-by-country","country":"BR"}`
- **Resposta de sucesso:**
  ```json
  {"success":true,"data":{"gateways":["paghiper","lknbbpix","lknc_cielo_credit_card_token","lkncielo3ds"]}}
  ```
- **Fallback:** país sem preferência → preferência global (`**`)
- **Erros:**
  - `400 {"success":false,"error":"invalid_action"}`
  - `400 {"success":false,"error":"invalid_country"}`
  - `403 {"success":false,"error":"forbidden"}` (requisição não same-origin)
  - `429 {"success":false,"error":"rate_limited"}` (30 req/min por IP)
  - `500 {"success":false,"error":"unavailable"}`

A lista usa `PrefByCountryRepository::getOne()` e `getGlobalPref()` — a mesma
fonte usada pelo painel de preferências, sem regra duplicada.

### Validação no servidor

O hook `ShoppingCartValidateCheckout` bloqueia o checkout quando o
`paymentmethod` postado não está na lista permitida para o pedido, seguindo a
prioridade do módulo: **preferência do cliente → preferência do país →
preferência global**. A mensagem de erro é traduzida (en, pt-BR, pt-PT).

### Limitação conhecida de CSRF

Esta instalação do WHMCS não expõe um helper de validação de token para
endpoints de addon, então a proteção é **same-origin** (`Origin`/`Referer`/
`Sec-Fetch-Site`) somada ao rate limit. O endpoint é somente leitura e devolve
apenas códigos de gateway.
