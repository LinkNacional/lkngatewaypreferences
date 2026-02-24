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
