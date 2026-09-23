This is the restructured README, cleanly split into **Part 1 (The PHP Website & Database)** first, followed completely by **Part 2 (The Node.js WhatsApp Bot)**.

```markdown
# PHP & Node.js WhatsApp Message Custom API Gateway

A robust, self-hosted integration built using the **Baileys library** that bridges a PHP web interface with a Node.js WhatsApp bot[cite: 3, 5, 6, 7]. It allows you to queue messages through a website dashboard, have a Node.js bot poll for pending messages[cite: 3], send them via WhatsApp, and automatically sync their delivery status back to the database[cite: 5].

---

## 💡 Use Cases & Why You Need This
* **Custom API Key Triggering:** Send automated messages programmatically from external websites or apps using your own API keys.
* **New User Notifications:** Instantly alert yourself or your admins via WhatsApp whenever a new user registers or signs up on your platform.
* **Personal Site Notifications:** Receive real-time system alerts, error logs, or form submissions straight to your personal WhatsApp number.

---

## 📂 Project Structure

```text
├── php-backend/
│   ├── db.php                 # MySQL database connection[cite: 2]
│   ├── index.php              # Web dashboard to queue & view messages[cite: 4]
│   ├── get-pending.php        # API endpoint to fetch pending messages[cite: 3]
│   └── update-status.php      # API endpoint to update message status[cite: 5]
│
└── node-bot/
    ├── index.js               # WhatsApp bot core polling script (Baileys powered)[cite: 6]
    ├── package.json           # Node dependencies configuration[cite: 7]
    └── auth_info_baileys/     # WhatsApp session folder (generated after scanning QR)[cite: 6]

```

---

## Part 1: The PHP Website & Database

1. **1. Create the Database:** MySQL Database Wizard.
Log in to your cPanel, create a new MySQL database and user, and run the following SQL schema to create the message queue table:

```sql
CREATE TABLE `whatsapp_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

```


2. **2. Configure Database Credentials:** db.php.
Open `db.php` and update it with your live cPanel database credentials:

```php
$host = 'localhost';
$username = 'your_database_username';
$password = 'your_database_password';
$database = 'your_database_name';

```


3. **3. Upload PHP Files:** File Manager.
Upload `db.php`, `index.php`, `get-pending.php`, and `update-status.php` to your main domain or folder in cPanel.

* **Verification:** Visit `https://yourwebsite.com/index.php` in your browser. You should see the dashboard load, allowing you to add and track messages in the database queue.




---

## Part 2: The Node.js WhatsApp Bot (cPanel)

1. **1. Configure Your Website URL:** index.js.
Open the bot's `index.js` file and update the `WEBSITE_URL` constant to point directly to your live PHP website domain/folder:

```javascript
const WEBSITE_URL = '[https://yourwebsite.com](https://yourwebsite.com)'; // Replace with your PHP website URL

```


2. **2. Run Locally & Scan QR Code (Recommended):** Local Machine.
To bypass terminal session restrictions on cPanel:

1. Run `npm install` on your personal local machine to install dependencies (`@whiskeysockets/baileys`, `node-fetch`, `pino`, `qrcode-terminal`).


2. Run `node index.js` locally.


3. Scan the generated terminal QR code with your mobile WhatsApp app (**Linked Devices** -> **Link a Device**).


4. Once connected, an `auth_info_baileys` session folder will be created automatically on your computer.


5. Zip the entire bot folder (including `node_modules` and `auth_info_baileys`).




3. **3. Deploy on cPanel:** cPanel Subdomain.
1. Create a dedicated **Subdomain** in cPanel for your bot (e.g., `bot.yourwebsite.com`).
2. Go to **Setup Node.js App** in cPanel and click **Create Application**.
3. Select your Node.js version (v18+), set application mode to **Production**, link it to your subdomain directory, and set the startup file to `index.js`.
4. Upload and extract your zipped bot folder directly into that subdomain directory.
5. Click **Restart** in the cPanel Node.js application manager. Your Baileys-powered bot will now run continuously, fetch pending messages from your PHP backend, send them, and update statuses back automatically!




---

## 🔌 API Endpoints Reference

* **Fetch Pending Messages:** `GET https://yourwebsite.com/get-pending.php`

* **Update Message Status:** `POST https://yourwebsite.com/update-status.php`

* *Payload Example:* `{"id": 1, "status": "sent"}`



---


📝 License
This project is open-source and available under the MIT License.



```
