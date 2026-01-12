# LicenseForge

[![SWUbanner](https://raw.githubusercontent.com/vshymanskyy/StandWithUkraine/main/banner2-direct.svg)](https://supportukrainenow.org/)

LicenseForge is a **self-hosted licensing and e-commerce platform** for selling digital products. It allows you to sell software licenses using one-time payments or subscriptions, while keeping full control over subscriptions, billing logic, and license management internally.

Payments are processed via Stripe or PayPal, but **all subscription logic is handled internally by LicenseForge**.

## 🚀 Features

- **Self-hosted** – full control over your data and infrastructure
- **Digital product licensing system**
- **One-time payments & subscriptions**
- **Stripe & PayPal payment processing**
- **Internal subscription management**
- **Automatic service suspension** for unpaid invoices
- **Admin and user dashboards**
- **Optional domain lock per license**
- **Cron-based automation**
- **Secure web installer with automatic lock after setup**

## 💳 Payment Providers

LicenseForge supports the following payment providers **for payment processing only**:
- **Stripe**
- **PayPal**

> Subscriptions are **not managed by Stripe or PayPal**.  
> LicenseForge handles subscription status and billing internally.

## 🔒 License Management

- Generate and manage license keys
- Assign licenses to customers and products
- Optional **domain lock per license**
  - Only the **main domain** needs to be specified
  - The license validation API automatically includes the `www.` variant
- Suspend licenses automatically when invoices are unpaid
- Manual license control via the admin dashboard

## 🧾 Billing & Subscriptions

- Internal subscription system
- Automated invoice generation
- Payment status tracking
- Automatic service suspension for unpaid invoices

> ⚠️ Grace periods and automatic reactivation are **not supported**.

## ⏱️ Cronjob (Required)

LicenseForge relies on a **cronjob** to handle recurring tasks such as:
- Checking subscription status
- Generating invoices
- Suspending services for unpaid invoices

⚠️ **The cronjob must be configured manually.**  
The required cron command is displayed in the **admin dashboard**.

## 🛠️ Tech Stack

- **Backend:** PHP
- **Frontend:** HTML, JavaScript
- **Database:** MySQL
- **Webhooks:** Not available at this time

## 📦 Installation

1. Upload all LicenseForge files to your server
2. Create a MySQL database
3. **Create the downloads directory (required)**
   In the **root folder** of the LicenseForge installation, create a directory named:

   ```
   downloads
   ```
4. **Upload your digital products**

   * Upload all sellable product files to the `downloads` directory
   * **The filename must exactly match the product name**
   * Example:
     * Product name: `LicenseForge`
     * File name: `LicenseForge.zip`

   **Supported file extensions (no code changes required):**
   * `zip`
   * `tar`
   * `tar.gz`
   * `rar`
   * `7z`

5. Open your browser and navigate to:
   ```
   https://yourdomain.tld/installer
   ```
6. Follow the on-screen steps to complete the installation

✅ After a successful installation:
* The installer **automatically disables itself**
* Accessing `/installer` will return a **404 page**
* All configuration options you entered during installation are saved in **config.php**
* You can **edit `config.php` manually later** to change database settings, mail settings, or other settings

> ⚠️ **Note:**
> Before running the installer, accessing the application may result in a default
> **500 Internal Server Error**. This is expected behavior.

## ⚠️ Error Handling During Installation

- Before installation, LicenseForge returns a **default 500 Internal Server Error**
- No custom 500 error page is shown at this stage
- After completing the web installer, a **custom 500 error page** is enabled
- The reason why the custom error page is not used before installation is currently under investigation

## 📊 Dashboards

- **Admin Dashboard:**  
```
https://yourdomain.tld/dashboard
```

- **User Dashboard:**  
```
https://yourdomain.tld/dashboard/me
```

## ⚙️ Configuration

Configuration options include:
- Database connection
- Stripe and/or PayPal API keys
- Cronjob setup
- Product and license settings

Most configuration can be managed via the **admin dashboard** after installation.

## 📚 Documentation

Additional documentation will be added in future releases.

## 🤝 Contributing

Contributions are welcome.

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Open a pull request

## 📄 License

LicenseForge is released under the **MIT License**.  
See the `LICENSE` file for more information.

**LicenseForge**  
A self-hosted platform for selling and managing digital product licenses.
